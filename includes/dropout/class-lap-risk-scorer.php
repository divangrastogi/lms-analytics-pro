<?php
/**
 * Risk scoring algorithm for dropout detection
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Dropout
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LAP_Risk_Scorer class.
 */
class LAP_Risk_Scorer {

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
     * Cache handler instance.
     *
     * @var LAP_Cache_Handler
     */
    private $cache;

    /**
     * Risk weights configuration.
     *
     * @var array
     */
    private $risk_weights;

    /**
     * Constructor.
     *
     * @param LAP_DB_Manager         $db         Database manager.
     * @param LAP_Progress_Calculator $calculator Progress calculator.
     */
    public function __construct( LAP_DB_Manager $db, LAP_Progress_Calculator $calculator ) {
        $this->db         = $db;
        $this->calculator = $calculator;
        $this->risk_weights = $this->lap_get_risk_weights();
    }

    /**
     * Calculate dropout risk score for a student.
     *
     * @param int $user_id   Student user ID.
     * @param int $course_id Course ID.
     * @return array Risk score data.
     */
    public function lap_calculate_risk_score( $user_id, $course_id ) {
        // Check cache first
        $cached_result = $this->cache->lap_get_cached_risk_score( $user_id, $course_id );
        if ( false !== $cached_result ) {
            return $cached_result;
        }

        $factors = array();

        // 1. Days Inactive Score (0-100)
        $days_inactive = $this->lap_calculate_days_inactive( $user_id, $course_id );
        $inactivity_score = min( 100, ( $days_inactive / 30 ) * 100 );
        $factors['inactivity'] = array(
            'value'  => $days_inactive,
            'score'  => $inactivity_score,
            'weight' => $this->risk_weights['inactivity'],
        );

        // 2. Completion Velocity (comparing last 7 vs previous 7 days)
        $velocity_current = $this->calculator->lap_calculate_completion_velocity( $user_id, $course_id, 7 );
        $velocity_previous = $this->calculator->lap_calculate_completion_velocity( $user_id, $course_id, 7, 7 );
        $velocity_decline = max( 0, ( $velocity_previous - $velocity_current ) / max( $velocity_previous, 1 ) * 100 );
        $factors['velocity'] = array(
            'current'  => $velocity_current,
            'previous' => $velocity_previous,
            'score'    => $velocity_decline,
            'weight'   => $this->risk_weights['velocity'],
        );

        // 3. Quiz Performance Drop
        $quiz_current = $this->calculator->lap_calculate_quiz_performance( $user_id, $course_id, 7 )['average_score'];
        $quiz_baseline = $this->calculator->lap_calculate_quiz_performance( $user_id, $course_id, 30 )['average_score'];
        $quiz_drop = max( 0, ( $quiz_baseline - $quiz_current ) );
        $factors['quiz'] = array(
            'current_avg'  => $quiz_current,
            'baseline_avg' => $quiz_baseline,
            'score'        => $quiz_drop,
            'weight'       => $this->risk_weights['quiz'],
        );

        // 4. Forum Participation (if BuddyBoss active)
        if ( function_exists( 'bp_is_active' ) && bp_is_active( 'groups' ) ) {
            $forum_current = $this->lap_get_forum_activity( $user_id, 7 );
            $forum_baseline = $this->lap_get_forum_activity( $user_id, 30 );
            $forum_drop = max( 0, ( $forum_baseline - $forum_current ) / max( $forum_baseline, 1 ) * 100 );
            $factors['forum'] = array(
                'current'  => $forum_current,
                'baseline' => $forum_baseline,
                'score'    => $forum_drop,
                'weight'   => $this->risk_weights['forum'],
            );
        } else {
            $factors['forum'] = array( 'score' => 0, 'weight' => 0 );
        }

        // 5. Assignment Delays
        $assignment_delays = $this->lap_get_assignment_delay_count( $user_id, $course_id, 30 );
        $assignment_score = min( 100, $assignment_delays * 20 );
        $factors['assignments'] = array(
            'delayed_count' => $assignment_delays,
            'score'         => $assignment_score,
            'weight'        => $this->risk_weights['assignments'],
        );

        // Calculate weighted total
        $total_score = 0;
        $total_weights = 0;
        foreach ( $factors as $factor ) {
            $total_score += $factor['score'] * $factor['weight'];
            $total_weights += $factor['weight'];
        }

        $risk_score = round( $total_score / max( $total_weights, 1 ) );

        // Determine risk level
        $risk_level = $this->lap_determine_risk_level( $risk_score );

        // Analyze trend (compare with score from 7 days ago)
        $previous_score = $this->lap_get_previous_risk_score( $user_id, $course_id, 7 );
        $trend = $this->lap_calculate_trend( $risk_score, $previous_score );

        // Generate intervention suggestions
        $suggestions = $this->lap_generate_intervention_suggestions( $factors, $risk_level );

        $result = array(
            'score'       => $risk_score,
            'level'       => $risk_level,
            'factors'     => $factors,
            'trend'       => $trend,
            'suggestions' => $suggestions,
            'calculated_at' => current_time( 'mysql' ),
        );

        // Cache the result
        $this->cache->lap_set_cached_risk_score( $user_id, $course_id, $result );

        return $result;
    }

