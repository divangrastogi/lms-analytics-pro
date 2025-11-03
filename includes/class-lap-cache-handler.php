<?php
/**
 * Cache handler for performance optimization
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Cache
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LAP_Cache_Handler class.
 */
class LAP_Cache_Handler {

    /**
     * Cache group prefix.
     *
     * @var string
     */
    private $cache_group = 'lap_analytics';

    /**
     * Default cache expiration (24 hours).
     *
     * @var int
     */
    private $default_expiration = 86400;

    /**
     * Cache keys for different data types.
     *
     * @var array
     */
    private $cache_keys = array(
        'risk_scores'      => 'lap_risk_scores_%s_%s', // user_id, course_id
        'activity_summary' => 'lap_activity_summary_%s_%s_%s', // user_id, start_date, end_date
        'heatmap_data'     => 'lap_heatmap_data_%s', // filters_hash
        'intervention_stats' => 'lap_intervention_stats_%s_%s', // course_id, days
        'at_risk_students' => 'lap_at_risk_students_%s', // filters_hash
        'engagement_score' => 'lap_engagement_score_%s_%s_%s', // user_id, course_id, days
    );

    /**
     * Constructor.
     */
    public function __construct() {
        // Hook into cache clearing events
        add_action( 'lap_clear_analytics_cache', array( $this, 'lap_clear_all_cache' ) );
        add_action( 'lap_clear_user_cache', array( $this, 'lap_clear_user_cache' ), 10, 1 );
        add_action( 'lap_clear_course_cache', array( $this, 'lap_clear_course_cache' ), 10, 1 );
    }

    /**
     * Get cached risk score data.
     *
     * @param int $user_id   User ID.
     * @param int $course_id Course ID.
     * @return array|null Cached data or null if not found.
     */
    public function lap_get_cached_risk_score( $user_id, $course_id ) {
        $key = sprintf( $this->cache_keys['risk_scores'], $user_id, $course_id );
        return $this->lap_get_cache( $key );
    }

    /**
     * Set cached risk score data.
     *
     * @param int   $user_id   User ID.
     * @param int   $course_id Course ID.
     * @param array $data      Risk score data.
     * @param int   $expiration Cache expiration in seconds.
     * @return bool Success.
     */
    public function lap_set_cached_risk_score( $user_id, $course_id, $data, $expiration = null ) {
        $key = sprintf( $this->cache_keys['risk_scores'], $user_id, $course_id );
        return $this->lap_set_cache( $key, $data, $expiration ?: $this->default_expiration );
    }

    /**
     * Get cached activity summary.
     *
     * @param int    $user_id    User ID.
     * @param string $start_date Start date.
     * @param string $end_date   End date.
     * @return array|null Cached data or null.
     */
    public function lap_get_cached_activity_summary( $user_id, $start_date, $end_date ) {
        $key = sprintf( $this->cache_keys['activity_summary'], $user_id, $start_date, $end_date );
        return $this->lap_get_cache( $key );
    }

    /**
     * Set cached activity summary.
     *
     * @param int    $user_id    User ID.
     * @param string $start_date Start date.
     * @param string $end_date   End date.
     * @param array  $data       Activity summary data.
     * @param int    $expiration Cache expiration.
     * @return bool Success.
     */
    public function lap_set_cached_activity_summary( $user_id, $start_date, $end_date, $data, $expiration = null ) {
        $key = sprintf( $this->cache_keys['activity_summary'], $user_id, $start_date, $end_date );
        return $this->lap_set_cache( $key, $data, $expiration ?: $this->default_expiration );
    }

    /**
     * Get cached heatmap data.
     *
     * @param array $filters Filters array.
     * @return array|null Cached data or null.
     */
    public function lap_get_cached_heatmap_data( $filters ) {
        $key = sprintf( $this->cache_keys['heatmap_data'], $this->lap_generate_filters_hash( $filters ) );
        return $this->lap_get_cache( $key );
    }

