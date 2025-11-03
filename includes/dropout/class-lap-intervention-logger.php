<?php
/**
 * Intervention logging and tracking system
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Dropout
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LAP_Intervention_Logger class.
 */
class LAP_Intervention_Logger {

    /**
     * Database manager instance.
     *
     * @var LAP_DB_Manager
     */
    private $db;

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
     * Log an intervention action.
     *
     * @param array $intervention_data Intervention data.
     * @return int|bool Intervention ID on success, false on failure.
     */
    public function lap_log_intervention( $intervention_data ) {
        $defaults = array(
            'user_id'           => 0,
            'course_id'         => 0,
            'instructor_id'     => get_current_user_id(),
            'intervention_type' => 'email',
            'message'           => '',
            'metadata'          => array(),
            'status'            => 'sent',
            'created_at'        => current_time( 'mysql' ),
        );

        $data = wp_parse_args( $intervention_data, $defaults );

        // Validate required fields
        if ( empty( $data['user_id'] ) || empty( $data['course_id'] ) ) {
            return false;
        }

        // Sanitize data
        $data['message'] = sanitize_textarea_field( $data['message'] );
        $data['metadata'] = maybe_serialize( $data['metadata'] );

        return $this->db->insert_intervention( $data );
    }

    /**
     * Update intervention status.
     *
     * @param int    $intervention_id Intervention ID.
     * @param string $status          New status.
     * @param array  $metadata        Additional metadata.
     * @return bool Success.
     */
    public function lap_update_intervention_status( $intervention_id, $status, $metadata = array() ) {
        global $wpdb;

        $table = $this->db->get_table( 'interventions' );

        $update_data = array(
            'status'     => sanitize_key( $status ),
            'updated_at' => current_time( 'mysql' ),
        );

        if ( ! empty( $metadata ) ) {
            $existing_metadata = $wpdb->get_var( $wpdb->prepare(
                "SELECT metadata FROM {$table} WHERE id = %d",
                $intervention_id
            ) );

            $existing_metadata = maybe_unserialize( $existing_metadata );
            if ( ! is_array( $existing_metadata ) ) {
                $existing_metadata = array();
            }

            $merged_metadata = array_merge( $existing_metadata, $metadata );
            $update_data['metadata'] = maybe_serialize( $merged_metadata );
        }

        $result = $wpdb->update(
            $table,
            $update_data,
            array( 'id' => $intervention_id ),
            array( '%s', '%s', '%s' ),
            array( '%d' )
        );

        return false !== $result;
    }

    /**
     * Log intervention response/outcome.
     *
     * @param int   $intervention_id Intervention ID.
     * @param array $response_data   Response data.
     * @return bool Success.
     */
    public function lap_log_intervention_response( $intervention_id, $response_data ) {
        $metadata = array(
            'response_logged_at' => current_time( 'mysql' ),
            'response_type'      => isset( $response_data['type'] ) ? sanitize_key( $response_data['type'] ) : 'unknown',
            'response_details'   => isset( $response_data['details'] ) ? sanitize_textarea_field( $response_data['details'] ) : '',
        );

        // Update status based on response
        $new_status = 'responded';
        if ( isset( $response_data['type'] ) ) {
            switch ( $response_data['type'] ) {
                case 'student_reengaged':
                    $new_status = 'successful';
                    break;
                case 'student_unresponsive':
                    $new_status = 'failed';
                    break;
                case 'instructor_followup':
                    $new_status = 'followup_needed';
                    break;
            }
        }

        return $this->lap_update_intervention_status( $intervention_id, $new_status, $metadata );
    }

    /**
     * Get interventions for a student.
     *
     * @param int $user_id   Student user ID.
     * @param int $course_id Optional course ID filter.
     * @param int $limit     Number of interventions to return.
     * @return array Interventions.
     */
    public function lap_get_student_interventions( $user_id, $course_id = 0, $limit = 50 ) {
        global $wpdb;

        $table = $this->db->get_table( 'interventions' );

        $where = array( 'user_id = %d' );
        $params = array( $user_id );

        if ( $course_id > 0 ) {
            $where[] = 'course_id = %d';
            $params[] = $course_id;
        }

        $where_clause = implode( ' AND ', $where );

        $sql = $wpdb->prepare(
            "SELECT * FROM {$table}
            WHERE {$where_clause}
            ORDER BY created_at DESC
            LIMIT %d",
            array_merge( $params, array( $limit ) )
        );

        $results = $wpdb->get_results( $sql, ARRAY_A );

        // Unserialize metadata
        foreach ( $results as &$result ) {
            $result['metadata'] = maybe_unserialize( $result['metadata'] );
        }

        return $results;
    }

