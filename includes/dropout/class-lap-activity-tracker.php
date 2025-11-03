<?php
/**
 * Activity tracking for dropout detection
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Dropout
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LAP_Activity_Tracker class.
 */
class LAP_Activity_Tracker {

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
     * @param LAP_DB_Manager $db Database manager.
     */
    public function __construct( LAP_DB_Manager $db ) {
        $this->db = $db;
    }

    /**
     * Track lesson completion activity.
     *
     * @param array $data Completion data from LearnDash.
     */
    public function lap_track_lesson_completion( $data ) {
        if ( ! isset( $data['user']->ID ) || ! isset( $data['lesson']->ID ) ) {
            return;
        }

        $user_id = $data['user']->ID;
        $lesson_id = $data['lesson']->ID;
        $course_id = $data['course']->ID ?? 0;

        // Update or insert progress record
        $this->db->insert_student_progress( array(
            'user_id'               => $user_id,
            'course_id'             => $course_id,
            'lesson_id'             => $lesson_id,
            'completion_status'     => 2, // completed
            'completion_percentage' => 100.00,
            'last_activity'         => current_time( 'mysql' ),
        ) );

        // Log activity
        $this->db->log_activity( array(
            'user_id'          => $user_id,
            'activity_type'    => 'lesson_complete',
            'course_id'        => $course_id,
            'lesson_id'        => $lesson_id,
            'metadata'         => array(
                'lesson_title' => $data['lesson']->post_title ?? '',
                'course_title' => $data['course']->post_title ?? '',
            ),
        ) );

        // Trigger risk recalculation
        do_action( 'lap_recalculate_risk_score', $user_id, $course_id );
    }

    /**
     * Track lesson access/view activity.
     *
     * @param int $user_id   User ID.
     * @param int $lesson_id Lesson ID.
     * @param int $course_id Course ID.
     */
    public function lap_track_lesson_view( $user_id, $lesson_id, $course_id ) {
        // Update progress record with view activity
        $this->db->insert_student_progress( array(
            'user_id'               => $user_id,
            'course_id'             => $course_id,
            'lesson_id'             => $lesson_id,
            'completion_status'     => 1, // in_progress
            'completion_percentage' => 0, // Will be calculated separately
            'last_activity'         => current_time( 'mysql' ),
        ) );

        // Log activity
        $this->db->log_activity( array(
            'user_id'       => $user_id,
            'activity_type' => 'lesson_view',
            'course_id'     => $course_id,
            'lesson_id'     => $lesson_id,
        ) );
    }

    /**
     * Track quiz attempt activity.
     *
     * @param int   $user_id   User ID.
     * @param int   $quiz_id   Quiz ID.
     * @param int   $course_id Course ID.
     * @param float $score     Quiz score.
     */
    public function lap_track_quiz_attempt( $user_id, $quiz_id, $course_id, $score ) {
        // Log activity
        $this->db->log_activity( array(
            'user_id'       => $user_id,
            'activity_type' => 'quiz_attempt',
            'course_id'     => $course_id,
            'lesson_id'     => $quiz_id, // Using lesson_id field for quiz_id
            'metadata'      => array(
                'score' => $score,
            ),
        ) );
    }

    /**
     * Track topic access activity.
     *
     * @param int $user_id   User ID.
     * @param int $topic_id  Topic ID.
     * @param int $lesson_id Lesson ID.
     * @param int $course_id Course ID.
     */
    public function lap_track_topic_view( $user_id, $topic_id, $lesson_id, $course_id ) {
        // Update progress record
        $this->db->insert_student_progress( array(
            'user_id'               => $user_id,
            'course_id'             => $course_id,
            'lesson_id'             => $lesson_id,
            'topic_id'              => $topic_id,
            'completion_status'     => 1, // in_progress
            'completion_percentage' => 0,
            'last_activity'         => current_time( 'mysql' ),
        ) );

        // Log activity
        $this->db->log_activity( array(
            'user_id'       => $user_id,
            'activity_type' => 'topic_view',
            'course_id'     => $course_id,
            'lesson_id'     => $topic_id,
        ) );
    }

