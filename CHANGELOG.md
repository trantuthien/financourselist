# Changelog

All notable changes to the Finan Course List plugin will be documented in this file.

## [1.1.0] - 2025-01-17

### Major Architecture Changes
- **Implemented Moodle Templates**: Migrated from legacy HTML generation to modern Mustache templates
- **Added Output API Support**: Implemented renderer and output classes following Moodle standards
- **Improved Maintainability**: Separated presentation logic from business logic
- **Better Code Organization**: Created proper MVC structure with templates, renderers, and output classes

### Files Added
- `classes/output/course_list_page.php` - Main output class for the course list page
- `classes/output/renderer.php` - Renderer class following Moodle standards
- `templates/course_list_page.mustache` - Main page template
- `templates/statistics.mustache` - Statistics section template
- `templates/filters.mustache` - Search and filter template
- `templates/course_grid.mustache` - Course grid template
- `templates/empty_state.mustache` - Empty state template
- `templates/pagination.mustache` - Pagination template

## [1.0.12] - 2025-01-17

### Performance Improvements
- **Fixed N+1 Query Problem**: Eliminated nested database queries within loops that were causing performance bottlenecks
- **Optimized Database Queries**: Replaced subqueries with LEFT JOINs for better performance on large datasets
- **Added Caching**: Implemented course image caching to reduce file system operations
- **Bulk Data Fetching**: All course data, enrollment counts, and activity counts are now fetched in single optimized queries

### Security Enhancements
- **Input Validation**: Added comprehensive parameter validation and sanitization
- **CSRF Protection**: Enhanced form security with proper input handling
- **DoS Prevention**: Limited search input length to prevent denial of service attacks
- **Category Validation**: Added validation to ensure requested categories exist and are visible

### Accessibility Improvements
- **ARIA Labels**: Added proper ARIA labels and roles for screen readers
- **Keyboard Navigation**: Improved focus management and keyboard navigation
- **Screen Reader Support**: Added descriptive labels for course statistics and actions
- **Semantic HTML**: Enhanced HTML structure with proper semantic elements
- **Focus Indicators**: Added visible focus indicators for better accessibility

### Mobile Responsiveness
- **Enhanced Mobile Layout**: Improved responsive design for tablets and mobile devices
- **Touch-Friendly Interface**: Optimized button sizes and spacing for touch devices
- **Flexible Grid System**: Better grid adaptation for different screen sizes
- **Improved Typography**: Better text scaling and readability on small screens

### Error Handling
- **Database Error Handling**: Added try-catch blocks for all database operations
- **Graceful Degradation**: Plugin continues to function even if some operations fail
- **Error Logging**: Proper error logging for debugging and monitoring
- **Safe Defaults**: Fallback values when configuration or data is invalid

### Code Quality
- **Better Documentation**: Enhanced code comments and documentation
- **Consistent Coding Standards**: Improved code formatting and structure
- **Memory Optimization**: Reduced memory usage through better data handling
- **Cache Management**: Proper cache invalidation and management

### Bug Fixes
- **Fixed Parameter Validation**: Proper validation of page and category parameters
- **Fixed Search Functionality**: Improved search with better error handling
- **Fixed Image Loading**: Added lazy loading for course images
- **Fixed Pagination**: Better pagination handling with proper bounds checking

## [1.0.11] - 2025-01-17

### Initial Release
- Basic course listing functionality
- Grid-based layout with responsive design
- Search and filtering capabilities
- Multi-language support (English and Vietnamese)
- Customizable branding and colors
- Statistics dashboard
- Pagination system

## Technical Details

### Database Optimizations
- Replaced individual queries with bulk operations
- Used LEFT JOINs instead of subqueries for better performance
- Implemented proper indexing considerations
- Added query result caching

### Cache Implementation
- Course image caching with 1-hour TTL
- Cache invalidation on course updates
- Memory-efficient cache storage
- Proper cache key management

### Security Measures
- Input length limits (255 characters for search)
- Parameter type validation
- SQL injection prevention through proper parameterization
- XSS prevention through output escaping

### Accessibility Features
- WCAG 2.1 AA compliance improvements
- Screen reader compatibility
- Keyboard navigation support
- High contrast focus indicators
- Semantic HTML structure

## Migration Notes

### From 1.0.11 to 1.0.12
- No database schema changes required
- Cache will be automatically created on first use
- Existing settings and configurations are preserved
- Performance improvements are automatic

## Compatibility

- **Moodle**: 4.1 - 4.4+
- **PHP**: 7.4+
- **Database**: MySQL 5.7+, PostgreSQL 10+, MariaDB 10.2+
- **Browser**: Modern browsers with ES6 support

## Known Issues

None reported in this version.

## Future Plans

- Advanced filtering options
- Course rating and review system
- Export functionality
- Advanced analytics dashboard
- Integration with external course providers 