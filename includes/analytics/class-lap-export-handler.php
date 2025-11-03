<?php
/**
 * Export functionality for analytics data
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Analytics
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LAP_Export_Handler class.
 */
class LAP_Export_Handler {

    /**
     * Export heatmap data to CSV.
     *
     * @param array $data     Heatmap data.
     * @param array $options  Export options.
     * @return string File path to generated CSV.
     */
    public function lap_export_to_csv( $data, $options = array() ) {
        $defaults = array(
            'filename' => 'heatmap-' . date( 'Y-m-d-His' ) . '.csv',
            'delimiter' => ',',
        );

        $options = wp_parse_args( $options, $defaults );

        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['path'] . '/' . $options['filename'];

        $fp = fopen( $filepath, 'w' );

        if ( false === $fp ) {
            return false;
        }

        // Header row
        $headers = array( 'Student Name', 'Email' );
        if ( ! empty( $data['lessons'] ) ) {
            foreach ( $data['lessons'] as $lesson ) {
                $headers[] = $lesson['title'];
            }
        }
        $headers[] = 'Average Completion %';
        $headers[] = 'Risk Score';
        $headers[] = 'Last Activity';

        fputcsv( $fp, $headers, $options['delimiter'] );

        // Data rows
        if ( ! empty( $data['students'] ) ) {
            foreach ( $data['students'] as $student ) {
                $row = array(
                    $student['name'],
                    $student['email'],
                );

                if ( ! empty( $data['lessons'] ) ) {
                    foreach ( $data['lessons'] as $lesson ) {
                        $progress = isset( $student['progress'][ $lesson['id'] ] ) ? $student['progress'][ $lesson['id'] ] : array( 'completion_percentage' => 0 );
                        $row[] = $progress['completion_percentage'] . '%';
                    }
                }

                $row[] = $student['average_completion'] . '%';
                $row[] = $student['risk_score'];
                $row[] = $student['last_activity'] ? date( 'Y-m-d H:i:s', strtotime( $student['last_activity'] ) ) : '';

                fputcsv( $fp, $row, $options['delimiter'] );
            }
        }

        fclose( $fp );

        return $filepath;
    }

    /**
     * Export heatmap data to Excel (XLSX).
     *
     * @param array $data    Heatmap data.
     * @param array $options Export options.
     * @return string File path to generated Excel file.
     */
    public function lap_export_to_excel( $data, $options = array() ) {
        // For now, fall back to CSV since we don't have a full Excel library
        // In production, you would use a library like PhpSpreadsheet
        return $this->lap_export_to_csv( $data, $options );
    }