    /**
     * Track login activity.
     *
     * @param int $user_id User ID.
     */
    public function lap_track_user_login( $user_id ) {
        // Update user meta with last login
        update_user_meta( $user_id, 'last_login', current_time( 'mysql' ) );

        // Log activity
        $this->db->log_activity( array(
            'user_id'       => $user_id,
            'activity_type' => 'login',
        ) );
    }

    /**
     * Track BuddyBoss activity.
     *
     * @param int   $user_id   User ID.
     * @param string $activity_type Activity type.
     * @param array  $metadata  Additional metadata.
     */
    public function lap_track_buddyboss_activity( $user_id, $activity_type, $metadata = array() ) {
        if ( ! function_exists( 'bp_is_active' ) ) {
            return;
        }

        $this->db->log_activity( array(
            'user_id'       => $user_id,
            'activity_type' => 'buddyboss_' . $activity_type,
            'metadata'      => $metadata,
        ) );
    }

    /**
     * Get user activity summary for a date range.
     *
     * @param int    $user_id   User ID.
     * @param string $start_date Start date (Y-m-d).
     * @param string $end_date   End date (Y-m-d).
     * @return array Activity summary.
     */
    public function lap_get_user_activity_summary( $user_id, $start_date, $end_date ) {
        global $wpdb;

        $table = $this->db->get_table( 'activity_log' );

        $sql = $wpdb->prepare(
            "SELECT
                activity_type,
                COUNT(*) as count,
                DATE(activity_timestamp) as date
            FROM {$table}
            WHERE user_id = %d
            AND DATE(activity_timestamp) BETWEEN %s AND %s
            GROUP BY activity_type, DATE(activity_timestamp)
            ORDER BY date DESC, activity_type",
            $user_id,
            $start_date,
            $end_date
        );

        $results = $wpdb->get_results( $sql, ARRAY_A );

        $summary = array(
            'total_activities' => 0,
            'by_type'          => array(),
            'by_date'          => array(),
            'date_range'       => array(
                'start' => $start_date,
                'end'   => $end_date,
            ),
        );

        foreach ( $results as $result ) {
            $summary['total_activities'] += $result['count'];

            // Group by type
            if ( ! isset( $summary['by_type'][ $result['activity_type'] ] ) ) {
                $summary['by_type'][ $result['activity_type'] ] = 0;
            }
            $summary['by_type'][ $result['activity_type'] ] += $result['count'];

            // Group by date
            if ( ! isset( $summary['by_date'][ $result['date'] ] ) ) {
                $summary['by_date'][ $result['date'] ] = array();
            }
            if ( ! isset( $summary['by_date'][ $result['date'] ][ $result['activity_type'] ] ) ) {
                $summary['by_date'][ $result['date'] ][ $result['activity_type'] ] = 0;
            }
            $summary['by_date'][ $result['date'] ][ $result['activity_type'] ] += $result['count'];
        }

        // Cache the result
        $this->cache->lap_set_cached_activity_summary( $user_id, $start_date, $end_date, $summary );

        return $summary;
    }

    /**
     * Get inactive users based on activity threshold.
     *
     * @param int $days_inactive Days without activity.
     * @param int $course_id     Optional course ID filter.
     * @return array Inactive users.
     */
    public function lap_get_inactive_users( $days_inactive = 7, $course_id = 0 ) {
        global $wpdb;

        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days_inactive} days" ) );

        $table = $this->db->get_table( 'student_progress' );
        $users_table = $wpdb->users;

        $where = array( '1=1' );
        $params = array();

        if ( $course_id > 0 ) {
            $where[] = 'sp.course_id = %d';
            $params[] = $course_id;
        }

        $where_clause = implode( ' AND ', $where );