    /**
     * Get intervention statistics.
     *
     * @param int $course_id Optional course ID filter.
     * @param int $days      Number of days to look back.
     * @return array Statistics.
     */
    public function lap_get_intervention_stats( $course_id = 0, $days = 30 ) {
        global $wpdb;

        $table = $this->db->get_table( 'interventions' );

        $where = array( '1=1' );
        $params = array();

        if ( $course_id > 0 ) {
            $where[] = 'course_id = %d';
            $params[] = $course_id;
        }

        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
        $where[] = 'created_at >= %s';
        $params[] = $cutoff_date;

        $where_clause = implode( ' AND ', $where );

        $sql = $wpdb->prepare(
            "SELECT
                intervention_type,
                status,
                COUNT(*) as count
            FROM {$table}
            WHERE {$where_clause}
            GROUP BY intervention_type, status
            ORDER BY intervention_type, status",
            $params
        );

        $results = $wpdb->get_results( $sql, ARRAY_A );

        $stats = array(
            'total_interventions' => 0,
            'by_type'             => array(),
            'by_status'           => array(),
            'success_rate'        => 0,
            'period_days'         => $days,
        );

        $successful_count = 0;
        $total_count = 0;

        foreach ( $results as $result ) {
            $stats['total_interventions'] += $result['count'];
            $total_count += $result['count'];

            // Group by type
            if ( ! isset( $stats['by_type'][ $result['intervention_type'] ] ) ) {
                $stats['by_type'][ $result['intervention_type'] ] = array(
                    'total' => 0,
                    'by_status' => array(),
                );
            }
            $stats['by_type'][ $result['intervention_type'] ]['total'] += $result['count'];
            $stats['by_type'][ $result['intervention_type'] ]['by_status'][ $result['status'] ] = $result['count'];

            // Group by status
            if ( ! isset( $stats['by_status'][ $result['status'] ] ) ) {
                $stats['by_status'][ $result['status'] ] = 0;
            }
            $stats['by_status'][ $result['status'] ] += $result['count'];

            // Count successful interventions
            if ( 'successful' === $result['status'] ) {
                $successful_count += $result['count'];
            }
        }

        // Calculate success rate
        if ( $total_count > 0 ) {
            $stats['success_rate'] = round( ( $successful_count / $total_count ) * 100, 1 );
        }

        // Cache the result
        $this->cache->lap_set_cached_intervention_stats( $course_id, $days, $stats );

        return $stats;
    }

    /**
     * Get pending interventions that need follow-up.
     *
     * @param int $course_id Optional course ID filter.
     * @return array Pending interventions.
     */
    public function lap_get_pending_interventions( $course_id = 0 ) {
        global $wpdb;

        $table = $this->db->get_table( 'interventions' );

        $where = array( "status IN ('sent', 'followup_needed')" );
        $params = array();

        if ( $course_id > 0 ) {
            $where[] = 'course_id = %d';
            $params[] = $course_id;
        }

        // Only interventions older than 7 days
        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
        $where[] = 'created_at <= %s';
        $params[] = $cutoff_date;

        $where_clause = implode( ' AND ', $where );

        $sql = $wpdb->prepare(
            "SELECT i.*,
                    u.display_name as student_name,
                    u.user_email as student_email,
                    p.post_title as course_name
            FROM {$table} i
            LEFT JOIN {$wpdb->users} u ON i.user_id = u.ID
            LEFT JOIN {$wpdb->posts} p ON i.course_id = p.ID
            WHERE {$where_clause}
            ORDER BY i.created_at ASC",
            $params
        );

        $results = $wpdb->get_results( $sql, ARRAY_A );

        // Unserialize metadata
        foreach ( $results as &$result ) {
            $result['metadata'] = maybe_unserialize( $result['metadata'] );
        }

        return $results;
    }

