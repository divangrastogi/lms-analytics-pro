# LMS Analytics Pro - Complete Development Specification

## 📋 Project Overview

**Plugin Name:** LMS Analytics Pro  
**Version:** 1.0.0  
**Description:** A comprehensive analytics and engagement tracking plugin for LearnDash/BuddyBoss that combines student progress visualization and dropout detection in a unified, beautiful interface.

**Core Philosophy:** Extend, don't replace. This plugin augments existing LMS functionality without modifying core BuddyPress/BuddyBoss behavior.

---

## 🎯 Feature Set

### Feature 1: Student Progress Heatmap
Visual analytics dashboard showing color-coded student progress across all courses, lessons, and topics.

**Capabilities:**
- Row = Student, Column = Lesson/Topic
- Color intensity indicates completion rate (0-100%)
- Multi-level filtering (course, group, date range)
- Interactive hover tooltips with detailed metrics
- Export functionality (CSV, PDF, Excel)
- BuddyBoss Groups integration
- Real-time data refresh

**Metrics Displayed:**
- Completion percentage
- Time spent per lesson
- Quiz scores
- Last activity timestamp
- Engagement score (calculated metric)

### Feature 2: Dropout Detector
Intelligent early warning system that identifies at-risk students and enables proactive intervention.

**Capabilities:**
- Inactivity tracking (customizable thresholds)
- Risk scoring algorithm (0-100 scale)
- Automated notifications (email + BuddyBoss)
- At-risk student dashboard
- Bulk re-engagement tools
- Intervention tracking
- Instructor performance metrics

**Detection Criteria:**
- Days since last login
- Lessons completion velocity (declining trend)
- Quiz attempt frequency
- Forum/group participation (BuddyBoss)
- Assignment submission patterns

---

## 🏗️ Architecture Requirements

### Plugin Structure (Boilerplate Pattern)

```
lms-analytics-pro/
├── lms-analytics-pro.php                 # Main plugin file (<200 lines)
├── README.md                              # Installation & usage guide
├── LICENSE.txt                            # GPL v2 or later
├── .gitignore
├── composer.json                          # PSR-4 autoloading
│
├── includes/
│   ├── class-lap-activator.php           # Activation routines (<300 lines)
│   ├── class-lap-deactivator.php         # Cleanup on deactivation (<200 lines)
│   ├── class-lap-core.php                # Main plugin orchestrator (<500 lines)
│   ├── class-lap-loader.php              # Hook management (<300 lines)
│   ├── class-lap-i18n.php                # Internationalization (<150 lines)
│   │
│   ├── analytics/
│   │   ├── class-lap-heatmap-engine.php       # Heatmap data processing (<800 lines)
│   │   ├── class-lap-progress-calculator.php  # Progress metrics (<600 lines)
│   │   ├── class-lap-data-aggregator.php      # Data collection (<700 lines)
│   │   └── class-lap-export-handler.php       # Export functionality (<500 lines)
│   │
│   ├── dropout/
│   │   ├── class-lap-risk-scorer.php          # Risk calculation algorithm (<700 lines)
│   │   ├── class-lap-activity-tracker.php     # Inactivity monitoring (<600 lines)
│   │   ├── class-lap-notification-manager.php # Alert system (<800 lines)
│   │   └── class-lap-intervention-logger.php  # Track interventions (<400 lines)
│   │
│   ├── integrations/
│   │   ├── class-lap-learndash-integration.php  # LearnDash hooks (<700 lines)
│   │   ├── class-lap-buddyboss-integration.php  # BuddyBoss integration (<700 lines)
│   │   └── class-lap-buddypress-compat.php      # BuddyPress fallback (<400 lines)
│   │
│   └── database/
│       ├── class-lap-db-manager.php        # Database operations (<600 lines)
│       ├── class-lap-query-builder.php     # SQL query helper (<500 lines)
│       └── class-lap-cache-handler.php     # Caching layer (<400 lines)
│
├── admin/
│   ├── class-lap-admin.php                # Admin area controller (<600 lines)
│   ├── class-lap-settings.php             # Settings API (<700 lines)
│   ├── class-lap-menu-manager.php         # Admin menu setup (<300 lines)
│   │
│   ├── views/
│   │   ├── dashboard.php                  # Main dashboard template (<400 lines)
│   │   ├── heatmap.php                    # Heatmap view (<500 lines)
│   │   ├── dropout-detector.php           # Dropout dashboard (<500 lines)
│   │   ├── settings.php                   # Settings page (<400 lines)
│   │   ├── reports.php                    # Reports interface (<400 lines)
│   │   └── partials/
│   │       ├── filters.php                # Filter UI component (<200 lines)
│   │       ├── student-row.php            # Heatmap row template (<150 lines)
│   │       ├── risk-card.php              # At-risk student card (<200 lines)
│   │       └── export-modal.php           # Export options modal (<250 lines)
│   │
│   ├── css/
│   │   ├── lap-admin.css                  # Main admin styles (<800 lines)
│   │   ├── lap-heatmap.css                # Heatmap-specific styles (<500 lines)
│   │   ├── lap-dropout.css                # Dropout detector styles (<400 lines)
│   │   └── lap-responsive.css             # Mobile responsiveness (<300 lines)
│   │
│   └── js/
│       ├── lap-admin.js                   # Core admin JS (<700 lines)
│       ├── lap-heatmap.js                 # Heatmap interactions (<800 lines)
│       ├── lap-dropout.js                 # Dropout dashboard JS (<600 lines)
│       ├── lap-filters.js                 # Filter logic (<400 lines)
│       ├── lap-charts.js                  # Chart.js wrapper (<500 lines)
│       └── lap-export.js                  # Export functionality (<400 lines)
│
├── public/
│   ├── class-lap-public.php               # Frontend controller (<400 lines)
│   ├── class-lap-shortcodes.php           # Shortcode definitions (<500 lines)
│   │
│   ├── css/
│   │   └── lap-public.css                 # Frontend styles (<400 lines)
│   │
│   └── js/
│       └── lap-public.js                  # Frontend JS (<400 lines)
│
├── languages/
│   └── lms-analytics-pro.pot              # Translation template
│
├── assets/
│   ├── images/
│   │   ├── icon-256x256.png
│   │   ├── banner-1544x500.png
│   │   └── screenshots/
│   │
│   └── fonts/                             # Icon fonts if needed
│
└── vendor/                                 # Composer dependencies
    └── autoload.php
```

**File Size Constraint:** No single PHP, CSS, or JS file shall exceed 1,000 lines. Split functionality into focused, single-responsibility classes.

---

## 🎨 UI/UX Requirements (10/10 Standard)

### Design Principles
1. **Modern & Clean:** Follow WordPress admin design patterns but with contemporary touches
2. **Responsive:** Mobile-first approach, works on tablets and phones
3. **Intuitive:** Zero learning curve for instructors
4. **Fast:** Perceived performance through loading states and progressive enhancement
5. **Accessible:** WCAG 2.1 AA compliant

### Color Scheme
```css
/* Primary Colors */
--lap-primary: #4F46E5;        /* Indigo - primary actions */
--lap-success: #10B981;        /* Green - positive metrics */
--lap-warning: #F59E0B;        /* Amber - caution states */
--lap-danger: #EF4444;         /* Red - critical alerts */

/* Heatmap Gradient */
--lap-heat-0: #F3F4F6;         /* Gray-100 - no progress */
--lap-heat-25: #DBEAFE;        /* Blue-100 */
--lap-heat-50: #93C5FD;        /* Blue-300 */
--lap-heat-75: #3B82F6;        /* Blue-500 */
--lap-heat-100: #1E40AF;       /* Blue-800 - complete */

/* Risk Levels */
--lap-risk-low: #D1FAE5;       /* Green tint */
--lap-risk-medium: #FEF3C7;    /* Yellow tint */
--lap-risk-high: #FEE2E2;      /* Red tint */
```