    /**
     * Export data to PDF.
     *
     * @param array $data    Data to export.
     * @param array $options Export options.
     * @return string File path to generated PDF.
     */
    public function lap_export_to_pdf( $data, $options = array() ) {
        $defaults = array(
            'filename' => 'analytics-report-' . date( 'Y-m-d-His' ) . '.pdf',
            'title'    => 'LMS Analytics Report',
        );

        $options = wp_parse_args( $options, $defaults );

        // Check if TCPDF is available (would need to be installed via composer)
        if ( ! class_exists( 'TCPDF' ) ) {
            // Fall back to basic HTML-to-PDF approach or error
            return false;
        }

        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['path'] . '/' . $options['filename'];

        $pdf = new TCPDF( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

        $pdf->SetCreator( 'LMS Analytics Pro' );
        $pdf->SetAuthor( 'WBCom Designs' );
        $pdf->SetTitle( $options['title'] );

        $pdf->AddPage();

        // Title
        $pdf->SetFont( 'helvetica', 'B', 16 );
        $pdf->Cell( 0, 10, $options['title'], 0, 1, 'C' );
        $pdf->Ln( 10 );

        // Metadata
        $pdf->SetFont( 'helvetica', '', 10 );
        if ( isset( $data['metadata'] ) ) {
            $pdf->Cell( 0, 5, 'Generated: ' . date( 'F j, Y g:i a' ), 0, 1 );
            $pdf->Cell( 0, 5, 'Total Students: ' . $data['metadata']['total_students'], 0, 1 );
            $pdf->Cell( 0, 5, 'Average Completion: ' . $data['metadata']['average_completion'] . '%', 0, 1 );
            $pdf->Ln( 10 );
        }

        // Generate table HTML
        $html = $this->lap_generate_pdf_table_html( $data );
        $pdf->writeHTML( $html, true, false, true, false, '' );

        $pdf->Output( $filepath, 'F' );

        return $filepath;
    }

    /**
     * Generate HTML table for PDF export.
     *
     * @param array $data Heatmap data.
     * @return string HTML table.
     */
    private function lap_generate_pdf_table_html( $data ) {
        $html = '<table border="1" cellpadding="4">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th><strong>Student Name</strong></th>
                    <th><strong>Email</strong></th>';

        if ( ! empty( $data['lessons'] ) ) {
            foreach ( $data['lessons'] as $lesson ) {
                $html .= '<th><strong>' . esc_html( $lesson['title'] ) . '</strong></th>';
            }
        }

        $html .= '<th><strong>Avg %</strong></th>
                <th><strong>Risk Score</strong></th>
                </tr>
            </thead>
            <tbody>';

        if ( ! empty( $data['students'] ) ) {
            foreach ( $data['students'] as $student ) {
                $html .= '<tr>
                    <td>' . esc_html( $student['name'] ) . '</td>
                    <td>' . esc_html( $student['email'] ) . '</td>';

                if ( ! empty( $data['lessons'] ) ) {
                    foreach ( $data['lessons'] as $lesson ) {
                        $progress = isset( $student['progress'][ $lesson['id'] ] ) ? $student['progress'][ $lesson['id'] ] : array( 'completion_percentage' => 0 );
                        $html .= '<td>' . $progress['completion_percentage'] . '%</td>';
                    }
                }

                $html .= '<td>' . $student['average_completion'] . '%</td>
                    <td>' . $student['risk_score'] . '</td>
                </tr>';
            }
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Export at-risk students data.
     *
     * @param array $data    At-risk students data.
     * @param array $options Export options.
     * @return string File path to generated file.
     */
    public function lap_export_risk_students( $data, $options = array() ) {
        $defaults = array(
            'filename' => 'at-risk-students-' . date( 'Y-m-d-His' ) . '.csv',
            'format'   => 'csv',
        );

        $options = wp_parse_args( $options, $defaults );

        if ( 'csv' === $options['format'] ) {
            return $this->lap_export_risk_students_csv( $data, $options );
        }

        return false;
    }

    /**
     * Export at-risk students to CSV.
     *
     * @param array $data    At-risk students data.
     * @param array $options Export options.
     * @return string File path to generated CSV.
     */
    private function lap_export_risk_students_csv( $data, $options ) {
        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['path'] . '/' . $options['filename'];

        $fp = fopen( $filepath, 'w' );

        if ( false === $fp ) {
            return false;
        }

        // Header row
        $headers = array(
            'Student Name',
            'Email',
            'Risk Score',
            'Risk Level',
            'Last Login',
            'Days Inactive',
            'Completion %',
            'Trend',
        );

        fputcsv( $fp, $headers );

        // Data rows
        if ( ! empty( $data ) ) {
            foreach ( $data as $student ) {
                $row = array(
                    $student['display_name'],
                    $student['user_email'],
                    $student['risk_score'],
                    $student['risk_level'],
                    $student['last_login'] ? date( 'Y-m-d', strtotime( $student['last_login'] ) ) : '',
                    $student['days_inactive'],
                    isset( $student['completion_percentage'] ) ? $student['completion_percentage'] . '%' : '',
                    $student['trend'],
                );

                fputcsv( $fp, $row );
            }
        }

        fclose( $fp );

        return $filepath;
    }

    /**
     * Send exported file to user.
     *
     * @param string $filepath File path.
     * @param string $filename Desired filename.
     */
    public function lap_send_file_download( $filepath, $filename ) {
        if ( ! file_exists( $filepath ) ) {
            wp_die( esc_html__( 'File not found.', 'lms-analytics-pro' ) );
        }

        $file_size = filesize( $filepath );
        $file_info = wp_check_filetype( $filepath );
        $content_type = $file_info['type'] ?: 'application/octet-stream';

        header( 'Content-Type: ' . $content_type );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Content-Length: ' . $file_size );
        header( 'Cache-Control: private, max-age=0, must-revalidate' );
        header( 'Pragma: public' );

        readfile( $filepath );

        // Clean up file after download (optional)
        // unlink( $filepath );

        exit;
    }

    /**
     * Get supported export formats.
     *
     * @return array Supported formats.
     */
    public function lap_get_supported_formats() {
        return array(
            'csv'  => __( 'CSV', 'lms-analytics-pro' ),
            'xlsx' => __( 'Excel (XLSX)', 'lms-analytics-pro' ),
            'pdf'  => __( 'PDF', 'lms-analytics-pro' ),
        );
    }

    /**
     * Validate export options.
     *
     * @param array $options Export options.
     * @return array Validated options.
     */
    public function lap_validate_export_options( $options ) {
        $supported_formats = array_keys( $this->lap_get_supported_formats() );

        $validated = array(
            'format' => in_array( $options['format'] ?? 'csv', $supported_formats, true ) ? $options['format'] : 'csv',
            'filename' => sanitize_file_name( $options['filename'] ?? '' ),
        );

        // Generate filename if not provided
        if ( empty( $validated['filename'] ) ) {
            $validated['filename'] = 'export-' . date( 'Y-m-d-His' ) . '.' . $validated['format'];
        }

        return $validated;
    }

    /**
     * Export intervention data.
     *
     * @param array $data    Intervention data.
     * @param array $options Export options.
     * @return string File path to generated file.
     */
    public function lap_export_interventions( $data, $options = array() ) {
        $defaults = array(
            'filename' => 'interventions-' . date( 'Y-m-d-His' ) . '.csv',
            'format'   => 'csv',
        );

        $options = wp_parse_args( $options, $defaults );

        if ( 'csv' === $options['format'] ) {
            return $this->lap_export_interventions_csv( $data, $options );
        }

        return false;
    }

    /**
     * Export interventions to CSV.
     *
     * @param array $data    Intervention data.
     * @param array $options Export options.
     * @return string File path to generated CSV.
     */
    private function lap_export_interventions_csv( $data, $options ) {
        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['path'] . '/' . $options['filename'];

        $fp = fopen( $filepath, 'w' );

        if ( false === $fp ) {
            return false;
        }

        // Header row
        $headers = array(
            'Intervention ID',
            'Student Name',
            'Student Email',
            'Course Name',
            'Type',
            'Message',
            'Status',
            'Created At',
            'Updated At',
            'Instructor',
        );

        fputcsv( $fp, $headers );

        // Data rows
        if ( ! empty( $data ) ) {
            foreach ( $data as $intervention ) {
                $instructor = get_userdata( $intervention['instructor_id'] );

                $row = array(
                    $intervention['id'],
                    $intervention['student_name'] ?: 'Unknown',
                    $intervention['student_email'] ?: 'Unknown',
                    $intervention['course_name'] ?: 'Unknown',
                    $intervention['intervention_type'],
                    $intervention['message'],
                    $intervention['status'],
                    $intervention['created_at'],
                    $intervention['updated_at'] ?: '',
                    $instructor ? $instructor->display_name : 'System',
                );

                fputcsv( $fp, $row );
            }
        }

        fclose( $fp );

        return $filepath;
    }

    /**
     * Export course analytics summary.
     *
     * @param array $data    Course analytics data.
     * @param array $options Export options.
     * @return string File path to generated file.
     */
    public function lap_export_course_analytics( $data, $options = array() ) {
        $defaults = array(
            'filename' => 'course-analytics-' . date( 'Y-m-d-His' ) . '.csv',
            'format'   => 'csv',
        );

        $options = wp_parse_args( $options, $defaults );

        if ( 'csv' === $options['format'] ) {
            return $this->lap_export_course_analytics_csv( $data, $options );
        }

        return false;
    }

    /**
     * Export course analytics to CSV.
     *
     * @param array $data    Course analytics data.
     * @param array $options Export options.
     * @return string File path to generated CSV.
     */
    private function lap_export_course_analytics_csv( $data, $options ) {
        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['path'] . '/' . $options['filename'];

        $fp = fopen( $filepath, 'w' );

        if ( false === $fp ) {
            return false;
        }

        // Header row
        $headers = array( 'Metric', 'Value', 'Description' );
        fputcsv( $fp, $headers );

        // Data rows
        if ( ! empty( $data ) ) {
            foreach ( $data as $row ) {
                fputcsv( $fp, $row );
            }
        }

        fclose( $fp );

        return $filepath;
    }

    /**
     * Export heatmap data with time series.
     *
     * @param array $data    Heatmap time series data.
     * @param array $options Export options.
     * @return string File path to generated file.
     */
    public function lap_export_heatmap_timeseries( $data, $options = array() ) {
        $defaults = array(
            'filename' => 'heatmap-timeseries-' . date( 'Y-m-d-His' ) . '.csv',
            'format'   => 'csv',
        );

        $options = wp_parse_args( $options, $defaults );

        if ( 'csv' === $options['format'] ) {
            return $this->lap_export_heatmap_timeseries_csv( $data, $options );
        }

        return false;
    }

    /**
     * Export heatmap time series to CSV.
     *
     * @param array $data    Heatmap time series data.
     * @param array $options Export options.
     * @return string File path to generated CSV.
     */
    private function lap_export_heatmap_timeseries_csv( $data, $options ) {
        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['path'] . '/' . $options['filename'];

        $fp = fopen( $filepath, 'w' );

        if ( false === $fp ) {
            return false;
        }

        // Header row
        $headers = array(
            'Date',
            'Day',
            'Total Activities',
            'Lesson Views',
            'Quiz Attempts',
            'Completions',
            'Logins',
            'Forum Posts',
        );

        fputcsv( $fp, $headers );

        // Data rows
        if ( ! empty( $data ) ) {
            foreach ( $data as $day_data ) {
                $row = array(
                    $day_data['date'],
                    date( 'l', strtotime( $day_data['date'] ) ),
                    $day_data['total_activities'],
                    $day_data['lesson_views'] ?? 0,
                    $day_data['quiz_attempts'] ?? 0,
                    $day_data['completions'] ?? 0,
                    $day_data['logins'] ?? 0,
                    $day_data['forum_posts'] ?? 0,
                );

                fputcsv( $fp, $row );
            }
        }

        fclose( $fp );

        return $filepath;
    }

    /**
     * Get export file URL for download.
     *
     * @param string $filepath File path.
     * @return string File URL.
     */
    public function lap_get_export_url( $filepath ) {
        $upload_dir = wp_upload_dir();
        return str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $filepath );
    }

    /**
     * Clean up old export files.
     *
     * @param int $days_old Days to keep files.
     * @return int Number of files deleted.
     */
    public function lap_cleanup_old_exports( $days_old = 7 ) {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/lap_exports';

        if ( ! file_exists( $export_dir ) ) {
            return 0;
        }

        $files = glob( $export_dir . '/*' );
        $deleted = 0;
        $cutoff_time = time() - ( $days_old * DAY_IN_SECONDS );

        foreach ( $files as $file ) {
            if ( filemtime( $file ) < $cutoff_time ) {
                unlink( $file );
                $deleted++;
            }
        }

        return $deleted;
    }
}