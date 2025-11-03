<?php
/**
 * Progress calculation utilities
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Analytics
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LAP_Progress_Calculator class.
 */
class LAP_Progress_Calculator {

    /**
     * Database manager instance.
     *
     * @var LAP_DB_Manager
     */
    private $db;

    /**
     * Constructor.
     *
     * @param LAP_DB_Manager $db Database manager.
     */
    public function __construct( LAP_DB_Manager $db ) {
        $this->db = $db;
    }

    /**
     * Calculate course completion percentage.
     *
     * @param int $user_id   Student user ID.
     * @param int $course_id Course ID.
     * @return float Completion percentage (0.00-100.00).
     */
    public function lap_calculate_completion_percentage( $user_id, $course_id ) {
        if ( ! function_exists( 'learndash_course_get_steps_count' ) ) {
            return 0.00;
        }

        $total_steps = learndash_course_get_steps_count( $course_id );
        if ( $total_steps === 0 ) {
            return 0.00;
        }

        $completed_steps = learndash_course_get_completed_steps( $user_id, $course_id );
        $percentage = ( $completed_steps / $total_steps ) * 100;

        return round( $percentage, 2 );
    }

    /**
     * Calculate lesson completion percentage.
     *
     * @param int $user_id  Student user ID.
     * @param int $lesson_id Lesson ID.
     * @return float Completion percentage (0.00-100.00).
     */
    public function lap_calculate_lesson_completion( $user_id, $lesson_id ) {
        if ( ! function_exists( 'learndash_get_lesson_topics_list' ) ) {
            return 0.00;
        }

        $topics = learndash_get_lesson_topics_list( $lesson_id );
        $total_topics = count( $topics );

        if ( $total_topics === 0 ) {
            // Check if lesson itself is completed
            $completed = learndash_is_lesson_complete( $user_id, $lesson_id );
            return $completed ? 100.00 : 0.00;
        }

        $completed_topics = 0;
        foreach ( $topics as $topic ) {
            if ( learndash_is_topic_complete( $user_id, $topic['id'] ) ) {
                $completed_topics++;
            }
        }

        $percentage = ( $completed_topics / $total_topics ) * 100;
        return round( $percentage, 2 );
    }

    /**
     * Calculate topic completion percentage.
     *
     * @param int $user_id  Student user ID.
     * @param int $topic_id Topic ID.
     * @return float Completion percentage (0.00-100.00).
     */
    public function lap_calculate_topic_completion( $user_id, $topic_id ) {
        $completed = learndash_is_topic_complete( $user_id, $topic_id );
        return $completed ? 100.00 : 0.00;
    }

    /**
     * Calculate time spent on a lesson/topic.
     *
     * @param int $user_id  Student user ID.
     * @param int $lesson_id Lesson ID.
     * @param int $topic_id  Topic ID (optional).
     * @return int Time spent in seconds.
     */
    public function lap_calculate_time_spent( $user_id, $lesson_id, $topic_id = 0 ) {
        global $wpdb;

        $table = $this->db->get_table( 'student_progress' );

        $result = $wpdb->get_var( $wpdb->prepare(
            "SELECT time_spent_seconds
            FROM {$table}
            WHERE user_id = %d AND lesson_id = %d AND topic_id = %d",
            $user_id,
            $lesson_id,
            $topic_id
        ) );

        return (int) $result;
    }

