<?php
/**
 * Settings view for LMS Analytics Pro
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Admin/Views
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Get active tab from URL parameter or default to general
$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
$valid_tabs = array( 'general', 'notifications', 'advanced' );
if ( ! in_array( $active_tab, $valid_tabs, true ) ) {
    $active_tab = 'general';
}

// Check for settings updated message
$settings_updated = isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true';
?>

<div class="wrap lap-settings-wrap">
    <?php if ( $settings_updated ) : ?>
        <div class="notice notice-success is-dismissible lap-settings-saved">
            <p><strong><?php esc_html_e( 'Settings saved successfully!', 'lms-analytics-pro' ); ?></strong></p>
        </div>
    <?php endif; ?>
    <div class="lap-settings-header">
        <div class="lap-header-content">
            <div class="lap-header-icon">
                <span class="dashicons dashicons-chart-bar"></span>
            </div>
            <div class="lap-header-text">
                <h1><?php esc_html_e( 'LMS Analytics Pro Settings', 'lms-analytics-pro' ); ?></h1>
                <p><?php esc_html_e( 'Configure your learning analytics preferences and risk assessment parameters.', 'lms-analytics-pro' ); ?></p>
            </div>
        </div>
    </div>

    <div class="lap-settings-container">
        <div class="lap-settings-sidebar">
            <nav class="lap-settings-nav">
                <a href="?page=lap-settings&tab=general" class="lap-nav-item <?php echo $active_tab === 'general' ? 'active' : ''; ?>" data-tab="general">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <span class="lap-nav-label"><?php esc_html_e( 'General', 'lms-analytics-pro' ); ?></span>
                </a>
                <a href="?page=lap-settings&tab=notifications" class="lap-nav-item <?php echo $active_tab === 'notifications' ? 'active' : ''; ?>" data-tab="notifications">
                    <span class="dashicons dashicons-bell"></span>
                    <span class="lap-nav-label"><?php esc_html_e( 'Notifications', 'lms-analytics-pro' ); ?></span>
                </a>
                <a href="?page=lap-settings&tab=advanced" class="lap-nav-item <?php echo $active_tab === 'advanced' ? 'active' : ''; ?>" data-tab="advanced">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <span class="lap-nav-label"><?php esc_html_e( 'Advanced', 'lms-analytics-pro' ); ?></span>
                </a>
            </nav>
        </div>

        <div class="lap-settings-content">
            <form method="post" action="options.php?tab=<?php echo esc_attr( $active_tab ); ?>">
                <?php settings_fields( 'lap_settings_group' ); ?>

                <!-- General Settings -->
                <div class="lap-settings-section <?php echo $active_tab === 'general' ? 'active' : ''; ?>" id="lap-general-section">
                    <div class="lap-section-header">
                        <h2>
                            <span class="dashicons dashicons-admin-settings"></span>
                            <?php esc_html_e( 'General Settings', 'lms-analytics-pro' ); ?>
                        </h2>
                        <p><?php esc_html_e( 'Configure basic plugin settings and risk assessment parameters.', 'lms-analytics-pro' ); ?></p>
                    </div>

                    <div class="lap-settings-grid">
                        <div class="lap-setting-card">
                            <div class="lap-card-header">
                                <h3><?php esc_html_e( 'Activity Monitoring', 'lms-analytics-pro' ); ?></h3>
                            </div>
                            <div class="lap-card-content">
                                <div class="lap-form-group">
                                    <label for="lap_inactivity_days">
                                        <?php esc_html_e( 'Inactivity Threshold (days)', 'lms-analytics-pro' ); ?>
                                    </label>
                                    <input type="number" id="lap_inactivity_days" name="lap_inactivity_days" value="<?php echo esc_attr( get_option( 'lap_inactivity_days', 7 ) ); ?>" min="1" max="365" class="lap-input-number">
                                    <p class="lap-description">
                                        <?php esc_html_e( 'Number of days without activity before a student is considered inactive.', 'lms-analytics-pro' ); ?>
                                    </p>
                                </div>

                                <div class="lap-form-group">
                                    <label for="lap_cache_duration">
                                        <?php esc_html_e( 'Cache Duration (seconds)', 'lms-analytics-pro' ); ?>
                                    </label>
                                    <input type="number" id="lap_cache_duration" name="lap_cache_duration" value="<?php echo esc_attr( get_option( 'lap_cache_duration', 3600 ) ); ?>" min="300" max="86400" class="lap-input-number">
                                    <p class="lap-description">
                                        <?php esc_html_e( 'How long to cache analytics data (default: 1 hour).', 'lms-analytics-pro' ); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="lap-setting-card">
                            <div class="lap-card-header">
                                <h3><?php esc_html_e( 'Notifications', 'lms-analytics-pro' ); ?></h3>
                            </div>
                            <div class="lap-card-content">
                                <div class="lap-form-group">
                                    <label class="lap-checkbox-label">
                                        <input type="checkbox" name="lap_enable_notifications" value="1" <?php checked( get_option( 'lap_enable_notifications', true ) ); ?>>
                                        <span class="lap-checkbox-text"><?php esc_html_e( 'Enable automated notifications', 'lms-analytics-pro' ); ?></span>
                                    </label>
                                    <p class="lap-description">
                                        <?php esc_html_e( 'Send automated notifications for at-risk students.', 'lms-analytics-pro' ); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="lap-setting-card lap-card-full">
                            <div class="lap-card-header">
                                <h3><?php esc_html_e( 'Risk Scoring Weights', 'lms-analytics-pro' ); ?></h3>
                                <p><?php esc_html_e( 'Adjust the weight of each factor in risk score calculation (total should equal 100%).', 'lms-analytics-pro' ); ?></p>
                            </div>
                            <div class="lap-card-content">
                                <div class="lap-risk-weights-grid">
                                    <?php
                                    $weights = get_option( 'lap_risk_weights', array(
                                        'inactivity'  => 35,
                                        'velocity'    => 25,
                                        'quiz'        => 20,
                                        'forum'       => 10,
                                        'assignments' => 10,
                                    ) );
                                    $weight_items = array(
                                        'inactivity'  => __( 'Inactivity', 'lms-analytics-pro' ),
                                        'velocity'    => __( 'Velocity Decline', 'lms-analytics-pro' ),
                                        'quiz'        => __( 'Quiz Performance', 'lms-analytics-pro' ),
                                        'forum'       => __( 'Forum Participation', 'lms-analytics-pro' ),
                                        'assignments' => __( 'Assignment Delays', 'lms-analytics-pro' ),
                                    );
                                    ?>
                                    <?php foreach ( $weight_items as $key => $label ) : ?>
                                        <div class="lap-weight-item">
                                            <label for="lap_weight_<?php echo esc_attr( $key ); ?>">
                                                <?php echo esc_html( $label ); ?>
                                            </label>
                                            <div class="lap-weight-input-group">
                                                <input type="number" id="lap_weight_<?php echo esc_attr( $key ); ?>" name="lap_risk_weights[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $weights[ $key ] ); ?>" min="0" max="100" class="lap-input-number lap-weight-input">
                                                <span class="lap-weight-percent">%</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="lap-setting-card lap-card-full">
                            <div class="lap-card-header">
                                <h3><?php esc_html_e( 'Risk Thresholds', 'lms-analytics-pro' ); ?></h3>
                                <p><?php esc_html_e( 'Score ranges for different risk levels.', 'lms-analytics-pro' ); ?></p>
                            </div>
                            <div class="lap-card-content">
                                <div class="lap-thresholds-grid">
                                    <?php
                                    $thresholds = get_option( 'lap_risk_thresholds', array(
                                        'low'      => 25,
                                        'medium'   => 50,
                                        'high'     => 75,
                                        'critical' => 90,
                                    ) );
                                    $threshold_items = array(
                                        'low'      => array( 'label' => __( 'Low Risk', 'lms-analytics-pro' ), 'range' => '0-' . $thresholds['low'] ),
                                        'medium'   => array( 'label' => __( 'Medium Risk', 'lms-analytics-pro' ), 'range' => $thresholds['low'] . '-' . $thresholds['medium'] ),
                                        'high'     => array( 'label' => __( 'High Risk', 'lms-analytics-pro' ), 'range' => $thresholds['medium'] . '-' . $thresholds['high'] ),
                                        'critical' => array( 'label' => __( 'Critical Risk', 'lms-analytics-pro' ), 'range' => $thresholds['high'] . '+' ),
                                    );
                                    ?>
                                    <?php foreach ( $threshold_items as $key => $item ) : ?>
                                        <div class="lap-threshold-item">
                                            <label for="lap_threshold_<?php echo esc_attr( $key ); ?>">
                                                <?php echo esc_html( $item['label'] ); ?> <small>(<?php echo esc_html( $item['range'] ); ?>)</small>
                                            </label>
                                            <input type="number" id="lap_threshold_<?php echo esc_attr( $key ); ?>" name="lap_risk_thresholds[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $thresholds[ $key ] ); ?>" min="0" max="100" class="lap-input-number">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Settings -->
                <div class="lap-settings-section <?php echo $active_tab === 'notifications' ? 'active' : ''; ?>" id="lap-notifications-section">
                    <div class="lap-section-header">
                        <h2>
                            <span class="dashicons dashicons-bell"></span>
                            <?php esc_html_e( 'Notification Settings', 'lms-analytics-pro' ); ?>
                        </h2>
                        <p><?php esc_html_e( 'Configure how and when notifications are sent for at-risk students.', 'lms-analytics-pro' ); ?></p>
                    </div>

                    <div class="lap-settings-grid">
                        <div class="lap-setting-card lap-card-full">
                            <div class="lap-card-header">
                                <h3><?php esc_html_e( 'Notification Schedule', 'lms-analytics-pro' ); ?></h3>
                            </div>
                            <div class="lap-card-content">
                                <div class="lap-form-group">
                                    <label for="lap_notification_schedule">
                                        <?php esc_html_e( 'Send notifications', 'lms-analytics-pro' ); ?>
                                    </label>
                                    <select id="lap_notification_schedule" name="lap_notification_schedule" class="lap-input-select">
                                        <option value="daily" <?php selected( get_option( 'lap_notification_schedule', 'daily' ), 'daily' ); ?>>
                                            <?php esc_html_e( 'Daily', 'lms-analytics-pro' ); ?>
                                        </option>
                                        <option value="weekly" <?php selected( get_option( 'lap_notification_schedule', 'daily' ), 'weekly' ); ?>>
                                            <?php esc_html_e( 'Weekly', 'lms-analytics-pro' ); ?>
                                        </option>
                                    </select>
                                    <p class="lap-description">
                                        <?php esc_html_e( 'How often to send automated risk notifications.', 'lms-analytics-pro' ); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Settings -->
                <div class="lap-settings-section <?php echo $active_tab === 'advanced' ? 'active' : ''; ?>" id="lap-advanced-section">
                    <div class="lap-section-header">
                        <h2>
                            <span class="dashicons dashicons-admin-tools"></span>
                            <?php esc_html_e( 'Advanced Settings', 'lms-analytics-pro' ); ?>
                        </h2>
                        <p><?php esc_html_e( 'Advanced configuration options for power users.', 'lms-analytics-pro' ); ?></p>
                    </div>

                    <div class="lap-settings-grid">
                        <div class="lap-setting-card">
                            <div class="lap-card-header">
                                <h3><?php esc_html_e( 'Debugging', 'lms-analytics-pro' ); ?></h3>
                            </div>
                            <div class="lap-card-content">
                                <div class="lap-form-group">
                                    <label class="lap-checkbox-label">
                                        <input type="checkbox" name="lap_debug_mode" value="1" <?php checked( get_option( 'lap_debug_mode', false ) ); ?>>
                                        <span class="lap-checkbox-text"><?php esc_html_e( 'Enable debug logging', 'lms-analytics-pro' ); ?></span>
                                    </label>
                                    <p class="lap-description">
                                        <?php esc_html_e( 'Log detailed information for troubleshooting.', 'lms-analytics-pro' ); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="lap-setting-card">
                            <div class="lap-card-header">
                                <h3><?php esc_html_e( 'Data Management', 'lms-analytics-pro' ); ?></h3>
                            </div>
                            <div class="lap-card-content">
                                <div class="lap-form-group">
                                    <button type="button" class="button button-secondary lap-btn-cleanup" id="lap-cleanup-data">
                                        <span class="dashicons dashicons-trash"></span>
                                        <?php esc_html_e( 'Clean Old Data', 'lms-analytics-pro' ); ?>
                                    </button>
                                    <p class="lap-description">
                                        <?php esc_html_e( 'Remove old activity logs and cached data.', 'lms-analytics-pro' ); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lap-settings-footer">
                    <?php submit_button( __( 'Save Settings', 'lms-analytics-pro' ), 'primary lap-btn-save' ); ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Data cleanup
    $('#lap-cleanup-data').on('click', function() {
        if (confirm('<?php esc_html_e( 'Are you sure you want to clean old data? This action cannot be undone.', 'lms-analytics-pro' ); ?>')) {
            // AJAX call to clean data
            alert('<?php esc_html_e( 'Data cleanup feature not yet implemented.', 'lms-analytics-pro' ); ?>');
        }
    });
});
</script>

<style>
/* LMS Analytics Pro Settings Styles */
.lap-settings-wrap {
    margin: 20px 20px 0 2px;
}