### Component Library
- **Cards:** Shadow-sm, rounded corners, hover effects
- **Tables:** Striped rows, sticky headers, sortable columns
- **Buttons:** Primary, secondary, ghost variants with loading states
- **Modals:** Smooth animations, backdrop blur
- **Tooltips:** Fast, contextual information on hover
- **Charts:** Chart.js for line/bar graphs, custom canvas for heatmap
- **Badges:** Color-coded status indicators
- **Notifications:** Toast-style success/error messages

### Heatmap Visualization
```
┌─────────────────────────────────────────────────────────┐
│  Student Progress Heatmap                        [≡]    │
├─────────────────────────────────────────────────────────┤
│  Filters: [All Courses ▾] [All Groups ▾] [Last 30d ▾]  │
│  Search: [🔍 Find student...]           [📊 Export ▾]  │
├──────────┬────┬────┬────┬────┬────┬────┬────┬──────────┤
│  Student │ L1 │ L2 │ L3 │ L4 │ L5 │ L6 │ L7 │ Avg %    │
├──────────┼────┼────┼────┼────┼────┼────┼────┼──────────┤
│ Alice    │████│████│████│░░░░│    │    │    │  52%     │
│ Bob      │████│████│████│████│████│░░░░│    │  75%     │
│ Charlie  │████│░░░░│    │    │    │    │    │  23% ⚠️  │
└──────────┴────┴────┴────┴────┴────┴────┴────┴──────────┘

Legend: [████ 100%] [░░░░ 50%] [     0%]
Hover any cell for details: completion %, time spent, quiz score
```

