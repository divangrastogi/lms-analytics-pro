<?php
/**
 * Dashboard view for LMS Analytics Pro
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Admin/Views
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap">
    <h1><?php esc_html_e( 'LMS Analytics Pro Dashboard', 'lms-analytics-pro' ); ?></h1>

    <div class="lap-dashboard-grid">
        <!-- Overview Cards -->
        <div class="lap-card">
            <h3><?php esc_html_e( 'Total Students', 'lms-analytics-pro' ); ?></h3>
            <div class="lap-stat">1,234</div>
        </div>

        <div class="lap-card">
            <h3><?php esc_html_e( 'Active Courses', 'lms-analytics-pro' ); ?></h3>
            <div class="lap-stat">12</div>
        </div>

        <div class="lap-card">
            <h3><?php esc_html_e( 'Completion Rate', 'lms-analytics-pro' ); ?></h3>
            <div class="lap-stat">78%</div>
        </div>

        <div class="lap-card lap-card--warning">
            <h3><?php esc_html_e( 'At-Risk Students', 'lms-analytics-pro' ); ?></h3>
            <div class="lap-stat">23</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="lap-card">
        <h3><?php esc_html_e( 'Quick Actions', 'lms-analytics-pro' ); ?></h3>
        <div class="lap-actions">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=lap-heatmap' ) ); ?>" class="button button-primary">
                <?php esc_html_e( 'View Progress Heatmap', 'lms-analytics-pro' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=lap-dropout' ) ); ?>" class="button button-secondary">
                <?php esc_html_e( 'Check At-Risk Students', 'lms-analytics-pro' ); ?>
            </a>
            <button class="button button-secondary" id="lap-export-dashboard">
                <?php esc_html_e( 'Export Dashboard Data', 'lms-analytics-pro' ); ?>
            </button>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=lap-settings' ) ); ?>" class="button button-secondary">
                <?php esc_html_e( 'Configure Settings', 'lms-analytics-pro' ); ?>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="lap-card">
        <h3><?php esc_html_e( 'Recent Activity', 'lms-analytics-pro' ); ?></h3>
        <div class="lap-activity-list">
            <p><?php esc_html_e( 'Activity tracking will be displayed here once students start using the LMS.', 'lms-analytics-pro' ); ?></p>
        </div>
    </div>
</div>

<style>
.lap-dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.lap-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.lap-card h3 {
    margin-top: 0;
    color: #23282d;
}

.lap-stat {
    font-size: 36px;
    font-weight: bold;
    color: #007cba;
    margin: 10px 0;
}

.lap-card--warning .lap-stat {
    color: #d63638;
}

.lap-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.lap-activity-list {
    max-height: 300px;
    overflow-y: auto;
}
</style>