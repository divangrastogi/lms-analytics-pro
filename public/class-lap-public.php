<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    LMS_Analytics_Pro
 * @subpackage LMS_Analytics_Pro/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    LMS_Analytics_Pro
 * @subpackage LMS_Analytics_Pro/public
 * @author     Divang Rastogi <divang@wbcomdesigns.com>
 */

defined( 'ABSPATH' ) || exit;

class LAP_Public {

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
     * Activity tracker instance.
     *
     * @since  1.0.0
     * @access private
     * @var    LAP_Activity_Tracker $activity_tracker Activity tracker.
     */
    private $activity_tracker;

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

        // Initialize activity tracker
        $this->activity_tracker = new LAP_Activity_Tracker( new LAP_DB_Manager() );

        $this->lap_init_hooks();
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
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

        wp_enqueue_style( $this->plugin_name, LAP_PLUGIN_URL . 'public/css/lap-public.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function lap_enqueue_scripts() {

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

        wp_enqueue_script( $this->plugin_name, LAP_PLUGIN_URL . 'public/js/lap-public.js', array( 'jquery' ), $this->version, false );
    }

    /**
     * Initialize LearnDash and BuddyBoss hooks for activity tracking.
     *
     * @since 1.0.0
     */
    public function lap_init_hooks() {
        // LearnDash lesson completion hooks
        add_action( 'learndash_lesson_completed', array( $this, 'lap_track_lesson_completion' ), 10, 1 );
        add_action( 'learndash_topic_completed', array( $this, 'lap_track_topic_completion' ), 10, 1 );

        // LearnDash quiz hooks
        add_action( 'learndash_quiz_completed', array( $this, 'lap_track_quiz_completion' ), 10, 2 );

        // LearnDash access hooks
        add_action( 'learndash_lesson_access', array( $this, 'lap_track_lesson_access' ), 10, 2 );
        add_action( 'learndash_topic_access', array( $this, 'lap_track_topic_access' ), 10, 2 );

        // User login tracking
        add_action( 'wp_login', array( $this, 'lap_track_user_login' ), 10, 2 );

        // BuddyBoss activity hooks (if available)
        if ( function_exists( 'bp_is_active' ) ) {
            add_action( 'bp_activity_add', array( $this, 'lap_track_buddyboss_activity' ), 10, 1 );
        }
    }

    /**
     * Track lesson completion.
     *
     * @since 1.0.0
     * @param array $data Completion data.
     */
    public function lap_track_lesson_completion( $data ) {
        $this->activity_tracker->lap_track_lesson_completion( $data );
    }

    /**
     * Track topic completion.
     *
     * @since 1.0.0
     * @param array $data Completion data.
     */
    public function lap_track_topic_completion( $data ) {
        // Similar to lesson completion but for topics
        if ( isset( $data['user']->ID ) && isset( $data['topic']->ID ) ) {
            $user_id = $data['user']->ID;
            $topic_id = $data['topic']->ID;
            $lesson_id = $data['lesson']->ID ?? 0;
            $course_id = $data['course']->ID ?? 0;

            $this->activity_tracker->lap_track_topic_view( $user_id, $topic_id, $lesson_id, $course_id );
        }
    }

    /**
     * Track quiz completion.
     *
     * @since 1.0.0
     * @param array $quiz_data Quiz data.
     * @param WP_User $user    User object.
     */
    public function lap_track_quiz_completion( $quiz_data, $user ) {
        if ( ! isset( $quiz_data['quiz'] ) || ! isset( $quiz_data['course'] ) ) {
            return;
        }

        $user_id = $user->ID;
        $quiz_id = $quiz_data['quiz'];
        $course_id = $quiz_data['course']->ID ?? 0;
        $score = $quiz_data['percentage'] ?? 0;

        $this->activity_tracker->lap_track_quiz_attempt( $user_id, $quiz_id, $course_id, $score );
    }

    /**
     * Track lesson access/view.
     *
     * @since 1.0.0
     * @param int $lesson_id Lesson ID.
     * @param int $user_id   User ID.
     */
    public function lap_track_lesson_access( $lesson_id, $user_id ) {
        $course_id = get_post_meta( $lesson_id, 'course_id', true );
        $this->activity_tracker->lap_track_lesson_view( $user_id, $lesson_id, $course_id );
    }

    /**
     * Track topic access/view.
     *
     * @since 1.0.0
     * @param int $topic_id  Topic ID.
     * @param int $user_id   User ID.
     */
    public function lap_track_topic_access( $topic_id, $user_id ) {
        $lesson_id = get_post_meta( $topic_id, 'lesson_id', true );
        $course_id = get_post_meta( $lesson_id, 'course_id', true );
        $this->activity_tracker->lap_track_topic_view( $user_id, $topic_id, $lesson_id, $course_id );
    }

    /**
     * Track user login.
     *
     * @since 1.0.0
     * @param string $user_login Username.
     * @param WP_User $user      User object.
     */
    public function lap_track_user_login( $user_login, $user ) {
        $this->activity_tracker->lap_track_user_login( $user->ID );
    }

    /**
     * Track BuddyBoss activity.
     *
     * @since 1.0.0
     * @param array $params Activity parameters.
     */
    public function lap_track_buddyboss_activity( $params ) {
        if ( isset( $params['user_id'] ) && isset( $params['type'] ) ) {
            $metadata = array(
                'component' => $params['component'] ?? '',
                'content'   => $params['content'] ?? '',
            );

            $this->activity_tracker->lap_track_buddyboss_activity( $params['user_id'], $params['type'], $metadata );
        }
    }
}