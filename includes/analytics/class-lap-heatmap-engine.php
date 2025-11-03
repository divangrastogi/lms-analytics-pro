<?php
/**
 * Heatmap data processing and generation
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Analytics
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LAP_Heatmap_Engine class.
 */
class LAP_Heatmap_Engine {

    /**
     * Database manager instance.
     *
     * @var LAP_DB_Manager
     */
    private $db;

    /**
     * Cache handler instance.
     *
     * @var LAP_Cache_Handler
     */
    private $cache;

    /**
     * Constructor.
     *
     * @param LAP_DB_Manager    $db    Database manager.
     * @param LAP_Cache_Handler $cache Cache handler.
     */
    public function __construct( LAP_DB_Manager $db, LAP_Cache_Handler $cache = null ) {
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * Generate heatmap data for given parameters.
     *
     * @param array $args {
     *     Optional. Arguments for filtering heatmap data.
     *
     *     @type int    $course_id   Course ID to filter by. Default 0 (all).
     *     @type int    $group_id    BuddyBoss group ID. Default 0 (all).
     *     @type string $date_from   Start date (Y-m-d format). Default 30 days ago.
     *     @type string $date_to     End date (Y-m-d format). Default today.
     *     @type int    $per_page    Students per page. Default 50.
     *     @type int    $page        Current page. Default 1.
     * }
     * @return array {
     *     Heatmap data structure.
     *
     *     @type array $students   Student rows with progress data.
     *     @type array $lessons    Lesson/topic columns.
     *     @type array $metadata   Summary statistics.
     * }
     */
    public function lap_generate_heatmap_data( $args = array() ) {
        $defaults = array(
            'course_id' => 0,
            'group_id'  => 0,
            'date_from' => date( 'Y-m-d', strtotime( '-30 days' ) ),
            'date_to'   => date( 'Y-m-d' ),
            'per_page'  => 50,
            'page'      => 1,
        );

        $args = wp_parse_args( $args, $defaults );

        // Check cache first
        $cache_key = 'heatmap_' . md5( wp_json_encode( $args ) );
        if ( $this->cache ) {
            $cached_data = $this->cache->lap_get( $cache_key, 'heatmap' );
            if ( false !== $cached_data ) {
                return $cached_data;
            }
        }

        // Get course structure
        $course_structure = $this->lap_get_course_structure( $args['course_id'] );
        if ( empty( $course_structure ) ) {
            return array(
                'students' => array(),
                'lessons'  => array(),
                'metadata' => array(
                    'total_students' => 0,
                    'total_lessons'  => 0,
                    'average_completion' => 0,
                ),
            );
        }

        // Get students
        $students = $this->lap_get_students_for_heatmap( $args );
        if ( empty( $students ) ) {
            return array(
                'students' => array(),
                'lessons'  => $course_structure,
                'metadata' => array(
                    'total_students' => 0,
                    'total_lessons'  => count( $course_structure ),
                    'average_completion' => 0,
                ),
            );
        }

        // Build progress data
        $heatmap_data = array();
        foreach ( $students as $student ) {
            $student_progress = $this->lap_get_student_progress_data( $student['user_id'], $args['course_id'], $course_structure );

            $heatmap_data[] = array(
                'user_id'           => $student['user_id'],
                'name'              => $student['display_name'],
                'email'             => $student['user_email'],
                'progress'          => $student_progress['progress'],
                'average_completion' => $student_progress['average_completion'],
                'last_activity'     => $student_progress['last_activity'],
                'risk_score'        => $student_progress['risk_score'],
            );
        }

        $result = array(
            'students' => $heatmap_data,
            'lessons'  => $course_structure,
            'metadata' => array(
                'total_students'     => count( $heatmap_data ),
                'total_lessons'      => count( $course_structure ),
                'average_completion' => $this->lap_calculate_average_completion( $heatmap_data ),
                'date_range'         => array(
                    'from' => $args['date_from'],
                    'to'   => $args['date_to'],
                ),
                'filters' => array(
                    'course_id' => $args['course_id'],
                    'group_id'  => $args['group_id'],
                ),
            ),
        );

        // Cache the result
        if ( $this->cache ) {
            $this->cache->lap_set( $cache_key, $result, 'heatmap', HOUR_IN_SECONDS );
        }

        return $result;
    }

    /**
     * Get course structure (lessons and topics).
     *
     * @param int $course_id Course ID.
     * @return array Course structure.
     */
    private function lap_get_course_structure( $course_id ) {
        if ( ! function_exists( 'learndash_get_course_lessons_list' ) ) {
            return array();
        }

        $lessons = learndash_get_course_lessons_list( $course_id );
        $structure = array();

        foreach ( $lessons as $lesson ) {
            $structure[] = array(
                'id'    => $lesson['id'],
                'title' => $lesson['post']->post_title,
                'type'  => 'lesson',
            );

            // Add topics if they exist
            $topics = learndash_get_lesson_topics_list( $lesson['id'] );
            if ( ! empty( $topics ) ) {
                foreach ( $topics as $topic ) {
                    $structure[] = array(
                        'id'    => $topic['id'],
                        'title' => '└─ ' . $topic['post']->post_title,
                        'type'  => 'topic',
                    );
                }
            }
        }

        return $structure;
    }

    /**
     * Get students for heatmap.
     *
     * @param array $args Query arguments.
     * @return array Students data.
     */
    private function lap_get_students_for_heatmap( $args ) {
        global $wpdb;

        $where = array( '1=1' );
        $params = array();

        // Filter by course if specified
        if ( $args['course_id'] > 0 ) {
            $where[] = 'um.meta_key = %s AND um.meta_value = %s';
            $params[] = '_sfwd-course_progress';
            $params[] = $args['course_id'];
        }

        // Filter by BuddyBoss group if specified
        if ( $args['group_id'] > 0 && function_exists( 'bp_is_active' ) ) {
            $group_members = groups_get_group_members( array( 'group_id' => $args['group_id'] ) );
            if ( ! empty( $group_members['members'] ) ) {
                $member_ids = wp_list_pluck( $group_members['members'], 'ID' );
                $placeholders = implode( ',', array_fill( 0, count( $member_ids ), '%d' ) );
                $where[] = "u.ID IN ({$placeholders})";
                $params = array_merge( $params, $member_ids );
            }
        }

        $where_clause = implode( ' AND ', $where );

        $sql = $wpdb->prepare(
            "SELECT DISTINCT u.ID, u.display_name, u.user_email
            FROM {$wpdb->users} u
            LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
            WHERE {$where_clause}
            ORDER BY u.display_name
            LIMIT %d OFFSET %d",
            array_merge( $params, array( $args['per_page'], ( $args['page'] - 1 ) * $args['per_page'] ) )
        );

        return $wpdb->get_results( $sql, ARRAY_A );
    }

    /**
     * Get student progress data for heatmap.
     *
     * @param int   $user_id         User ID.
     * @param int   $course_id       Course ID.
     * @param array $course_structure Course structure.
     * @return array Student progress data.
     */
    private function lap_get_student_progress_data( $user_id, $course_id, $course_structure ) {
        $progress = array();
        $total_completion = 0;
        $completed_items = 0;
        $last_activity = null;

        foreach ( $course_structure as $item ) {
            $completion = $this->lap_get_item_completion( $user_id, $item['id'], $item['type'] );
            $progress[ $item['id'] ] = array(
                'completion_percentage' => $completion['percentage'],
                'time_spent'           => $completion['time_spent'],
                'last_activity'        => $completion['last_activity'],
            );

            $total_completion += $completion['percentage'];
            $completed_items++;

            if ( $completion['last_activity'] && ( ! $last_activity || $completion['last_activity'] > $last_activity ) ) {
                $last_activity = $completion['last_activity'];
            }
        }

        $average_completion = $completed_items > 0 ? round( $total_completion / $completed_items, 2 ) : 0;

        return array(
            'progress'           => $progress,
            'average_completion' => $average_completion,
            'last_activity'      => $last_activity,
            'risk_score'         => $this->lap_calculate_risk_score( $user_id, $course_id ),
        );
    }

    /**
     * Get completion data for a specific item.
     *
     * @param int    $user_id   User ID.
     * @param int    $item_id   Item ID (lesson or topic).
     * @param string $item_type Item type (lesson or topic).
     * @return array Completion data.
     */
    private function lap_get_item_completion( $user_id, $item_id, $item_type ) {
        global $wpdb;

        $table = $this->db->get_table( 'student_progress' );

        $result = $wpdb->get_row( $wpdb->prepare(
            "SELECT completion_percentage, time_spent_seconds, last_activity
            FROM {$table}
            WHERE user_id = %d AND lesson_id = %d AND topic_id = %d",
            $user_id,
            'lesson' === $item_type ? $item_id : 0,
            'topic' === $item_type ? $item_id : 0
        ) );

        if ( $result ) {
            return array(
                'percentage'   => (float) $result->completion_percentage,
                'time_spent'   => (int) $result->time_spent_seconds,
                'last_activity' => $result->last_activity,
            );
        }

        // Check LearnDash directly if no custom data
        if ( function_exists( 'learndash_get_user_activity' ) ) {
            $activity = learndash_get_user_activity( array(
                'user_id'     => $user_id,
                'post_id'     => $item_id,
                'activity_type' => 'lesson' === $item_type ? 'lesson' : 'topic',
            ) );

            if ( ! empty( $activity ) ) {
                $percentage = 0;
                if ( isset( $activity[0]->activity_completed ) && $activity[0]->activity_completed > 0 ) {
                    $percentage = 100;
                }

                return array(
                    'percentage'   => $percentage,
                    'time_spent'   => 0,
                    'last_activity' => $activity[0]->activity_updated ?? null,
                );
            }
        }

        return array(
            'percentage'   => 0,
            'time_spent'   => 0,
            'last_activity' => null,
        );
    }

    /**
     * Calculate average completion across all students.
     *
     * @param array $students Students data.
     * @return float Average completion percentage.
     */
    private function lap_calculate_average_completion( $students ) {
        if ( empty( $students ) ) {
            return 0;
        }

        $total = 0;
        foreach ( $students as $student ) {
            $total += $student['average_completion'];
        }

        return round( $total / count( $students ), 2 );
    }

    /**
     * Calculate risk score for a student (placeholder).
     *
     * @param int $user_id   User ID.
     * @param int $course_id Course ID.
     * @return int Risk score.
     */
    private function lap_calculate_risk_score( $user_id, $course_id ) {
        // Placeholder - will be implemented in risk scorer class
        return rand( 0, 100 );
    }
}