    /**
     * Set cached heatmap data.
     *
     * @param array $filters    Filters array.
     * @param array $data       Heatmap data.
     * @param int   $expiration Cache expiration.
     * @return bool Success.
     */
    public function lap_set_cached_heatmap_data( $filters, $data, $expiration = null ) {
        $key = sprintf( $this->cache_keys['heatmap_data'], $this->lap_generate_filters_hash( $filters ) );
        return $this->lap_set_cache( $key, $data, $expiration ?: $this->default_expiration );
    }

    /**
     * Get cached intervention stats.
     *
     * @param int $course_id Course ID (0 for all).
     * @param int $days      Number of days.
     * @return array|null Cached data or null.
     */
    public function lap_get_cached_intervention_stats( $course_id, $days ) {
        $key = sprintf( $this->cache_keys['intervention_stats'], $course_id, $days );
        return $this->lap_get_cache( $key );
    }

    /**
     * Set cached intervention stats.
     *
     * @param int   $course_id  Course ID.
     * @param int   $days       Number of days.
     * @param array $data       Stats data.
     * @param int   $expiration Cache expiration.
     * @return bool Success.
     */
    public function lap_set_cached_intervention_stats( $course_id, $days, $data, $expiration = null ) {
        $key = sprintf( $this->cache_keys['intervention_stats'], $course_id, $days );
        return $this->lap_set_cache( $key, $data, $expiration ?: 3600 ); // 1 hour for stats
    }

    /**
     * Get cached at-risk students.
     *
     * @param array $filters Filters array.
     * @return array|null Cached data or null.
     */
    public function lap_get_cached_at_risk_students( $filters ) {
        $key = sprintf( $this->cache_keys['at_risk_students'], $this->lap_generate_filters_hash( $filters ) );
        return $this->lap_get_cache( $key );
    }

    /**
     * Set cached at-risk students.
     *
     * @param array $filters    Filters array.
     * @param array $data       Students data.
     * @param int   $expiration Cache expiration.
     * @return bool Success.
     */
    public function lap_set_cached_at_risk_students( $filters, $data, $expiration = null ) {
        $key = sprintf( $this->cache_keys['at_risk_students'], $this->lap_generate_filters_hash( $filters ) );
        return $this->lap_set_cache( $key, $data, $expiration ?: 1800 ); // 30 minutes for dynamic data
    }

    /**
     * Get cached engagement score.
     *
     * @param int $user_id   User ID.
     * @param int $course_id Course ID.
     * @param int $days      Number of days.
     * @return float|null Cached score or null.
     */
    public function lap_get_cached_engagement_score( $user_id, $course_id, $days ) {
        $key = sprintf( $this->cache_keys['engagement_score'], $user_id, $course_id, $days );
        return $this->lap_get_cache( $key );
    }

    /**
     * Set cached engagement score.
     *
     * @param int   $user_id   User ID.
     * @param int   $course_id Course ID.
     * @param int   $days      Number of days.
     * @param float $score     Engagement score.
     * @param int   $expiration Cache expiration.
     * @return bool Success.
     */
    public function lap_set_cached_engagement_score( $user_id, $course_id, $days, $score, $expiration = null ) {
        $key = sprintf( $this->cache_keys['engagement_score'], $user_id, $course_id, $days );
        return $this->lap_set_cache( $key, $score, $expiration ?: $this->default_expiration );
    }

    /**
     * Clear all analytics cache.
     */
    public function lap_clear_all_cache() {
        wp_cache_flush_group( $this->cache_group );
    }

