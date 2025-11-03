<?php
/**
 * Test DB Manager
 *
 * @package LMS_Analytics_Pro
 */

use PHPUnit\Framework\TestCase;

/**
 * DB Manager test case.
 */
class Test_DB_Manager extends TestCase {

    /**
     * DB Manager instance.
     *
     * @var LAP_DB_Manager
     */
    private $db_manager;

    /**
     * Set up test.
     */
    public function setUp(): void {
        parent::setUp();
        $this->db_manager = new LAP_DB_Manager();
    }

    /**
     * Test get_at_risk_students method.
     */
    public function test_get_at_risk_students() {
        $args = array(
            'min_risk_score' => 50,
            'limit'          => 10,
            'offset'         => 0,
        );

        $result = $this->db_manager->get_at_risk_students( $args );

        $this->assertIsArray( $result );
    }

    /**
     * Test get_at_risk_students_count method.
     */
    public function test_get_at_risk_students_count() {
        $args = array(
            'min_risk_score' => 50,
        );

        $result = $this->db_manager->get_at_risk_students_count( $args );

        $this->assertIsInt( $result );
        $this->assertGreaterThanOrEqual( 0, $result );
    }

    /**
     * Test get_heatmap_data method.
     */
    public function test_get_heatmap_data() {
        $args = array(
            'course_id' => 0,
            'limit'     => 10,
            'offset'    => 0,
        );

        $result = $this->db_manager->get_heatmap_data( $args );

        $this->assertIsArray( $result );
    }

    /**
     * Test get_heatmap_data_count method.
     */
    public function test_get_heatmap_data_count() {
        $args = array(
            'course_id' => 0,
        );

        $result = $this->db_manager->get_heatmap_data_count( $args );

        $this->assertIsInt( $result );
        $this->assertGreaterThanOrEqual( 0, $result );
    }

    /**
     * Test upsert_risk_score method.
     */
    public function test_upsert_risk_score() {
        $data = array(
            'user_id'       => 1,
            'course_id'     => 1,
            'risk_score'    => 75,
            'risk_level'    => 'high',
            'factors'       => array(
                'inactivity' => array( 'score' => 30, 'value' => 7 ),
                'velocity'   => array( 'score' => 25, 'value' => 0.5 ),
                'quiz'       => array( 'score' => 20, 'value' => 65 ),
            ),
            'last_login'    => '2024-01-15 10:00:00',
            'days_inactive' => 7,
        );

        $result = $this->db_manager->upsert_risk_score( $data );

        $this->assertTrue( $result );
    }

    /**
     * Test get_table method.
     */
    public function test_get_table() {
        $table = $this->db_manager->get_table( 'risk_scores' );

        $this->assertStringStartsWith( 'wp_', $table );
        $this->assertStringContains( 'lap_risk_scores', $table );
    }
}