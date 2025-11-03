/**
 * LMS Analytics Pro - Public JavaScript
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Public/JS
 * @since      1.0.0
 */

(function ($) {
  "use strict";

  /**
   * LAP Public class.
   */
  class LAPPublic {
    /**
     * Constructor.
     */
    constructor() {
      this.init();
    }

    /**
     * Initialize public functionality.
     */
    init() {
      // Add any public-facing JavaScript here
    }
  }

  // Initialize when document is ready
  $(document).ready(function () {
    window.lapPublic = new LAPPublic();
  });
})(jQuery);
