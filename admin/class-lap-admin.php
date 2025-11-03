<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    LMS_Analytics_Pro
 * @subpackage LMS_Analytics_Pro/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    LMS_Analytics_Pro
 * @subpackage LMS_Analytics_Pro/admin
 * @author     Divang Rastogi <divang@wbcomdesigns.com>
 */

defined( 'ABSPATH' ) || exit;

class LAP_Admin {

    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string $version The current version of this plugin.
     */
    private $version;

    /**
     * Database manager instance.
     *
     * @since  1.0.0
     * @access private
     * @var    LAP_DB_Manager $db Database manager.
     */
    private $db;

    /**
     * Risk scorer instance.
     *
     * @since  1.0.0
     * @access private
     * @var    LAP_Risk_Scorer $risk_scorer Risk scorer.
     */
    private $risk_scorer;

    /**
     * Activity tracker instance.
     *
     * @since  1.0.0
     * @access private
     * @var    LAP_Activity_Tracker $activity_tracker Activity tracker.
     */
    private $activity_tracker;

    /**
     * Notification manager instance.
     *
     * @since  1.0.0
     * @access private
     * @var    LAP_Notification_Manager $notification_manager Notification manager.
     */
    private $notification_manager;

    /**
     * Intervention logger instance.
     *
     * @since  1.0.0
     * @access private
     * @var    LAP_Intervention_Logger $intervention_logger Intervention logger.
     */
    private $intervention_logger;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        // Lazy load database manager
    }

    /**
     * Get database manager instance.
     *
     * @since 1.0.0
     * @return LAP_DB_Manager Database manager.
     */
    private function get_db() {
        if ( ! $this->db ) {
            $this->db = new LAP_DB_Manager();
        }
        return $this->db;
    }

    /**
     * Get risk scorer instance.
     *
     * @since 1.0.0
     * @return LAP_Risk_Scorer Risk scorer.
     */
    private function get_risk_scorer() {
        if ( ! $this->risk_scorer ) {
            $calculator = new LAP_Progress_Calculator( $this->get_db() );
            $this->risk_scorer = new LAP_Risk_Scorer( $this->get_db(), $calculator );
        }
        return $this->risk_scorer;
    }

    /**
     * Get activity tracker instance.
     *
     * @since 1.0.0
     * @return LAP_Activity_Tracker Activity tracker.
     */
    private function get_activity_tracker() {
        if ( ! $this->activity_tracker ) {
            $this->activity_tracker = new LAP_Activity_Tracker( $this->get_db() );
        }
        return $this->activity_tracker;
    }

    /**
     * Get notification manager instance.
     *
     * @since 1.0.0
     * @return LAP_Notification_Manager Notification manager.
     */
    private function get_notification_manager() {
        if ( ! $this->notification_manager ) {
            $this->notification_manager = new LAP_Notification_Manager( $this->get_db() );
        }
        return $this->notification_manager;
    }

    /**
     * Get intervention logger instance.
     *
     * @since 1.0.0
     * @return LAP_Intervention_Logger Intervention logger.
     */
    private function get_intervention_logger() {
        if ( ! $this->intervention_logger ) {
            $this->intervention_logger = new LAP_Intervention_Logger( $this->get_db() );
        }
        return $this->intervention_logger;
    }

    /**
     * Register settings.
     *
     * @since 1.0.0
     */
    public function lap_register_settings() {
        // Register settings group
        register_setting(
            'lap_settings_group',
            'lap_inactivity_days',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 7,
            )
        );

        register_setting(
            'lap_settings_group',
            'lap_enable_notifications',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => function( $value ) {
                    return $value ? 1 : 0;
                },
                'default'           => 1,
            )
        );

        register_setting(
            'lap_settings_group',
            'lap_cache_duration',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 3600,
            )
        );

        register_setting(
            'lap_settings_group',
            'lap_risk_weights',
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'lap_sanitize_risk_weights' ),
                'default'           => array(
                    'inactivity'  => 35,
                    'velocity'    => 25,
                    'quiz'        => 20,
                    'forum'       => 10,
                    'assignments' => 10,
                ),
            )
        );

        register_setting(
            'lap_settings_group',
            'lap_risk_thresholds',
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'lap_sanitize_risk_thresholds' ),
                'default'           => array(
                    'low'      => 25,
                    'medium'   => 50,
                    'high'     => 75,
                    'critical' => 90,
                ),
            )
        );

        register_setting(
            'lap_settings_group',
            'lap_notification_schedule',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'daily',
            )
        );

        register_setting(
            'lap_settings_group',
            'lap_debug_mode',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => function( $value ) {
                    return $value ? 1 : 0;
                },
                'default'           => 0,
            )
        );

        // Add settings sections
        add_settings_section(
            'lap_general_settings',
            __( 'General Settings', 'lms-analytics-pro' ),
            array( $this, 'lap_general_settings_callback' ),
            'lap_settings'
        );

        add_settings_section(
            'lap_notification_settings',
            __( 'Notification Settings', 'lms-analytics-pro' ),
            array( $this, 'lap_notification_settings_callback' ),
            'lap_settings'
        );

        add_settings_section(
            'lap_advanced_settings',
            __( 'Advanced Settings', 'lms-analytics-pro' ),
            array( $this, 'lap_advanced_settings_callback' ),
            'lap_settings'
        );
    }

    /**
     * Sanitize risk weights.
     *
     * @param array $weights Raw weights.
     * @return array Sanitized weights.
     */
    public function lap_sanitize_risk_weights( $weights ) {
        if ( ! is_array( $weights ) ) {
            return array(
                'inactivity'  => 35,
                'velocity'    => 25,
                'quiz'        => 20,
                'forum'       => 10,
                'assignments' => 10,
            );
        }

        $sanitized = array();
        $total = 0;

        foreach ( $weights as $key => $value ) {
            $sanitized[ sanitize_key( $key ) ] = absint( $value );
            $total += $sanitized[ $key ];
        }

        // Ensure total equals 100
        if ( $total !== 100 ) {
            // Normalize to 100
            foreach ( $sanitized as $key => $value ) {
                $sanitized[ $key ] = round( ( $value / $total ) * 100 );
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize risk thresholds.
     *
     * @param array $thresholds Raw thresholds.
     * @return array Sanitized thresholds.
     */
    public function lap_sanitize_risk_thresholds( $thresholds ) {
        if ( ! is_array( $thresholds ) ) {
            return array(
                'low'      => 25,
                'medium'   => 50,
                'high'     => 75,
                'critical' => 90,
            );
        }

        $sanitized = array();
        foreach ( $thresholds as $key => $value ) {
            $sanitized[ sanitize_key( $key ) ] = absint( $value );
        }

        // Ensure logical order
        $sanitized['low'] = min( $sanitized['low'], $sanitized['medium'] - 1 );
        $sanitized['medium'] = min( $sanitized['medium'], $sanitized['high'] - 1 );
        $sanitized['high'] = min( $sanitized['high'], $sanitized['critical'] - 1 );

        return $sanitized;
    }

    /**
     * General settings section callback.
     */
    public function lap_general_settings_callback() {
        echo '<p>' . esc_html__( 'Configure general plugin settings.', 'lms-analytics-pro' ) . '</p>';
    }

    /**
     * Notification settings section callback.
     */
    public function lap_notification_settings_callback() {
        echo '<p>' . esc_html__( 'Configure notification and alert settings.', 'lms-analytics-pro' ) . '</p>';
    }

    /**
     * Advanced settings section callback.
     */
    public function lap_advanced_settings_callback() {
        echo '<p>' . esc_html__( 'Advanced configuration options.', 'lms-analytics-pro' ) . '</p>';
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since 1.0.0
     */
    public function lap_enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in LAP_Core as all of the hooks are defined
         * in that particular class.
         *
         * The LAP_Core will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, LAP_PLUGIN_URL . 'admin/css/lap-admin.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 1.0.0
     */
    public function lap_enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name, LAP_PLUGIN_URL . 'admin/js/lap-admin.js', array( 'jquery' ), $this->version, false );

        wp_localize_script(
            $this->plugin_name,
            'lapAdminAjax',
            array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'lap_admin_nonce' ),
            )
        );
    }

    /**
     * Add admin menu.
     *
     * @since 1.0.0
     */
    public function lap_add_admin_menu() {
        add_menu_page(
            __( 'LMS Analytics Pro', 'lms-analytics-pro' ),
            __( 'LMS Analytics', 'lms-analytics-pro' ),
            'lap_view_analytics',
            'lap-dashboard',
            array( $this, 'lap_display_dashboard' ),
            'dashicons-chart-bar',
            30
        );

        add_submenu_page(
            'lap-dashboard',
            __( 'Dashboard', 'lms-analytics-pro' ),
            __( 'Dashboard', 'lms-analytics-pro' ),
            'lap_view_analytics',
            'lap-dashboard',
            array( $this, 'lap_display_dashboard' )
        );

        add_submenu_page(
            'lap-dashboard',
            __( 'Heatmap', 'lms-analytics-pro' ),
            __( 'Progress Heatmap', 'lms-analytics-pro' ),
            'lap_view_analytics',
            'lap-heatmap',
            array( $this, 'lap_display_heatmap' )
        );

        add_submenu_page(
            'lap-dashboard',
            __( 'Dropout Detector', 'lms-analytics-pro' ),
            __( 'Dropout Detector', 'lms-analytics-pro' ),
            'lap_view_analytics',
            'lap-dropout',
            array( $this, 'lap_display_dropout' )
        );

        add_submenu_page(
            'lap-dashboard',
            __( 'Settings', 'lms-analytics-pro' ),
            __( 'Settings', 'lms-analytics-pro' ),
            'lap_manage_settings',
            'lap-settings',
            array( $this, 'lap_display_settings' )
        );
    }

    /**
     * Display dashboard page.
     *
     * @since 1.0.0
     */
    public function lap_display_dashboard() {
        if ( ! current_user_can( 'lap_view_analytics' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'lms-analytics-pro' ) );
        }

        include_once LAP_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Display heatmap page.
     *
     * @since 1.0.0
     */
    public function lap_display_heatmap() {
        if ( ! current_user_can( 'lap_view_analytics' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'lms-analytics-pro' ) );
        }

        include_once LAP_PLUGIN_DIR . 'admin/views/heatmap.php';
    }

    /**
     * Display dropout detector page.
     *
     * @since 1.0.0
     */
    public function lap_display_dropout() {
        if ( ! current_user_can( 'lap_view_analytics' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'lms-analytics-pro' ) );
        }

        include_once LAP_PLUGIN_DIR . 'admin/views/dropout-detector.php';
    }

    /**
     * Display settings page.
     *
     * @since 1.0.0
     */
    public function lap_display_settings() {
        if ( ! current_user_can( 'lap_manage_settings' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'lms-analytics-pro' ) );
        }

        include_once LAP_PLUGIN_DIR . 'admin/views/settings.php';
    }

    /**
     * Register AJAX handlers.
     *
     * @since 1.0.0
     */
    public function lap_register_ajax_handlers() {
        add_action( 'wp_ajax_lap_get_heatmap_data', array( $this, 'lap_ajax_get_heatmap_data' ) );
        add_action( 'wp_ajax_lap_get_at_risk_students', array( $this, 'lap_ajax_get_at_risk_students' ) );
        add_action( 'wp_ajax_lap_calculate_risk_score', array( $this, 'lap_ajax_calculate_risk_score' ) );
        add_action( 'wp_ajax_lap_send_intervention', array( $this, 'lap_ajax_send_intervention' ) );
        add_action( 'wp_ajax_lap_get_intervention_stats', array( $this, 'lap_ajax_get_intervention_stats' ) );
    }

    /**
     * AJAX handler for heatmap data.
     *
     * @since 1.0.0
     */
    public function lap_ajax_get_heatmap_data() {
        check_ajax_referer( 'lap_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'lap_view_analytics' ) ) {
            wp_send_json_error( array(
                'message' => __( 'You do not have permission to view analytics.', 'lms-analytics-pro' ),
            ), 403 );
        }

        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 50;
        $filters = isset( $_POST['filters'] ) ? json_decode( wp_unslash( $_POST['filters'] ), true ) : array();
        $filters = $this->lap_sanitize_filters( $filters );

        $args = array(
            'limit'  => $per_page,
            'offset' => ( $page - 1 ) * $per_page,
        );

        $data = $this->get_db()->get_heatmap_data( $filters, $args );

        // Get total count for pagination
        $total_count = $this->get_db()->get_heatmap_data_count( $filters );

        wp_send_json_success( array(
            'data'        => $data,
            'total_count' => $total_count,
            'page'        => $page,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_count / $per_page ),
        ) );
    }

    /**
     * AJAX handler for at-risk students.
     *
     * @since 1.0.0
     */
    public function lap_ajax_get_at_risk_students() {
        check_ajax_referer( 'lap_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'lap_view_analytics' ) ) {
            wp_send_json_error( array(
                'message' => __( 'You do not have permission to view analytics.', 'lms-analytics-pro' ),
            ), 403 );
        }

        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 20;
        $course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;
        $risk_level = isset( $_POST['risk_level'] ) ? sanitize_text_field( $_POST['risk_level'] ) : '';

        $args = array(
            'min_risk_score' => 50,
            'limit'          => $per_page,
            'offset'         => ( $page - 1 ) * $per_page,
        );

        if ( $course_id > 0 ) {
            $args['course_id'] = $course_id;
        }

        if ( ! empty( $risk_level ) ) {
            $args['risk_level'] = $risk_level;
        }

        $data = $this->db->get_at_risk_students( $args );

        // Get total count for pagination
        $total_count = $this->db->get_at_risk_students_count( $args );

        wp_send_json_success( array(
            'data'        => $data,
            'total_count' => $total_count,
            'page'        => $page,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_count / $per_page ),
        ) );
    }

    /**
     * Sanitize filter inputs.
     *
     * @since 1.0.0
     * @param array $filters Raw filters.
     * @return array Sanitized filters.
     */
    private function lap_sanitize_filters( $filters ) {
        $clean = array();

        if ( isset( $filters['course_id'] ) ) {
            $clean['course_id'] = absint( $filters['course_id'] );
        }

        if ( isset( $filters['group_id'] ) ) {
            $clean['group_id'] = absint( $filters['group_id'] );
        }

        if ( isset( $filters['date_from'] ) ) {
            $clean['date_from'] = sanitize_text_field( $filters['date_from'] );
        }

        if ( isset( $filters['date_to'] ) ) {
            $clean['date_to'] = sanitize_text_field( $filters['date_to'] );
        }

        return $clean;
    }

    /**
     * AJAX handler for calculating risk score.
     *
     * @since 1.0.0
     */
    public function lap_ajax_calculate_risk_score() {
        check_ajax_referer( 'lap_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'lap_view_analytics' ) ) {
            wp_send_json_error( array(
                'message' => __( 'You do not have permission to calculate risk scores.', 'lms-analytics-pro' ),
            ), 403 );
        }

        $user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
        $course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;

        if ( ! $user_id || ! $course_id ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid user or course ID.', 'lms-analytics-pro' ),
            ) );
        }

        $risk_data = $this->get_risk_scorer()->lap_calculate_risk_score( $user_id, $course_id );

        // Store the risk score
        $this->get_db()->upsert_risk_score( array(
            'user_id'     => $user_id,
            'course_id'   => $course_id,
            'risk_score'  => $risk_data['score'],
            'risk_level'  => $risk_data['level'],
            'factors'     => maybe_serialize( $risk_data['factors'] ),
            'calculated_at' => $risk_data['calculated_at'],
        ) );

        wp_send_json_success( array(
            'risk_data' => $risk_data,
        ) );
    }

    /**
     * AJAX handler for sending intervention.
     *
     * @since 1.0.0
     */
    public function lap_ajax_send_intervention() {
        check_ajax_referer( 'lap_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'lap_manage_interventions' ) ) {
            wp_send_json_error( array(
                'message' => __( 'You do not have permission to send interventions.', 'lms-analytics-pro' ),
            ), 403 );
        }

        $user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
        $course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;
        $intervention_type = isset( $_POST['intervention_type'] ) ? sanitize_key( $_POST['intervention_type'] ) : 'email';

        if ( ! $user_id || ! $course_id ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid user or course ID.', 'lms-analytics-pro' ),
            ) );
        }

        $risk_data = $this->get_risk_scorer()->lap_calculate_risk_score( $user_id, $course_id );

        $success = false;
        if ( 'email' === $intervention_type ) {
            $success = $this->get_notification_manager()->lap_send_risk_alert( $user_id, $course_id, $risk_data );
        } elseif ( 'reengagement' === $intervention_type ) {
            $success = $this->get_notification_manager()->lap_send_reengagement_message( $user_id, $course_id, $risk_data );
        }

        if ( $success ) {
            wp_send_json_success( array(
                'message' => __( 'Intervention sent successfully.', 'lms-analytics-pro' ),
            ) );
        } else {
            wp_send_json_error( array(
                'message' => __( 'Failed to send intervention.', 'lms-analytics-pro' ),
            ) );
        }
    }

    /**
     * AJAX handler for intervention statistics.
     *
     * @since 1.0.0
     */
    public function lap_ajax_get_intervention_stats() {
        check_ajax_referer( 'lap_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'lap_view_analytics' ) ) {
            wp_send_json_error( array(
                'message' => __( 'You do not have permission to view intervention stats.', 'lms-analytics-pro' ),
            ), 403 );
        }

        $course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;
        $days = isset( $_POST['days'] ) ? absint( $_POST['days'] ) : 30;

        $stats = $this->get_intervention_logger()->lap_get_intervention_stats( $course_id, $days );

        wp_send_json_success( array(
            'stats' => $stats,
        ) );
    }
}