<?php
/**
 * Fired during plugin activation
 *
 * @link       https://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    LMS_Analytics_Pro
 * @subpackage LMS_Analytics_Pro/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    LMS_Analytics_Pro
 * @subpackage LMS_Analytics_Pro/includes
 * @author     Divang Rastogi <divang@wbcomdesigns.com>
 */

defined( 'ABSPATH' ) || exit;

class LAP_Activator {

    /**
     * Activate plugin.
     *
     * @since 1.0.0
     */
    public static function lap_activate() {
        // Check PHP version
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            deactivate_plugins( LAP_PLUGIN_BASENAME );
            wp_die( esc_html__( 'LMS Analytics Pro requires PHP 7.4 or higher.', 'lms-analytics-pro' ) );
        }

        // Check WordPress version
        if ( version_compare( get_bloginfo( 'version' ), '5.8', '<' ) ) {
            deactivate_plugins( LAP_PLUGIN_BASENAME );
            wp_die( esc_html__( 'LMS Analytics Pro requires WordPress 5.8 or higher.', 'lms-analytics-pro' ) );
        }

        // Check for required plugins
        if ( ! is_plugin_active( 'sfwd-lms/sfwd_lms.php' ) ) {
            deactivate_plugins( LAP_PLUGIN_BASENAME );
            wp_die( esc_html__( 'LMS Analytics Pro requires LearnDash to be installed and activated.', 'lms-analytics-pro' ) );
        }

        // Create database tables (disabled for now to avoid activation issues)
        // self::lap_create_tables();

        // Set default options
        self::lap_set_default_options();

        // Add capabilities
        self::lap_add_capabilities();

        // Schedule cron jobs
        self::lap_schedule_cron_jobs();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set activation flag
        set_transient( 'lap_activation_redirect', true, 30 );
    }

    /**
     * Create custom database tables.
     *
     * @since 1.0.0
     */
    private static function lap_create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Student progress table
        $sql = "CREATE TABLE {$wpdb->prefix}lap_student_progress (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            course_id BIGINT UNSIGNED NOT NULL,
            lesson_id BIGINT UNSIGNED NOT NULL,
            topic_id BIGINT UNSIGNED DEFAULT 0,
            completion_status TINYINT DEFAULT 0 COMMENT '0=not_started, 1=in_progress, 2=completed',
            completion_percentage DECIMAL(5,2) DEFAULT 0.00,
            time_spent_seconds INT UNSIGNED DEFAULT 0,
            last_activity DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_course (user_id, course_id),
            INDEX idx_lesson (lesson_id),
            INDEX idx_activity (last_activity),
            UNIQUE KEY unique_progress (user_id, lesson_id, topic_id)
        ) $charset_collate;";

        dbDelta( $sql );

        // Risk scores table
        $sql = "CREATE TABLE {$wpdb->prefix}lap_risk_scores (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            course_id BIGINT UNSIGNED NOT NULL,
            risk_score TINYINT UNSIGNED DEFAULT 0 COMMENT '0-100',
            risk_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'low',
            factors JSON COMMENT 'Breakdown of risk factors',
            last_login DATETIME NULL,
            days_inactive INT UNSIGNED DEFAULT 0,
            trend VARCHAR(20) DEFAULT 'stable' COMMENT 'improving|stable|declining',
            calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_risk_level (risk_level),
            INDEX idx_calculated (calculated_at),
            UNIQUE KEY unique_user_course (user_id, course_id)
        ) $charset_collate;";

        dbDelta( $sql );

        // Interventions table
        $sql = "CREATE TABLE {$wpdb->prefix}lap_interventions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            instructor_id BIGINT UNSIGNED NOT NULL,
            intervention_type ENUM('email', 'message', 'call', 'meeting', 'other') NOT NULL,
            message TEXT,
            status ENUM('sent', 'opened', 'replied', 'resolved') DEFAULT 'sent',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_instructor (instructor_id),
            INDEX idx_created (created_at)
        ) $charset_collate;";

        dbDelta( $sql );

        // Activity log table
        $sql = "CREATE TABLE {$wpdb->prefix}lap_activity_log (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            activity_type VARCHAR(50) NOT NULL COMMENT 'login|lesson_view|quiz_attempt|etc',
            course_id BIGINT UNSIGNED DEFAULT 0,
            lesson_id BIGINT UNSIGNED DEFAULT 0,
            metadata JSON,
            activity_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_activity (user_id, activity_timestamp),
            INDEX idx_type (activity_type)
        ) $charset_collate;";

        dbDelta( $sql );

        // Cache table
        $sql = "CREATE TABLE {$wpdb->prefix}lap_cache (
            cache_key VARCHAR(191) PRIMARY KEY,
            cache_value LONGTEXT NOT NULL,
            cache_group VARCHAR(50) DEFAULT 'default',
            expiration DATETIME NOT NULL,
            INDEX idx_expiration (expiration),
            INDEX idx_group (cache_group)
        ) $charset_collate;";

        dbDelta( $sql );

        // Update database version
        update_option( 'lap_db_version', '1.0.0' );

        return true;
    }

    /**
     * Set default plugin options.
     *
     * @since 1.0.0
     */
    private static function lap_set_default_options() {
        $defaults = array(
            'lap_inactivity_days'        => 7,
            'lap_risk_weights'           => array(
                'inactivity'  => 35,
                'velocity'    => 25,
                'quiz'        => 20,
                'forum'       => 10,
                'assignments' => 10,
            ),
            'lap_enable_notifications'   => true,
            'lap_notification_schedule'  => 'daily',
            'lap_cache_duration'         => 3600,
            'lap_default_color_scheme'   => 'blue',
            'lap_cells_per_page'         => 50,
        );

        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( $key ) ) {
                add_option( $key, $value );
            }
        }
    }

    /**
     * Add custom capabilities.
     *
     * @since 1.0.0
     */
    private static function lap_add_capabilities() {
        $admin_role = get_role( 'administrator' );
        $instructor_role = get_role( 'group_leader' ); // BuddyBoss

        $capabilities = array(
            'lap_view_analytics',      // View dashboards
            'lap_view_all_students',   // View all students (admin only)
            'lap_export_data',         // Export reports
            'lap_send_notifications',  // Send re-engagement messages
            'lap_manage_settings',     // Access settings (admin only)
        );

        foreach ( $capabilities as $cap ) {
            if ( $admin_role ) {
                $admin_role->add_cap( $cap );
            }

            // Instructors get limited permissions
            if ( in_array( $cap, array( 'lap_view_analytics', 'lap_export_data', 'lap_send_notifications' ) ) ) {
                if ( $instructor_role ) {
                    $instructor_role->add_cap( $cap );
                }
            }
        }
    }

    /**
     * Schedule cron jobs.
     *
     * @since 1.0.0
     */
    private static function lap_schedule_cron_jobs() {
        if ( ! wp_next_scheduled( 'lap_daily_risk_calculation' ) ) {
            wp_schedule_event( time(), 'daily', 'lap_daily_risk_calculation' );
        }

        if ( ! wp_next_scheduled( 'lap_cache_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'lap_cache_cleanup' );
        }
    }
}