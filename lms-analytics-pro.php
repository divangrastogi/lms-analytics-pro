<?php
/**
 * Plugin Name:       LMS Analytics Pro
 * Plugin URI:        https://example.com/lms-analytics-pro
 * Description:       Comprehensive student analytics with progress heatmaps and intelligent dropout detection for LearnDash and BuddyBoss.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Divang Rastogi
 * Author URI:        https://wbcomdesigns.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lms-analytics-pro
 * Domain Path:       /languages
 *
 * @package LMS_Analytics_Pro
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants
define( 'LAP_VERSION', '1.0.0' );
define( 'LAP_PLUGIN_FILE', __FILE__ );
define( 'LAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LAP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Include the main plugin class
require_once LAP_PLUGIN_DIR . 'includes/class-lap-core.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function lap_run_plugin() {
    $plugin = new LAP_Core();
    $plugin->lap_run();
}
lap_run_plugin();

/**
 * Plugin activation hook.
 */
function lap_activate_plugin() {
    require_once LAP_PLUGIN_DIR . 'includes/class-lap-activator.php';
    LAP_Activator::lap_activate();
}
register_activation_hook( LAP_PLUGIN_FILE, 'lap_activate_plugin' );

/**
 * Plugin deactivation hook.
 */
function lap_deactivate_plugin() {
    require_once LAP_PLUGIN_DIR . 'includes/class-lap-deactivator.php';
    LAP_Deactivator::lap_deactivate();
}
register_deactivation_hook( LAP_PLUGIN_FILE, 'lap_deactivate_plugin' );