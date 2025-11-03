# LMS Analytics Pro - AI/Developer Training Guide

## Overview

This guide provides comprehensive information for developers and AI assistants working with LMS Analytics Pro. It covers the plugin architecture, key components, data structures, and development workflows.

## Plugin Architecture

### Directory Structure

```
lms-analytics-pro/
├── admin/                    # Admin interface files
│   ├── css/                 # Admin stylesheets
│   ├── js/                  # Admin JavaScript
│   ├── views/               # Admin page templates
│   └── class-lap-admin.php  # Main admin class
├── includes/                # Core plugin files
│   ├── analytics/          # Analytics components
│   ├── database/           # Database management
│   ├── dropout/            # Dropout detection
│   └── class-lap-*.php     # Core classes
├── public/                  # Public-facing files
├── languages/              # Translation files
├── tests/                  # PHPUnit test files
└── vendor/                 # Composer dependencies
```

### Core Classes

#### LAP_DB_Manager (`includes/database/class-lap-db-manager.php`)
**Purpose**: Handles all database operations and table management.

**Key Methods**:
- `get_at_risk_students($args)`: Retrieve students at risk of dropping out
- `get_heatmap_data($filters, $args)`: Get progress data for heatmap visualization
- `upsert_risk_score($data)`: Insert or update risk score data
- `get_table($name)`: Get properly prefixed table name

**Data Tables**:
- `lap_risk_scores`: Student risk assessment data
- `lap_student_progress`: Detailed progress tracking
- `lap_intervention_logs`: Intervention history
- `lap_activity_logs`: Student activity tracking

#### LAP_Risk_Scorer (`includes/dropout/class-lap-risk-scorer.php`)
**Purpose**: Calculates dropout risk scores using multiple factors.

**Key Methods**:
- `calculate_risk_score($user_data)`: Main risk calculation method
- `get_risk_factors($user_data)`: Extract risk factors from user data
- `get_risk_level($score)`: Convert numeric score to risk level
- `get_intervention_suggestions($factors)`: Generate intervention recommendations

**Risk Factors**:
- **Inactivity**: Days since last activity (weight: 35%)
- **Velocity**: Completion speed vs. expected (weight: 25%)
- **Quiz Performance**: Recent quiz scores (weight: 20%)
- **Progress**: Overall course completion (weight: 20%)

#### LAP_Cache_Handler (`includes/class-lap-cache-handler.php`)
**Purpose**: Manages caching for performance optimization.

**Key Methods**:
- `set($key, $data, $ttl)`: Store data in cache
- `get($key)`: Retrieve cached data
- `delete($key)`: Remove specific cache entry
- `clear()`: Clear all plugin cache
- `generate_key($prefix, $params)`: Generate consistent cache keys

**Cache Groups**:
- `lap_risk_scores`: Individual student risk data
- `lap_activity_summary`: Student activity summaries
- `lap_heatmap_data`: Course heatmap data
- `lap_intervention_stats`: Intervention statistics

#### LAP_Admin (`admin/class-lap-admin.php`)
**Purpose**: Handles admin interface and AJAX endpoints.

**Key Methods**:
- `lap_ajax_get_at_risk_students()`: AJAX handler for student list
- `lap_ajax_get_heatmap_data()`: AJAX handler for heatmap data
- `lap_ajax_send_intervention()`: AJAX handler for interventions
- `lap_ajax_calculate_risk_score()`: AJAX handler for risk calculation

## Data Flow

### Risk Assessment Flow

1. **Data Collection**: Activity tracker gathers student data
2. **Risk Calculation**: Risk scorer analyzes multiple factors
3. **Data Storage**: Results stored in risk_scores table
4. **Caching**: Results cached for performance
5. **Display**: Admin interface shows results with pagination

### Intervention Flow

1. **Risk Detection**: Admin views identify at-risk students
2. **Intervention Selection**: User chooses intervention type
3. **Message Generation**: System creates personalized messages
4. **Delivery**: Email/BuddyBoss message sent to student/instructor
5. **Logging**: All interventions logged for tracking
6. **Follow-up**: Success rates monitored and reported

## Key Hooks and Filters

### Action Hooks

```php
// Risk calculation lifecycle
do_action('lap_before_risk_calculation', $user_id, $course_id);
do_action('lap_after_risk_calculation', $user_id, $course_id, $risk_data);

// Intervention lifecycle
do_action('lap_intervention_sent', $intervention_data);
do_action('lap_intervention_delivered', $intervention_id, $success);

// Data cleanup
do_action('lap_cleanup_old_data');
```

### Filter Hooks

```php
// Customize risk calculation
$risk_factors = apply_filters('lap_risk_factors', $factors, $user_data);
$risk_score = apply_filters('lap_calculated_risk_score', $score, $user_data);

// Customize interventions
$intervention_types = apply_filters('lap_intervention_types', $types);
$intervention_message = apply_filters('lap_intervention_message', $message, $intervention_data);

// Export customization
$export_data = apply_filters('lap_export_data', $data, $format);
$export_filename = apply_filters('lap_export_filename', $filename, $type);
```

## Database Schema

### lap_risk_scores
```sql
CREATE TABLE wp_lap_risk_scores (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint(20) unsigned NOT NULL,
    course_id bigint(20) unsigned NOT NULL,
    risk_score int(11) NOT NULL,
    risk_level varchar(20) NOT NULL,
    factors longtext NOT NULL,
    last_login datetime DEFAULT NULL,
    days_inactive int(11) DEFAULT 0,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY user_course (user_id, course_id),
    KEY risk_score (risk_score),
    KEY risk_level (risk_level)
);
```