### Dropout Detector Dashboard
```
┌─────────────────────────────────────────────────────────┐
│  At-Risk Students (12)                    [⚙️ Settings] │
├─────────────────────────────────────────────────────────┤
│  Risk Level: [All ▾] [🔴 High] [🟡 Medium] [🟢 Low]    │
├─────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────┐   │
│  │ 🔴 Charlie Wilson              Risk Score: 87   │   │
│  │ ─────────────────────────────────────────────── │   │
│  │ • Last login: 18 days ago                       │   │
│  │ • Completion: 23% (declining)                   │   │
│  │ • No quiz attempts in 21 days                   │   │
│  │                                                  │   │
│  │ [📧 Send Message] [✓ Mark Contacted] [⋮ More]  │   │
│  └─────────────────────────────────────────────────┘   │
│                                                          │
│  ┌─────────────────────────────────────────────────┐   │
│  │ 🟡 Dana Martinez              Risk Score: 54    │   │
│  │ ... (similar layout)                            │   │
│  └─────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

---

## 💾 Database Schema

### Custom Tables (Prefix: `wp_lap_`)

#### 1. `wp_lap_student_progress`
Stores granular progress tracking data.

```sql
CREATE TABLE wp_lap_student_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    lesson_id BIGINT UNSIGNED NOT NULL,
    topic_id BIGINT UNSIGNED DEFAULT 0,
    completion_status TINYINT DEFAULT 0 COMMENT '0=not_started, 1=in_progress, 2=completed',
    completion_percentage DECIMAL(5,2) DEFAULT 0.00,
    time_spent_seconds INT UNSIGNED DEFAULT 0,
    last_activity DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_course (user_id, course_id),
    INDEX idx_lesson (lesson_id),
    INDEX idx_activity (last_activity),
    UNIQUE KEY unique_progress (user_id, lesson_id, topic_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 2. `wp_lap_risk_scores`
Tracks dropout risk calculations.

```sql
CREATE TABLE wp_lap_risk_scores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    risk_score TINYINT UNSIGNED DEFAULT 0 COMMENT '0-100',
    risk_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'low',
    factors JSON COMMENT 'Breakdown of risk factors',
    last_login DATETIME NULL,
    days_inactive INT UNSIGNED DEFAULT 0,
    trend VARCHAR(20) DEFAULT 'stable' COMMENT 'improving|stable|declining',
    calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user (user_id),
    INDEX idx_risk_level (risk_level),
    INDEX idx_calculated (calculated_at),
    UNIQUE KEY unique_user_course (user_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 3. `wp_lap_interventions`
Logs instructor outreach and intervention attempts.

```sql
CREATE TABLE wp_lap_interventions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    instructor_id BIGINT UNSIGNED NOT NULL,
    intervention_type ENUM('email', 'message', 'call', 'meeting', 'other') NOT NULL,
    message TEXT,
    status ENUM('sent', 'opened', 'replied', 'resolved') DEFAULT 'sent',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user (user_id),
    INDEX idx_instructor (instructor_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 4. `wp_lap_activity_log`
High-frequency activity tracking (for trend analysis).

```sql
CREATE TABLE wp_lap_activity_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    activity_type VARCHAR(50) NOT NULL COMMENT 'login|lesson_view|quiz_attempt|etc',
    course_id BIGINT UNSIGNED DEFAULT 0,
    lesson_id BIGINT UNSIGNED DEFAULT 0,
    metadata JSON,
    activity_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_activity (user_id, activity_timestamp),
    INDEX idx_type (activity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 5. `wp_lap_cache`
Custom caching layer for expensive queries.

```sql
CREATE TABLE wp_lap_cache (
    cache_key VARCHAR(191) PRIMARY KEY,
    cache_value LONGTEXT NOT NULL,
    cache_group VARCHAR(50) DEFAULT 'default',
    expiration DATETIME NOT NULL,
    
    INDEX idx_expiration (expiration),
    INDEX idx_group (cache_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔧 Technical Specifications

### Code Standards

#### PHP (WordPress Coding Standards)
```php
<?php
/**
 * Class LAP_Heatmap_Engine
 *
 * Processes and generates heatmap visualization data from student progress records.
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Analytics
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

class LAP_Heatmap_Engine {
    
    /**
     * Function prefix for all public methods.
     * 
     * @var string
     */
    private const PREFIX = 'lap_';
    
    /**
     * Database manager instance.
     *
     * @var LAP_DB_Manager
     */
    private $db;
    
    /**
     * Cache handler instance.
     *
     * @var LAP_Cache_Handler
     */
    private $cache;
    
    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     * @param LAP_DB_Manager     $db    Database manager.
     * @param LAP_Cache_Handler  $cache Cache handler.
     */
    public function __construct( LAP_DB_Manager $db, LAP_Cache_Handler $cache ) {
        $this->db    = $db;
        $this->cache = $cache;
    }
    
    /**
     * Generate heatmap data for given parameters.
     *
     * @since 1.0.0
     *
     * @param array $args {
     *     Optional. Arguments for filtering heatmap data.
     *
     *     @type int    $course_id   Course ID to filter by. Default 0 (all).
     *     @type int    $group_id    BuddyBoss group ID. Default 0 (all).
     *     @type string $date_from   Start date (Y-m-d format). Default 30 days ago.
     *     @type string $date_to     End date (Y-m-d format). Default today.
     *     @type int    $per_page    Students per page. Default 50.
     *     @type int    $page        Current page. Default 1.
     * }
     * @return array {
     *     Heatmap data structure.
     *
     *     @type array $students   Student rows with progress data.
     *     @type array $lessons    Lesson/topic columns.
     *     @type array $metadata   Summary statistics.
     * }
     */
    public function lap_generate_heatmap_data( $args = array() ) {
        // Implementation here...
    }
    
    // Additional methods...
}
```

**Key Requirements:**
- ✅ All classes prefixed with `LAP_`
- ✅ All public functions prefixed with `lap_`
- ✅ PHPDoc blocks for every class, method, property
- ✅ Type hints (PHP 7.4+)
- ✅ Strict mode: `defined( 'ABSPATH' ) || exit;`
- ✅ Single responsibility per class
- ✅ Dependency injection via constructor

#### JavaScript (ES6+)
```javascript
/**
 * LMS Analytics Pro - Heatmap Visualization
 * 
 * @package    LMS_Analytics_Pro
 * @subpackage Admin/JS
 * @since      1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Heatmap controller class.
     */
    class LAPHeatmap {
        
        /**
         * Constructor.
         *
         * @param {string} containerId - DOM element ID.
         * @param {Object} options     - Configuration options.
         */
        constructor(containerId, options = {}) {
            this.container = document.getElementById(containerId);
            this.options = {
                cellSize: 40,
                colorScheme: 'blue',
                refreshInterval: 30000,
                ...options
            };
            
            this.data = null;
            this.filters = {};
            
            this.init();
        }
        
        /**
         * Initialize heatmap.
         */
        init() {
            this.setupEventListeners();
            this.loadData();
        }
        
        /**
         * Load heatmap data via AJAX.
         *
         * @return {Promise}
         */
        async loadData() {
            try {
                const response = await fetch(lapAdminAjax.ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'lap_get_heatmap_data',
                        nonce: lapAdminAjax.nonce,
                        filters: JSON.stringify(this.filters)
                    })
                });
                
                if (!response.ok) throw new Error('Network response failed');
                
                this.data = await response.json();
                this.render();
                
            } catch (error) {
                console.error('LAP Heatmap Error:', error);
                this.showError('Failed to load heatmap data');
            }
        }
        
        // Additional methods...
    }
    
    // Initialize on DOM ready
    $(document).ready(function() {
        if ($('#lap-heatmap-container').length) {
            window.lapHeatmap = new LAPHeatmap('lap-heatmap-container');
        }
    });
    
})(jQuery);
```

**Key Requirements:**
- ✅ ES6 classes for organization
- ✅ Async/await for AJAX
- ✅ JSDoc comments
- ✅ jQuery wrapper for compatibility
- ✅ Namespace prefix `lap`
- ✅ Error handling

#### CSS (BEM Methodology)
```css
/**
 * LMS Analytics Pro - Heatmap Styles
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Admin/CSS
 */

/* Heatmap Container */
.lap-heatmap {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 24px;
}

.lap-heatmap__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.lap-heatmap__title {
    font-size: 20px;
    font-weight: 600;
    color: #1F2937;
}

.lap-heatmap__filters {
    display: flex;
    gap: 12px;
}

/* Heatmap Grid */
.lap-heatmap__grid {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.lap-heatmap__table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 2px;
}

.lap-heatmap__cell {
    width: 40px;
    height: 40px;
    border-radius: 4px;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
}

.lap-heatmap__cell:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 10;
}

/* Heat levels (0-100%) */
.lap-heatmap__cell--0   { background-color: #F3F4F6; }
.lap-heatmap__cell--25  { background-color: #DBEAFE; }
.lap-heatmap__cell--50  { background-color: #93C5FD; }
.lap-heatmap__cell--75  { background-color: #3B82F6; }
.lap-heatmap__cell--100 { background-color: #1E40AF; }

/* Responsive */
@media (max-width: 768px) {
    .lap-heatmap__cell {
        width: 30px;
        height: 30px;
    }
}
```

**Key Requirements:**
- ✅ BEM naming convention
- ✅ Prefix all classes with `lap-`
- ✅ CSS custom properties for theming
- ✅ Responsive design
- ✅ Smooth transitions

---

## 🔌 Integration Strategy

### BuddyPress/BuddyBoss Compatibility

**Philosophy:** Hook into existing actions/filters; never modify core files.

#### Example: Group Integration
```php
/**
 * BuddyBoss Groups Integration
 *
 * @package    LMS_Analytics_Pro
 * @subpackage Integrations
 */
class LAP_BuddyBoss_Integration {
    
    /**
     * Register hooks.
     */
    public function lap_register_hooks() {
        // Add heatmap to group admin tab
        add_action( 'bp_group_admin_tabs', array( $this, 'lap_add_group_analytics_tab' ), 10, 2 );
        
        // Display analytics content
        add_action( 'bp_group_admin_edit_after', array( $this, 'lap_render_group_analytics' ) );
        
        // Track group activity
        add_action( 'bp_activity_posted_update', array( $this, 'lap_log_group_activity' ), 10, 3 );
    }
    
    /**
     * Add analytics tab to group settings.
     *
     * @param string      $active_tab Current active tab.
     * @param BP_Groups_Group $group  Current group object.
     */
    public function lap_add_group_analytics_tab( $active_tab, $group ) {
        $is_active = ( 'lap-analytics' === $active_tab ) ? ' class="current"' : '';
        
        echo '<li' . $is_active . '>';
        echo '<a href="' . esc_url( bp_get_group_admin_permalink( $group, 'lap-analytics' ) ) . '">';
        echo esc_html__( 'Analytics', 'lms-analytics-pro' );
        echo '</a>';
        echo '</li>';
    }
    
    // Additional methods...
}
```

**Integration Checklist:**
- ✅ Check if BuddyBoss/BuddyPress is active before hooks
- ✅ Use conditional loading: `if ( function_exists( 'bp_is_active' ) )`
- ✅ Respect group privacy settings
- ✅ Follow BP's template hierarchy
- ✅ Add settings to manage integration features

### LearnDash Hooks
```php
/**
 * Track lesson completion.
 *
 * @param array $data Completion data from LearnDash.
 */
public function lap_track_lesson_completion( $data ) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'lap_student_progress';
    
    $wpdb->insert(
        $table,
        array(
            'user_id'              => $data['user']->ID,
            'course_id'            => $data['course']->ID,
            'lesson_id'            => $data['lesson']->ID,
            'completion_status'    => 2, // completed
            'completion_percentage' => 100.00,
            'last_activity'        => current_time( 'mysql' ),
        ),
        array( '%d', '%d', '%d', '%d', '%f', '%s' )
    );
    
    // Recalculate risk score
    do_action( 'lap_recalculate_risk_score', $data['user']->ID, $data['course']->ID );
}

// Hook into LearnDash
add_action( 'learndash_lesson_completed', array( $this, 'lap_track_lesson_completion' ) );
```

---

## ⚙️ Settings & Configuration

### Settings Page Structure
Located at: **WordPress Admin → LMS Analytics Pro → Settings**

#### Tabs:
1. **General Settings**
   - Enable/disable features
   - Default filters
   - Cache duration
   
2. **Heatmap Settings**
   - Color scheme selection
   - Default view (course/group)
   - Cells per page
   - Hover tooltip options
   
3. **Dropout Detector**
   - Inactivity threshold (days)
   - Risk score weights (configurable algorithm)
   - Notification preferences
   - Auto-notification toggle
   
4. **Notifications**
   - Email templates (customizable)
   - BuddyBoss message templates
   - Notification schedule
   - Recipients (instructors/admins)
   
5. **Integrations**
   - BuddyBoss groups sync
   - LearnDash settings
   - Export format defaults
   
6. **Advanced**
   - Database cleanup schedule
   - Debug mode
   - Performance optimizations

#### Example: Settings API Implementation
```php
/**
 * Register plugin settings.
 */
public function lap_register_settings() {
    // General section
    add_settings_section(
        'lap_general_section',
        __( 'General Settings', 'lms-analytics-pro' ),
        array( $this, 'lap_general_section_callback' ),
        'lap-settings'
    );
    
    // Inactivity threshold
    add_settings_field(
        'lap_inactivity_days',
        __( 'Inactivity Threshold (days)', 'lms-analytics-pro' ),
        array( $this, 'lap_inactivity_days_callback' ),
        'lap-settings',
        'lap_general_section'
    );
    
    register_setting(
        'lap_settings_group',
        'lap_inactivity_days',
        array(
            'type'              => 'integer',
            'default'           => 7,
            'sanitize_callback' => 'absint',
        )
    );
}
```

---

## 📊 Algorithms & Calculations

### Risk Score Algorithm

**Formula:**
```
Risk Score (0-100) = (
    (Days Inactive × Weight_Inactivity) +
    (Completion Velocity Decline × Weight_Velocity) +
    (Quiz Performance Drop × Weight_Quiz) +
    (Forum Participation Drop × Weight_Forum) +
    (Assignment Delays × Weight_Assignment)
) / Total_Weights × 100

Default Weights (Configurable):
- Inactivity: 35%
- Completion Velocity: 25%
- Quiz Performance: 20%
- Forum Participation: 10%
- Assignment Delays: 10%
```

**Implementation:**
```php
/**
 * Calculate dropout risk score for a student.
 *
 * @since 1.0.0
 *
 * @param int $user_id   Student user ID.
 * @param int $course_id Course ID.
 * @return array {
 *     Risk score data.
 *
 *     @type int    $score       Risk score (0-100).
 *     @type string $level       Risk level (low|medium|high|critical).
 *     @type array  $factors     Breakdown of contributing factors.
 *     @type string $trend       Direction (improving|stable|declining).
 *     @type array  $suggestions Recommended interventions.
 * }
 */
public function lap_calculate_risk_score( $user_id, $course_id ) {
    $weights = $this->lap_get_risk_weights();
    $factors = array();
    
    // 1. Days Inactive Score (0-100)
    $last_login = $this->lap_get_last_login( $user_id );
    $days_inactive = $this->lap_calculate_days_inactive( $last_login );
    $inactivity_score = min( 100, ( $days_inactive / 30 ) * 100 );
    $factors['inactivity'] = array(
        'value' => $days_inactive,
        'score' => $inactivity_score,
        'weight' => $weights['inactivity'],
    );
    
    // 2. Completion Velocity (comparing last 7 vs previous 7 days)
    $velocity_current = $this->lap_get_completion_velocity( $user_id, $course_id, 7 );
    $velocity_previous = $this->lap_get_completion_velocity( $user_id, $course_id, 14, 7 );
    $velocity_decline = max( 0, ( $velocity_previous - $velocity_current ) / max( $velocity_previous, 1 ) * 100 );
    $factors['velocity'] = array(
        'current' => $velocity_current,
        'previous' => $velocity_previous,
        'score' => $velocity_decline,
        'weight' => $weights['velocity'],
    );
    
    // 3. Quiz Performance Drop
    $quiz_current = $this->lap_get_average_quiz_score( $user_id, $course_id, 7 );
    $quiz_baseline = $this->lap_get_average_quiz_score( $user_id, $course_id, 30 );
    $quiz_drop = max( 0, ( $quiz_baseline - $quiz_current ) );
    $factors['quiz'] = array(
        'current_avg' => $quiz_current,
        'baseline_avg' => $quiz_baseline,
        'score' => $quiz_drop,
        'weight' => $weights['quiz'],
    );
    
    // 4. Forum Participation (if BuddyBoss active)
    if ( function_exists( 'bp_is_active' ) ) {
        $forum_current = $this->lap_get_forum_activity( $user_id, 7 );
        $forum_baseline = $this->lap_get_forum_activity( $user_id, 30 );
        $forum_drop = max( 0, ( $forum_baseline - $forum_current ) / max( $forum_baseline, 1 ) * 100 );
        $factors['forum'] = array(
            'current' => $forum_current,
            'baseline' => $forum_baseline,
            'score' => $forum_drop,
            'weight' => $weights['forum'],
        );
    } else {
        $factors['forum'] = array( 'score' => 0, 'weight' => 0 );
    }
    
    // 5. Assignment Delays
    $assignment_delays = $this->lap_get_assignment_delay_count( $user_id, $course_id, 30 );
    $assignment_score = min( 100, $assignment_delays * 20 );
    $factors['assignments'] = array(
        'delayed_count' => $assignment_delays,
        'score' => $assignment_score,
        'weight' => $weights['assignments'],
    );
    
    // Calculate weighted total
    $total_score = 0;
    $total_weights = 0;
    foreach ( $factors as $factor ) {
        $total_score += $factor['score'] * $factor['weight'];
        $total_weights += $factor['weight'];
    }
    
    $risk_score = round( $total_score / max( $total_weights, 1 ) );
    
    // Determine risk level
    $risk_level = $this->lap_determine_risk_level( $risk_score );
    
    // Analyze trend (compare with score from 7 days ago)
    $previous_score = $this->lap_get_previous_risk_score( $user_id, $course_id, 7 );
    $trend = $this->lap_calculate_trend( $risk_score, $previous_score );
    
    // Generate intervention suggestions
    $suggestions = $this->lap_generate_intervention_suggestions( $factors, $risk_level );
    
    return array(
        'score' => $risk_score,
        'level' => $risk_level,
        'factors' => $factors,
        'trend' => $trend,
        'suggestions' => $suggestions,
        'calculated_at' => current_time( 'mysql' ),
    );
}

/**
 * Determine risk level from score.
 *
 * @param int $score Risk score (0-100).
 * @return string Risk level.
 */
private function lap_determine_risk_level( $score ) {
    if ( $score >= 75 ) {
        return 'critical';
    } elseif ( $score >= 50 ) {
        return 'high';
    } elseif ( $score >= 25 ) {
        return 'medium';
    } else {
        return 'low';
    }
}
```

### Completion Percentage Calculation
```php
/**
 * Calculate course completion percentage.
 *
 * @param int $user_id   Student user ID.
 * @param int $course_id Course ID.
 * @return float Completion percentage (0.00-100.00).
 */
public function lap_calculate_completion_percentage( $user_id, $course_id ) {
    // Get all lessons/topics in course
    $total_items = $this->lap_get_course_content_count( $course_id );
    
    if ( $total_items === 0 ) {
        return 0.00;
    }
    
    // Get completed items
    $completed_items = $this->lap_get_completed_items_count( $user_id, $course_id );
    
    return round( ( $completed_items / $total_items ) * 100, 2 );
}
```

---

## 🔔 Notification System

### Email Templates

#### 1. At-Risk Student Alert (to Instructor)
```php
/**
 * Email template for at-risk student notification.
 *
 * @param array $data Student and risk data.
 * @return string HTML email content.
 */
public function lap_get_risk_alert_template( $data ) {
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
                    <?php if ( $factor_data['score'] > 30 ) : ?>
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
```

#### 2. Re-Engagement Message (to Student)
```php
/**
 * Email template for student re-engagement.
 *
 * @param array $data Student and course data.
 * @return string HTML email content.
 */
public function lap_get_reengagement_template( $data ) {
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
                        <div class="stat-value"><?php echo esc_html( $data['lessons_remaining'] ); ?></div>
                        <div class="stat-label">Lessons Left</div>
                    </div>
                </div>
                
                <p>You're doing great! Just <strong><?php echo esc_html( $data['lessons_remaining'] ); ?> more lessons</strong> to complete this course.</p>
                
                <p><strong>Next up:</strong> <?php echo esc_html( $data['next_lesson_title'] ); ?></p>
                
                <center>
                    <a href="<?php echo esc_url( $data['next_lesson_url'] ); ?>" class="action-button">
                        Continue Learning →
                    </a>
                </center>
                
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
```

### BuddyBoss Private Message Integration
```php
/**
 * Send BuddyBoss private message to at-risk student.
 *
 * @param int   $user_id        Student user ID.
 * @param int   $instructor_id  Instructor user ID.
 * @param array $risk_data      Risk score data.
 * @return bool|int Message ID on success, false on failure.
 */
public function lap_send_buddyboss_message( $user_id, $instructor_id, $risk_data ) {
    if ( ! function_exists( 'messages_new_message' ) ) {
        return false;
    }
    
    $subject = sprintf(
        __( 'Checking in on your progress in %s', 'lms-analytics-pro' ),
        $risk_data['course_name']
    );
    
    $message = sprintf(
        __( 'Hi %1$s,

I noticed you haven\'t been active in %2$s recently. I wanted to reach out and see if everything is okay, and if there\'s anything I can help with.

Your current progress: %3$d%%

If you\'re facing any challenges or have questions, please don\'t hesitate to reply to this message. I\'m here to support you!

Best regards,
%4$s', 'lms-analytics-pro' ),
        bp_core_get_user_displayname( $user_id ),
        $risk_data['course_name'],
        $risk_data['completion_percentage'],
        bp_core_get_user_displayname( $instructor_id )
    );
    
    $message_id = messages_new_message( array(
        'sender_id'  => $instructor_id,
        'recipients' => array( $user_id ),
        'subject'    => $subject,
        'content'    => $message,
    ) );
    
    // Log intervention
    if ( $message_id ) {
        $this->lap_log_intervention( $user_id, $instructor_id, 'message', $message );
    }
    
    return $message_id;
}
```

---

## 📤 Export Functionality

### Export Formats

#### CSV Export
```php
/**
 * Export heatmap data to CSV.
 *
 * @param array $data     Heatmap data.
 * @param array $options  Export options.
 * @return string File path to generated CSV.
 */
public function lap_export_to_csv( $data, $options = array() ) {
    $filename = 'heatmap-' . date( 'Y-m-d-His' ) . '.csv';
    $filepath = wp_upload_dir()['path'] . '/' . $filename;
    
    $fp = fopen( $filepath, 'w' );
    
    // Header row
    $headers = array( 'Student Name', 'Email' );
    foreach ( $data['lessons'] as $lesson ) {
        $headers[] = $lesson['title'];
    }
    $headers[] = 'Average Completion %';
    $headers[] = 'Risk Score';
    
    fputcsv( $fp, $headers );
    
    // Data rows
    foreach ( $data['students'] as $student ) {
        $row = array(
            $student['name'],
            $student['email'],
        );
        
        foreach ( $student['progress'] as $progress ) {
            $row[] = $progress['completion_percentage'] . '%';
        }
        
        $row[] = $student['average_completion'] . '%';
        $row[] = $student['risk_score'];
        
        fputcsv( $fp, $row );
    }
    
    fclose( $fp );
    
    return $filepath;
}
```

#### PDF Export (using TCPDF)
```php
/**
 * Export heatmap to PDF.
 *
 * @param array $data    Heatmap data.
 * @param array $options Export options.
 * @return string File path to generated PDF.
 */
public function lap_export_to_pdf( $data, $options = array() ) {
    require_once plugin_dir_path( __FILE__ ) . '../vendor/tecnickcom/tcpdf/tcpdf.php';
    
    $pdf = new TCPDF( 'L', PDF_UNIT, 'A4', true, 'UTF-8', false );
    
    $pdf->SetCreator( 'LMS Analytics Pro' );
    $pdf->SetTitle( 'Student Progress Heatmap' );
    
    $pdf->AddPage();
    
    // Title
    $pdf->SetFont( 'helvetica', 'B', 16 );
    $pdf->Cell( 0, 10, 'Student Progress Heatmap', 0, 1, 'C' );
    $pdf->Ln( 5 );
    
    // Metadata
    $pdf->SetFont( 'helvetica', '', 10 );
    $pdf->Cell( 0, 5, 'Generated: ' . date( 'F j, Y g:i a' ), 0, 1 );
    $pdf->Cell( 0, 5, 'Course: ' . $data['course_name'], 0, 1 );
    $pdf->Ln( 10 );
    
    // Table
    $html = $this->lap_generate_pdf_table_html( $data );
    $pdf->writeHTML( $html, true, false, true, false, '' );
    
    // Save
    $filename = 'heatmap-' . date( 'Y-m-d-His' ) . '.pdf';
    $filepath = wp_upload_dir()['path'] . '/' . $filename;
    $pdf->Output( $filepath, 'F' );
    
    return $filepath;
}
```

---

## 🔐 Security & Permissions

### Capability Management
```php
/**
 * Register custom capabilities.
 */
public function lap_register_capabilities() {
    $admin_role = get_role( 'administrator' );
    $instructor_role = get_role( 'group_leader' ); // BuddyBoss
    
    $capabilities = array(
        'lap_view_analytics',      // View dashboards
        'lap_view_all_students',   // View all students (admin only)
        'lap_export_data',         // Export reports
        'lap_send_notifications',  // Send re-engagement messages
        'lap_manage_settings',     // Access settings (admin only)
    );
    
    foreach ( $capabilities as $cap ) {
        $admin_role->add_cap( $cap );
        
        // Instructors get limited permissions
        if ( in_array( $cap, array( 'lap_view_analytics', 'lap_export_data', 'lap_send_notifications' ) ) ) {
            $instructor_role->add_cap( $cap );
        }
    }
}

/**
 * Check if user can view student analytics.
 *
 * @param int $user_id         User to check.
 * @param int $student_user_id Student being viewed.
 * @return bool True if allowed.
 */
public function lap_user_can_view_student( $user_id, $student_user_id ) {
    // Admins can view anyone
    if ( user_can( $user_id, 'lap_view_all_students' ) ) {
        return true;
    }
    
    // Instructors can only view their own students
    if ( user_can( $user_id, 'lap_view_analytics' ) ) {
        return $this->lap_is_student_of_instructor( $student_user_id, $user_id );
    }
    
    // Students can only view themselves
    return $user_id === $student_user_id;
}
```

### Data Sanitization
```php
/**
 * Sanitize filter input.
 *
 * @param array $filters Raw filter data.
 * @return array Sanitized filters.
 */
public function lap_sanitize_filters( $filters ) {
    $clean = array();
    
    if ( isset( $filters['course_id'] ) ) {
        $clean['course_id'] = absint( $filters['course_id'] );
    }
    
    if ( isset( $filters['group_id'] ) ) {
        $clean['group_id'] = absint( $filters['group_id'] );
    }
    
    if ( isset( $filters['date_from'] ) ) {
        $clean['date_from'] = sanitize_text_field( $filters['date_from'] );
        // Validate date format
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $clean['date_from'] ) ) {
            unset( $clean['date_from'] );
        }
    }
    
    if ( isset( $filters['date_to'] ) ) {
        $clean['date_to'] = sanitize_text_field( $filters['date_to'] );
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $clean['date_to'] ) ) {
            unset( $clean['date_to'] );
        }
    }
    
    return $clean;
}
```

### AJAX Security
```php
/**
 * AJAX handler for heatmap data.
 */
public function lap_ajax_get_heatmap_data() {
    // Verify nonce
    check_ajax_referer( 'lap_admin_nonce', 'nonce' );
    
    // Check permissions
    if ( ! current_user_can( 'lap_view_analytics' ) ) {
        wp_send_json_error( array(
            'message' => __( 'You do not have permission to view analytics.', 'lms-analytics-pro' ),
        ), 403 );
    }
    
    // Sanitize input
    $filters = $this->lap_sanitize_filters( $_POST['filters'] ?? array() );
    
    // Rate limiting
    if ( ! $this->lap_check_rate_limit( get_current_user_id() ) ) {
        wp_send_json_error( array(
            'message' => __( 'Too many requests. Please wait a moment.', 'lms-analytics-pro' ),
        ), 429 );
    }
    
    // Get data
    $heatmap = new LAP_Heatmap_Engine( $this->db, $this->cache );
    $data = $heatmap->lap_generate_heatmap_data( $filters );
    
    wp_send_json_success( $data );
}
```

---

## ⚡ Performance Optimization

### Caching Strategy
```php
/**
 * Cache handler with automatic invalidation.
 */
class LAP_Cache_Handler {
    
    /**
     * Cache duration in seconds.
     *
     * @var int
     */
    private $cache_duration = 3600; // 1 hour
    
    /**
     * Get cached data.
     *
     * @param string $key   Cache key.
     * @param string $group Cache group.
     * @return mixed|false Cached data or false.
     */
    public function lap_get( $key, $group = 'default' ) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'lap_cache';
        $cache_key = $this->lap_generate_cache_key( $key, $group );
        
        $result = $wpdb->get_row( $wpdb->prepare(
            "SELECT cache_value, expiration FROM {$table} 
            WHERE cache_key = %s AND expiration > NOW()",
            $cache_key
        ) );
        
        if ( $result ) {
            return maybe_unserialize( $result->cache_value );
        }
        
        return false;
    }
    
    /**
     * Set cache data.
     *
     * @param string $key      Cache key.
     * @param mixed  $value    Data to cache.
     * @param string $group    Cache group.
     * @param int    $duration Cache duration in seconds.
     * @return bool Success.
     */
    public function lap_set( $key, $value, $group = 'default', $duration = null ) {
        global $wpdb;
        
        $duration = $duration ?? $this->cache_duration;
        $table = $wpdb->prefix . 'lap_cache';
        $cache_key = $this->lap_generate_cache_key( $key, $group );
        
        return $wpdb->replace(
            $table,
            array(
                'cache_key'   => $cache_key,
                'cache_value' => maybe_serialize( $value ),
                'cache_group' => $group,
                'expiration'  => date( 'Y-m-d H:i:s', time() + $duration ),
            ),
            array( '%s', '%s', '%s', '%s' )
        );
    }
    
    /**
     * Invalidate cache by group.
     *
     * @param string $group Cache group.
     * @return bool Success.
     */
    public function lap_invalidate_group( $group ) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'lap_cache';
        
        return $wpdb->delete(
            $table,
            array( 'cache_group' => $group ),
            array( '%s' )
        );
    }
    
    /**
     * Clean expired cache entries.
     */
    public function lap_clean_expired() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'lap_cache';
        
        $wpdb->query( "DELETE FROM {$table} WHERE expiration < NOW()" );
    }
}
```

### Database Query Optimization
```php
/**
 * Optimized query for heatmap data.
 *
 * Uses JOINs instead of multiple queries.
 */
public function lap_get_heatmap_data_optimized( $course_id, $limit = 50, $offset = 0 ) {
    global $wpdb;
    
    $progress_table = $wpdb->prefix . 'lap_student_progress';
    $users_table = $wpdb->users;
    
    $sql = $wpdb->prepare(
        "SELECT 
            u.ID as user_id,
            u.display_name,
            u.user_email,
            p.lesson_id,
            p.completion_percentage,
            p.time_spent_seconds,
            p.last_activity
        FROM {$users_table} u
        INNER JOIN {$progress_table} p ON u.ID = p.user_id
        WHERE p.course_id = %d
        ORDER BY u.display_name, p.lesson_id
        LIMIT %d OFFSET %d",
        $course_id,
        $limit,
        $offset
    );
    
    return $wpdb->get_results( $sql, ARRAY_A );
}
```

### Lazy Loading
```javascript
/**
 * Lazy load heatmap cells for better performance.
 */
class LAPHeatmapLazyLoader {
    constructor(container, cellsData) {
        this.container = container;
        this.cellsData = cellsData;
        this.observer = null;
        this.init();
    }
    
    init() {
        // Intersection Observer for lazy loading
        this.observer = new IntersectionObserver(
            (entries) => this.handleIntersection(entries),
            {
                root: this.container,
                rootMargin: '100px',
                threshold: 0.01
            }
        );
        
        // Observe all cells
        this.container.querySelectorAll('.lap-heatmap__cell--lazy').forEach(cell => {
            this.observer.observe(cell);
        });
    }
    
    handleIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                this.loadCell(entry.target);
                this.observer.unobserve(entry.target);
            }
        });
    }
    
    loadCell(cell) {
        const userId = cell.dataset.userId;
        const lessonId = cell.dataset.lessonId;
        const data = this.cellsData[`${userId}-${lessonId}`];
        
        if (data) {
            cell.style.backgroundColor = this.getColorForCompletion(data.completion);
            cell.classList.remove('lap-heatmap__cell--lazy');
            cell.classList.add('lap-heatmap__cell--loaded');
            
            // Add tooltip data
            cell.setAttribute('title', `${data.completion}% complete`);
        }
    }
    
    getColorForCompletion(percentage) {
        if (percentage >= 90) return '#1E40AF';
        if (percentage >= 70) return '#3B82F6';
        if (percentage >= 50) return '#93C5FD';
        if (percentage >= 25) return '#DBEAFE';
        return '#F3F4F6';
    }
}
```

---

## 🧪 Testing Requirements

### Unit Tests (PHPUnit)
```php
/**
 * Test risk score calculation.
 *
 * @covers LAP_Risk_Scorer::lap_calculate_risk_score
 */
class Test_LAP_Risk_Scorer extends WP_UnitTestCase {
    
    private $scorer;
    
    public function setUp(): void {
        parent::setUp();
        $this->scorer = new LAP_Risk_Scorer();
    }
    
    /**
     * Test basic risk score calculation.
     */
    public function test_basic_risk_calculation() {
        $user_id = $this->factory->user->create();
        $course_id = $this->factory->post->create( array( 'post_type' => 'sfwd-courses' ) );
        
        // Mock inactivity
        $this->mock_user_inactivity( $user_id, 15 ); // 15 days
        
        $result = $this->scorer->lap_calculate_risk_score( $user_id, $course_id );
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'score', $result );
        $this->assertGreaterThan( 0, $result['score'] );
        $this->assertLessThanOrEqual( 100, $result['score'] );
    }
    
    /**
     * Test risk level determination.
     */
    public function test_risk_level_determination() {
        $this->assertEquals( 'low', $this->scorer->lap_determine_risk_level( 10 ) );
        $this->assertEquals( 'medium', $this->scorer->lap_determine_risk_level( 30 ) );
        $this->assertEquals( 'high', $this->scorer->lap_determine_risk_level( 60 ) );
        $this->assertEquals( 'critical', $this->scorer->lap_determine_risk_level( 85 ) );
    }
    
    /**
     * Test that risk score doesn't exceed 100.
     */
    public function test_risk_score_max_boundary() {
        $user_id = $this->factory->user->create();
        $course_id = $this->factory->post->create( array( 'post_type' => 'sfwd-courses' ) );
        
        // Mock extreme inactivity
        $this->mock_user_inactivity( $user_id, 365 );
        
        $result = $this->scorer->lap_calculate_risk_score( $user_id, $course_id );
        
        $this->assertLessThanOrEqual( 100, $result['score'] );
    }
}
```

### JavaScript Tests (Jest)
```javascript
/**
 * Test heatmap data processing.
 */
describe('LAPHeatmap', () => {
    let heatmap;
    
    beforeEach(() => {
        document.body.innerHTML = '<div id="lap-heatmap-container"></div>';
        heatmap = new LAPHeatmap('lap-heatmap-container');
    });
    
    test('should initialize with default options', () => {
        expect(heatmap.options.cellSize).toBe(40);
        expect(heatmap.options.colorScheme).toBe('blue');
    });
    
    test('should calculate correct color for completion percentage', () => {
        expect(heatmap.getColorForCompletion(100)).toBe('#1E40AF');
        expect(heatmap.getColorForCompletion(75)).toBe('#3B82F6');
        expect(heatmap.getColorForCompletion(50)).toBe('#93C5FD');
        expect(heatmap.getColorForCompletion(25)).toBe('#DBEAFE');
        expect(heatmap.getColorForCompletion(0)).toBe('#F3F4F6');
    });
    
    test('should handle empty data gracefully', () => {
        heatmap.data = { students: [], lessons: [] };
        expect(() => heatmap.render()).not.toThrow();
    });
});
```

### Browser Testing Matrix
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile Safari (iOS 14+)
- ✅ Chrome Mobile (Android)

---

## 📱 Responsive Design Breakpoints

```css
/* Mobile First Approach */

/* Base styles (Mobile, < 640px) */
.lap-heatmap__table {
    font-size: 12px;
}

.lap-heatmap__cell {
    width: 30px;
    height: 30px;
}

/* Tablet (640px - 1024px) */
@media (min-width: 640px) {
    .lap-heatmap__table {
        font-size: 14px;
    }
    
    .lap-heatmap__cell {
        width: 35px;
        height: 35px;
    }
    
    .lap-heatmap__filters {
        flex-direction: row;
    }
}

/* Desktop (1024px - 1280px) */
@media (min-width: 1024px) {
    .lap-heatmap__cell {
        width: 40px;
        height: 40px;
    }
    
    .lap-admin-layout {
        display: grid;
        grid-template-columns: 250px 1fr;
    }
}

/* Large Desktop (> 1280px) */
@media (min-width: 1280px) {
    .lap-heatmap__cell {
        width: 45px;
        height: 45px;
    }
    
    .lap-container {
        max-width: 1400px;
        margin: 0 auto;
    }
}

/* Touch Device Adjustments */
@media (hover: none) and (pointer: coarse) {
    .lap-heatmap__cell {
        width: 40px;
        height: 40px;
        /* Larger tap targets */
    }
    
    .lap-tooltip {
        display: none; /* Use click instead */
    }
}
```

---

## 🌐 Internationalization (i18n)

### Translation Functions
```php
/**
 * Load plugin text domain.
 */
public function lap_load_textdomain() {
    load_plugin_textdomain(
        'lms-analytics-pro',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
}

// Hook
add_action( 'plugins_loaded', array( $this, 'lap_load_textdomain' ) );

/**
 * Example translated strings.
 */
__( 'Student Progress Heatmap', 'lms-analytics-pro' );
__( 'At-Risk Students', 'lms-analytics-pro' );
_n( '%s student', '%s students', $count, 'lms-analytics-pro' );
_x( 'High', 'risk level', 'lms-analytics-pro' );
esc_html__( 'Export Report', 'lms-analytics-pro' );

// Translators comment
/* translators: %s: student name */
sprintf( __( 'Message sent to %s', 'lms-analytics-pro' ), $name );
```

### JavaScript Translations
```php
/**
 * Enqueue script with translations.
 */
public function lap_enqueue_admin_scripts() {
    wp_enqueue_script(
        'lap-admin',
        LAP_PLUGIN_URL . 'admin/js/lap-admin.js',
        array( 'jquery' ),
        LAP_VERSION,
        true
    );
    
    // Make translations available to JS
    wp_set_script_translations( 'lap-admin', 'lms-analytics-pro' );
}
```

```javascript
// In JavaScript
import { __, _n, sprintf } from '@wordpress/i18n';

const message = __( 'Data loaded successfully', 'lms-analytics-pro' );
const count = _n( '%d student', '%d students', total, 'lms-analytics-pro' );
const formatted = sprintf( __( 'Risk score: %d', 'lms-analytics-pro' ), score );
```

---

## 🚀 Deployment & Updates

### Plugin Header
```php
<?php
/**
 * Plugin Name:       LMS Analytics Pro
 * Plugin URI:        https://example.com/lms-analytics-pro
 * Description:       Comprehensive student analytics with progress heatmaps and intelligent dropout detection for LearnDash and BuddyBoss.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lms-analytics-pro
 * Domain Path:       /languages
 *
 * @package LMS_Analytics_Pro
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants
define( 'LAP_VERSION', '1.0.0' );
define( 'LAP_PLUGIN_FILE', __FILE__ );
define( 'LAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LAP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
```

### Activation Hook
```php
/**
 * Plugin activation.
 */
function lap_activate_plugin() {
    require_once LAP_PLUGIN_DIR . 'includes/class-lap-activator.php';
    LAP_Activator::lap_activate();
}
register_activation_hook( LAP_PLUGIN_FILE, 'lap_activate_plugin' );

/**
 * Activator class.
 */
class LAP_Activator {
    
    /**
     * Activate plugin.
     */
    public static function lap_activate() {
        // Check PHP version
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            deactivate_plugins( LAP_PLUGIN_BASENAME );
            wp_die( __( 'LMS Analytics Pro requires PHP 7.4 or higher.', 'lms-analytics-pro' ) );
        }
        
        // Check WordPress version
        if ( version_compare( get_bloginfo( 'version' ), '5.8', '<' ) ) {
            deactivate_plugins( LAP_PLUGIN_BASENAME );
            wp_die( __( 'LMS Analytics Pro requires WordPress 5.8 or higher.', 'lms-analytics-pro' ) );
        }
        
        // Check for required plugins
        if ( ! is_plugin_active( 'sfwd-lms/sfwd_lms.php' ) ) {
            deactivate_plugins( LAP_PLUGIN_BASENAME );
            wp_die( __( 'LMS Analytics Pro requires LearnDash to be installed and activated.', 'lms-analytics-pro' ) );
        }
        
        // Create database tables
        self::lap_create_tables();
        
        // Set default options
        self::lap_set_default_options();
        
        // Add capabilities
        self::lap_add_capabilities();
        
        // Schedule cron jobs
        self::lap_schedule_cron_jobs();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation flag
        set_transient( 'lap_activation_redirect', true, 30 );
    }
    
    /**
     * Create custom database tables.
     */
    private static function lap_create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        // Student progress table
        $sql = "CREATE TABLE {$wpdb->prefix}lap_student_progress (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            course_id BIGINT UNSIGNED NOT NULL,
            lesson_id BIGINT UNSIGNED NOT NULL,
            topic_id BIGINT UNSIGNED DEFAULT 0,
            completion_status TINYINT DEFAULT 0,
            completion_percentage DECIMAL(5,2) DEFAULT 0.00,
            time_spent_seconds INT UNSIGNED DEFAULT 0,
            last_activity DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_course (user_id, course_id),
            INDEX idx_lesson (lesson_id),
            INDEX idx_activity (last_activity),
            UNIQUE KEY unique_progress (user_id, lesson_id, topic_id)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Add other tables similarly...
        
        // Update database version
        update_option( 'lap_db_version', '1.0.0' );
    }
    
    /**
     * Set default plugin options.
     */
    private static function lap_set_default_options() {
        $defaults = array(
            'lap_inactivity_days'        => 7,
            'lap_risk_weights'           => array(
                'inactivity'  => 35,
                'velocity'    => 25,
                'quiz'        => 20,
                'forum'       => 10,
                'assignments' => 10,
            ),
            'lap_enable_notifications'   => true,
            'lap_notification_schedule'  => 'daily',
            'lap_cache_duration'         => 3600,
            'lap_default_color_scheme'   => 'blue',
            'lap_cells_per_page'         => 50,
        );
        
        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( $key ) ) {
                add_option( $key, $value );
            }
        }
    }
    
    /**
     * Schedule cron jobs.
     */
    private static function lap_schedule_cron_jobs() {
        if ( ! wp_next_scheduled( 'lap_daily_risk_calculation' ) ) {
            wp_schedule_event( time(), 'daily', 'lap_daily_risk_calculation' );
        }
        
        if ( ! wp_next_scheduled( 'lap_cleanup_cache' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'lap_cleanup_cache' );
        }
        
        if ( ! wp_next_scheduled( 'lap_send_risk_notifications' ) ) {
            wp_schedule_event( time(), 'daily', 'lap_send_risk_notifications' );
        }
    }
}
```

### Deactivation Hook
```php
/**
 * Plugin deactivation.
 */
function lap_deactivate_plugin() {
    require_once LAP_PLUGIN_DIR . 'includes/class-lap-deactivator.php';
    LAP_Deactivator::lap_deactivate();
}
register_deactivation_hook( LAP_PLUGIN_FILE, 'lap_deactivate_plugin' );

/**
 * Deactivator class.
 */
class LAP_Deactivator {
    
    /**
     * Deactivate plugin.
     */
    public static function lap_deactivate() {
        // Clear scheduled cron jobs
        wp_clear_scheduled_hook( 'lap_daily_risk_calculation' );
        wp_clear_scheduled_hook( 'lap_cleanup_cache' );
        wp_clear_scheduled_hook( 'lap_send_risk_notifications' );
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Note: We don't delete data on deactivation
        // Only on uninstall (see uninstall.php)
    }
}
```

### Uninstall (uninstall.php)
```php
<?php
/**
 * Plugin uninstall handler.
 *
 * @package LMS_Analytics_Pro
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Delete custom tables
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lap_student_progress" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lap_risk_scores" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lap_interventions" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lap_activity_log" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lap_cache" );

// Delete all plugin options
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'lap_%'" );

// Remove capabilities
$roles = array( 'administrator', 'group_leader' );
$capabilities = array(
    'lap_view_analytics',
    'lap_view_all_students',
    'lap_export_data',
    'lap_send_notifications',
    'lap_manage_settings',
);

foreach ( $roles as $role_name ) {
    $role = get_role( $role_name );
    if ( $role ) {
        foreach ( $capabilities as $cap ) {
            $role->remove_cap( $cap );
        }
    }
}

// Clear any cached data
wp_cache_flush();
```

---

## 📋 Implementation Checklist

### Phase 1: Core Setup (Week 1)
- [ ] Create plugin boilerplate structure
- [ ] Set up Composer autoloading
- [ ] Create database schema
- [ ] Implement activation/deactivation hooks
- [ ] Set up admin menu structure
- [ ] Create settings API framework

### Phase 2: Data Collection (Week 2)
- [ ] Implement LearnDash integration hooks
- [ ] Create activity tracking system
- [ ] Build progress calculation engine
- [ ] Set up database abstraction layer
- [ ] Implement caching system
- [ ] Create data aggregation functions

### Phase 3: Heatmap Feature (Week 3)
- [ ] Build heatmap data engine
- [ ] Create heatmap visualization UI
- [ ] Implement filtering system
- [ ] Add hover tooltips
- [ ] Build export functionality (CSV/PDF)
- [ ] Optimize for large datasets

### Phase 4: Dropout Detector (Week 4)
- [ ] Implement risk scoring algorithm
- [ ] Create activity monitoring system
- [ ] Build at-risk student dashboard
- [ ] Implement notification system
- [ ] Create email/BuddyBoss templates
- [ ] Add intervention logging

### Phase 5: BuddyBoss Integration (Week 5)
- [ ] Implement group integration
- [ ] Add group analytics tab
- [ ] Create private messaging integration
- [ ] Test with BuddyPress fallback
- [ ] Add forum activity tracking

### Phase 6: Polish & Testing (Week 6)
- [ ] Write unit tests (PHPUnit)
- [ ] Write JavaScript tests (Jest)
- [ ] Browser compatibility testing
- [ ] Mobile responsiveness testing
- [ ] Performance optimization
- [ ] Security audit
- [ ] Code documentation review
- [ ] Create user documentation

---

## 📖 Usage Documentation

### For Instructors

#### Viewing the Heatmap
1. Navigate to **WordPress Admin → LMS Analytics Pro → Heatmap**
2. Select filters:
   - **Course**: Choose specific course or "All Courses"
   - **Group**: Filter by BuddyBoss group (optional)
   - **Date Range**: Select time period
3. Hover over any cell to see detailed metrics
4. Click **Export** to download reports

#### Understanding Risk Scores
- **0-24 (Low)**: Student is on track
- **25-49 (Medium)**: Some disengagement signs
- **50-74 (High)**: Requires attention
- **75-100 (Critical)**: Immediate intervention needed

#### Taking Action
1. Go to **LMS Analytics Pro → At-Risk Students**
2. Review student risk cards
3. Click **Send Message** to reach out via:
   - Email (with template)
   - BuddyBoss private message
4. Mark as **Contacted** to track follow-ups

### For Administrators

#### Configuring Risk Detection
1. Go to **Settings → Dropout Detector**
2. Set **Inactivity Threshold** (default: 7 days)
3. Adjust **Risk Weights** to match your priorities
4. Enable **Auto-Notifications** for daily alerts
5. Customize email templates

#### Performance Settings
1. **Cache Duration**: Higher values = faster load, less real-time
2. **Cells Per Page**: Lower values = faster rendering
3. **Database Cleanup**: Schedule weekly cleanup for old logs

---

## 🔧 Developer Hooks & Filters

### Actions
```php
/**
 * Fires after risk score is calculated.
 *
 * @param int   $user_id   Student user ID.
 * @param int   $course_id Course ID.
 * @param array $risk_data Risk score data.
 */
do_action( 'lap_after_risk_calculation', $user_id, $course_id, $risk_data );

/**
 * Fires before sending notification.
 *
 * @param int    $user_id Student user ID.
 * @param string $type    Notification type (email|message).
 * @param array  $data    Notification data.
 */
do_action( 'lap_before_send_notification', $user_id, $type, $data );
```

### Filters
```php
/**
 * Filter risk score weights.
 *
 * @param array $weights Default weights.
 * @return array Modified weights.
 */
$weights = apply_filters( 'lap_risk_weights', array(
    'inactivity'  => 35,
    'velocity'    => 25,
    'quiz'        => 20,
    'forum'       => 10,
    'assignments' => 10,
) );

/**
 * Filter heatmap color scheme.
 *
 * @param array $colors Color definitions.
 * @return array Modified colors.
 */
$colors = apply_filters( 'lap_heatmap_colors', array(
    0   => '#F3F4F6',
    25  => '#DBEAFE',
    50  => '#93C5FD',
    75  => '#3B82F6',
    100 => '#1E40AF',
) );
```

---

## 🐛 Error Handling & Logging

### Debug Mode
```php
/**
 * Enable debug logging.
 */
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    define( 'LAP_DEBUG', true );
}

/**
 * Log debug message.
 *
 * @param string $message Log message.
 * @param string $level   Log level (info|warning|error).
 */
function lap_log( $message, $level = 'info' ) {
    if ( ! defined( 'LAP_DEBUG' ) || ! LAP_DEBUG ) {
        return;
    }
    
    $log_file = WP_CONTENT_DIR . '/lap-debug.log';
    $timestamp = date( 'Y-m-d H:i:s' );
    $formatted = "[{$timestamp}] [{$level}] {$message}\n";
    
    error_log( $formatted, 3, $log_file );
}

// Usage
lap_log( 'Risk score calculated for user #123', 'info' );
lap_log( 'Failed to send notification', 'error' );
```

---

## 📦 Dependencies

### PHP
- WordPress 5.8+
- PHP 7.4+
- LearnDash 3.0+
- BuddyPress 8.0+ or BuddyBoss Platform 1.7+

### JavaScript Libraries (via CDN)
- jQuery 3.6+ (bundled with WordPress)
- Chart.js 3.9+
- TCPDF (via Composer)

### Composer Packages
```json
{
    "require": {
        "php": ">=7.4",
        "tecnickcom/tcpdf": "^6.4"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.5",
        "wp-coding-standards/wpcs": "^2.3"
    },
    "autoload": {
        "psr-4": {
            "LMS_Analytics_Pro\\": "includes/"
        }
    }
}
```

---

## 🎯 Success Metrics

### Plugin Quality (10/10 Standard)
- ✅ **Code Quality**: PHPCS compliant, no warnings
- ✅ **Performance**: Page load < 2s, AJAX < 500ms
- ✅ **Accessibility**: WCAG 2.1 AA compliant
- ✅ **Security**: All inputs sanitized, outputs escaped
- ✅ **Documentation**: 100% PHPDoc coverage
- ✅ **Testing**: 80%+ code coverage
- ✅ **UI/UX**: Mobile-first, intuitive, polished
- ✅ **Compatibility**: Works with latest WP/LearnDash/BuddyBoss
- ✅ **Scalability**: Handles 1000+ students efficiently
- ✅ **Maintainability**: Modular, DRY, single-responsibility

---

## 📝 Final Notes for AI Implementation

### Critical Reminders
1. **Never exceed 1,000 lines per file** - Split into smaller classes
2. **Always prefix functions**: `lap_` for public, `LAP_` for classes
3. **Complete documentation**: Every class, method, parameter
4. **Security first**: Sanitize inputs, escape outputs, nonce verification
5. **Test thoroughly**: Unit tests for logic, browser tests for UI
6. **BuddyPress/BuddyBoss**: Check existence before using functions
7. **Performance**: Use caching, optimize queries, lazy loading
8. **Responsive**: Mobile-first, touch-friendly
9. **Accessibility**: Keyboard navigation, screen readers, ARIA labels
10. **Translations**: Use `__()`, `_e()`, `_n()` everywhere

### Development Order
1. Core infrastructure (database, settings, hooks)
2. Data collection (tracking, calculations)
3. Backend logic (risk scoring, heatmap engine)
4. Admin UI (dashboards, visualizations)
5. Integrations (LearnDash, BuddyBoss)
6. Notifications (email, messages)
7. Export functionality
8. Testing and optimization

### Quality Checks Before Completion
- [ ] All files under 1,000 lines
- [ ] All functions prefixed correctly
- [ ] Complete PHPDoc comments
- [ ] No direct database queries (use $wpdb)
- [ ] All strings translatable
- [ ] AJAX properly secured (nonces)
- [ ] CSS follows BEM methodology
- [ ] JavaScript is modular and documented
- [ ] Mobile responsive tested
- [ ] Works without JavaScript (graceful degradation)

---

## 🚀 Ready to Build!

This specification provides everything needed to build a production-ready, 10/10 quality plugin. Follow the structure, adhere to the coding standards, and create an exceptional learning analytics solution that instructors will love using.

**Remember**: Quality over speed. Every line of code should be intentional, documented, and tested.