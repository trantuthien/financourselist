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
 * Finan Course List main page.
 *
 * @package    local_financourselist
 * @copyright  2025 Orwell <thien.trantu@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

// Require login.
require_login();

// Check capability.
require_capability('local/financourselist:view', context_system::instance());

// Set up the page.
$PAGE->set_url('/local/financourselist/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('courselist_page_title', 'local_financourselist'));
$PAGE->set_heading(''); // Hide default page heading.
$PAGE->set_pagelayout('standard'); // Use standard layout.

// Get parameters with validation and security.
$search = optional_param('search', '', PARAM_TEXT);
$category = optional_param('category', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

// Validate and sanitize parameters.
if ($page < 0) {
    $page = 0;
}
if ($category < 0) {
    $category = 0;
}

// Additional security: limit search length to prevent DoS.
if (strlen($search) > 255) {
    $search = substr($search, 0, 255);
}

// Additional security: validate category exists if specified.
if ($category > 0) {
    $categoryexists = $DB->record_exists('course_categories', ['id' => $category, 'visible' => 1]);
    if (!$categoryexists) {
        $category = 0; // Reset to show all categories.
    }
}

// Get settings with validation.
$perpage = get_config('local_financourselist', 'coursesperpage') ?: 15;
if ($perpage < 1 || $perpage > 100) {
    $perpage = 15; // Default to safe value if invalid.
}
$showstats = get_config('local_financourselist', 'showstats');
$imagemode = get_config('local_financourselist', 'imagemode') ?: 'courseimage';
$gridcolumns = get_config('local_financourselist', 'gridcolumns') ?: 3;
$primarycolor = get_config('local_financourselist', 'primarycolor') ?: '#0FD46B';
$secondarycolor = get_config('local_financourselist', 'secondarycolor') ?: '#0FB56B';
$darkgreen = get_config('local_financourselist', 'darkgreen') ?: '#4D825E';
$lightgreen = get_config('local_financourselist', 'lightgreen') ?: '#EEFFF2';
$headertextcolor = get_config('local_financourselist', 'headertextcolor') ?: '#FFFFFF';
$pagetitle = get_config('local_financourselist', 'pagetitle') ?: get_string('courselist_page_heading', 'local_financourselist');
$pagesubtitle = get_config('local_financourselist', 'pagesubtitle') ?: get_string('page_description', 'local_financourselist');
$defaultcompletionrate = get_config('local_financourselist', 'defaultcompletionrate') ?: 94;
$defaultaveragerating = get_config('local_financourselist', 'defaultaveragerating') ?: 4.8;

// Get courses data with optimized bulk queries.
$courses = [];
$totalcount = 0;

// Build SQL query.
$params = [];
$whereclauses = ['c.visible = 1', 'c.id != 1']; // Exclude site course.

if (!empty($search)) {
    $whereclauses[] = "(c.fullname LIKE :search1 OR c.summary LIKE :search2)";
    $params['search1'] = '%' . $search . '%';
    $params['search2'] = '%' . $search . '%';
}

if ($category > 0) {
    $whereclauses[] = "c.category = :category";
    $params['category'] = $category;
}

$where = implode(' AND ', $whereclauses);

// Get total count with error handling.
$countsql = "SELECT COUNT(c.id) FROM {course} c WHERE " . $where;
try {
    $totalcount = $DB->count_records_sql($countsql, $params);
} catch (Exception $e) {
    // Log error and set safe default.
    debugging(get_string('error_counting_courses', 'local_financourselist', $e->getMessage()));
    $totalcount = 0;
}

// Get courses with enrollment and activity counts using optimized subqueries.
// Using LEFT JOINs instead of subqueries for better performance on large datasets.
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
        WHERE " . $where . " 
        ORDER BY c.sortorder ASC";

try {
    $courses = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);
} catch (Exception $e) {
    // Log error and set safe default.
    debugging(get_string('error_fetching_courses', 'local_financourselist', $e->getMessage()));
    $courses = [];
}

// Process courses data - no more nested queries in the loop!
$coursesdata = [];
$totalstudents = 0;

foreach ($courses as $course) {
    // Use pre-fetched counts from the optimized main query.
    $enrolledcount = $course->enrolledcount;
    $activitiescount = $course->activitiescount;
    $totalstudents += $enrolledcount;
    
    // Determine category type and icon.
    $categorytype = 'business';
    $categoryname = strtolower($course->categoryname ?: '');
    
    if (strpos($categoryname, 'tài chính') !== false || strpos($categoryname, 'finance') !== false) {
        $categorytype = 'finance';
        $icon = 'fas fa-chart-line';
    } else if (strpos($categoryname, 'kế toán') !== false || strpos($categoryname, 'accounting') !== false) {
        $categorytype = 'accounting';
        $icon = 'fas fa-calculator';
    } else if (strpos($categoryname, 'đầu tư') !== false || strpos($categoryname, 'investment') !== false) {
        $categorytype = 'investment';
        $icon = 'fas fa-trending-up';
    } else if (strpos($categoryname, 'công nghệ') !== false || strpos($categoryname, 'technology') !== false) {
        $categorytype = 'technology';
        $icon = 'fas fa-microchip';
    } else if (strpos($categoryname, 'marketing') !== false) {
        $categorytype = 'marketing';
        $icon = 'fas fa-bullhorn';
    } else {
        $icon = 'fas fa-briefcase';
    }
    
    // Get course image with caching optimization.
    $courseimage = null;
    if ($imagemode == 'courseimage' || $imagemode == 'both') {
        // Use cache to avoid repeated file system operations.
        $cachekey = 'course_image_' . $course->id;
        $courseimage = cache::make('local_financourselist', 'courseimages')->get($cachekey);
        
        if ($courseimage === false) {
            // Cache miss - fetch from file system.
            require_once($CFG->libdir . '/filelib.php');
            $context = context_course::instance($course->id);
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', 0, 'filename', false);
            if (!empty($files)) {
                $file = reset($files);
                // Use the correct URL generation for course overview files.
                $courseimage = file_encode_url($CFG->wwwroot . '/pluginfile.php',
                    '/' . $context->id . '/course/overviewfiles/' . $file->get_filename());
            }
            
            // Cache the result for 1 hour.
            cache::make('local_financourselist', 'courseimages')->set($cachekey, $courseimage, 3600);
        }
    }
    
    $coursesdata[] = [
        'id' => $course->id,
        'title' => format_string($course->fullname),
        'description' => strip_tags(format_text($course->summary, $course->summaryformat)),
        'category' => $categorytype,
        'categoryname' => $course->categoryname ?: get_string('other_category', 'local_financourselist'),
        'icon' => $icon,
        'courseimage' => $courseimage,
        'students' => $enrolledcount,
        'activities' => $activitiescount,
        'url' => new moodle_url('/course/view.php', ['id' => $course->id]),
    ];
}

// Get categories for filter with error handling.
try {
    $categories = $DB->get_records('course_categories', ['visible' => 1], 'name ASC');
} catch (Exception $e) {
    // Log error and set safe default.
    debugging(get_string('error_fetching_categories', 'local_financourselist', $e->getMessage()));
    $categories = [];
}

// Calculate pagination.
$totalpages = ceil($totalcount / $perpage);

// Build pagination data.
$pagination = [];
if ($totalpages > 1) {
    $pagination['showingfrom'] = ($page * $perpage) + 1;
    $pagination['showingto'] = min(($page + 1) * $perpage, $totalcount);
    $pagination['totalcount'] = $totalcount;
    
    // Previous link.
    $pagination['previous'] = [
        'disabled' => ($page == 0),
        'url' => ($page > 0) ? 
            (new moodle_url('/local/financourselist/index.php', [
                'search' => $search, 
                'category' => $category, 
                'page' => $page - 1
            ]))->out() : '#'
    ];
    
    // Page links.
    $pagination['pages'] = [];
    
    // Calculate page range.
    $start = max(0, $page - 2);
    $end = min($totalpages - 1, $page + 2);
    
    // First page.
    if ($start > 0) {
        $pagination['pages'][] = [
            'number' => 1,
            'url' => (new moodle_url('/local/financourselist/index.php', [
                'search' => $search,
                'category' => $category,
                'page' => 0
            ]))->out(),
            'active' => false
        ];
        
        if ($start > 1) {
            $pagination['pages'][] = ['ellipsis' => true];
        }
    }
    
    // Page range.
    for ($i = $start; $i <= $end; $i++) {
        $pagination['pages'][] = [
            'number' => $i + 1,
            'url' => (new moodle_url('/local/financourselist/index.php', [
                'search' => $search,
                'category' => $category,
                'page' => $i
            ]))->out(),
            'active' => ($i == $page)
        ];
    }
    
    // Last page.
    if ($end < $totalpages - 1) {
        if ($end < $totalpages - 2) {
            $pagination['pages'][] = ['ellipsis' => true];
        }
        
        $pagination['pages'][] = [
            'number' => $totalpages,
            'url' => (new moodle_url('/local/financourselist/index.php', [
                'search' => $search,
                'category' => $category,
                'page' => $totalpages - 1
            ]))->out(),
            'active' => false
        ];
    }
    
    // Next link.
    $pagination['next'] = [
        'disabled' => ($page >= $totalpages - 1),
        'url' => ($page < $totalpages - 1) ? 
            (new moodle_url('/local/financourselist/index.php', [
                'search' => $search, 
                'category' => $category, 
                'page' => $page + 1
            ]))->out() : '#'
    ];
}

// Build statistics data.
$stats = [
    'totalcourses' => $totalcount,
    'totalstudents' => $totalstudents,
    'completionrate' => $defaultcompletionrate,
    'averagerating' => $defaultaveragerating
];

// Load CSS file using Moodle's standard approach.
$PAGE->requires->css(new moodle_url('/local/financourselist/styles.css'));

// Add dynamic CSS variables using Moodle's approach.
$customcss = ":root {
    --finan-primary: $primarycolor;
    --finan-secondary: $secondarycolor;
    --finan-dark-green: $darkgreen;
    --finan-light-green: $lightgreen;
    --finan-gradient: linear-gradient(135deg, $primarycolor 0%, $secondarycolor 100%);
    --finan-header-text: $headertextcolor;
}";
$PAGE->requires->css_init_code($customcss);

// Get the renderer.
$output = $PAGE->get_renderer('local_financourselist');

// Create the page object.
$courselistpage = new \local_financourselist\output\course_list_page(
    $pagetitle,
    $pagesubtitle,
    $showstats,
    $stats,
    $coursesdata,
    $categories,
    $search,
    $category,
    $pagination
);

// Output the page.
echo $output->header();
echo $output->render($courselistpage);
echo $output->footer();