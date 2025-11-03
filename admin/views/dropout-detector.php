<?php
/**
 * Dropout detector view for LMS Analytics Pro
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Admin/Views
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Dropout Detector', 'lms-analytics-pro' ); ?></h1>

    <div class="lap-dropout">
        <!-- Statistics Overview -->
        <div class="lap-dropout__stats">
            <div class="lap-stats-grid">
                <div class="lap-stat-card">
                    <h3><?php esc_html_e( 'Total At-Risk', 'lms-analytics-pro' ); ?></h3>
                    <div class="lap-stat-value" id="lap-total-risk">0</div>
                </div>
                <div class="lap-stat-card">
                    <h3><?php esc_html_e( 'High Risk', 'lms-analytics-pro' ); ?></h3>
                    <div class="lap-stat-value lap-stat--high" id="lap-high-risk">0</div>
                </div>
                <div class="lap-stat-card">
                    <h3><?php esc_html_e( 'Interventions Sent', 'lms-analytics-pro' ); ?></h3>
                    <div class="lap-stat-value" id="lap-interventions-sent">0</div>
                </div>
                <div class="lap-stat-card">
                    <h3><?php esc_html_e( 'Success Rate', 'lms-analytics-pro' ); ?></h3>
                    <div class="lap-stat-value lap-stat--success" id="lap-success-rate">0%</div>
                </div>
            </div>
        </div>

        <div class="lap-dropout__header">
            <h2><?php esc_html_e( 'At-Risk Students', 'lms-analytics-pro' ); ?></h2>
            <div class="lap-dropout__actions">
                <button class="button button-primary" id="lap-run-risk-calculation">
                    <?php esc_html_e( 'Calculate Risk Scores', 'lms-analytics-pro' ); ?>
                </button>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=lap-settings' ) ); ?>" class="button button-secondary">
                    <?php esc_html_e( 'Settings', 'lms-analytics-pro' ); ?>
                </a>
            </div>
        </div>

        <div class="lap-dropout__filters">
            <select id="lap-course-filter">
                <option value=""><?php esc_html_e( 'All Courses', 'lms-analytics-pro' ); ?></option>
                <?php
                $courses = get_posts( array(
                    'post_type' => 'sfwd-courses',
                    'numberposts' => -1,
                    'post_status' => 'publish',
                ) );
                foreach ( $courses as $course ) {
                    echo '<option value="' . esc_attr( $course->ID ) . '">' . esc_html( $course->post_title ) . '</option>';
                }
                ?>
            </select>

            <select id="lap-risk-level-filter">
                <option value=""><?php esc_html_e( 'All Risk Levels', 'lms-analytics-pro' ); ?></option>
                <option value="critical"><?php esc_html_e( 'Critical Risk', 'lms-analytics-pro' ); ?></option>
                <option value="high"><?php esc_html_e( 'High Risk', 'lms-analytics-pro' ); ?></option>
                <option value="medium"><?php esc_html_e( 'Medium Risk', 'lms-analytics-pro' ); ?></option>
                <option value="low"><?php esc_html_e( 'Low Risk', 'lms-analytics-pro' ); ?></option>
            </select>

            <button class="button button-primary" id="lap-refresh-risk">
                <?php esc_html_e( 'Refresh', 'lms-analytics-pro' ); ?>
            </button>
        </div>

        <div id="lap-risk-students-container">
            <p><?php esc_html_e( 'Loading at-risk students...', 'lms-analytics-pro' ); ?></p>
        </div>

        <div id="lap-pagination-container" class="lap-pagination" style="display: none;">
            <button class="button" id="lap-prev-page" disabled><?php esc_html_e( 'Previous', 'lms-analytics-pro' ); ?></button>
            <span id="lap-page-info"><?php esc_html_e( 'Page 1 of 1', 'lms-analytics-pro' ); ?></span>
            <button class="button" id="lap-next-page" disabled><?php esc_html_e( 'Next', 'lms-analytics-pro' ); ?></button>
            <select id="lap-per-page">
                <option value="10">10 per page</option>
                <option value="20" selected>20 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>

        <!-- Intervention Modal -->
        <div id="lap-intervention-modal" class="lap-modal" style="display: none;">
            <div class="lap-modal__overlay"></div>
            <div class="lap-modal__content">
                <div class="lap-modal__header">
                    <h3><?php esc_html_e( 'Send Intervention', 'lms-analytics-pro' ); ?></h3>
                    <button class="lap-modal__close">&times;</button>
                </div>
                <div class="lap-modal__body">
                    <div id="lap-intervention-details"></div>
                    <div class="lap-intervention-options">
                        <label>
                            <input type="radio" name="intervention_type" value="email" checked>
                            <?php esc_html_e( 'Send Risk Alert Email to Instructor', 'lms-analytics-pro' ); ?>
                        </label>
                        <label>
                            <input type="radio" name="intervention_type" value="reengagement">
                            <?php esc_html_e( 'Send Re-engagement Email to Student', 'lms-analytics-pro' ); ?>
                        </label>
                        <label>
                            <input type="radio" name="intervention_type" value="message">
                            <?php esc_html_e( 'Send BuddyBoss Message to Student', 'lms-analytics-pro' ); ?>
                        </label>
                    </div>
                </div>
                <div class="lap-modal__footer">
                    <button class="button button-secondary" id="lap-cancel-intervention">
                        <?php esc_html_e( 'Cancel', 'lms-analytics-pro' ); ?>
                    </button>
                    <button class="button button-primary" id="lap-send-intervention">
                        <?php esc_html_e( 'Send Intervention', 'lms-analytics-pro' ); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let currentStudentData = null;
    let currentPage = 1;
    let perPage = 20;
    let totalPages = 1;

    // Load data when page loads
    loadAtRiskStudents();
    loadInterventionStats();

    // Calculate risk scores button
    $('#lap-run-risk-calculation').on('click', function() {
        $(this).prop('disabled', true).text('<?php esc_html_e( 'Calculating...', 'lms-analytics-pro' ); ?>');
        calculateRiskScores();
    });

    // Refresh button
    $('#lap-refresh-risk').on('click', function() {
        currentPage = 1;
        loadAtRiskStudents(1);
        loadInterventionStats();
    });

    // Filter changes
    $('#lap-course-filter, #lap-risk-level-filter').on('change', function() {
        currentPage = 1;
        loadAtRiskStudents(1);
    });

    // Modal interactions
    $('.lap-modal__close, .lap-modal__overlay, #lap-cancel-intervention').on('click', function() {
        $('#lap-intervention-modal').hide();
    });

    $('#lap-send-intervention').on('click', function() {
        sendIntervention();
    });

    function loadAtRiskStudents(page = 1) {
        $('#lap-risk-students-container').html('<p><?php esc_html_e( 'Loading...', 'lms-analytics-pro' ); ?>');

        const courseId = $('#lap-course-filter').val();
        const riskLevel = $('#lap-risk-level-filter').val();

        $.ajax({
            url: lapAdminAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lap_get_at_risk_students',
                nonce: lapAdminAjax.nonce,
                course_id: courseId,
                risk_level: riskLevel,
                page: page,
                per_page: perPage
            },
            success: function(response) {
                if (response.success) {
                    currentPage = response.data.page;
                    totalPages = response.data.total_pages;
                    renderRiskStudents(response.data.data);
                    updatePagination(response.data);
                } else {
                    $('#lap-risk-students-container').html('<p><?php esc_html_e( 'Error loading data.', 'lms-analytics-pro' ); ?></p>');
                }
            },
            error: function() {
                $('#lap-risk-students-container').html('<p><?php esc_html_e( 'Error loading data.', 'lms-analytics-pro' ); ?></p>');
            }
        });
    }

    function loadInterventionStats() {
        $.ajax({
            url: lapAdminAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lap_get_intervention_stats',
                nonce: lapAdminAjax.nonce,
                days: 30
            },
            success: function(response) {
                if (response.success) {
                    updateStatsDisplay(response.data.stats);
                }
            }
        });
    }

    function calculateRiskScores() {
        // This would trigger batch risk calculation for all enrolled students
        // For now, just refresh the display
        setTimeout(function() {
            $('#lap-run-risk-calculation').prop('disabled', false).text('<?php esc_html_e( 'Calculate Risk Scores', 'lms-analytics-pro' ); ?>');
            currentPage = 1;
            loadAtRiskStudents(1);
        }, 2000);
    }

    function renderRiskStudents(students) {
        if (!students || students.length === 0) {
            $('#lap-risk-students-container').html('<p><?php esc_html_e( 'No at-risk students found.', 'lms-analytics-pro' ); ?></p>');
            return;
        }

        let html = '';
        students.forEach(function(student) {
            const riskLevel = student.risk_level || 'low';
            const riskClass = 'lap-risk-badge--' + riskLevel;

            html += '<div class="lap-risk-card">';
            html += '<div class="lap-risk-card__header">';
            html += '<h3 class="lap-risk-card__name">' + student.display_name + '</h3>';
            html += '<span class="lap-risk-badge ' + riskClass + '">' + riskLevel.charAt(0).toUpperCase() + riskLevel.slice(1) + ' (' + student.risk_score + ')</span>';
            html += '</div>';

            html += '<div class="lap-risk-card__details">';
            html += '<p><strong><?php esc_html_e( 'Last Login:', 'lms-analytics-pro' ); ?></strong> ' + (student.last_login || '<?php esc_html_e( 'Never', 'lms-analytics-pro' ); ?>') + '</p>';
            html += '<p><strong><?php esc_html_e( 'Days Inactive:', 'lms-analytics-pro' ); ?></strong> ' + (student.days_inactive || 0) + '</p>';
            html += '<p><strong><?php esc_html_e( 'Course:', 'lms-analytics-pro' ); ?></strong> ' + (student.course_title || '<?php esc_html_e( 'N/A', 'lms-analytics-pro' ); ?>') + '</p>';
            html += '</div>';

            html += '<div class="lap-risk-card__actions">';
            html += '<button class="button button-primary lap-intervention-btn" data-user-id="' + student.user_id + '" data-course-id="' + (student.course_id || 0) + '">';
            html += '<?php esc_html_e( 'Send Intervention', 'lms-analytics-pro' ); ?>';
            html += '</button>';
            html += '<button class="button button-secondary lap-risk-details-btn" data-user-id="' + student.user_id + '" data-course-id="' + (student.course_id || 0) + '">';
            html += '<?php esc_html_e( 'View Details', 'lms-analytics-pro' ); ?>';
            html += '</button>';
            html += '</div>';

            html += '</div>';
        });

        $('#lap-risk-students-container').html(html);

        // Bind intervention buttons
        $('.lap-intervention-btn').on('click', function() {
            const userId = $(this).data('user-id');
            const courseId = $(this).data('course-id');
            showInterventionModal(userId, courseId);
        });

        // Bind details buttons
        $('.lap-risk-details-btn').on('click', function() {
            const userId = $(this).data('user-id');
            const courseId = $(this).data('course-id');
            showRiskDetails(userId, courseId);
        });
    }

    function updateStatsDisplay(stats) {
        $('#lap-total-risk').text(stats.total_interventions || 0);
        $('#lap-high-risk').text((stats.by_status && stats.by_status.high) ? stats.by_status.high : 0);
        $('#lap-interventions-sent').text(stats.total_interventions || 0);
        $('#lap-success-rate').text(stats.success_rate ? stats.success_rate + '%' : '0%');
    }

    function showInterventionModal(userId, courseId) {
        $('#lap-intervention-details').html('<p><?php esc_html_e( 'Loading student details...', 'lms-analytics-pro' ); ?></p>');
        $('#lap-intervention-modal').show();

        // Calculate current risk score
        $.ajax({
            url: lapAdminAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lap_calculate_risk_score',
                nonce: lapAdminAjax.nonce,
                user_id: userId,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    currentStudentData = response.data.risk_data;
                    renderInterventionDetails(response.data.risk_data);
                } else {
                    $('#lap-intervention-details').html('<p><?php esc_html_e( 'Error loading student data.', 'lms-analytics-pro' ); ?></p>');
                }
            },
            error: function() {
                $('#lap-intervention-details').html('<p><?php esc_html_e( 'Error loading student data.', 'lms-analytics-pro' ); ?></p>');
            }
        });
    }

    function renderInterventionDetails(riskData) {
        let html = '<div class="lap-intervention-summary">';
        html += '<h4>' + riskData.student_name + '</h4>';
        html += '<p><strong><?php esc_html_e( 'Risk Score:', 'lms-analytics-pro' ); ?></strong> ' + riskData.score + '/100 (' + riskData.level + ')</p>';
        html += '<p><strong><?php esc_html_e( 'Key Factors:', 'lms-analytics-pro' ); ?></strong></p>';
        html += '<ul>';

        if (riskData.factors.inactivity && riskData.factors.inactivity.score > 30) {
            html += '<li><?php esc_html_e( 'Inactive for', 'lms-analytics-pro' ); ?> ' + riskData.factors.inactivity.value + ' <?php esc_html_e( 'days', 'lms-analytics-pro' ); ?></li>';
        }
        if (riskData.factors.velocity && riskData.factors.velocity.score > 20) {
            html += '<li><?php esc_html_e( 'Completion velocity declined', 'lms-analytics-pro' ); ?></li>';
        }
        if (riskData.factors.quiz && riskData.factors.quiz.score > 15) {
            html += '<li><?php esc_html_e( 'Quiz performance dropped', 'lms-analytics-pro' ); ?></li>';
        }

        html += '</ul>';
        html += '<p><strong><?php esc_html_e( 'Suggested Actions:', 'lms-analytics-pro' ); ?></strong></p>';
        html += '<ul>';

        riskData.suggestions.forEach(function(suggestion) {
            html += '<li>' + suggestion + '</li>';
        });

        html += '</ul>';
        html += '</div>';

        $('#lap-intervention-details').html(html);
    }

    function sendIntervention() {
        if (!currentStudentData) return;

        const interventionType = $('input[name="intervention_type"]:checked').val();

        $('#lap-send-intervention').prop('disabled', true).text('<?php esc_html_e( 'Sending...', 'lms-analytics-pro' ); ?>');

        $.ajax({
            url: lapAdminAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lap_send_intervention',
                nonce: lapAdminAjax.nonce,
                user_id: currentStudentData.user_id,
                course_id: currentStudentData.course_id,
                intervention_type: interventionType
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php esc_html_e( 'Intervention sent successfully!', 'lms-analytics-pro' ); ?>');
                    $('#lap-intervention-modal').hide();
                    loadAtRiskStudents(currentPage);
                    loadInterventionStats();
                } else {
                    alert('<?php esc_html_e( 'Error sending intervention.', 'lms-analytics-pro' ); ?>');
                }
            },
            error: function() {
                alert('<?php esc_html_e( 'Error sending intervention.', 'lms-analytics-pro' ); ?>');
            },
            complete: function() {
                $('#lap-send-intervention').prop('disabled', false).text('<?php esc_html_e( 'Send Intervention', 'lms-analytics-pro' ); ?>');
            }
        });
    }

    function showRiskDetails(userId, courseId) {
        // Calculate and show detailed risk analysis
        $.ajax({
            url: lapAdminAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lap_calculate_risk_score',
                nonce: lapAdminAjax.nonce,
                user_id: userId,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    const riskData = response.data.risk_data;
                    let details = '<?php esc_html_e( 'Risk Score:', 'lms-analytics-pro' ); ?> ' + riskData.score + '\n';
                    details += '<?php esc_html_e( 'Level:', 'lms-analytics-pro' ); ?> ' + riskData.level + '\n\n';
                    details += '<?php esc_html_e( 'Factors:', 'lms-analytics-pro' ); ?>\n';

                    Object.keys(riskData.factors).forEach(function(key) {
                        const factor = riskData.factors[key];
                        if (factor.score > 0) {
                            details += key + ': ' + factor.score + '\n';
                        }
                    });

                    alert(details);
                }
            }
        });
    }

    // Pagination event handlers
    $('#lap-prev-page').on('click', function() {
        if (currentPage > 1) {
            loadAtRiskStudents(currentPage - 1);
        }
    });

    $('#lap-next-page').on('click', function() {
        if (currentPage < totalPages) {
            loadAtRiskStudents(currentPage + 1);
        }
    });

    $('#lap-per-page').on('change', function() {
        perPage = parseInt($(this).val());
        currentPage = 1;
        loadAtRiskStudents(1);
    });

    function updatePagination(data) {
        const totalCount = data.total_count;
        const hasData = totalCount > 0;

        $('#lap-pagination-container').toggle(hasData);

        if (hasData) {
            $('#lap-page-info').text('<?php esc_html_e( 'Page', 'lms-analytics-pro' ); ?> ' + currentPage + ' <?php esc_html_e( 'of', 'lms-analytics-pro' ); ?> ' + totalPages + ' (<?php esc_html_e( 'Total:', 'lms-analytics-pro' ); ?> ' + totalCount + ')');
            $('#lap-prev-page').prop('disabled', currentPage <= 1);
            $('#lap-next-page').prop('disabled', currentPage >= totalPages);
        }
    }
});
</script>