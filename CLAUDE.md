# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Moodle LMS local plugin (`local_financourselist`) that provides an enhanced course listing interface with modern grid layout, search functionality, and customizable branding.

**Key Information:**
- Plugin Type: Moodle local plugin
- Version: 1.0.10
- Moodle Compatibility: 4.1 - 4.4+
- License: GPL v3 (required for Moodle plugins)
- Languages: English, Vietnamese

## Architecture & Key Components

### Entry Points
- `index.php` - Main course listing display page, handles search/filtering logic
- `lib.php` - Navigation integration hooks (`local_financourselist_extend_navigation()`)
- `settings.php` - Admin configuration interface for colors, display options, content

### Core Functionality Flow
1. **Navigation Hook** (lib.php) adds menu item to Moodle navigation
2. **Main Display** (index.php) handles:
   - Course data retrieval with category joins
   - Search/filter processing
   - Grid layout rendering with customizable columns
   - Pagination logic
3. **Admin Settings** (settings.php) manages all customization options

### Database & Capabilities
- `db/access.php` - Defines `local/financourselist:view` capability (granted to all authenticated users)
- No custom database tables - uses Moodle core course/category tables

## Development Guidelines

### Moodle Plugin Standards
- Always check `defined('MOODLE_INTERNAL') || die()` at file start
- Use `get_string()` for all user-facing text (lang/en/local_financourselist.php)
- Implement proper capability checks: `has_capability('local/financourselist:view', $context)`
- Follow GPL v3 header format in all PHP files

### Common Development Tasks

**Testing Changes:**
1. Deploy files to Moodle's `/local/financourselist/` directory
2. Visit Site Administration → Notifications to trigger upgrade
3. Clear Moodle caches: Site Administration → Development → Purge all caches

**Adding New Settings:**
1. Add setting definition in `settings.php`
2. Add language strings in `lang/en/local_financourselist.php`
3. Retrieve in code: `get_config('local_financourselist', 'setting_name')`

**Modifying Course Display:**
- Main rendering logic in `index.php` (lines ~85-204)
- CSS inline styles for grid layout and responsive design
- Image handling supports both course images and category patterns

### Version Updates
When making changes that require database/capability updates:
1. Increment version in `version.php`
2. Update `$plugin->release` string
3. Moodle will auto-run upgrade on next admin visit

### Key Integration Points
- Course images: `$course->overviewfiles` via Moodle file API
- Category colors: Extracted from category ID (modulo 4)
- Search: Uses `$DB->sql_like()` for database-agnostic LIKE queries
- Pagination: Standard Moodle pagination with `$OUTPUT->paging_bar()`