    /**
     * Bulk update intervention statuses.
     *
     * @param array  $intervention_ids Array of intervention IDs.
     * @param string $status           New status.
     * @param string $note             Optional note.
     * @return int Number of updated records.
     */
    public function lap_bulk_update_interventions( $intervention_ids, $status, $note = '' ) {
        if ( empty( $intervention_ids ) || ! is_array( $intervention_ids ) ) {
            return 0;
        }

        $updated = 0;
        $metadata = array();

        if ( ! empty( $note ) ) {
            $metadata['bulk_update_note'] = sanitize_textarea_field( $note );
            $metadata['bulk_updated_at'] = current_time( 'mysql' );
        }

        foreach ( $intervention_ids as $id ) {
            if ( $this->lap_update_intervention_status( (int) $id, $status, $metadata ) ) {
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Delete old intervention records.
     *
     * @param int $days_old Days to keep.
     * @return int Number of deleted records.
     */
    public function lap_cleanup_old_interventions( $days_old = 365 ) {
        global $wpdb;

        $table = $this->db->get_table( 'interventions' );
        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days_old} days" ) );

        return $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$table} WHERE created_at < %s",
            $cutoff_date
        ) );
    }

    /**
     * Get intervention effectiveness report.
     *
     * @param int $course_id Optional course ID filter.
     * @param int $months    Number of months to analyze.
     * @return array Effectiveness report.
     */
    public function lap_get_effectiveness_report( $course_id = 0, $months = 6 ) {
        $stats = $this->lap_get_intervention_stats( $course_id, $months * 30 );

        $report = array(
            'period_months'      => $months,
            'total_interventions' => $stats['total_interventions'],
            'success_rate'        => $stats['success_rate'],
            'most_effective_type' => '',
            'recommendations'     => array(),
            'trends'             => array(),
        );

        // Find most effective intervention type
        $best_rate = 0;
        foreach ( $stats['by_type'] as $type => $type_data ) {
            $successful = isset( $type_data['by_status']['successful'] ) ? $type_data['by_status']['successful'] : 0;
            $total = $type_data['total'];
            $rate = $total > 0 ? ( $successful / $total ) * 100 : 0;

            if ( $rate > $best_rate ) {
                $best_rate = $rate;
                $report['most_effective_type'] = $type;
            }
        }

        // Generate recommendations
        if ( $stats['success_rate'] < 50 ) {
            $report['recommendations'][] = __( 'Consider trying different intervention methods or timing', 'lms-analytics-pro' );
        }

        if ( isset( $stats['by_status']['failed'] ) && $stats['by_status']['failed'] > $stats['total_interventions'] * 0.3 ) {
            $report['recommendations'][] = __( 'High failure rate detected - review intervention content and delivery', 'lms-analytics-pro' );
        }

        // Get monthly trends (simplified)
        $report['trends'] = $this->lap_get_intervention_trends( $course_id, $months );

        return $report;
    }

    /**
     * Get intervention trends over time.
     *
     * @param int $course_id Optional course ID filter.
     * @param int $months    Number of months.
     * @return array Monthly trends.
     */
    private function lap_get_intervention_trends( $course_id, $months ) {
        global $wpdb;

        $table = $this->db->get_table( 'interventions' );

        $trends = array();
        for ( $i = $months - 1; $i >= 0; $i-- ) {
            $month_start = date( 'Y-m-01', strtotime( "-{$i} months" ) );
            $month_end = date( 'Y-m-t', strtotime( "-{$i} months" ) );

            $where = array( 'created_at >= %s', 'created_at <= %s' );
            $params = array( $month_start, $month_end );

            if ( $course_id > 0 ) {
                $where[] = 'course_id = %d';
                $params[] = $course_id;
            }

            $where_clause = implode( ' AND ', $where );

            $sql = $wpdb->prepare(
                "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'successful' THEN 1 ELSE 0 END) as successful
                FROM {$table}
                WHERE {$where_clause}",
                $params
            );

            $result = $wpdb->get_row( $sql, ARRAY_A );

            $trends[ date( 'M Y', strtotime( $month_start ) ) ] = array(
                'total'      => (int) $result['total'],
                'successful' => (int) $result['successful'],
                'rate'       => $result['total'] > 0 ? round( ( $result['successful'] / $result['total'] ) * 100, 1 ) : 0,
            );
        }

        return $trends;
    }

    /**
     * Export interventions data.
     *
     * @param int $course_id Optional course ID filter.
     * @param int $days      Number of days to export.
     * @return array Export data.
     */
    public function lap_export_interventions( $course_id = 0, $days = 30 ) {
        $interventions = $this->lap_get_student_interventions( 0, $course_id, 1000 ); // Get all for export

        $export_data = array();
        $export_data[] = array(
            'Intervention ID',
            'Student Name',
            'Student Email',
            'Course Name',
            'Type',
            'Message',
            'Status',
            'Created At',
            'Updated At',
        );

        foreach ( $interventions as $intervention ) {
            $student = get_userdata( $intervention['user_id'] );
            $course = get_post( $intervention['course_id'] );

            $export_data[] = array(
                $intervention['id'],
                $student ? $student->display_name : 'Unknown',
                $student ? $student->user_email : 'Unknown',
                $course ? $course->post_title : 'Unknown',
                $intervention['intervention_type'],
                $intervention['message'],
                $intervention['status'],
                $intervention['created_at'],
                $intervention['updated_at'] ?? '',
            );
        }

        return $export_data;
    }
}