.lap-settings-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    color: white;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.lap-header-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.lap-header-icon {
    font-size: 48px;
    opacity: 0.9;
}

.lap-header-text h1 {
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 700;
}

.lap-header-text p {
    margin: 0;
    opacity: 0.9;
    font-size: 16px;
}

.lap-settings-saved {
    margin-bottom: 20px !important;
    border-left: 4px solid #46b450;
}

.lap-settings-container {
    display: flex;
    gap: 30px;
    align-items: flex-start;
}

.lap-settings-sidebar {
    flex: 0 0 250px;
}

.lap-settings-nav {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.lap-nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    text-decoration: none;
    color: #666;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.lap-nav-item:hover {
    background: #f8f9ff;
    color: #667eea;
}

.lap-nav-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-color: #667eea;
}

.lap-nav-item.active:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
}

.lap-nav-item:last-child {
    border-bottom: none;
}

.lap-nav-label {
    font-weight: 500;
}

.lap-settings-content {
    flex: 1;
    min-width: 0;
}

.lap-settings-section {
    display: none;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.lap-settings-section.active {
    display: block;
}

.lap-section-header {
    padding: 30px 30px 20px;
    border-bottom: 1px solid #f0f0f0;
    background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
}

.lap-section-header h2 {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 600;
    color: #1a202c;
    display: flex;
    align-items: center;
    gap: 12px;
}

.lap-section-header h2 .dashicons {
    color: #667eea;
}

.lap-section-header p {
    margin: 0;
    color: #718096;
    font-size: 14px;
}

.lap-settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 24px;
    padding: 30px;
}

