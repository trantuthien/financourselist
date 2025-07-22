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
 * Performance testing script for Finan Course List plugin
 *
 * This script helps validate the performance improvements made to the plugin.
 * It compares the old N+1 query approach with the new optimized approach.
 *
 * @package    local_financourselist
 * @copyright  2025 Orwell <thien.trantu@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('../../lib/adminlib.php');

// Require admin access
require_login();
require_capability('moodle/site:config', context_system::instance());

// Set up the page
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/financourselist/performance_test.php');
$PAGE->set_title('Performance Test - Finan Course List');
$PAGE->set_heading('Performance Test Results');

echo $OUTPUT->header();

echo html_writer::tag('h2', 'Performance Test Results');
echo html_writer::tag('p', 'This page tests the performance improvements made to the Finan Course List plugin.');

// Test parameters
$iterations = 5;
$coursesperpage = 15;

echo html_writer::tag('h3', 'Test Configuration');
echo html_writer::tag('p', "Iterations: $iterations");
echo html_writer::tag('p', "Courses per page: $coursesperpage");

// Get total course count
$totalcourses = $DB->count_records('course', ['visible' => 1]);

echo html_writer::tag('p', "Total courses in system: $totalcourses");

// Test 1: Old approach (N+1 queries)
echo html_writer::tag('h3', 'Test 1: Old Approach (N+1 Queries)');
$oldtimes = [];

for ($i = 0; $i < $iterations; $i++) {
    $starttime = microtime(true);
    
    // Simulate old approach with nested queries
    $courses = $DB->get_records('course', ['visible' => 1], 'sortorder ASC', '*', $i * $coursesperpage, $coursesperpage);
    
    foreach ($courses as $course) {
        // Simulate the old nested queries
        $enrolledcount = $DB->count_records_sql(
            "SELECT COUNT(DISTINCT ue.userid) FROM {user_enrolments} ue 
             JOIN {enrol} e ON ue.enrolid = e.id 
             WHERE e.courseid = ? AND ue.status = 0",
            [$course->id]
        );
        
        $activitiescount = $DB->count_records('course_modules', ['course' => $course->id, 'visible' => 1]);
    }
    
    $endtime = microtime(true);
    $oldtimes[] = ($endtime - $starttime) * 1000; // Convert to milliseconds
}

$oldavg = array_sum($oldtimes) / count($oldtimes);
$oldmin = min($oldtimes);
$oldmax = max($oldtimes);

echo html_writer::tag('p', "Average time: " . number_format($oldavg, 2) . " ms");
echo html_writer::tag('p', "Min time: " . number_format($oldmin, 2) . " ms");
echo html_writer::tag('p', "Max time: " . number_format($oldmax, 2) . " ms");

// Test 2: New optimized approach
echo html_writer::tag('h3', 'Test 2: New Optimized Approach');
$newtimes = [];

for ($i = 0; $i < $iterations; $i++) {
    $starttime = microtime(true);
    
    // Use the new optimized query
    $sql = "SELECT c.*, cat.name as categoryname,
            COALESCE(enrollment_counts.count, 0) as enrolledcount,
            COALESCE(activity_counts.count, 0) as activitiescount
            FROM {course} c 
            LEFT JOIN {course_categories} cat ON c.category = cat.id 
            LEFT JOIN (
                SELECT e.courseid, COUNT(DISTINCT ue.userid) as count
                FROM {enrol} e
                JOIN {user_enrolments} ue ON e.id = ue.enrolid
                WHERE ue.status = 0
                GROUP BY e.courseid
            ) enrollment_counts ON c.id = enrollment_counts.courseid
            LEFT JOIN (
                SELECT course, COUNT(*) as count
                FROM {course_modules}
                WHERE visible = 1
                GROUP BY course
            ) activity_counts ON c.id = activity_counts.course
            WHERE c.visible = 1 AND c.id != 1
            ORDER BY c.sortorder ASC";
    
    $courses = $DB->get_records_sql($sql, [], $i * $coursesperpage, $coursesperpage);
    
    $endtime = microtime(true);
    $newtimes[] = ($endtime - $starttime) * 1000; // Convert to milliseconds
}

$newavg = array_sum($newtimes) / count($newtimes);
$newmin = min($newtimes);
$newmax = max($newtimes);

echo html_writer::tag('p', "Average time: " . number_format($newavg, 2) . " ms");
echo html_writer::tag('p', "Min time: " . number_format($newmin, 2) . " ms");
echo html_writer::tag('p', "Max time: " . number_format($newmax, 2) . " ms");

// Calculate improvement
$improvement = (($oldavg - $newavg) / $oldavg) * 100;

echo html_writer::tag('h3', 'Performance Improvement');
echo html_writer::tag('p', "Performance improvement: " . number_format($improvement, 1) . "%");

if ($improvement > 0) {
    echo html_writer::tag('p', html_writer::tag('strong', '✅ Performance improvement achieved!'), ['class' => 'text-success']);
} else {
    echo html_writer::tag('p', html_writer::tag('strong', '⚠️ No performance improvement detected.'), ['class' => 'text-warning']);
}

// Database query count comparison
echo html_writer::tag('h3', 'Database Query Analysis');
echo html_writer::tag('p', "Old approach: " . ($coursesperpage * 2 + 1) . " queries per page load");
echo html_writer::tag('p', "New approach: 1 query per page load");
echo html_writer::tag('p', "Query reduction: " . (($coursesperpage * 2 + 1) - 1) . " queries eliminated");

// Recommendations
echo html_writer::tag('h3', 'Recommendations');
echo html_writer::tag('ul', 
    html_writer::tag('li', 'Monitor performance in production environment') .
    html_writer::tag('li', 'Consider adding database indexes if performance is still suboptimal') .
    html_writer::tag('li', 'Implement query result caching for frequently accessed data') .
    html_writer::tag('li', 'Monitor cache hit rates and adjust TTL as needed')
);

echo $OUTPUT->footer(); 