<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    LMS_Analytics_Pro
 * @subpackage LMS_Analytics_Pro/includes
 * @author     Divang Rastogi <divang@wbcomdesigns.com>
 */

defined( 'ABSPATH' ) || exit;

class LAP_Core {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    LAP_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function __construct() {
        if ( defined( 'LAP_VERSION' ) ) {
            $this->version = LAP_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'lms-analytics-pro';

        $this->lap_load_dependencies();
        $this->lap_set_locale();
        $this->lap_define_admin_hooks();
        $this->lap_define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - LAP_Loader. Orchestrates the hooks of the plugin.
     * - LAP_I18n. Defines internationalization functionality.
     * - LAP_Admin. Defines all hooks for the admin area.
     * - LAP_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since  1.0.0
     * @access private
     */
    private function lap_load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * entire plugin.
         */
        require_once LAP_PLUGIN_DIR . 'includes/class-lap-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once LAP_PLUGIN_DIR . 'includes/class-lap-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once LAP_PLUGIN_DIR . 'admin/class-lap-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once LAP_PLUGIN_DIR . 'public/class-lap-public.php';

        /**
         * Core classes.
         */
        require_once LAP_PLUGIN_DIR . 'includes/class-lap-cache-handler.php';
        require_once LAP_PLUGIN_DIR . 'includes/database/class-lap-db-manager.php';

        /**
         * Analytics classes.
         */
        require_once LAP_PLUGIN_DIR . 'includes/analytics/class-lap-data-aggregator.php';
        require_once LAP_PLUGIN_DIR . 'includes/analytics/class-lap-export-handler.php';
        require_once LAP_PLUGIN_DIR . 'includes/analytics/class-lap-buddyboss-analytics.php';
        require_once LAP_PLUGIN_DIR . 'includes/analytics/class-lap-progress-calculator.php';
        require_once LAP_PLUGIN_DIR . 'includes/analytics/class-lap-heatmap-engine.php';

        /**
         * Dropout detection classes.
         */
        require_once LAP_PLUGIN_DIR . 'includes/dropout/class-lap-risk-scorer.php';
        require_once LAP_PLUGIN_DIR . 'includes/dropout/class-lap-activity-tracker.php';
        require_once LAP_PLUGIN_DIR . 'includes/dropout/class-lap-notification-manager.php';
        require_once LAP_PLUGIN_DIR . 'includes/dropout/class-lap-intervention-logger.php';

        $this->loader = new LAP_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the LAP_I18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since  1.0.0
     * @access private
     */
    private function lap_set_locale() {

        $plugin_i18n = new LAP_I18n();

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'lap_load_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     */
    private function lap_define_admin_hooks() {

        $plugin_admin = new LAP_Admin( $this->lap_get_plugin_name(), $this->lap_get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'lap_enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'lap_enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'lap_add_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'lap_register_settings' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'lap_register_ajax_handlers' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     */
    private function lap_define_public_hooks() {

        $plugin_public = new LAP_Public( $this->lap_get_plugin_name(), $this->lap_get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'lap_enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'lap_enqueue_scripts' );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since 1.0.0
     */
    public function lap_run() {
        $this->loader->lap_run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since  1.0.0
     * @return string The name of the plugin.
     */
    public function lap_get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since  1.0.0
     * @return LAP_Loader Orchestrates the hooks of the plugin.
     */
    public function lap_get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since  1.0.0
     * @return string The version number of the plugin.
     */
    public function lap_get_version() {
        return $this->version;
    }
}