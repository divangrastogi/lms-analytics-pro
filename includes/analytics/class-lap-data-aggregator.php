<?php
/**
 * Data collection and aggregation utilities
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Analytics
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LAP_Data_Aggregator class.
 */
class LAP_Data_Aggregator {

    /**
     * Database manager instance.
     *
     * @var LAP_DB_Manager
     */
    private $db;

    /**
     * Progress calculator instance.
     *
     * @var LAP_Progress_Calculator
     */
    private $calculator;

    /**
     * Constructor.
     *
     * @param LAP_DB_Manager         $db         Database manager.
     * @param LAP_Progress_Calculator $calculator Progress calculator.
     */
    public function __construct( LAP_DB_Manager $db, LAP_Progress_Calculator $calculator ) {
        $this->db         = $db;
        $this->calculator = $calculator;
    }

    /**
     * Aggregate student progress data for a course.
     *
     * @param int $course_id Course ID.
     * @param array $args    Additional arguments.
     * @return array Aggregated data.
     */
    public function lap_aggregate_course_progress( $course_id, $args = array() ) {
        $defaults = array(
            'date_from' => date( 'Y-m-d', strtotime( '-30 days' ) ),
            'date_to'   => date( 'Y-m-d' ),
            'group_id'  => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $students = $this->lap_get_course_students( $course_id, $args );
        $progress_data = array();

        foreach ( $students as $student ) {
            $progress_data[] = array(
                'user_id'            => $student['ID'],
                'name'               => $student['display_name'],
                'email'              => $student['user_email'],
                'completion_percentage' => $this->calculator->lap_calculate_completion_percentage( $student['ID'], $course_id ),
                'engagement_score'   => $this->calculator->lap_calculate_engagement_score( $student['ID'], $course_id ),
                'last_activity'      => $this->lap_get_last_activity( $student['ID'], $course_id ),
                'time_spent'         => $this->lap_get_total_time_spent( $student['ID'], $course_id ),
                'quiz_performance'   => $this->calculator->lap_calculate_quiz_performance( $student['ID'], $course_id ),
            );
        }

        return array(
            'course_id'     => $course_id,
            'course_name'   => get_the_title( $course_id ),
            'students'      => $progress_data,
            'summary'       => $this->lap_calculate_course_summary( $progress_data ),
            'date_range'    => array(
                'from' => $args['date_from'],
                'to'   => $args['date_to'],
            ),
        );
    }

    /**
     * Get students enrolled in a course.
     *
     * @param int   $course_id Course ID.
     * @param array $args      Query arguments.
     * @return array Students data.
     */
    private function lap_get_course_students( $course_id, $args ) {
        if ( ! function_exists( 'learndash_get_users_for_course' ) ) {
            return array();
        }

        $course_users = learndash_get_users_for_course( $course_id, array( 'fields' => 'all' ) );
        if ( empty( $course_users ) ) {
            return array();
        }

        $students = array();
        foreach ( $course_users as $user ) {
            // Apply group filter if specified
            if ( $args['group_id'] > 0 && function_exists( 'bp_is_active' ) ) {
                if ( ! groups_is_user_member( $user->ID, $args['group_id'] ) ) {
                    continue;
                }
            }

            $students[] = array(
                'ID'           => $user->ID,
                'display_name' => $user->display_name,
                'user_email'   => $user->user_email,
            );
        }

        return $students;
    }

    /**
     * Get last activity for a student in a course.
     *
     * @param int $user_id   Student user ID.
     * @param int $course_id Course ID.
     * @return string|null Last activity timestamp.
     */
    private function lap_get_last_activity( $user_id, $course_id ) {
        global $wpdb;

        $table = $this->db->get_table( 'student_progress' );

        return $wpdb->get_var( $wpdb->prepare(
            "SELECT last_activity
            FROM {$table}
            WHERE user_id = %d AND course_id = %d
            ORDER BY last_activity DESC
            LIMIT 1",
            $user_id,
            $course_id
        ) );
    }

    /**
     * Get total time spent by a student in a course.
     *
     * @param int $user_id   Student user ID.
     * @param int $course_id Course ID.
     * @return int Total time in seconds.
     */
    private function lap_get_total_time_spent( $user_id, $course_id ) {
        global $wpdb;

        $table = $this->db->get_table( 'student_progress' );

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(time_spent_seconds)
            FROM {$table}
            WHERE user_id = %d AND course_id = %d",
            $user_id,
            $course_id
        ) );
    }

    /**
     * Calculate course summary statistics.
     *
     * @param array $progress_data Student progress data.
     * @return array Summary statistics.
     */
    private function lap_calculate_course_summary( $progress_data ) {
        if ( empty( $progress_data ) ) {
            return array(
                'total_students'      => 0,
                'average_completion'  => 0,
                'average_engagement'  => 0,
                'completion_distribution' => array(),
                'engagement_distribution' => array(),
            );
        }

        $total_students = count( $progress_data );
        $total_completion = 0;
        $total_engagement = 0;
        $completion_ranges = array( '0-25' => 0, '26-50' => 0, '51-75' => 0, '76-100' => 0 );
        $engagement_ranges = array( '0-25' => 0, '26-50' => 0, '51-75' => 0, '76-100' => 0 );

        foreach ( $progress_data as $student ) {
            $completion = $student['completion_percentage'];
            $engagement = $student['engagement_score'];

            $total_completion += $completion;
            $total_engagement += $engagement;

            // Completion distribution
            if ( $completion <= 25 ) {
                $completion_ranges['0-25']++;
            } elseif ( $completion <= 50 ) {
                $completion_ranges['26-50']++;
            } elseif ( $completion <= 75 ) {
                $completion_ranges['51-75']++;
            } else {
                $completion_ranges['76-100']++;
            }

            // Engagement distribution
            if ( $engagement <= 25 ) {
                $engagement_ranges['0-25']++;
            } elseif ( $engagement <= 50 ) {
                $engagement_ranges['26-50']++;
            } elseif ( $engagement <= 75 ) {
                $engagement_ranges['51-75']++;
            } else {
                $engagement_ranges['76-100']++;
            }
        }

        return array(
            'total_students'      => $total_students,
            'average_completion'  => round( $total_completion / $total_students, 2 ),
            'average_engagement'  => round( $total_engagement / $total_students, 2 ),
            'completion_distribution' => $completion_ranges,
            'engagement_distribution' => $engagement_ranges,
        );
    }

    /**
     * Aggregate activity data for trend analysis.
     *
     * @param int    $course_id Course ID.
     * @param string $period    Time period (daily, weekly, monthly).
     * @param int    $days      Number of days to look back.
     * @return array Activity trends.
     */
    public function lap_aggregate_activity_trends( $course_id, $period = 'daily', $days = 30 ) {
        global $wpdb;

        $table = $this->db->get_table( 'activity_log' );
        $data = array();

        if ( 'daily' === $period ) {
            for ( $i = $days - 1; $i >= 0; $i-- ) {
                $date = date( 'Y-m-d', strtotime( "-{$i} days" ) );
                $count = $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*)
                    FROM {$table}
                    WHERE course_id = %d AND DATE(activity_timestamp) = %s",
                    $course_id,
                    $date
                ) );

                $data[] = array(
                    'date'  => $date,
                    'count' => (int) $count,
                );
            }
        }

        return array(
            'period' => $period,
            'data'   => $data,
            'summary' => array(
                'total_activities' => array_sum( wp_list_pluck( $data, 'count' ) ),
                'average_daily'    => round( array_sum( wp_list_pluck( $data, 'count' ) ) / $days, 2 ),
                'peak_day'         => $this->lap_find_peak_day( $data ),
            ),
        );
    }

    /**
     * Find the day with most activity.
     *
     * @param array $data Activity data.
     * @return array Peak day data.
     */
    private function lap_find_peak_day( $data ) {
        if ( empty( $data ) ) {
            return null;
        }

        $max_count = 0;
        $peak_day = null;

        foreach ( $data as $day ) {
            if ( $day['count'] > $max_count ) {
                $max_count = $day['count'];
                $peak_day = $day;
            }
        }

        return $peak_day;
    }

    /**
     * Aggregate quiz performance data.
     *
     * @param int $course_id Course ID.
     * @param array $args    Additional arguments.
     * @return array Quiz performance data.
     */
    public function lap_aggregate_quiz_performance( $course_id, $args = array() ) {
        $defaults = array(
            'date_from' => date( 'Y-m-d', strtotime( '-30 days' ) ),
            'date_to'   => date( 'Y-m-d' ),
        );

        $args = wp_parse_args( $args, $defaults );

        if ( ! function_exists( 'learndash_get_course_quiz_list' ) ) {
            return array(
                'quizzes' => array(),
                'summary' => array(
                    'total_quizzes' => 0,
                    'average_score' => 0,
                    'pass_rate'     => 0,
                ),
            );
        }

        $quizzes = learndash_get_course_quiz_list( $course_id );
        $quiz_data = array();

        foreach ( $quizzes as $quiz ) {
            $quiz_stats = $this->lap_get_quiz_statistics( $quiz['id'], $args );
            $quiz_data[] = array(
                'quiz_id'   => $quiz['id'],
                'title'     => $quiz['post']->post_title,
                'statistics' => $quiz_stats,
            );
        }

        return array(
            'quizzes' => $quiz_data,
            'summary' => $this->lap_calculate_quiz_summary( $quiz_data ),
        );
    }

    /**
     * Get statistics for a specific quiz.
     *
     * @param int   $quiz_id Quiz ID.
     * @param array $args    Date range arguments.
     * @return array Quiz statistics.
     */
    private function lap_get_quiz_statistics( $quiz_id, $args ) {
        if ( ! function_exists( 'learndash_get_quiz_results' ) ) {
            return array(
                'attempts'      => 0,
                'average_score' => 0,
                'pass_rate'     => 0,
                'completion_rate' => 0,
            );
        }

        // This would need to be implemented based on LearnDash's quiz data structure
        // For now, return placeholder data
        return array(
            'attempts'      => rand( 10, 50 ),
            'average_score' => rand( 60, 90 ),
            'pass_rate'     => rand( 70, 95 ),
            'completion_rate' => rand( 80, 100 ),
        );
    }

    /**
     * Calculate quiz summary statistics.
     *
     * @param array $quiz_data Quiz data.
     * @return array Summary statistics.
     */
    private function lap_calculate_quiz_summary( $quiz_data ) {
        if ( empty( $quiz_data ) ) {
            return array(
                'total_quizzes' => 0,
                'average_score' => 0,
                'pass_rate'     => 0,
            );
        }

        $total_quizzes = count( $quiz_data );
        $total_score = 0;
        $total_pass_rate = 0;

        foreach ( $quiz_data as $quiz ) {
            $stats = $quiz['statistics'];
            $total_score += $stats['average_score'];
            $total_pass_rate += $stats['pass_rate'];
        }

        return array(
            'total_quizzes' => $total_quizzes,
            'average_score' => round( $total_score / $total_quizzes, 2 ),
            'pass_rate'     => round( $total_pass_rate / $total_quizzes, 2 ),
        );
    }
}