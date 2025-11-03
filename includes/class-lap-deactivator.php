<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    LMS_Analytics_Pro
 * @subpackage LMS_Analytics_Pro/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    LMS_Analytics_Pro
 * @subpackage LMS_Analytics_Pro/includes
 * @author     Divang Rastogi <divang@wbcomdesigns.com>
 */

defined( 'ABSPATH' ) || exit;

class LAP_Deactivator {

    /**
     * Deactivate plugin.
     *
     * @since 1.0.0
     */
    public static function lap_deactivate() {
        // Clear scheduled cron jobs
        $timestamp = wp_next_scheduled( 'lap_daily_risk_calculation' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'lap_daily_risk_calculation' );
        }

        $timestamp = wp_next_scheduled( 'lap_cache_cleanup' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'lap_cache_cleanup' );
        }

        // Clear any transients
        delete_transient( 'lap_activation_redirect' );

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}