    /**
     * Clear cache for a specific user.
     *
     * @param int $user_id User ID.
     */
    public function lap_clear_user_cache( $user_id ) {
        // Clear all cache keys that contain the user ID
        $patterns = array(
            sprintf( $this->cache_keys['risk_scores'], $user_id, '*' ),
            sprintf( $this->cache_keys['activity_summary'], $user_id, '*', '*' ),
            sprintf( $this->cache_keys['engagement_score'], $user_id, '*', '*' ),
        );

        foreach ( $patterns as $pattern ) {
            $this->lap_delete_cache_pattern( $pattern );
        }
    }

    /**
     * Clear cache for a specific course.
     *
     * @param int $course_id Course ID.
     */
    public function lap_clear_course_cache( $course_id ) {
        // Clear heatmap and intervention stats cache
        $this->lap_delete_cache_pattern( sprintf( $this->cache_keys['heatmap_data'], '*' ) );
        $this->lap_delete_cache_pattern( sprintf( $this->cache_keys['intervention_stats'], $course_id, '*' ) );
        $this->lap_delete_cache_pattern( sprintf( $this->cache_keys['at_risk_students'], '*' ) );
    }

    /**
     * Invalidate cache when new activity is logged.
     *
     * @param int $user_id   User ID.
     * @param int $course_id Course ID.
     */
    public function lap_invalidate_activity_cache( $user_id, $course_id ) {
        // Clear user-specific caches
        $this->lap_clear_user_cache( $user_id );

        // Clear course-specific caches
        $this->lap_clear_course_cache( $course_id );

        // Clear at-risk students cache as it may have changed
        $this->lap_delete_cache_pattern( sprintf( $this->cache_keys['at_risk_students'], '*' ) );
    }

    /**
     * Get cache with group.
     *
     * @param string $key Cache key.
     * @return mixed Cached value or false.
     */
    private function lap_get_cache( $key ) {
        return wp_cache_get( $key, $this->cache_group );
    }

    /**
     * Set cache with group.
     *
     * @param string $key        Cache key.
     * @param mixed  $data       Data to cache.
     * @param int    $expiration Expiration time.
     * @return bool Success.
     */
    private function lap_set_cache( $key, $data, $expiration ) {
        return wp_cache_set( $key, $data, $this->cache_group, $expiration );
    }

    /**
     * Delete cache.
     *
     * @param string $key Cache key.
     * @return bool Success.
     */
    private function lap_delete_cache( $key ) {
        return wp_cache_delete( $key, $this->cache_group );
    }

    /**
     * Delete cache by pattern (simplified implementation).
     *
     * @param string $pattern Pattern to match.
     */
    private function lap_delete_cache_pattern( $pattern ) {
        // In a real implementation, you might need a more sophisticated
        // cache backend that supports pattern deletion
        // For now, we'll clear the entire group when patterns are used
        if ( strpos( $pattern, '*' ) !== false ) {
            // Pattern detected, clear group
            wp_cache_flush_group( $this->cache_group );
        } else {
            $this->lap_delete_cache( $pattern );
        }
    }

    /**
     * Generate hash for filters array.
     *
     * @param array $filters Filters array.
     * @return string Hash.
     */
    private function lap_generate_filters_hash( $filters ) {
        ksort( $filters ); // Ensure consistent ordering
        return md5( serialize( $filters ) );
    }

    /**
     * Get cache statistics for debugging.
     *
     * @return array Cache stats.
     */
    public function lap_get_cache_stats() {
        // This would require a custom cache backend to track stats
        // For now, return basic info
        return array(
            'cache_group' => $this->cache_group,
            'default_expiration' => $this->default_expiration,
            'cache_keys' => array_keys( $this->cache_keys ),
        );
    }

    /**
     * Warm up cache for frequently accessed data.
     */
    public function lap_warm_cache() {
        // Pre-calculate and cache common queries
        // This could be called during plugin activation or via cron

        // Cache intervention stats for common time periods
        $time_periods = array( 7, 30, 90 );
        foreach ( $time_periods as $days ) {
            // This would trigger calculation and caching
            do_action( 'lap_warm_intervention_stats', $days );
        }
    }
}