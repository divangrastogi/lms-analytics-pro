# LMS Analytics Pro - User Guide

## Overview

LMS Analytics Pro provides comprehensive student analytics, intelligent dropout detection, and automated intervention management for LearnDash-powered learning management systems. This guide will help you get started with installation, configuration, and daily usage.

## Installation

### System Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **LearnDash**: 3.0 or higher (required)
- **BuddyBoss Platform**: Optional but recommended for enhanced social learning analytics
- **MySQL**: 5.6 or higher

### Installation Steps

1. **Download the Plugin**
   - Download `lms-analytics-pro.zip` from your purchase confirmation email
   - Or download from the WordPress plugin repository

2. **Install via WordPress Admin**
   - Navigate to **Plugins > Add New**
   - Click **Upload Plugin**
   - Choose the `lms-analytics-pro.zip` file
   - Click **Install Now**
   - Click **Activate** when installation completes

3. **Verify Installation**
   - Check for the "LMS Analytics Pro" menu item in your WordPress admin sidebar
   - Visit **LMS Analytics > Dashboard** to confirm the plugin is working

### Initial Setup

1. **Configure Settings**
   - Go to **LMS Analytics > Settings**
   - Set risk scoring weights according to your institution's preferences
   - Configure notification schedules
   - Set cache duration (recommended: 1 hour)

2. **Grant Permissions**
   - Go to **Users > User Role Editor** (or similar)
   - Add `lap_view_analytics` capability to instructors and admins
   - Add `lap_manage_interventions` for users who can send interventions

## Dashboard Overview

The main dashboard provides a high-level view of your LMS analytics:

### Key Metrics
- **Total Students**: Total enrolled students across all courses
- **Active Courses**: Number of courses with recent activity
- **Completion Rate**: Average course completion percentage
- **At-Risk Students**: Students identified as potentially dropping out

### Quick Actions
- **View Progress Heatmap**: Visual representation of student progress
- **Check At-Risk Students**: Detailed list of students needing attention
- **Export Dashboard Data**: Download analytics in CSV/PDF format
- **Configure Settings**: Access plugin configuration

## Dropout Detection

### Understanding Risk Levels

LMS Analytics Pro uses a sophisticated algorithm to identify at-risk students:

- **Low Risk (0-39)**: Students progressing normally
- **Medium Risk (40-69)**: Students showing some concerning patterns
- **High Risk (70-89)**: Students requiring immediate attention
- **Critical Risk (90-100)**: Students at imminent risk of dropping out

### Risk Factors Analyzed

1. **Inactivity**: Days since last login or activity
2. **Completion Velocity**: Rate of lesson completion
3. **Quiz Performance**: Recent quiz scores and trends
4. **Course Progress**: Overall completion percentage

### Managing At-Risk Students

1. **Access Dropout Detector**
   - Navigate to **LMS Analytics > Dropout Detector**

2. **Filter Students**
   - Filter by course, risk level, or date range
   - Use pagination for large student lists

3. **Send Interventions**
   - Click "Send Intervention" next to any at-risk student
   - Choose intervention type:
     - **Email Instructor**: Alert course instructor
     - **Re-engagement Email**: Send motivational email to student
     - **BuddyBoss Message**: Send private message (if BuddyBoss enabled)

4. **Track Success**
   - Monitor intervention success rates
   - View detailed intervention history

## Progress Heatmap

### Understanding the Heatmap

The progress heatmap provides a visual representation of student progress across all lessons in a course:

- **Green (70-100%)**: Excellent progress
- **Yellow (50-69%)**: Moderate progress
- **Orange (25-49%)**: Needs improvement
- **Red (0-24%)**: Significant difficulty

### Using the Heatmap

1. **Navigate to Heatmap**
   - Go to **LMS Analytics > Progress Heatmap**

2. **Filter Data**
   - Select specific courses or groups
   - Choose date ranges for historical analysis

3. **Analyze Patterns**
   - Identify lessons causing widespread difficulty
   - Spot individual students struggling with specific topics
   - Track progress trends over time