### lap_student_progress
```sql
CREATE TABLE wp_lap_student_progress (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint(20) unsigned NOT NULL,
    course_id bigint(20) unsigned NOT NULL,
    lesson_id bigint(20) unsigned NOT NULL,
    completion_percentage decimal(5,2) DEFAULT 0.00,
    time_spent_seconds int(11) DEFAULT 0,
    last_activity datetime DEFAULT NULL,
    quiz_attempts int(11) DEFAULT 0,
    best_quiz_score decimal(5,2) DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY user_lesson (user_id, lesson_id),
    KEY course_user (course_id, user_id)
);
```

### lap_intervention_logs
```sql
CREATE TABLE wp_lap_intervention_logs (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint(20) unsigned NOT NULL,
    course_id bigint(20) unsigned NOT NULL,
    intervention_type varchar(50) NOT NULL,
    intervention_data longtext NOT NULL,
    sent_by bigint(20) unsigned NOT NULL,
    sent_at datetime DEFAULT CURRENT_TIMESTAMP,
    status varchar(20) DEFAULT 'sent',
    response_data longtext,
    notes text,
    PRIMARY KEY (id),
    KEY user_course (user_id, course_id),
    KEY intervention_type (intervention_type),
    KEY sent_at (sent_at)
);
```

## Development Workflow

### Setting Up Development Environment

1. **Clone Repository**
   ```bash
   git clone https://github.com/wbcomdesigns/lms-analytics-pro.git
   cd lms-analytics-pro
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Set Up Testing**
   ```bash
   # Run PHPUnit tests
   vendor/bin/phpunit

   # Run with coverage
   vendor/bin/phpunit --coverage-html coverage
   ```

4. **Code Quality Checks**
   ```bash
   # PHP CodeSniffer
   vendor/bin/phpcs --standard=WordPress includes/ admin/

   # Fix coding standards
   vendor/bin/phpcbf --standard=WordPress includes/ admin/
   ```

### Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test class
vendor/bin/phpunit tests/test-db-manager.php

# Run with verbose output
vendor/bin/phpunit --verbose

# Generate coverage report
vendor/bin/phpunit --coverage-html coverage
```

### Code Standards

The plugin follows WordPress Coding Standards:

- **PHP**: PSR-4 autoloading, WordPress PHP standards
- **JavaScript**: WordPress JavaScript standards, ES6+ features
- **CSS**: WordPress CSS standards, BEM methodology
- **Documentation**: PHPDoc for all classes and methods

### Adding New Features

1. **Plan the Feature**
   - Define requirements and data structures
   - Identify integration points with existing code
   - Plan database changes if needed

2. **Implement Core Logic**
   - Add new classes in appropriate directories
   - Follow existing naming conventions
   - Include comprehensive error handling

3. **Add Database Support**
   - Create migration scripts for schema changes
   - Update DB Manager with new methods
   - Ensure backward compatibility

4. **Implement UI Components**
   - Add admin views for new functionality
   - Include proper sanitization and escaping
   - Add AJAX handlers if needed

5. **Add Tests**
   - Create unit tests for new classes
   - Test edge cases and error conditions
   - Ensure test coverage > 80%

6. **Update Documentation**
   - Add new hooks/filters to this guide
   - Update user guide if needed
   - Document any breaking changes

### Performance Considerations

- **Caching Strategy**: Use appropriate TTL values (1-24 hours)
- **Database Queries**: Always use prepared statements
- **Batch Processing**: Process large datasets in chunks
- **Memory Usage**: Monitor memory consumption in loops
- **AJAX Optimization**: Implement debouncing for user inputs

### Security Best Practices

- **Input Validation**: Sanitize all user inputs
- **Output Escaping**: Escape all output to prevent XSS
- **Capability Checks**: Verify user permissions
- **Nonce Verification**: Use nonces for all forms/AJAX
- **SQL Injection**: Use prepared statements
- **CSRF Protection**: Implement proper token validation

## Common Development Tasks

### Adding a New Risk Factor

1. Update `LAP_Risk_Scorer::get_risk_factors()` to include new factor
2. Add weight configuration in admin settings
3. Update risk calculation formula
4. Add tests for new factor logic

### Creating a New Intervention Type

1. Add intervention type to settings
2. Create message template
3. Update `LAP_Intervention_Logger` with new type handling
4. Add UI components for new intervention
5. Test delivery mechanism

### Adding Export Format

1. Extend `LAP_Export_Handler` with new format method
2. Add format option to admin UI
3. Implement data transformation for new format
4. Add proper headers and filename generation

## Troubleshooting

### Common Issues

**Tests Failing**
- Check PHP version compatibility
- Ensure all dependencies are installed
- Verify database connections in tests

**CodeSniffer Errors**
- Run `phpcbf` to auto-fix issues
- Check for custom rules in phpcs.xml
- Review WordPress standards documentation

**Performance Issues**
- Enable query monitoring
- Check cache hit rates
- Profile memory usage

**JavaScript Errors**
- Check browser console for errors
- Verify AJAX endpoints are accessible
- Test with different browsers

### Debug Tools

- **WP_DEBUG**: Enable for PHP error logging
- **Query Monitor**: Monitor database queries
- **Debug Bar**: Extended debugging information
- **Browser DevTools**: JavaScript debugging

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make changes following coding standards
4. Add comprehensive tests
5. Submit pull request with detailed description
6. Ensure all CI checks pass

## Support Resources

- **WordPress Codex**: Core WordPress documentation
- **LearnDash Developer Docs**: LMS-specific integration
- **BuddyBoss Developer Docs**: Social features integration
- **PHPUnit Documentation**: Testing framework reference
- **WordPress Coding Standards**: Code style guidelines

---

*This guide is maintained alongside the codebase. Please update it when making significant changes to the plugin architecture.*