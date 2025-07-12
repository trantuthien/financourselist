<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * Add course list page to navigation
 */
function local_financourselist_extend_navigation(global_navigation $navigation) {
    global $CFG;
    
    // Add to main navigation
    $courselist = $navigation->add(
        'Danh sách khóa học',
        new moodle_url('/local/courselist/index.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'courselist',
        new pix_icon('i/course', 'Danh sách khóa học')
    );
    
    $courselist->showinflatnavigation = true;
}

/**
 * Add course list to flat navigation
 */
function local_financourselist_extend_navigation_frontpage(navigation_node $frontpage, $course, $context) {
    global $CFG;
    
    $frontpage->add(
        'Danh sách khóa học',
        new moodle_url('/local/courselist/index.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'courselist'
    );
}