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
 * Navigation integration for Finan Course List.
 *
 * @package    local_financourselist
 * @copyright  2025 Orwell <thien.trantu@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add course list page to navigation.
 *
 * @package    local_financourselist
 * @param global_navigation $navigation The global navigation object
 */
function local_financourselist_extend_navigation(global_navigation $navigation) {
    global $CFG;

    // Add to main navigation.
    $courselist = $navigation->add(
        get_string('navigation_title', 'local_financourselist'),
        new moodle_url('/local/financourselist/index.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'courselist',
        new pix_icon('i/course', get_string('navigation_title', 'local_financourselist'))
    );

    $courselist->showinflatnavigation = true;
}

/**
 * Add course list to flat navigation.
 *
 * @package    local_financourselist
 * @param navigation_node $frontpage The frontpage navigation node
 * @param stdClass $course The course object
 * @param context $context The context object
 */
function local_financourselist_extend_navigation_frontpage(navigation_node $frontpage, $course, $context) {
    global $CFG;

    $frontpage->add(
        get_string('navigation_title', 'local_financourselist'),
        new moodle_url('/local/financourselist/index.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'courselist'
    );
}
