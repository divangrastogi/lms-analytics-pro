<?php
/**
 * Test Risk Scorer
 *
 * @package LMS_Analytics_Pro
 */

use PHPUnit\Framework\TestCase;

/**
 * Risk Scorer test case.
 */
class Test_Risk_Scorer extends TestCase {

    /**
     * Risk Scorer instance.
     *
     * @var LAP_Risk_Scorer
     */
    private $risk_scorer;

    /**
     * Set up test.
     */
    public function setUp(): void {
        parent::setUp();
        $this->risk_scorer = new LAP_Risk_Scorer();
    }

    /**
     * Test calculate_risk_score method with low risk data.
     */
    public function test_calculate_risk_score_low() {
        $user_data = array(
            'user_id'       => 1,
            'course_id'     => 1,
            'last_login'    => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
            'days_inactive' => 1,
            'completion_velocity' => 1.2,
            'quiz_performance'    => 85,
            'total_lessons'       => 10,
            'completed_lessons'   => 8,
        );

        $result = $this->risk_scorer->calculate_risk_score( $user_data );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'score', $result );
        $this->assertArrayHasKey( 'level', $result );
        $this->assertArrayHasKey( 'factors', $result );
        $this->assertArrayHasKey( 'suggestions', $result );
        $this->assertLessThanOrEqual( 30, $result['score'] );
        $this->assertEquals( 'low', $result['level'] );
    }

    /**
     * Test calculate_risk_score method with high risk data.
     */
    public function test_calculate_risk_score_high() {
        $user_data = array(
            'user_id'       => 2,
            'course_id'     => 1,
            'last_login'    => date( 'Y-m-d H:i:s', strtotime( '-14 days' ) ),
            'days_inactive' => 14,
            'completion_velocity' => 0.1,
            'quiz_performance'    => 45,
            'total_lessons'       => 10,
            'completed_lessons'   => 2,
        );

        $result = $this->risk_scorer->calculate_risk_score( $user_data );

        $this->assertIsArray( $result );
        $this->assertGreaterThanOrEqual( 70, $result['score'] );
        $this->assertEquals( 'high', $result['level'] );
    }

    /**
     * Test calculate_risk_score method with critical risk data.
     */
    public function test_calculate_risk_score_critical() {
        $user_data = array(
            'user_id'       => 3,
            'course_id'     => 1,
            'last_login'    => date( 'Y-m-d H:i:s', strtotime( '-30 days' ) ),
            'days_inactive' => 30,
            'completion_velocity' => 0.0,
            'quiz_performance'    => 20,
            'total_lessons'       => 10,
            'completed_lessons'   => 0,
        );

        $result = $this->risk_scorer->calculate_risk_score( $user_data );

        $this->assertIsArray( $result );
        $this->assertGreaterThanOrEqual( 90, $result['score'] );
        $this->assertEquals( 'critical', $result['level'] );
    }

    /**
     * Test get_risk_level method.
     */
    public function test_get_risk_level() {
        $this->assertEquals( 'low', $this->risk_scorer->get_risk_level( 25 ) );
        $this->assertEquals( 'medium', $this->risk_scorer->get_risk_level( 45 ) );
        $this->assertEquals( 'high', $this->risk_scorer->get_risk_level( 75 ) );
        $this->assertEquals( 'critical', $this->risk_scorer->get_risk_level( 95 ) );
    }

    /**
     * Test get_risk_factors method.
     */
    public function test_get_risk_factors() {
        $user_data = array(
            'last_login'    => date( 'Y-m-d H:i:s', strtotime( '-7 days' ) ),
            'days_inactive' => 7,
            'completion_velocity' => 0.5,
            'quiz_performance'    => 60,
        );

        $factors = $this->risk_scorer->get_risk_factors( $user_data );

        $this->assertIsArray( $factors );
        $this->assertArrayHasKey( 'inactivity', $factors );
        $this->assertArrayHasKey( 'velocity', $factors );
        $this->assertArrayHasKey( 'quiz', $factors );

        foreach ( $factors as $factor ) {
            $this->assertArrayHasKey( 'score', $factor );
            $this->assertArrayHasKey( 'value', $factor );
            $this->assertIsNumeric( $factor['score'] );
        }
    }

    /**
     * Test get_intervention_suggestions method.
     */
    public function test_get_intervention_suggestions() {
        $factors = array(
            'inactivity' => array( 'score' => 35, 'value' => 10 ),
            'velocity'   => array( 'score' => 20, 'value' => 0.3 ),
            'quiz'       => array( 'score' => 15, 'value' => 70 ),
        );

        $suggestions = $this->risk_scorer->get_intervention_suggestions( $factors );

        $this->assertIsArray( $suggestions );
        $this->assertNotEmpty( $suggestions );

        foreach ( $suggestions as $suggestion ) {
            $this->assertIsString( $suggestion );
        }
    }
}