.lap-setting-card {
    background: #f8f9ff;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.lap-setting-card:hover {
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
}

.lap-card-full {
    grid-column: 1 / -1;
}

.lap-card-header {
    padding: 20px 24px;
    background: white;
    border-bottom: 1px solid #e2e8f0;
}

.lap-card-header h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #2d3748;
}

.lap-card-header p {
    margin: 0;
    color: #718096;
    font-size: 14px;
}

.lap-card-content {
    padding: 24px;
}

.lap-form-group {
    margin-bottom: 20px;
}

.lap-form-group:last-child {
    margin-bottom: 0;
}

.lap-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #4a5568;
}

.lap-input-number,
.lap-input-select {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
}

.lap-input-number:focus,
.lap-input-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.lap-description {
    margin: 8px 0 0 0;
    font-size: 13px;
    color: #718096;
    line-height: 1.4;
}

.lap-checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: 500 !important;
    margin-bottom: 8px !important;
}

.lap-checkbox-text {
    font-weight: 500;
    color: #4a5568;
}

.lap-risk-weights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.lap-weight-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.lap-weight-input-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.lap-weight-input {
    flex: 1;
}

.lap-weight-percent {
    font-weight: 600;
    color: #667eea;
    min-width: 20px;
}

.lap-thresholds-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.lap-threshold-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.lap-threshold-item small {
    color: #718096;
    font-weight: 400;
}