    /**
     * Calculate completion velocity (lessons per day over a period).
     *
     * @param int    $user_id   Student user ID.
     * @param int    $course_id Course ID.
     * @param int    $days      Number of days to look back.
     * @param int    $offset    Days to skip from today.
     * @return float Lessons completed per day.
     */
    public function lap_calculate_completion_velocity( $user_id, $course_id, $days = 7, $offset = 0 ) {
        global $wpdb;

        $table = $this->db->get_table( 'student_progress' );

        $start_date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days -{$offset} days" ) );
        $end_date = date( 'Y-m-d H:i:s', strtotime( "-{$offset} days" ) );

        $completed_lessons = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*)
            FROM {$table}
            WHERE user_id = %d AND course_id = %d AND completion_status = 2
            AND last_activity BETWEEN %s AND %s",
            $user_id,
            $course_id,
            $start_date,
            $end_date
        ) );

        return round( $completed_lessons / $days, 2 );
    }

    /**
     * Calculate quiz performance.
     *
     * @param int $user_id   Student user ID.
     * @param int $course_id Course ID.
     * @param int $days      Number of days to look back.
     * @return array Quiz performance data.
     */
    public function lap_calculate_quiz_performance( $user_id, $course_id, $days = 30 ) {
        if ( ! function_exists( 'learndash_get_course_quiz_list' ) ) {
            return array(
                'average_score' => 0,
                'attempts'      => 0,
                'passed'        => 0,
            );
        }

        $quizzes = learndash_get_course_quiz_list( $course_id );
        if ( empty( $quizzes ) ) {
            return array(
                'average_score' => 0,
                'attempts'      => 0,
                'passed'        => 0,
            );
        }

        $total_score = 0;
        $total_attempts = 0;
        $passed_count = 0;

        foreach ( $quizzes as $quiz ) {
            $attempts = learndash_get_user_quiz_attempts( $user_id, $quiz['id'] );

            foreach ( $attempts as $attempt ) {
                // Check if attempt is within the time period
                $attempt_date = strtotime( $attempt['time'] );
                $cutoff_date = strtotime( "-{$days} days" );

                if ( $attempt_date >= $cutoff_date ) {
                    $total_score += $attempt['percentage'];
                    $total_attempts++;

                    if ( $attempt['percentage'] >= 70 ) { // Assuming 70% is passing
                        $passed_count++;
                    }
                }
            }
        }

        return array(
            'average_score' => $total_attempts > 0 ? round( $total_score / $total_attempts, 2 ) : 0,
            'attempts'      => $total_attempts,
            'passed'        => $passed_count,
        );
    }

    /**
     * Calculate engagement score based on multiple factors.
     *
     * @param int $user_id   Student user ID.
     * @param int $course_id Course ID.
     * @return float Engagement score (0-100).
     */
    public function lap_calculate_engagement_score( $user_id, $course_id ) {
        $factors = array(
            'completion_rate' => $this->lap_calculate_completion_percentage( $user_id, $course_id ) / 100,
            'recent_activity' => $this->lap_calculate_recent_activity_score( $user_id, $course_id ),
            'quiz_performance' => $this->lap_calculate_quiz_performance( $user_id, $course_id, 30 )['average_score'] / 100,
            'consistency'     => $this->lap_calculate_consistency_score( $user_id, $course_id ),
        );

        $weights = array(
            'completion_rate' => 0.4,
            'recent_activity' => 0.3,
            'quiz_performance' => 0.2,
            'consistency'     => 0.1,
        );

        $score = 0;
        foreach ( $factors as $factor => $value ) {
            $score += $value * $weights[ $factor ];
        }

        return round( $score * 100, 2 );
    }

    /**
     * Calculate recent activity score.
     *
     * @param int $user_id   Student user ID.
     * @param int $course_id Course ID.
     * @return float Activity score (0-1).
     */
    private function lap_calculate_recent_activity_score( $user_id, $course_id ) {
        global $wpdb;

        $table = $this->db->get_table( 'student_progress' );

        $last_activity = $wpdb->get_var( $wpdb->prepare(
            "SELECT last_activity
            FROM {$table}
            WHERE user_id = %d AND course_id = %d
            ORDER BY last_activity DESC
            LIMIT 1",
            $user_id,
            $course_id
        ) );

        if ( ! $last_activity ) {
            return 0;
        }

        $days_since_activity = ( time() - strtotime( $last_activity ) ) / DAY_IN_SECONDS;

        // Score decreases as days since activity increases
        if ( $days_since_activity <= 1 ) {
            return 1;
        } elseif ( $days_since_activity <= 7 ) {
            return 0.8;
        } elseif ( $days_since_activity <= 14 ) {
            return 0.6;
        } elseif ( $days_since_activity <= 30 ) {
            return 0.4;
        } else {
            return 0.2;
        }
    }

    /**
     * Calculate consistency score based on regular activity.
     *
     * @param int $user_id   Student user ID.
     * @param int $course_id Course ID.
     * @return float Consistency score (0-1).
     */
    private function lap_calculate_consistency_score( $user_id, $course_id ) {
        // Calculate activity over the last 30 days
        $activity_days = array();
        for ( $i = 0; $i < 30; $i++ ) {
            $date = date( 'Y-m-d', strtotime( "-{$i} days" ) );
            $activity = $this->lap_get_daily_activity( $user_id, $course_id, $date );
            if ( $activity > 0 ) {
                $activity_days[] = $date;
            }
        }

        $active_days = count( $activity_days );
        $consistency = min( $active_days / 30, 1 ); // Max 1.0

        return round( $consistency, 2 );
    }

    /**
     * Get daily activity for a specific date.
     *
     * @param int    $user_id   Student user ID.
     * @param int    $course_id Course ID.
     * @param string $date      Date (Y-m-d format).
     * @return int Activity count.
     */
    private function lap_get_daily_activity( $user_id, $course_id, $date ) {
        global $wpdb;

        $table = $this->db->get_table( 'student_progress' );

        return $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*)
            FROM {$table}
            WHERE user_id = %d AND course_id = %d
            AND DATE(last_activity) = %s",
            $user_id,
            $course_id,
            $date
        ) );
    }
}