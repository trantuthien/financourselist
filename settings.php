<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Settings for local_financourselist plugin
 *
 * @package    local_financourselist
 * @copyright  2025 Orwell <thien.trantu@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Create the settings page.
    $settings = new admin_settingpage('local_financourselist_settings', get_string('pluginname', 'local_financourselist'));

    // Color settings.
    $settings->add(new admin_setting_heading('local_financourselist/colorsheading',
        get_string('color_settings', 'local_financourselist'),
        get_string('color_settings_desc', 'local_financourselist')
    ));

    $settings->add(new admin_setting_configcolourpicker('local_financourselist/primarycolor',
        get_string('primary_color', 'local_financourselist'),
        get_string('primary_color_desc', 'local_financourselist'),
        '#0FD46B',
        null
    ));

    $settings->add(new admin_setting_configcolourpicker('local_financourselist/secondarycolor',
        get_string('secondary_color', 'local_financourselist'),
        get_string('secondary_color_desc', 'local_financourselist'),
        '#0FB56B',
        null
    ));

    $settings->add(new admin_setting_configcolourpicker('local_financourselist/darkgreen',
        get_string('dark_green', 'local_financourselist'),
        get_string('dark_green_desc', 'local_financourselist'),
        '#4D825E',
        null
    ));

    $settings->add(new admin_setting_configcolourpicker('local_financourselist/lightgreen',
        get_string('light_green', 'local_financourselist'),
        get_string('light_green_desc', 'local_financourselist'),
        '#EEFFF2',
        null
    ));

    $settings->add(new admin_setting_configcolourpicker('local_financourselist/headertextcolor',
        get_string('header_text_color', 'local_financourselist'),
        get_string('header_text_color_desc', 'local_financourselist'),
        '#FFFFFF',
        null
    ));

    // Display settings.
    $settings->add(new admin_setting_heading('local_financourselist/displaysheading',
        get_string('display_settings', 'local_financourselist'),
        get_string('display_settings_desc', 'local_financourselist')
    ));

    $settings->add(new admin_setting_configcheckbox('local_financourselist/showstats',
        get_string('show_stats', 'local_financourselist'),
        get_string('show_stats_desc', 'local_financourselist'),
        1
    ));

    $imageoptions = [
        'courseimage' => get_string('use_course_image', 'local_financourselist'),
        'icon' => get_string('use_category_icon', 'local_financourselist'),
        'both' => get_string('use_both_fallback', 'local_financourselist'),
    ];

    $settings->add(new admin_setting_configselect('local_financourselist/imagemode',
        get_string('image_mode', 'local_financourselist'),
        get_string('image_mode_desc', 'local_financourselist'),
        'courseimage',
        $imageoptions
    ));

    $settings->add(new admin_setting_configtext('local_financourselist/coursesperpage',
        get_string('courses_per_page', 'local_financourselist'),
        get_string('courses_per_page_desc', 'local_financourselist'),
        15,
        PARAM_INT
    ));

    $gridoptions = [
        2 => '2 ' . get_string('columns', 'local_financourselist'),
        3 => '3 ' . get_string('columns', 'local_financourselist'),
        4 => '4 ' . get_string('columns', 'local_financourselist'),
        5 => '5 ' . get_string('columns', 'local_financourselist'),
    ];

    $settings->add(new admin_setting_configselect('local_financourselist/gridcolumns',
        get_string('grid_columns', 'local_financourselist'),
        get_string('grid_columns_desc', 'local_financourselist'),
        3,
        $gridoptions
    ));

    // Content settings.
    $settings->add(new admin_setting_heading('local_financourselist/contentsheading',
        get_string('content_settings', 'local_financourselist'),
        get_string('content_settings_desc', 'local_financourselist')
    ));

    $settings->add(new admin_setting_configtext('local_financourselist/pagetitle',
        get_string('page_title', 'local_financourselist'),
        get_string('page_title_desc', 'local_financourselist'),
        get_string('page_description', 'local_financourselist'),
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtextarea('local_financourselist/pagesubtitle',
        get_string('page_subtitle', 'local_financourselist'),
        get_string('page_subtitle_desc', 'local_financourselist'),
        get_string('page_description', 'local_financourselist'),
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext('local_financourselist/defaultcompletionrate',
        get_string('default_completion_rate', 'local_financourselist'),
        get_string('default_completion_rate_desc', 'local_financourselist'),
        '94',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext('local_financourselist/defaultaveragerating',
        get_string('default_average_rating', 'local_financourselist'),
        get_string('default_average_rating_desc', 'local_financourselist'),
        '4.8',
        PARAM_FLOAT
    ));

    // Add settings page to the local plugins category.
    $ADMIN->add('localplugins', $settings);
}
