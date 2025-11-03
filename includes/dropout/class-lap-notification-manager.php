<?php
/**
 * Notification system for dropout alerts
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Dropout
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LAP_Notification_Manager class.
 */
class LAP_Notification_Manager {

    /**
     * Database manager instance.
     *
     * @var LAP_DB_Manager
     */
    private $db;

    /**
     * Constructor.
     *
     * @param LAP_DB_Manager $db Database manager.
     */
    public function __construct( LAP_DB_Manager $db ) {
        $this->db = $db;
    }

    /**
     * Send risk alert notification to instructors.
     *
     * @param int   $student_id Student user ID.
     * @param int   $course_id  Course ID.
     * @param array $risk_data  Risk assessment data.
     * @return bool Success.
     */
    public function lap_send_risk_alert( $student_id, $course_id, $risk_data ) {
        $instructors = $this->lap_get_course_instructors( $course_id );
        $student = get_userdata( $student_id );
        $course = get_post( $course_id );

        if ( empty( $instructors ) || ! $student || ! $course ) {
            return false;
        }

        $email_data = array(
            'student_name'       => $student->display_name,
            'student_email'      => $student->user_email,
            'course_name'        => $course->post_title,
            'risk_score'         => $risk_data['score'],
            'risk_level'         => $risk_data['level'],
            'factors'            => $risk_data['factors'],
            'suggestions'        => $risk_data['suggestions'],
            'dashboard_url'      => admin_url( 'admin.php?page=lap-dropout' ),
        );

        $success = true;

        foreach ( $instructors as $instructor_id ) {
            $instructor = get_userdata( $instructor_id );
            if ( ! $instructor ) {
                continue;
            }

            $email_data['instructor_name'] = $instructor->display_name;

            // Send email
            $email_sent = $this->lap_send_risk_alert_email( $instructor->user_email, $email_data );

            // Send BuddyBoss message if available
            if ( function_exists( 'bp_is_active' ) && bp_is_active( 'messages' ) ) {
                $this->lap_send_buddyboss_message( $student_id, $instructor_id, $risk_data );
            }

            // Log intervention
            $this->db->log_intervention( array(
                'user_id'           => $student_id,
                'instructor_id'     => $instructor_id,
                'intervention_type' => 'email',
                'message'           => sprintf(
                    __( 'Risk alert sent for %s in %s (Risk Score: %d)', 'lms-analytics-pro' ),
                    $student->display_name,
                    $course->post_title,
                    $risk_data['score']
                ),
            ) );

            if ( ! $email_sent ) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Send re-engagement message to student.
     *
     * @param int   $student_id Student user ID.
     * @param int   $course_id  Course ID.
     * @param array $risk_data  Risk assessment data.
     * @return bool Success.
     */
    public function lap_send_reengagement_message( $student_id, $course_id, $risk_data ) {
        $student = get_userdata( $student_id );
        $course = get_post( $course_id );

        if ( ! $student || ! $course ) {
            return false;
        }

        $email_data = array(
            'student_first_name' => $student->first_name ?: $student->display_name,
            'course_name'        => $course->post_title,
            'completion_percentage' => $this->lap_get_completion_percentage( $student_id, $course_id ),
            'days_inactive'      => $this->lap_calculate_days_inactive( $student_id, $course_id ),
            'next_lesson_title'  => $this->lap_get_next_lesson_title( $student_id, $course_id ),
            'next_lesson_url'    => $this->lap_get_next_lesson_url( $student_id, $course_id ),
            'dashboard_url'      => get_permalink( $course_id ),
        );

        $email_sent = $this->lap_send_reengagement_email( $student->user_email, $email_data );

        // Log intervention
        $this->db->log_intervention( array(
            'user_id'           => $student_id,
            'instructor_id'     => get_current_user_id(),
            'intervention_type' => 'email',
            'message'           => sprintf(
                __( 'Re-engagement email sent to %s for course %s', 'lms-analytics-pro' ),
                $student->display_name,
                $course->post_title
            ),
        ) );

        return $email_sent;
    }

    /**
     * Send BuddyBoss private message.
     *
     * @param int   $student_id   Student user ID.
     * @param int   $instructor_id Instructor user ID.
     * @param array $risk_data    Risk assessment data.
     * @return bool|int Message ID on success, false on failure.
     */
    public function lap_send_buddyboss_message( $student_id, $instructor_id, $risk_data ) {
        if ( ! function_exists( 'messages_new_message' ) ) {
            return false;
        }

        $student = get_userdata( $student_id );
        $course = get_post( $risk_data['course_id'] ?? 0 );

        if ( ! $student || ! $course ) {
            return false;
        }

        $subject = sprintf(
            __( 'Checking in on your progress in %s', 'lms-analytics-pro' ),
            $course->post_title
        );

        $message = sprintf(
            __( 'Hi %1$s,

I noticed you haven\'t been active in %2$s recently. I wanted to reach out and see if everything is okay, and if there\'s anything I can help with.

Your current progress: %3$d%%
Risk Level: %4$s

If you\'re facing any challenges or have questions, please don\'t hesitate to reply to this message. I\'m here to support you!

Best regards,
%5$s', 'lms-analytics-pro' ),
            bp_core_get_user_displayname( $student_id ),
            $course->post_title,
            $this->lap_get_completion_percentage( $student_id, $risk_data['course_id'] ),
            ucfirst( $risk_data['level'] ),
            bp_core_get_user_displayname( $instructor_id )
        );

        $message_id = messages_new_message( array(
            'sender_id'  => $instructor_id,
            'recipients' => array( $student_id ),
            'subject'    => $subject,
            'content'    => $message,
        ) );

        if ( $message_id ) {
            // Log intervention
            $this->db->log_intervention( array(
                'user_id'           => $student_id,
                'instructor_id'     => $instructor_id,
                'intervention_type' => 'message',
                'message'           => $subject,
            ) );
        }

        return $message_id;
    }

    /**
     * Send risk alert email.
     *
     * @param string $email   Recipient email.
     * @param array  $data    Email data.
     * @return bool Success.
     */
    private function lap_send_risk_alert_email( $email, $data ) {
        $subject = sprintf(
            __( '⚠️ Student At-Risk Alert: %s in %s', 'lms-analytics-pro' ),
            $data['student_name'],
            $data['course_name']
        );

        $message = $this->lap_get_risk_alert_template( $data );

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
        );

        return wp_mail( $email, $subject, $message, $headers );
    }

    /**
     * Send re-engagement email.
     *
     * @param string $email Recipient email.
     * @param array  $data  Email data.
     * @return bool Success.
     */
    private function lap_send_reengagement_email( $email, $data ) {
        $subject = sprintf(
            __( 'We Miss You, %s! 👋', 'lms-analytics-pro' ),
            $data['student_first_name']
        );

        $message = $this->lap_get_reengagement_template( $data );

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
        );

        return wp_mail( $email, $subject, $message, $headers );
    }

    /**
     * Get risk alert email template.
     *
     * @param array $data Email data.
     * @return string HTML email content.
     */
    private function lap_get_risk_alert_template( $data ) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #EF4444; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
                .content { background: #fff; padding: 30px; border: 1px solid #ddd; }
                .risk-badge { display: inline-block; padding: 8px 16px; border-radius: 20px; font-weight: bold; }
                .risk-high { background: #FEE2E2; color: #DC2626; }
                .factor { margin: 15px 0; padding: 15px; background: #F9FAFB; border-radius: 6px; }
                .action-button { display: inline-block; padding: 12px 24px; background: #4F46E5; color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>⚠️ Student At-Risk Alert</h2>
                </div>
                <div class="content">
                    <p><strong><?php echo esc_html( $data['student_name'] ); ?></strong> is showing signs of disengagement in <strong><?php echo esc_html( $data['course_name'] ); ?></strong>.</p>

                    <p>
                        <span class="risk-badge risk-high">
                            Risk Score: <?php echo esc_html( $data['risk_score'] ); ?>/100 (<?php echo esc_html( ucfirst( $data['risk_level'] ) ); ?>)
                        </span>
                    </p>

                    <h3>Key Factors:</h3>
                    <?php foreach ( $data['factors'] as $factor_name => $factor_data ) : ?>
                        <?php if ( is_array( $factor_data ) && isset( $factor_data['score'] ) && $factor_data['score'] > 30 ) : ?>
                            <div class="factor">
                                <strong><?php echo esc_html( ucfirst( $factor_name ) ); ?>:</strong><br>
                                <?php echo esc_html( $this->lap_format_factor_description( $factor_name, $factor_data ) ); ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <h3>Recommended Actions:</h3>
                    <ul>
                        <?php foreach ( $data['suggestions'] as $suggestion ) : ?>
                            <li><?php echo esc_html( $suggestion ); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <a href="<?php echo esc_url( $data['dashboard_url'] ); ?>" class="action-button">
                        View Full Analytics →
                    </a>

                    <p style="margin-top: 30px; font-size: 12px; color: #6B7280;">
                        This is an automated alert from LMS Analytics Pro. You're receiving this because you're listed as an instructor for this course.
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Get re-engagement email template.
     *
     * @param array $data Email data.
     * @return string HTML email content.
     */
    private function lap_get_reengagement_template( $data ) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px 8px 0 0; text-align: center; }
                .content { background: #fff; padding: 30px; border: 1px solid #ddd; }
                .stats { display: flex; justify-content: space-around; margin: 20px 0; }
                .stat { text-align: center; }
                .stat-value { font-size: 32px; font-weight: bold; color: #4F46E5; }
                .stat-label { font-size: 14px; color: #6B7280; }
                .action-button { display: inline-block; padding: 14px 28px; background: #10B981; color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>We Miss You, <?php echo esc_html( $data['student_first_name'] ); ?>! 👋</h2>
                </div>
                <div class="content">
                    <p>We noticed you haven't been active in <strong><?php echo esc_html( $data['course_name'] ); ?></strong> for a while.</p>

                    <div class="stats">
                        <div class="stat">
                            <div class="stat-value"><?php echo esc_html( $data['completion_percentage'] ); ?>%</div>
                            <div class="stat-label">Completed</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value"><?php echo esc_html( $data['days_inactive'] ); ?></div>
                            <div class="stat-label">Days Away</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value"><?php echo esc_html( $data['next_lesson_title'] ?: 'N/A' ); ?></div>
                            <div class="stat-label">Next Lesson</div>
                        </div>
                    </div>

                    <p>You're doing great! Just keep going and you'll complete this course.</p>

                    <?php if ( $data['next_lesson_url'] ) : ?>
                        <center>
                            <a href="<?php echo esc_url( $data['next_lesson_url'] ); ?>" class="action-button">
                                Continue Learning →
                            </a>
                        </center>
                    <?php endif; ?>

                    <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                        Need help? Reply to this email and we'll get back to you within 24 hours.
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Get course instructors.
     *
     * @param int $course_id Course ID.
     * @return array Instructor user IDs.
     */
    private function lap_get_course_instructors( $course_id ) {
        // Get course author
        $course = get_post( $course_id );
        $instructors = array();

        if ( $course ) {
            $instructors[] = $course->post_author;
        }

        // Add group leaders if BuddyBoss is active
        if ( function_exists( 'bp_is_active' ) && bp_is_active( 'groups' ) ) {
            $group_id = get_post_meta( $course_id, 'bp_course_group', true );
            if ( $group_id ) {
                $group = groups_get_group( $group_id );
                if ( $group ) {
                    $moderators = groups_get_group_members( array(
                        'group_id' => $group_id,
                        'per_page' => 10,
                        'exclude_admins_mods' => false,
                    ) );

                    foreach ( $moderators['members'] as $member ) {
                        if ( groups_is_user_mod( $member->user_id, $group_id ) || groups_is_user_admin( $member->user_id, $group_id ) ) {
                            $instructors[] = $member->user_id;
                        }
                    }
                }
            }
        }

        return array_unique( $instructors );
    }

    /**
     * Format factor description for email.
     *
     * @param string $factor_name Factor name.
     * @param array  $factor_data Factor data.
     * @return string Description.
     */
    private function lap_format_factor_description( $factor_name, $factor_data ) {
        switch ( $factor_name ) {
            case 'inactivity':
                return sprintf(
                    __( '%d days since last activity', 'lms-analytics-pro' ),
                    $factor_data['value']
                );
            case 'velocity':
                return sprintf(
                    __( 'Completion velocity declined by %.1f%%', 'lms-analytics-pro' ),
                    $factor_data['score']
                );
            case 'quiz':
                return sprintf(
                    __( 'Quiz performance dropped by %.1f points', 'lms-analytics-pro' ),
                    $factor_data['score']
                );
            case 'forum':
                return sprintf(
                    __( 'Forum participation down by %.1f%%', 'lms-analytics-pro' ),
                    $factor_data['score']
                );
            case 'assignments':
                return sprintf(
                    __( '%d assignments delayed', 'lms-analytics-pro' ),
                    $factor_data['delayed_count']
                );
            default:
                return sprintf(
                    __( 'Score: %.1f', 'lms-analytics-pro' ),
                    $factor_data['score']
                );
        }
    }

    /**
     * Get completion percentage (placeholder).
     *
     * @param int $user_id   User ID.
     * @param int $course_id Course ID.
     * @return int Completion percentage.
     */
    private function lap_get_completion_percentage( $user_id, $course_id ) {
        // Placeholder - would use progress calculator
        return rand( 10, 90 );
    }

    /**
     * Calculate days inactive (placeholder).
     *
     * @param int $user_id   User ID.
     * @param int $course_id Course ID.
     * @return int Days inactive.
     */
    private function lap_calculate_days_inactive( $user_id, $course_id ) {
        // Placeholder
        return rand( 5, 30 );
    }

    /**
     * Get next lesson title (placeholder).
     *
     * @param int $user_id   User ID.
     * @param int $course_id Course ID.
     * @return string Next lesson title.
     */
    private function lap_get_next_lesson_title( $user_id, $course_id ) {
        // Placeholder
        return 'Next Lesson Title';
    }

    /**
     * Get next lesson URL (placeholder).
     *
     * @param int $user_id   User ID.
     * @param int $course_id Course ID.
     * @return string Next lesson URL.
     */
    private function lap_get_next_lesson_url( $user_id, $course_id ) {
        // Placeholder
        return '#';
    }
}