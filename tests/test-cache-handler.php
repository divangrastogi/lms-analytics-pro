<?php
/**
 * Test Cache Handler
 *
 * @package LMS_Analytics_Pro
 */

use PHPUnit\Framework\TestCase;

/**
 * Cache Handler test case.
 */
class Test_Cache_Handler extends TestCase {

    /**
     * Cache Handler instance.
     *
     * @var LAP_Cache_Handler
     */
    private $cache_handler;

    /**
     * Set up test.
     */
    public function setUp(): void {
        parent::setUp();
        $this->cache_handler = new LAP_Cache_Handler();
    }

    /**
     * Test set and get cache methods.
     */
    public function test_set_and_get_cache() {
        $key = 'test_key';
        $data = array( 'test' => 'data', 'value' => 123 );
        $ttl = 3600;

        // Set cache
        $this->cache_handler->set( $key, $data, $ttl );

        // Get cache
        $cached_data = $this->cache_handler->get( $key );

        $this->assertEquals( $data, $cached_data );
    }

    /**
     * Test cache miss.
     */
    public function test_cache_miss() {
        $key = 'nonexistent_key';

        $cached_data = $this->cache_handler->get( $key );

        $this->assertFalse( $cached_data );
    }

    /**
     * Test delete cache.
     */
    public function test_delete_cache() {
        $key = 'test_delete_key';
        $data = array( 'delete' => 'test' );

        // Set cache
        $this->cache_handler->set( $key, $data );

        // Verify it's set
        $this->assertEquals( $data, $this->cache_handler->get( $key ) );

        // Delete cache
        $this->cache_handler->delete( $key );

        // Verify it's deleted
        $this->assertFalse( $this->cache_handler->get( $key ) );
    }

    /**
     * Test clear all cache.
     */
    public function test_clear_cache() {
        $keys = array( 'key1', 'key2', 'key3' );
        $data = array( 'test' => 'data' );

        // Set multiple cache entries
        foreach ( $keys as $key ) {
            $this->cache_handler->set( $key, $data );
        }

        // Verify they're set
        foreach ( $keys as $key ) {
            $this->assertEquals( $data, $this->cache_handler->get( $key ) );
        }

        // Clear all cache
        $this->cache_handler->clear();

        // Verify they're cleared
        foreach ( $keys as $key ) {
            $this->assertFalse( $this->cache_handler->get( $key ) );
        }
    }

    /**
     * Test cache key generation.
     */
    public function test_generate_cache_key() {
        $prefix = 'test_prefix';
        $params = array( 'user_id' => 1, 'course_id' => 2 );

        $key = $this->cache_handler->generate_key( $prefix, $params );

        $this->assertStringStartsWith( $prefix . '_', $key );
        $this->assertStringContains( 'user_id_1', $key );
        $this->assertStringContains( 'course_id_2', $key );
    }

    /**
     * Test risk scores cache methods.
     */
    public function test_risk_scores_cache() {
        $user_id = 1;
        $course_id = 1;
        $data = array(
            'score' => 75,
            'level' => 'high',
            'factors' => array( 'inactivity' => 30 )
        );

        // Set risk score cache
        $this->cache_handler->set_risk_score( $user_id, $course_id, $data );

        // Get risk score cache
        $cached_data = $this->cache_handler->get_risk_score( $user_id, $course_id );

        $this->assertEquals( $data, $cached_data );
    }

    /**
     * Test activity summary cache methods.
     */
    public function test_activity_summary_cache() {
        $user_id = 1;
        $course_id = 1;
        $data = array(
            'total_lessons' => 10,
            'completed_lessons' => 8,
            'last_activity' => '2024-01-15'
        );

        // Set activity summary cache
        $this->cache_handler->set_activity_summary( $user_id, $course_id, $data );

        // Get activity summary cache
        $cached_data = $this->cache_handler->get_activity_summary( $user_id, $course_id );

        $this->assertEquals( $data, $cached_data );
    }

    /**
     * Test heatmap data cache methods.
     */
    public function test_heatmap_data_cache() {
        $course_id = 1;
        $data = array(
            array(
                'user_id' => 1,
                'lesson_id' => 1,
                'completion_percentage' => 100
            )
        );

        // Set heatmap data cache
        $this->cache_handler->set_heatmap_data( $course_id, $data );

        // Get heatmap data cache
        $cached_data = $this->cache_handler->get_heatmap_data( $course_id );

        $this->assertEquals( $data, $cached_data );
    }

    /**
     * Test intervention stats cache methods.
     */
    public function test_intervention_stats_cache() {
        $data = array(
            'total_interventions' => 25,
            'successful' => 18,
            'pending' => 7
        );

        // Set intervention stats cache
        $this->cache_handler->set_intervention_stats( $data );

        // Get intervention stats cache
        $cached_data = $this->cache_handler->get_intervention_stats();

        $this->assertEquals( $data, $cached_data );
    }
}