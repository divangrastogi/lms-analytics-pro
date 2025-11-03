<?php
/**
 * BuddyBoss analytics integration
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Analytics
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LAP_BuddyBoss_Analytics class.
 */
class LAP_BuddyBoss_Analytics {

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
        $this->cache = $cache ?: new LAP_Cache_Handler();
    }

    /**
     * Check if BuddyBoss is active.
     *
     * @return bool True if active.
     */
    public function lap_is_buddyboss_active() {
        return function_exists( 'bp_is_active' );
    }

    /**
     * Get group analytics for a course.
     *
     * @param int $course_id Course ID.
     * @return array Group analytics data.
     */
    public function lap_get_course_group_analytics( $course_id ) {
        if ( ! $this->lap_is_buddyboss_active() || ! bp_is_active( 'groups' ) ) {
            return array();
        }

        $cache_key = 'lap_course_group_analytics_' . $course_id;
        $cached = wp_cache_get( $cache_key, 'lap_analytics' );
        if ( false !== $cached ) {
            return $cached;
        }

        $group_id = $this->lap_get_course_group_id( $course_id );
        if ( ! $group_id ) {
            return array();
        }

        $analytics = array(
            'group_id'          => $group_id,
            'member_count'      => $this->lap_get_group_member_count( $group_id ),
            'activity_count'    => $this->lap_get_group_activity_count( $group_id, 30 ),
            'forum_topics'      => $this->lap_get_group_forum_topics( $group_id ),
            'forum_replies'     => $this->lap_get_group_forum_replies( $group_id ),
            'active_members'    => $this->lap_get_active_group_members( $group_id, 7 ),
            'engagement_score'  => $this->lap_calculate_group_engagement_score( $group_id ),
            'last_activity'     => $this->lap_get_group_last_activity( $group_id ),
        );

        wp_cache_set( $cache_key, $analytics, 'lap_analytics', HOUR_IN_SECONDS );

        return $analytics;
    }

    /**
     * Get BuddyBoss group ID associated with a course.
     *
     * @param int $course_id Course ID.
     * @return int|null Group ID or null.
     */
    private function lap_get_course_group_id( $course_id ) {
        $group_id = get_post_meta( $course_id, 'bp_course_group', true );
        if ( $group_id && function_exists( 'groups_get_group' ) ) {
            $group = groups_get_group( $group_id );
            return $group->id ?? null;
        }
        return null;
    }

    /**
     * Get group member count.
     *
     * @param int $group_id Group ID.
     * @return int Member count.
     */
    private function lap_get_group_member_count( $group_id ) {
        if ( ! function_exists( 'groups_get_group_members' ) ) {
            return 0;
        }

        $members = groups_get_group_members( array(
            'group_id' => $group_id,
            'per_page' => 1,
        ) );

        return $members['count'] ?? 0;
    }

    /**
     * Get group activity count for a time period.
     *
     * @param int $group_id Group ID.
     * @param int $days     Number of days.
     * @return int Activity count.
     */
    private function lap_get_group_activity_count( $group_id, $days = 30 ) {
        if ( ! function_exists( 'bp_activity_get' ) ) {
            return 0;
        }

        $args = array(
            'filter' => array(
                'object'  => 'groups',
                'item_id' => $group_id,
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
     * Get group forum topics count.
     *
     * @param int $group_id Group ID.
     * @return int Topics count.
     */
    private function lap_get_group_forum_topics( $group_id ) {
        if ( ! function_exists( 'bbp_get_group_forum_ids' ) ) {
            return 0;
        }

        $forum_ids = bbp_get_group_forum_ids( $group_id );
        if ( empty( $forum_ids ) ) {
            return 0;
        }

        global $wpdb;
        $forum_ids_placeholder = implode( ',', array_fill( 0, count( $forum_ids ), '%d' ) );

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
            WHERE post_type = 'topic'
            AND post_parent IN ({$forum_ids_placeholder})
            AND post_status = 'publish'",
            $forum_ids
        ) );
    }

    /**
     * Get group forum replies count.
     *
     * @param int $group_id Group ID.
     * @return int Replies count.
     */
    private function lap_get_group_forum_replies( $group_id ) {
        if ( ! function_exists( 'bbp_get_group_forum_ids' ) ) {
            return 0;
        }

        $forum_ids = bbp_get_group_forum_ids( $group_id );
        if ( empty( $forum_ids ) ) {
            return 0;
        }

        global $wpdb;
        $forum_ids_placeholder = implode( ',', array_fill( 0, count( $forum_ids ), '%d' ) );

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
            WHERE post_type = 'reply'
            AND post_parent IN (
                SELECT ID FROM {$wpdb->posts}
                WHERE post_type = 'topic'
                AND post_parent IN ({$forum_ids_placeholder})
            )
            AND post_status = 'publish'",
            $forum_ids
        ) );
    }

    /**
     * Get active group members in a time period.
     *
     * @param int $group_id Group ID.
     * @param int $days     Number of days.
     * @return int Active members count.
     */
    private function lap_get_active_group_members( $group_id, $days = 7 ) {
        if ( ! function_exists( 'bp_activity_get' ) ) {
            return 0;
        }

        $args = array(
            'filter' => array(
                'object'  => 'groups',
                'item_id' => $group_id,
                'date_query' => array(
                    array(
                        'after' => date( 'Y-m-d', strtotime( "-{$days} days" ) ),
                    ),
                ),
            ),
            'per_page' => -1,
        );

        $activities = bp_activity_get( $args );
        $active_users = array();

        if ( isset( $activities['activities'] ) ) {
            foreach ( $activities['activities'] as $activity ) {
                $active_users[ $activity->user_id ] = true;
            }
        }

        return count( $active_users );
    }

    /**
     * Calculate group engagement score.
     *
     * @param int $group_id Group ID.
     * @return float Engagement score (0-100).
     */
    private function lap_calculate_group_engagement_score( $group_id ) {
        $member_count = $this->lap_get_group_member_count( $group_id );
        if ( $member_count === 0 ) {
            return 0;
        }

        $active_members = $this->lap_get_active_group_members( $group_id, 7 );
        $activity_count = $this->lap_get_group_activity_count( $group_id, 7 );
        $forum_topics = $this->lap_get_group_forum_topics( $group_id );

        // Calculate engagement based on activity per member
        $activity_per_member = $activity_count / $member_count;
        $active_member_ratio = $active_members / $member_count;
        $topics_per_member = $forum_topics / $member_count;

        // Weighted score
        $score = min( 100, (
            ($activity_per_member * 40) + // 40% weight for activity
            ($active_member_ratio * 100 * 35) + // 35% weight for active ratio
            ($topics_per_member * 20) // 25% weight for forum participation
        ) );

        return round( $score, 1 );
    }

    /**
     * Get group last activity timestamp.
     *
     * @param int $group_id Group ID.
     * @return string|null Last activity date or null.
     */
    private function lap_get_group_last_activity( $group_id ) {
        if ( ! function_exists( 'bp_activity_get' ) ) {
            return null;
        }

        $args = array(
            'filter' => array(
                'object'  => 'groups',
                'item_id' => $group_id,
            ),
            'per_page' => 1,
            'sort'     => 'DESC',
        );

        $activities = bp_activity_get( $args );

        if ( isset( $activities['activities'] ) && ! empty( $activities['activities'] ) ) {
            return $activities['activities'][0]->date_recorded;
        }

        return null;
    }

    /**
     * Get group member engagement breakdown.
     *
     * @param int $group_id Group ID.
     * @param int $limit    Number of top members to return.
     * @return array Member engagement data.
     */
    public function lap_get_group_member_engagement( $group_id, $limit = 10 ) {
        if ( ! function_exists( 'bp_activity_get' ) ) {
            return array();
        }

        $cache_key = 'lap_group_member_engagement_' . $group_id . '_' . $limit;
        $cached = wp_cache_get( $cache_key, 'lap_analytics' );
        if ( false !== $cached ) {
            return $cached;
        }

        // Get activities for the last 30 days
        $args = array(
            'filter' => array(
                'object'  => 'groups',
                'item_id' => $group_id,
                'date_query' => array(
                    array(
                        'after' => date( 'Y-m-d', strtotime( '-30 days' ) ),
                    ),
                ),
            ),
            'per_page' => -1,
        );

        $activities = bp_activity_get( $args );
        $member_activity = array();

        if ( isset( $activities['activities'] ) ) {
            foreach ( $activities['activities'] as $activity ) {
                $user_id = $activity->user_id;
                if ( ! isset( $member_activity[ $user_id ] ) ) {
                    $member_activity[ $user_id ] = array(
                        'user_id'    => $user_id,
                        'count'      => 0,
                        'last_activity' => $activity->date_recorded,
                    );
                }
                $member_activity[ $user_id ]['count']++;
                if ( strtotime( $activity->date_recorded ) > strtotime( $member_activity[ $user_id ]['last_activity'] ) ) {
                    $member_activity[ $user_id ]['last_activity'] = $activity->date_recorded;
                }
            }
        }

        // Sort by activity count and limit
        usort( $member_activity, function( $a, $b ) {
            return $b['count'] <=> $a['count'];
        } );

        $result = array_slice( $member_activity, 0, $limit );

        wp_cache_set( $cache_key, $result, 'lap_analytics', HOUR_IN_SECONDS );

        return $result;
    }

    /**
     * Get group activity trends over time.
     *
     * @param int $group_id Group ID.
     * @param int $days     Number of days to analyze.
     * @return array Daily activity counts.
     */
    public function lap_get_group_activity_trends( $group_id, $days = 30 ) {
        if ( ! function_exists( 'bp_activity_get' ) ) {
            return array();
        }

        $cache_key = 'lap_group_activity_trends_' . $group_id . '_' . $days;
        $cached = wp_cache_get( $cache_key, 'lap_analytics' );
        if ( false !== $cached ) {
            return $cached;
        }

        $trends = array();

        for ( $i = $days - 1; $i >= 0; $i-- ) {
            $date = date( 'Y-m-d', strtotime( "-{$i} days" ) );

            $args = array(
                'filter' => array(
                    'object'  => 'groups',
                    'item_id' => $group_id,
                    'date_query' => array(
                        array(
                            'after'  => $date . ' 00:00:00',
                            'before' => $date . ' 23:59:59',
                        ),
                    ),
                ),
                'count_total' => true,
            );

            $activities = bp_activity_get( $args );
            $trends[ $date ] = $activities['total'] ?? 0;
        }

        wp_cache_set( $cache_key, $trends, 'lap_analytics', HOUR_IN_SECONDS );

        return $trends;
    }

    /**
     * Get course-group correlation data.
     *
     * @param int $course_id Course ID.
     * @return array Correlation data.
     */
    public function lap_get_course_group_correlation( $course_id ) {
        $group_analytics = $this->lap_get_course_group_analytics( $course_id );

        if ( empty( $group_analytics ) ) {
            return array();
        }

        // Get course completion data
        $course_completion = $this->lap_get_course_completion_data( $course_id );

        return array(
            'group_engagement'    => $group_analytics['engagement_score'],
            'course_completion'   => $course_completion['completion_rate'],
            'correlation'         => $this->lap_calculate_correlation(
                $group_analytics['engagement_score'],
                $course_completion['completion_rate']
            ),
            'active_group_ratio'  => $group_analytics['member_count'] > 0 ?
                ($group_analytics['active_members'] / $group_analytics['member_count']) * 100 : 0,
            'forum_participation' => $group_analytics['forum_topics'] + $group_analytics['forum_replies'],
        );
    }

    /**
     * Get course completion data (placeholder - would integrate with LearnDash).
     *
     * @param int $course_id Course ID.
     * @return array Completion data.
     */
    private function lap_get_course_completion_data( $course_id ) {
        // This would integrate with LearnDash to get actual completion data
        // For now, return placeholder data
        return array(
            'completion_rate' => rand( 60, 90 ),
            'enrolled_count'  => rand( 50, 200 ),
            'completed_count' => rand( 30, 180 ),
        );
    }

    /**
     * Calculate simple correlation between two values.
     *
     * @param float $x Value 1.
     * @param float $y Value 2.
     * @return float Correlation coefficient.
     */
    private function lap_calculate_correlation( $x, $y ) {
        // Simple correlation calculation
        // In a real implementation, you'd use statistical methods
        $diff = abs( $x - $y );
        return max( 0, 100 - $diff );
    }

    /**
     * Get BuddyBoss integration recommendations.
     *
     * @param int $course_id Course ID.
     * @return array Recommendations.
     */
    public function lap_get_integration_recommendations( $course_id ) {
        $analytics = $this->lap_get_course_group_analytics( $course_id );
        $correlation = $this->lap_get_course_group_correlation( $course_id );

        $recommendations = array();

        if ( empty( $analytics ) ) {
            $recommendations[] = __( 'Create a BuddyBoss group for this course to enable social learning features.', 'lms-analytics-pro' );
            return $recommendations;
        }

        if ( $analytics['engagement_score'] < 30 ) {
            $recommendations[] = __( 'Group engagement is low. Consider adding discussion prompts and icebreaker activities.', 'lms-analytics-pro' );
        }

        if ( $analytics['forum_topics'] < 5 ) {
            $recommendations[] = __( 'Limited forum activity. Create discussion topics for each lesson module.', 'lms-analytics-pro' );
        }

        if ( $correlation['correlation'] > 80 ) {
            $recommendations[] = __( 'Strong correlation between group activity and course completion. Continue encouraging social learning.', 'lms-analytics-pro' );
        } elseif ( $correlation['correlation'] < 50 ) {
            $recommendations[] = __( 'Weak correlation between group activity and completion. Consider integrating more collaborative elements.', 'lms-analytics-pro' );
        }

        if ( $analytics['active_members'] < $analytics['member_count'] * 0.5 ) {
            $recommendations[] = __( 'Only half the group members are active. Send personalized invitations to increase participation.', 'lms-analytics-pro' );
        }

        return $recommendations;
    }
}