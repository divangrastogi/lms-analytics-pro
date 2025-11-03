/**
 * LMS Analytics Pro - Admin JavaScript
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Admin/JS
 * @since      1.0.0
 */

(function ($) {
  "use strict";

  /**
   * LAP Admin class.
   */
  class LAPAdmin {
    /**
     * Constructor.
     */
    constructor() {
      this.ajaxUrl = lapAdminAjax.ajaxurl;
      this.nonce = lapAdminAjax.nonce;
      this.init();
    }

    /**
     * Initialize admin functionality.
     */
    init() {
      this.bindEvents();
      this.initTooltips();
    }

    /**
     * Bind event handlers.
     */
    bindEvents() {
      // Heatmap interactions
      $(document).on(
        "click",
        ".lap-heatmap__cell",
        this.handleCellClick.bind(this),
      );
      $(document).on(
        "change",
        ".lap-heatmap-filters select",
        this.handleFilterChange.bind(this),
      );

      // Risk card actions
      $(document).on(
        "click",
        ".lap-risk-action",
        this.handleRiskAction.bind(this),
      );

      // Export functionality
      $(document).on("click", ".lap-export-btn", this.handleExport.bind(this));
    }

    /**
     * Initialize tooltips.
     */
    initTooltips() {
      // Simple tooltip implementation
      $(document).on("mouseenter", "[data-tooltip]", function () {
        const tooltip = $(this).data("tooltip");
        if (tooltip) {
          LAPAdmin.showTooltip($(this), tooltip);
        }
      });

      $(document).on("mouseleave", "[data-tooltip]", function () {
        LAPAdmin.hideTooltip();
      });
    }

    /**
     * Handle heatmap cell clicks.
     *
     * @param {Event} e Click event.
     */
    handleCellClick(e) {
      e.preventDefault();
      const $cell = $(e.currentTarget);
      const userId = $cell.data("user-id");
      const lessonId = $cell.data("lesson-id");

      // Show detailed modal or tooltip
      this.showCellDetails(userId, lessonId);
    }

    /**
     * Handle filter changes.
     *
     * @param {Event} e Change event.
     */
    handleFilterChange(e) {
      const $filter = $(e.currentTarget);
      const filterType = $filter.data("filter-type");
      const value = $filter.val();

      this.updateFilters(filterType, value);
      this.refreshHeatmap();
    }

    /**
     * Handle risk actions.
     *
     * @param {Event} e Click event.
     */
    handleRiskAction(e) {
      e.preventDefault();
      const $btn = $(e.currentTarget);
      const action = $btn.data("action");
      const userId = $btn.data("user-id");

      switch (action) {
        case "send-message":
          this.sendReengagementMessage(userId);
          break;
        case "mark-contacted":
          this.markAsContacted(userId);
          break;
        case "view-details":
          this.showUserDetails(userId);
          break;
      }
    }

    /**
     * Handle export actions.
     *
     * @param {Event} e Click event.
     */
    handleExport(e) {
      e.preventDefault();
      const $btn = $(e.currentTarget);
      const format = $btn.data("format") || "csv";

      this.exportData(format);
    }

    /**
     * Show cell details in a modal.
     *
     * @param {number} userId   User ID.
     * @param {number} lessonId Lesson ID.
     */
    showCellDetails(userId, lessonId) {
      // AJAX call to get detailed data
      $.ajax({
        url: this.ajaxUrl,
        type: "POST",
        data: {
          action: "lap_get_cell_details",
          user_id: userId,
          lesson_id: lessonId,
          nonce: this.nonce,
        },
        success: (response) => {
          if (response.success) {
            this.displayCellModal(response.data);
          }
        },
        error: () => {
          LAPAdmin.showNotice("Error loading cell details", "error");
        },
      });
    }

    /**
     * Update filters.
     *
     * @param {string} type  Filter type.
     * @param {string} value Filter value.
     */
    updateFilters(type, value) {
      // Update filter state
      this.filters = this.filters || {};
      this.filters[type] = value;
    }

    /**
     * Refresh heatmap data.
     */
    refreshHeatmap() {
      const $container = $(".lap-heatmap__grid");

      $container.addClass("loading");

      $.ajax({
        url: this.ajaxUrl,
        type: "POST",
        data: {
          action: "lap_refresh_heatmap",
          filters: this.filters,
          nonce: this.nonce,
        },
        success: (response) => {
          if (response.success) {
            $container.html(response.data.html);
          }
        },
        error: () => {
          LAPAdmin.showNotice("Error refreshing heatmap", "error");
        },
        complete: () => {
          $container.removeClass("loading");
        },
      });
    }

    /**
     * Send re-engagement message.
     *
     * @param {number} userId User ID.
     */
    sendReengagementMessage(userId) {
      if (!confirm("Send re-engagement message to this student?")) {
        return;
      }

      $.ajax({
        url: this.ajaxUrl,
        type: "POST",
        data: {
          action: "lap_send_reengagement",
          user_id: userId,
          nonce: this.nonce,
        },
        success: (response) => {
          if (response.success) {
            LAPAdmin.showNotice("Message sent successfully", "success");
          } else {
            LAPAdmin.showNotice(
              response.data.message || "Error sending message",
              "error",
            );
          }
        },
        error: () => {
          LAPAdmin.showNotice("Error sending message", "error");
        },
      });
    }

    /**
     * Mark student as contacted.
     *
     * @param {number} userId User ID.
     */
    markAsContacted(userId) {
      $.ajax({
        url: this.ajaxUrl,
        type: "POST",
        data: {
          action: "lap_mark_contacted",
          user_id: userId,
          nonce: this.nonce,
        },
        success: (response) => {
          if (response.success) {
            LAPAdmin.showNotice("Student marked as contacted", "success");
            // Refresh the risk cards
            location.reload();
          }
        },
      });
    }

    /**
     * Export data.
     *
     * @param {string} format Export format.
     */
    exportData(format) {
      const $btn = $(".lap-export-btn");
      const originalText = $btn.text();

      $btn.text("Exporting...").prop("disabled", true);

      $.ajax({
        url: this.ajaxUrl,
        type: "POST",
        data: {
          action: "lap_export_data",
          format: format,
          filters: this.filters,
          nonce: this.nonce,
        },
        success: (response) => {
          if (response.success && response.data.url) {
            // Trigger download
            window.location.href = response.data.url;
            LAPAdmin.showNotice("Export completed", "success");
          } else {
            LAPAdmin.showNotice("Export failed", "error");
          }
        },
        error: () => {
          LAPAdmin.showNotice("Export failed", "error");
        },
        complete: () => {
          $btn.text(originalText).prop("disabled", false);
        },
      });
    }

    /**
     * Display cell details modal.
     *
     * @param {Object} data Cell data.
     */
    displayCellModal(data) {
      // Simple modal implementation
      const modal = `
                <div class="lap-modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
                    <div class="lap-modal" style="background: white; border-radius: 8px; padding: 24px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto;">
                        <h3>${data.student_name} - ${data.lesson_title}</h3>
                        <div class="lap-modal-content">
                            <p><strong>Completion:</strong> ${data.completion_percentage}%</p>
                            <p><strong>Time Spent:</strong> ${data.time_spent_formatted}</p>
                            <p><strong>Last Activity:</strong> ${data.last_activity_formatted}</p>
                            ${data.quiz_score ? `<p><strong>Quiz Score:</strong> ${data.quiz_score}%</p>` : ""}
                        </div>
                        <button class="lap-modal-close lap-btn lap-btn--secondary" style="margin-top: 16px;">Close</button>
                    </div>
                </div>
            `;

      $("body").append(modal);

      $(".lap-modal-close").on("click", function () {
        $(".lap-modal-overlay").remove();
      });
    }

    /**
     * Show user details.
     *
     * @param {number} userId User ID.
     */
    showUserDetails(userId) {
      // Redirect to user profile or show modal
      window.location.href = `user-edit.php?user_id=${userId}`;
    }

    /**
     * Static method to show notices.
     *
     * @param {string} message Notice message.
     * @param {string} type    Notice type (success, error, warning, info).
     */
    static showNotice(message, type = "info") {
      // Use WordPress admin notices
      const noticeClass = `notice notice-${type} is-dismissible`;
      const notice = `<div class="${noticeClass}"><p>${message}</p></div>`;

      $(".wrap > h1").after(notice);

      // Auto-dismiss after 5 seconds
      setTimeout(() => {
        $(".notice").fadeOut();
      }, 5000);
    }

    /**
     * Static method to show tooltip.
     *
     * @param {jQuery} $element Element to show tooltip for.
     * @param {string} content  Tooltip content.
     */
    static showTooltip($element, content) {
      const tooltip = `<div class="lap-tooltip" style="position: absolute; background: #333; color: white; padding: 8px 12px; border-radius: 4px; font-size: 12px; z-index: 1001; pointer-events: none;">${content}</div>`;

      $("body").append(tooltip);

      const $tooltip = $(".lap-tooltip");
      const offset = $element.offset();
      const elementHeight = $element.outerHeight();

      $tooltip.css({
        top: offset.top + elementHeight + 5,
        left: offset.left,
      });
    }

    /**
     * Static method to hide tooltip.
     */
    static hideTooltip() {
      $(".lap-tooltip").remove();
    }
  }

  // Initialize when document is ready
  $(document).ready(function () {
    if (typeof lapAdminAjax !== "undefined") {
      window.lapAdmin = new LAPAdmin();
    }
  });
})(jQuery);