.lap-settings-footer {
    padding: 30px;
    border-top: 1px solid #f0f0f0;
    background: #f8f9ff;
    text-align: right;
}

.lap-btn-save {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 12px 24px !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    color: white !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3) !important;
}

.lap-btn-save:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%) !important;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4) !important;
    transform: translateY(-1px);
}

.lap-btn-cleanup {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    background: #fed7d7 !important;
    border: 1px solid #feb2b2 !important;
    color: #c53030 !important;
    border-radius: 6px !important;
    padding: 8px 16px !important;
    font-size: 14px !important;
    transition: all 0.3s ease !important;
}

.lap-btn-cleanup:hover {
    background: #feb2b2 !important;
    border-color: #fc8181 !important;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .lap-settings-container {
        flex-direction: column;
        gap: 20px;
    }

    .lap-settings-sidebar {
        flex: none;
    }

    .lap-settings-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .lap-settings-header {
        padding: 20px;
    }

    .lap-header-content {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .lap-header-icon {
        font-size: 36px;
    }

    .lap-header-text h1 {
        font-size: 24px;
    }

    .lap-settings-grid {
        padding: 20px;
        gap: 16px;
    }

    .lap-card-content {
        padding: 20px;
    }

    .lap-settings-footer {
        padding: 20px;
        text-align: center;
    }
}
</style>