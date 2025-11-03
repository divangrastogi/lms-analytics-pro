<?php
/**
 * PHPUnit bootstrap file for LMS Analytics Pro
 *
 * @package LMS_Analytics_Pro
 */

// Define test constants
define( 'LMS_ANALYTICS_PRO_VERSION', '1.0.0' );
define( 'LMS_ANALYTICS_PRO_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
define( 'LMS_ANALYTICS_PRO_PLUGIN_URL', 'http://example.com/wp-content/plugins/lms-analytics-pro/' );

// Load Composer autoloader if available
if ( file_exists( dirname( __DIR__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __DIR__ ) . '/vendor/autoload.php';
}

// Load WordPress test functions
if ( ! function_exists( 'wp_parse_args' ) ) {
    // Mock basic WordPress functions for testing
    function wp_parse_args( $args, $defaults = array() ) {
        if ( is_object( $args ) ) {
            $args = get_object_vars( $args );
        }
        if ( is_array( $args ) ) {
            return array_merge( $defaults, $args );
        }
        return $defaults;
    }

    function wp_json_encode( $data ) {
        return json_encode( $data );
    }

    function wp_unslash( $value ) {
        return is_string( $value ) ? stripslashes( $value ) : $value;
    }

    function absint( $maybeint ) {
        return abs( (int) $maybeint );
    }

    function sanitize_text_field( $str ) {
        return trim( strip_tags( $str ) );
    }

    function esc_html( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }

    function esc_attr( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }

    function esc_url( $url ) {
        return filter_var( $url, FILTER_SANITIZE_URL );
    }

    function __() {
        $args = func_get_args();
        return $args[0]; // Just return the string for testing
    }

    function _e() {
        $args = func_get_args();
        echo $args[0];
    }

    function esc_html__() {
        $args = func_get_args();
        return esc_html( $args[0] );
    }
}

// Mock WordPress database
global $wpdb;
$wpdb = (object) array(
    'prefix' => 'wp_',
    'prepare' => function( $query ) {
        $args = func_get_args();
        array_shift( $args );
        return vsprintf( str_replace( '%s', "'%s'", str_replace( '%d', '%d', $query ) ), $args );
    },
    'get_results' => function( $query, $output = OBJECT ) {
        // Mock some test data
        return array();
    },
    'get_var' => function( $query ) {
        return 0;
    },
    'replace' => function( $table, $data ) {
        return true;
    },
    'users' => 'wp_users',
);

// Load plugin files
require_once dirname( __DIR__ ) . '/includes/class-lap-db-manager.php';
require_once dirname( __DIR__ ) . '/includes/dropout/class-lap-risk-scorer.php';
require_once dirname( __DIR__ ) . '/includes/dropout/class-lap-activity-tracker.php';
require_once dirname( __DIR__ ) . '/includes/class-lap-cache-handler.php';