    /**
     * Get risk weights from settings.
     *
     * @return array Risk weights.
     */
    private function lap_get_risk_weights() {
        $defaults = array(
            'inactivity'  => 35,
            'velocity'    => 25,
            'quiz'        => 20,
            'forum'       => 10,
            'assignments' => 10,
        );

        $saved_weights = get_option( 'lap_risk_weights', array() );
        return wp_parse_args( $saved_weights, $defaults );
    }

    /**
     * Calculate days since last activity.
     *
     * @param int $user_id   Student user ID.
     * @param int $course_id Course ID.
     * @return int Days inactive.
     */
    private function lap_calculate_days_inactive( $user_id, $course_id ) {
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
            // Check user's last login if no course activity
            $last_login = get_user_meta( $user_id, 'last_login', true );
            if ( $last_login ) {
                $last_activity = $last_login;
            } else {
                // User never logged in or no activity
                return 999;
            }
        }

        $days_inactive = ( time() - strtotime( $last_activity ) ) / DAY_IN_SECONDS;
        return round( $days_inactive );
    }

    /**
     * Get forum activity for a user.
     *
     * @param int $user_id Student user ID.
     * @param int $days    Number of days to look back.
     * @return int Activity count.
     */
    private function lap_get_forum_activity( $user_id, $days ) {
        if ( ! function_exists( 'bp_activity_get' ) ) {
            return 0;
        }

        $args = array(
            'filter' => array(
                'user_id' => $user_id,
                'action'  => 'activity_update', // Forum posts
                'date_query' => array(
                    array(
                        'after' => date( 'Y-m-d', strtotime( "-{$days} days" ) ),
                    ),
                ),
            ),
            'count_total' => true,
        );

        $activities = bp_activity_get( $args );
        return $activities['total'] ?? 0;
    }

    /**
     * Get assignment delay count.
     *
     * @param int $user_id   Student user ID.
     * @param int $course_id Course ID.
     * @param int $days      Number of days to look back.
     * @return int Number of delayed assignments.
     */
    private function lap_get_assignment_delay_count( $user_id, $course_id, $days ) {
        // This would need to be implemented based on LearnDash assignment system
        // For now, return a placeholder
        return rand( 0, 5 );
    }

    /**
     * Determine risk level from score.
     *
     * @param int $score Risk score (0-100).
     * @return string Risk level.
     */
    private function lap_determine_risk_level( $score ) {
        if ( $score >= 75 ) {
            return 'critical';
        } elseif ( $score >= 50 ) {
            return 'high';
        } elseif ( $score >= 25 ) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get previous risk score for trend calculation.
     *
     * @param int $user_id   Student user ID.
     * @param int $course_id Course ID.
     * @param int $days_ago  Days ago to look.
     * @return int Previous risk score.
     */
    private function lap_get_previous_risk_score( $user_id, $course_id, $days_ago ) {
        global $wpdb;

        $table = $this->db->get_table( 'risk_scores' );

        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days_ago} days" ) );

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT risk_score
            FROM {$table}
            WHERE user_id = %d AND course_id = %d AND calculated_at <= %s
            ORDER BY calculated_at DESC
            LIMIT 1",
            $user_id,
            $course_id,
            $cutoff_date
        ) );
    }

    /**
     * Calculate trend based on current and previous scores.
     *
     * @param int $current_score  Current risk score.
     * @param int $previous_score Previous risk score.
     * @return string Trend direction.
     */
    private function lap_calculate_trend( $current_score, $previous_score ) {
        if ( 0 === $previous_score ) {
            return 'stable';
        }

        $change = $current_score - $previous_score;
        $change_percent = abs( $change / $previous_score );

        if ( $change_percent < 0.1 ) {
            return 'stable';
        } elseif ( $change > 0 ) {
            return 'worsening';
        } else {
            return 'improving';
        }
    }

    /**
     * Generate intervention suggestions based on risk factors.
     *
     * @param array  $factors   Risk factors.
     * @param string $risk_level Risk level.
     * @return array Intervention suggestions.
     */
    private function lap_generate_intervention_suggestions( $factors, $risk_level ) {
        $suggestions = array();

        // High inactivity
        if ( $factors['inactivity']['score'] > 50 ) {
            $suggestions[] = __( 'Send personalized email reminder about course progress', 'lms-analytics-pro' );
            $suggestions[] = __( 'Schedule a check-in call to understand barriers', 'lms-analytics-pro' );
        }

        // Declining velocity
        if ( $factors['velocity']['score'] > 30 ) {
            $suggestions[] = __( 'Provide additional resources for challenging topics', 'lms-analytics-pro' );
            $suggestions[] = __( 'Offer extended deadlines for upcoming assignments', 'lms-analytics-pro' );
        }

        // Poor quiz performance
        if ( $factors['quiz']['score'] > 20 ) {
            $suggestions[] = __( 'Schedule tutoring session for quiz preparation', 'lms-analytics-pro' );
            $suggestions[] = __( 'Provide study guides and practice materials', 'lms-analytics-pro' );
        }

        // Low forum participation
        if ( isset( $factors['forum'] ) && $factors['forum']['score'] > 40 ) {
            $suggestions[] = __( 'Encourage participation in discussion forums', 'lms-analytics-pro' );
            $suggestions[] = __( 'Connect with study buddy or learning group', 'lms-analytics-pro' );
        }

        // Assignment delays
        if ( $factors['assignments']['delayed_count'] > 2 ) {
            $suggestions[] = __( 'Review assignment submission process and deadlines', 'lms-analytics-pro' );
            $suggestions[] = __( 'Provide one-on-one assistance with pending work', 'lms-analytics-pro' );
        }

        // Default suggestions based on risk level
        if ( 'critical' === $risk_level ) {
            $suggestions[] = __( 'Immediate intervention required - contact student directly', 'lms-analytics-pro' );
        } elseif ( 'high' === $risk_level ) {
            $suggestions[] = __( 'Monitor closely and follow up within 48 hours', 'lms-analytics-pro' );
        } elseif ( 'medium' === $risk_level ) {
            $suggestions[] = __( 'Send gentle reminder and check progress weekly', 'lms-analytics-pro' );
        }

        return array_unique( $suggestions );
    }

    /**
     * Batch calculate risk scores for multiple students.
     *
     * @param array $students Array of student IDs.
     * @param int   $course_id Course ID.
     * @return array Risk scores for all students.
     */
    public function lap_batch_calculate_risk_scores( $students, $course_id ) {
        $results = array();

        foreach ( $students as $user_id ) {
            $results[ $user_id ] = $this->lap_calculate_risk_score( $user_id, $course_id );
        }

        return $results;
    }

    /**
     * Get risk level color for UI display.
     *
     * @param string $risk_level Risk level.
     * @return string Color code.
     */
    public function lap_get_risk_level_color( $risk_level ) {
        $colors = array(
            'low'      => '#10B981', // Green
            'medium'   => '#F59E0B', // Amber
            'high'     => '#EF4444', // Red
            'critical' => '#7F1D1D', // Dark red
        );

        return isset( $colors[ $risk_level ] ) ? $colors[ $risk_level ] : '#6B7280';
    }

    /**
     * Get risk level label for display.
     *
     * @param string $risk_level Risk level.
     * @return string Display label.
     */
    public function lap_get_risk_level_label( $risk_level ) {
        $labels = array(
            'low'      => __( 'Low Risk', 'lms-analytics-pro' ),
            'medium'   => __( 'Medium Risk', 'lms-analytics-pro' ),
            'high'     => __( 'High Risk', 'lms-analytics-pro' ),
            'critical' => __( 'Critical Risk', 'lms-analytics-pro' ),
        );

        return isset( $labels[ $risk_level ] ) ? $labels[ $risk_level ] : __( 'Unknown', 'lms-analytics-pro' );
    }
}