        $sql = $wpdb->prepare(
            "SELECT DISTINCT
                u.ID as user_id,
                u.display_name,
                u.user_email,
                MAX(sp.last_activity) as last_activity
            FROM {$users_table} u
            LEFT JOIN {$table} sp ON u.ID = sp.user_id
            WHERE {$where_clause}
            GROUP BY u.ID, u.display_name, u.user_email
            HAVING last_activity IS NULL OR last_activity < %s
            ORDER BY last_activity ASC",
            array_merge( $params, array( $cutoff_date ) )
        );

        return $wpdb->get_results( $sql, ARRAY_A );
    }

    /**
     * Calculate user engagement score based on activity patterns.
     *
     * @param int $user_id   User ID.
     * @param int $course_id Course ID.
     * @param int $days      Number of days to analyze.
     * @return float Engagement score (0-100).
     */
    public function lap_calculate_engagement_score( $user_id, $course_id, $days = 30 ) {
        $start_date = date( 'Y-m-d', strtotime( "-{$days} days" ) );
        $end_date = date( 'Y-m-d' );

        $activity_summary = $this->lap_get_user_activity_summary( $user_id, $start_date, $end_date );

        if ( $activity_summary['total_activities'] === 0 ) {
            return 0;
        }

        // Calculate engagement based on activity frequency and diversity
        $active_days = count( $activity_summary['by_date'] );
        $activity_types = count( $activity_summary['by_type'] );
        $avg_daily_activity = $activity_summary['total_activities'] / $days;

        // Weights for different factors
        $frequency_score = min( 40, $avg_daily_activity * 10 ); // Max 40 points for frequency
        $consistency_score = min( 30, ( $active_days / $days ) * 30 ); // Max 30 points for consistency
        $diversity_score = min( 30, $activity_types * 10 ); // Max 30 points for activity diversity

        return round( $frequency_score + $consistency_score + $diversity_score );
    }

    /**
     * Clean up old activity logs.
     *
     * @param int $days_old Days to keep.
     * @return int Number of records deleted.
     */
    public function lap_cleanup_old_activity_logs( $days_old = 365 ) {
        global $wpdb;

        $table = $this->db->get_table( 'activity_log' );
        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days_old} days" ) );

        return $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$table} WHERE activity_timestamp < %s",
            $cutoff_date
        ) );
    }

    /**
     * Get activity statistics for reporting.
     *
     * @param int    $course_id Course ID.
     * @param string $period    Time period (daily, weekly, monthly).
     * @param int    $limit     Number of periods to return.
     * @return array Activity statistics.
     */
    public function lap_get_activity_statistics( $course_id, $period = 'daily', $limit = 30 ) {
        global $wpdb;

        $table = $this->db->get_table( 'activity_log' );

        $date_format = 'daily' === $period ? '%Y-%m-%d' : '%Y-%u';

        $sql = $wpdb->prepare(
            "SELECT
                DATE_FORMAT(activity_timestamp, %s) as period,
                activity_type,
                COUNT(*) as count
            FROM {$table}
            WHERE course_id = %d
            GROUP BY period, activity_type
            ORDER BY period DESC
            LIMIT %d",
            $date_format,
            $course_id,
            $limit * 10 // Multiply by 10 to account for different activity types
        );

        $results = $wpdb->get_results( $sql, ARRAY_A );

        $statistics = array();
        foreach ( $results as $result ) {
            if ( ! isset( $statistics[ $result['period'] ] ) ) {
                $statistics[ $result['period'] ] = array(
                    'period' => $result['period'],
                    'total'  => 0,
                    'by_type' => array(),
                );
            }

            $statistics[ $result['period'] ]['total'] += $result['count'];
            $statistics[ $result['period'] ]['by_type'][ $result['activity_type'] ] = $result['count'];
        }

        // Sort by period and limit results
        krsort( $statistics );
        return array_slice( array_values( $statistics ), 0, $limit );
    }
}