<?php
/**
 * Database manager for LMS Analytics Pro
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Database
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LAP_DB_Manager class.
 */
class LAP_DB_Manager {

    /**
     * Table names.
     *
     * @var array
     */
    private $tables;

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;

        $this->tables = array(
            'student_progress' => $wpdb->prefix . 'lap_student_progress',
            'risk_scores'      => $wpdb->prefix . 'lap_risk_scores',
            'interventions'    => $wpdb->prefix . 'lap_interventions',
            'activity_log'     => $wpdb->prefix . 'lap_activity_log',
            'cache'            => $wpdb->prefix . 'lap_cache',
        );
    }

    /**
     * Get table name.
     *
     * @param string $table Table key.
     * @return string Table name.
     */
    public function get_table( $table ) {
        return isset( $this->tables[ $table ] ) ? $this->tables[ $table ] : '';
    }

    /**
     * Insert student progress data.
     *
     * @param array $data Progress data.
     * @return int|false Insert ID or false on failure.
     */
    public function insert_student_progress( $data ) {
        global $wpdb;

        $table = $this->get_table( 'student_progress' );

        $result = $wpdb->insert(
            $table,
            array(
                'user_id'               => $data['user_id'],
                'course_id'             => $data['course_id'],
                'lesson_id'             => $data['lesson_id'],
                'topic_id'              => $data['topic_id'] ?? 0,
                'completion_status'     => $data['completion_status'],
                'completion_percentage' => $data['completion_percentage'],
                'time_spent_seconds'    => $data['time_spent_seconds'] ?? 0,
                'last_activity'         => $data['last_activity'],
            ),
            array( '%d', '%d', '%d', '%d', '%d', '%f', '%d', '%s' )
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update student progress data.
     *
     * @param int   $progress_id Progress ID.
     * @param array $data        Progress data.
     * @return bool Success.
     */
    public function update_student_progress( $progress_id, $data ) {
        global $wpdb;

        $table = $this->get_table( 'student_progress' );

        $update_data = array();
        $format = array();

        if ( isset( $data['completion_status'] ) ) {
            $update_data['completion_status'] = $data['completion_status'];
            $format[] = '%d';
        }

        if ( isset( $data['completion_percentage'] ) ) {
            $update_data['completion_percentage'] = $data['completion_percentage'];
            $format[] = '%f';
        }

        if ( isset( $data['time_spent_seconds'] ) ) {
            $update_data['time_spent_seconds'] = $data['time_spent_seconds'];
            $format[] = '%d';
        }

        if ( isset( $data['last_activity'] ) ) {
            $update_data['last_activity'] = $data['last_activity'];
            $format[] = '%s';
        }

        $update_data['updated_at'] = current_time( 'mysql' );
        $format[] = '%s';

        return $wpdb->update(
            $table,
            $update_data,
            array( 'id' => $progress_id ),
            $format,
            array( '%d' )
        ) !== false;
    }

    /**
     * Get student progress for heatmap.
     *
     * @param array $args Query arguments.
     * @return array Progress data.
     */
    public function get_heatmap_data( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'course_id' => 0,
            'group_id'  => 0,
            'date_from' => date( 'Y-m-d', strtotime( '-30 days' ) ),
            'date_to'   => date( 'Y-m-d' ),
            'limit'     => 50,
            'offset'    => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $progress_table = $this->get_table( 'student_progress' );
        $users_table = $wpdb->users;

        $where = array( '1=1' );
        $params = array();

        if ( $args['course_id'] > 0 ) {
            $where[] = 'p.course_id = %d';
            $params[] = $args['course_id'];
        }

        if ( ! empty( $args['date_from'] ) ) {
            $where[] = 'p.last_activity >= %s';
            $params[] = $args['date_from'] . ' 00:00:00';
        }

        if ( ! empty( $args['date_to'] ) ) {
            $where[] = 'p.last_activity <= %s';
            $params[] = $args['date_to'] . ' 23:59:59';
        }

        $where_clause = implode( ' AND ', $where );

        $sql = $wpdb->prepare(
            "SELECT
                u.ID as user_id,
                u.display_name,
                u.user_email,
                p.lesson_id,
                p.completion_percentage,
                p.time_spent_seconds,
                p.last_activity
            FROM {$users_table} u
            INNER JOIN {$progress_table} p ON u.ID = p.user_id
            WHERE {$where_clause}
            ORDER BY u.display_name, p.lesson_id
            LIMIT %d OFFSET %d",
            array_merge( $params, array( $args['limit'], $args['offset'] ) )
        );

        return $wpdb->get_results( $sql, ARRAY_A );
    }

    /**
     * Get count of heatmap data records.
     *
     * @since 1.0.0
     * @param array $args Query arguments.
     * @return int Count of heatmap data records.
     */
    public function get_heatmap_data_count( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'course_id' => 0,
            'group_id'  => 0,
            'date_from' => date( 'Y-m-d', strtotime( '-30 days' ) ),
            'date_to'   => date( 'Y-m-d' ),
        );

        $args = wp_parse_args( $args, $defaults );

        $progress_table = $this->get_table( 'student_progress' );
        $users_table = $wpdb->users;

        $where = array( '1=1' );
        $params = array();

        if ( $args['course_id'] > 0 ) {
            $where[] = 'p.course_id = %d';
            $params[] = $args['course_id'];
        }

        if ( ! empty( $args['date_from'] ) ) {
            $where[] = 'p.last_activity >= %s';
            $params[] = $args['date_from'] . ' 00:00:00';
        }

        if ( ! empty( $args['date_to'] ) ) {
            $where[] = 'p.last_activity <= %s';
            $params[] = $args['date_to'] . ' 23:59:59';
        }

        $where_clause = implode( ' AND ', $where );

        $sql = $wpdb->prepare(
            "SELECT COUNT(DISTINCT u.ID) FROM {$users_table} u
            INNER JOIN {$progress_table} p ON u.ID = p.user_id
            WHERE {$where_clause}",
            $params
        );

        return (int) $wpdb->get_var( $sql );
    }

    /**
     * Get at-risk students.
     *
     * @param array $args Query arguments.
     * @return array Risk data.
     */
    public function get_at_risk_students( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'min_risk_score' => 50,
            'limit'          => 20,
            'offset'         => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $risk_table = $this->get_table( 'risk_scores' );
        $users_table = $wpdb->users;

        $where_clauses = array( 'r.risk_score >= %d' );
        $where_values = array( $args['min_risk_score'] );

        if ( isset( $args['course_id'] ) && $args['course_id'] > 0 ) {
            $where_clauses[] = 'r.course_id = %d';
            $where_values[] = $args['course_id'];
        }

        if ( isset( $args['risk_level'] ) && ! empty( $args['risk_level'] ) ) {
            $where_clauses[] = 'r.risk_level = %s';
            $where_values[] = $args['risk_level'];
        }

        $where_sql = 'WHERE ' . implode( ' AND ', $where_clauses );

        $sql = $wpdb->prepare(
            "SELECT
                r.*,
                u.display_name,
                u.user_email
            FROM {$risk_table} r
            INNER JOIN {$users_table} u ON r.user_id = u.ID
            {$where_sql}
            ORDER BY r.risk_score DESC, r.last_login ASC
            LIMIT %d OFFSET %d",
            array_merge( $where_values, array( $args['limit'], $args['offset'] ) )
        );

        return $wpdb->get_results( $sql, ARRAY_A );
    }

    /**
     * Get count of at-risk students.
     *
     * @since 1.0.0
     * @param array $args Query arguments.
     * @return int Count of at-risk students.
     */
    public function get_at_risk_students_count( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'min_risk_score' => 50,
        );

        $args = wp_parse_args( $args, $defaults );

        $risk_table = $this->get_table( 'risk_scores' );
        $users_table = $wpdb->users;

        $where_clauses = array( 'r.risk_score >= %d' );
        $where_values = array( $args['min_risk_score'] );

        if ( isset( $args['course_id'] ) && $args['course_id'] > 0 ) {
            $where_clauses[] = 'r.course_id = %d';
            $where_values[] = $args['course_id'];
        }

        if ( isset( $args['risk_level'] ) && ! empty( $args['risk_level'] ) ) {
            $where_clauses[] = 'r.risk_level = %s';
            $where_values[] = $args['risk_level'];
        }

        $where_sql = 'WHERE ' . implode( ' AND ', $where_clauses );

        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$risk_table} r
            INNER JOIN {$users_table} u ON r.user_id = u.ID
            {$where_sql}",
            $where_values
        );

        return (int) $wpdb->get_var( $sql );
    }

    /**
     * Insert or update risk score.
     *
     * @param array $data Risk data.
     * @return bool Success.
     */
    public function upsert_risk_score( $data ) {
        global $wpdb;

        $table = $this->get_table( 'risk_scores' );

        return $wpdb->replace(
            $table,
            array(
                'user_id'       => $data['user_id'],
                'course_id'     => $data['course_id'],
                'risk_score'    => $data['risk_score'],
                'risk_level'    => $data['risk_level'],
                'factors'       => wp_json_encode( $data['factors'] ),
                'last_login'    => $data['last_login'],
                'days_inactive' => $data['days_inactive'],
                'trend'         => $data['trend'],
                'calculated_at' => $data['calculated_at'],
            ),
            array( '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s' )
        ) !== false;
    }

    /**
     * Log intervention.
     *
     * @param array $data Intervention data.
     * @return int|false Insert ID or false.
     */
    public function log_intervention( $data ) {
        global $wpdb;

        $table = $this->get_table( 'interventions' );

        $result = $wpdb->insert(
            $table,
            array(
                'user_id'           => $data['user_id'],
                'instructor_id'     => $data['instructor_id'],
                'intervention_type' => $data['intervention_type'],
                'message'           => $data['message'] ?? '',
                'status'            => $data['status'] ?? 'sent',
            ),
            array( '%d', '%d', '%s', '%s', '%s' )
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Log activity.
     *
     * @param array $data Activity data.
     * @return int|false Insert ID or false.
     */
    public function log_activity( $data ) {
        global $wpdb;

        $table = $this->get_table( 'activity_log' );

        $result = $wpdb->insert(
            $table,
            array(
                'user_id'          => $data['user_id'],
                'activity_type'    => $data['activity_type'],
                'course_id'        => $data['course_id'] ?? 0,
                'lesson_id'        => $data['lesson_id'] ?? 0,
                'metadata'         => wp_json_encode( $data['metadata'] ?? array() ),
                'activity_timestamp' => $data['activity_timestamp'] ?? current_time( 'mysql' ),
            ),
            array( '%d', '%s', '%d', '%d', '%s', '%s' )
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Clean old data.
     *
     * @param int $days_old Days to keep.
     * @return bool Success.
     */
    public function clean_old_data( $days_old = 365 ) {
        global $wpdb;

        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days_old} days" ) );

        // Clean old activity logs
        $activity_table = $this->get_table( 'activity_log' );
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$activity_table} WHERE activity_timestamp < %s",
            $cutoff_date
        ) );

        // Clean old interventions (keep for 2 years)
        $intervention_table = $this->get_table( 'interventions' );
        $intervention_cutoff = date( 'Y-m-d H:i:s', strtotime( '-2 years' ) );
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$intervention_table} WHERE created_at < %s",
            $intervention_cutoff
        ) );

        return true;
    }
}