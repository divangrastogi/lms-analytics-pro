<?php
/**
 * Heatmap view for LMS Analytics Pro
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Admin/Views
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Student Progress Heatmap', 'lms-analytics-pro' ); ?></h1>

    <div class="lap-heatmap">
        <div class="lap-heatmap__header">
            <h2><?php esc_html_e( 'Progress Visualization', 'lms-analytics-pro' ); ?></h2>
            <div class="lap-heatmap__actions">
                <button class="button button-secondary lap-export-btn" data-format="csv">
                    <?php esc_html_e( 'Export CSV', 'lms-analytics-pro' ); ?>
                </button>
                <button class="button button-secondary lap-export-btn" data-format="pdf">
                    <?php esc_html_e( 'Export PDF', 'lms-analytics-pro' ); ?>
                </button>
            </div>
        </div>

        <div class="lap-heatmap__filters">
            <select id="lap-course-filter">
                <option value=""><?php esc_html_e( 'All Courses', 'lms-analytics-pro' ); ?></option>
                <!-- Course options will be populated dynamically -->
            </select>

            <select id="lap-group-filter">
                <option value=""><?php esc_html_e( 'All Groups', 'lms-analytics-pro' ); ?></option>
                <!-- Group options will be populated dynamically -->
            </select>

            <input type="date" id="lap-date-from" value="<?php echo esc_attr( date( 'Y-m-d', strtotime( '-30 days' ) ) ); ?>">
            <input type="date" id="lap-date-to" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">

            <button class="button button-primary" id="lap-refresh-heatmap">
                <?php esc_html_e( 'Refresh', 'lms-analytics-pro' ); ?>
            </button>
        </div>

        <div class="lap-heatmap__grid">
            <div id="lap-heatmap-container">
                <p><?php esc_html_e( 'Loading heatmap data...', 'lms-analytics-pro' ); ?></p>
            </div>
        </div>

        <div id="lap-heatmap-pagination" class="lap-pagination" style="display: none;">
            <button class="button" id="lap-heatmap-prev" disabled><?php esc_html_e( 'Previous', 'lms-analytics-pro' ); ?></button>
            <span id="lap-heatmap-page-info"><?php esc_html_e( 'Page 1 of 1', 'lms-analytics-pro' ); ?></span>
            <button class="button" id="lap-heatmap-next" disabled><?php esc_html_e( 'Next', 'lms-analytics-pro' ); ?></button>
            <select id="lap-heatmap-per-page">
                <option value="25">25 per page</option>
                <option value="50" selected>50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>

        <div class="lap-heatmap__legend">
            <h4><?php esc_html_e( 'Legend', 'lms-analytics-pro' ); ?></h4>
            <div class="lap-legend-items">
                <span class="lap-legend-item">
                    <span class="lap-legend-color lap-legend-color--0"></span>
                    <?php esc_html_e( '0%', 'lms-analytics-pro' ); ?>
                </span>
                <span class="lap-legend-item">
                    <span class="lap-legend-color lap-legend-color--25"></span>
                    <?php esc_html_e( '25%', 'lms-analytics-pro' ); ?>
                </span>
                <span class="lap-legend-item">
                    <span class="lap-legend-color lap-legend-color--50"></span>
                    <?php esc_html_e( '50%', 'lms-analytics-pro' ); ?>
                </span>
                <span class="lap-legend-item">
                    <span class="lap-legend-color lap-legend-color--75"></span>
                    <?php esc_html_e( '75%', 'lms-analytics-pro' ); ?>
                </span>
                <span class="lap-legend-item">
                    <span class="lap-legend-color lap-legend-color--100"></span>
                    <?php esc_html_e( '100%', 'lms-analytics-pro' ); ?>
                </span>
            </div>
        </div>
    </div>
</div>

<style>
.lap-heatmap__actions {
    display: flex;
    gap: 10px;
}

.lap-heatmap__legend {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.lap-legend-items {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.lap-legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.lap-legend-color {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    display: inline-block;
}

.lap-legend-color--0 { background-color: #F3F4F6; }
.lap-legend-color--25 { background-color: #DBEAFE; }
.lap-legend-color--50 { background-color: #93C5FD; }
.lap-legend-color--75 { background-color: #3B82F6; }
.lap-legend-color--100 { background-color: #1E40AF; }
</style>

<script>
jQuery(document).ready(function($) {
    let heatmapCurrentPage = 1;
    let heatmapPerPage = 50;
    let heatmapTotalPages = 1;

    // Initialize heatmap when page loads
    loadHeatmapData();

    // Refresh button
    $('#lap-refresh-heatmap').on('click', function() {
        heatmapCurrentPage = 1;
        loadHeatmapData(1);
    });

    // Filter changes
    $('#lap-course-filter, #lap-group-filter, #lap-date-from, #lap-date-to').on('change', function() {
        heatmapCurrentPage = 1;
        loadHeatmapData(1);
    });

    function loadHeatmapData(page = 1) {
        const filters = {
            course_id: $('#lap-course-filter').val(),
            group_id: $('#lap-group-filter').val(),
            date_from: $('#lap-date-from').val(),
            date_to: $('#lap-date-to').val(),
        };

        $('#lap-heatmap-container').html('<p><?php esc_html_e( 'Loading...', 'lms-analytics-pro' ); ?></p>');

        $.ajax({
            url: lapAdminAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lap_get_heatmap_data',
                nonce: lapAdminAjax.nonce,
                filters: JSON.stringify(filters),
                page: page,
                per_page: heatmapPerPage
            },
            success: function(response) {
                if (response.success) {
                    heatmapCurrentPage = response.data.page;
                    heatmapTotalPages = response.data.total_pages;
                    renderHeatmap(response.data);
                    updateHeatmapPagination(response.data);
                } else {
                    $('#lap-heatmap-container').html('<p><?php esc_html_e( 'Error loading data.', 'lms-analytics-pro' ); ?></p>');
                }
            },
            error: function() {
                $('#lap-heatmap-container').html('<p><?php esc_html_e( 'Error loading data.', 'lms-analytics-pro' ); ?></p>');
            }
        });
    }

    function renderHeatmap(data) {
        if (!data.students || data.students.length === 0) {
            $('#lap-heatmap-container').html('<p><?php esc_html_e( 'No data available for the selected filters.', 'lms-analytics-pro' ); ?></p>');
            return;
        }

        let html = '<table class="lap-heatmap__table">';
        html += '<thead><tr><th><?php esc_html_e( 'Student', 'lms-analytics-pro' ); ?></th>';

        // Add lesson columns
        data.lessons.forEach(function(lesson) {
            html += '<th>' + lesson.title + '</th>';
        });
        html += '<th><?php esc_html_e( 'Avg %', 'lms-analytics-pro' ); ?></th></tr></thead><tbody>';

        // Add student rows
        data.students.forEach(function(student) {
            html += '<tr>';
            html += '<td>' + student.name + '</td>';

            data.lessons.forEach(function(lesson) {
                const progress = student.progress[lesson.id] || { completion_percentage: 0 };
                const percentage = progress.completion_percentage || 0;
                const colorClass = getColorClass(percentage);

                html += '<td><div class="lap-heatmap__cell ' + colorClass + '" data-tooltip="' + percentage + '% complete">' + percentage + '%</div></td>';
            });

            html += '<td>' + student.average_completion + '%</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';

        $('#lap-heatmap-container').html(html);
    }

    function getColorClass(percentage) {
        if (percentage >= 90) return 'lap-heatmap__cell--100';
        if (percentage >= 70) return 'lap-heatmap__cell--75';
        if (percentage >= 50) return 'lap-heatmap__cell--50';
        if (percentage >= 25) return 'lap-heatmap__cell--25';
        return 'lap-heatmap__cell--0';
    }

    // Heatmap pagination event handlers
    $('#lap-heatmap-prev').on('click', function() {
        if (heatmapCurrentPage > 1) {
            loadHeatmapData(heatmapCurrentPage - 1);
        }
    });

    $('#lap-heatmap-next').on('click', function() {
        if (heatmapCurrentPage < heatmapTotalPages) {
            loadHeatmapData(heatmapCurrentPage + 1);
        }
    });

    $('#lap-heatmap-per-page').on('change', function() {
        heatmapPerPage = parseInt($(this).val());
        heatmapCurrentPage = 1;
        loadHeatmapData(1);
    });

    function updateHeatmapPagination(data) {
        const totalCount = data.total_count || 0;
        const hasData = totalCount > 0;

        $('#lap-heatmap-pagination').toggle(hasData);

        if (hasData) {
            $('#lap-heatmap-page-info').text('<?php esc_html_e( 'Page', 'lms-analytics-pro' ); ?> ' + heatmapCurrentPage + ' <?php esc_html_e( 'of', 'lms-analytics-pro' ); ?> ' + heatmapTotalPages + ' (<?php esc_html_e( 'Total:', 'lms-analytics-pro' ); ?> ' + totalCount + ')');
            $('#lap-heatmap-prev').prop('disabled', heatmapCurrentPage <= 1);
            $('#lap-heatmap-next').prop('disabled', heatmapCurrentPage >= heatmapTotalPages);
        }
    }
});
</script>