4. **Export Data**
   - Download heatmap data as CSV or PDF
   - Use for reporting or further analysis

## Settings Configuration

### Risk Scoring Settings

- **Inactivity Weight**: Importance of inactivity in risk calculation (default: 35%)
- **Velocity Weight**: Importance of completion speed (default: 25%)
- **Quiz Weight**: Importance of quiz performance (default: 20%)
- **Progress Weight**: Importance of overall progress (default: 20%)

### Notification Settings

- **Email Notifications**: Enable/disable automated alerts
- **Notification Schedule**: How often to send intervention reminders
- **Instructor Alerts**: Which instructors receive notifications

### Performance Settings

- **Cache Duration**: How long to cache analytics data (default: 1 hour)
- **Batch Size**: Number of students to process in batch operations
- **Debug Mode**: Enable for troubleshooting (disable in production)

## Common Workflows

### Daily Monitoring

1. Check the dashboard for at-risk student counts
2. Review new high-risk students in dropout detector
3. Send interventions to critical cases
4. Monitor intervention success rates

### Weekly Reporting

1. Generate progress heatmap reports
2. Export analytics data for administration
3. Review course completion trends
4. Adjust risk scoring weights if needed

### Monthly Analysis

1. Analyze overall completion rates
2. Identify courses needing improvement
3. Review intervention effectiveness
4. Plan curriculum adjustments

## Troubleshooting

### Common Issues

**Plugin Not Activating**
- Ensure LearnDash is installed and activated
- Check PHP version compatibility (7.4+)
- Verify file permissions on plugin directory

**No Data Showing**
- Run risk calculation manually from dropout detector
- Check that students are enrolled in courses
- Verify LearnDash data structure

**Slow Performance**
- Increase cache duration in settings
- Reduce batch sizes for processing
- Check server resources and database optimization

**Email Notifications Not Sending**
- Verify WordPress email configuration
- Check spam folders
- Test with different email providers

### Debug Mode

Enable debug mode in settings to get detailed error logs:

1. Go to **LMS Analytics > Settings**
2. Enable "Debug Mode"
3. Check WordPress debug log for errors
4. Disable debug mode when finished

### Getting Help

- **Documentation**: Check this guide and plugin documentation
- **Support Forum**: Post questions in the support forum
- **System Status**: Use the system status tool for technical information

## Best Practices

### Risk Management
- Review at-risk students daily
- Send interventions promptly for high-risk cases
- Monitor intervention success rates
- Adjust risk scoring based on institutional data

### Performance Optimization
- Use appropriate cache durations
- Schedule heavy operations during off-peak hours
- Regularly clean up old analytics data
- Monitor server resources

### Data Privacy
- Understand data retention policies
- Respect student privacy in interventions
- Use anonymized data for reporting
- Comply with institutional data policies

### User Training
- Train instructors on using analytics effectively
- Establish protocols for intervention procedures
- Create guidelines for data interpretation
- Regular training sessions for new features

## API Reference

### Available Hooks

**Action Hooks:**
- `lap_before_risk_calculation`: Before risk scores are calculated
- `lap_after_risk_calculation`: After risk scores are calculated
- `lap_intervention_sent`: When an intervention is sent

**Filter Hooks:**
- `lap_risk_factors`: Modify risk calculation factors
- `lap_intervention_types`: Add custom intervention types
- `lap_export_data`: Filter exported data

### Custom Development

For custom integrations, use the provided hooks and filters. The plugin follows WordPress coding standards and provides extensive customization options.

## Changelog

### Version 1.0.0
- Initial release with core analytics features
- Dropout detection and intervention management
- Progress heatmap visualization
- Export functionality
- Comprehensive caching system

## Support

For technical support, feature requests, or bug reports:

- **Email**: support@wbcomdesigns.com
- **Forum**: Community support forum
- **Documentation**: Online knowledge base
- **Priority Support**: Available for licensed customers

---

*This guide is regularly updated. Check for the latest version in your plugin directory or on the support website.*