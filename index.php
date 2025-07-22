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

// Validate and sanitize parameters
if ($page < 0) {
    $page = 0;
}
if ($category < 0) {
    $category = 0;
}

// Additional security: limit search length to prevent DoS
if (strlen($search) > 255) {
    $search = substr($search, 0, 255);
}

// Additional security: validate category exists if specified
if ($category > 0) {
    $categoryexists = $DB->record_exists('course_categories', ['id' => $category, 'visible' => 1]);
    if (!$categoryexists) {
        $category = 0; // Reset to show all categories
    }
}

// Get settings with validation.
$perpage = get_config('local_financourselist', 'coursesperpage') ?: 15;
if ($perpage < 1 || $perpage > 100) {
    $perpage = 15; // Default to safe value if invalid
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

// Get total count with error handling
$countsql = "SELECT COUNT(c.id) FROM {course} c WHERE " . $where;
try {
    $totalcount = $DB->count_records_sql($countsql, $params);
} catch (Exception $e) {
    // Log error and set safe default
    debugging(get_string('error_counting_courses', 'local_financourselist', $e->getMessage()));
    $totalcount = 0;
}

// Get courses with enrollment and activity counts using optimized subqueries.
// Using LEFT JOINs instead of subqueries for better performance on large datasets
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
    // Log error and set safe default
    debugging(get_string('error_fetching_courses', 'local_financourselist', $e->getMessage()));
    $courses = [];
}

// Process courses data - no more nested queries in the loop!
$coursesdata = [];
foreach ($courses as $course) {
    // Use pre-fetched counts from the optimized main query.
    $enrolledcount = $course->enrolledcount;
    $activitiescount = $course->activitiescount;
    
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
        // Use cache to avoid repeated file system operations
        $cachekey = 'course_image_' . $course->id;
        $courseimage = cache::make('local_financourselist', 'courseimages')->get($cachekey);
        
        if ($courseimage === false) {
            // Cache miss - fetch from file system
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
            
            // Cache the result for 1 hour
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
    // Log error and set safe default
    debugging(get_string('error_fetching_categories', 'local_financourselist', $e->getMessage()));
    $categories = [];
}

// Calculate pagination.
$totalpages = ceil($totalcount / $perpage);

// Load CSS file.
$PAGE->requires->css('/local/financourselist/styles/styles.css');

echo $OUTPUT->header();

// Output CSS variables dynamically.
echo html_writer::start_tag('style');
echo ':root {';
echo '    --finan-primary: ' . $primarycolor . ';';
echo '    --finan-secondary: ' . $secondarycolor . ';';
echo '    --finan-dark-green: ' . $darkgreen . ';';
echo '    --finan-light-green: ' . $lightgreen . ';';
echo '    --finan-gradient: linear-gradient(135deg, ' . $primarycolor . ' 0%, ' . $secondarycolor . ' 100%);';
echo '    --finan-header-text: ' . $headertextcolor . ';';
echo '}';
echo html_writer::end_tag('style');
?>

<div class="local-financourselist-header">
    <div class="container">
        <h1><i class="fas fa-graduation-cap me-3"></i><?php echo format_string($pagetitle); ?></h1>
        <p><?php echo format_string($pagesubtitle); ?></p>
    </div>
</div>

<div class="container">
    <!-- Statistics -->
    <?php if ($showstats): ?>
    <div class="local-financourselist-stats-section">
        <div class="local-financourselist-stats-grid">
            <div>
                <div class="local-financourselist-stat-number"><?php echo $totalcount; ?></div>
                <div class="local-financourselist-stat-label"><?php echo get_string('total_courses', 'local_financourselist'); ?></div>
            </div>
            <div>
                <div class="local-financourselist-stat-number"><?php echo array_sum(array_column($coursesdata, 'students')); ?></div>
                <div class="local-financourselist-stat-label"><?php echo get_string('total_students', 'local_financourselist'); ?></div>
            </div>
            <div>
                <div class="local-financourselist-stat-number"><?php echo $defaultcompletionrate; ?>%</div>
                <div class="local-financourselist-stat-label"><?php echo get_string('completion_rate', 'local_financourselist'); ?></div>
            </div>
            <div>
                <div class="local-financourselist-stat-number"><?php echo $defaultaveragerating; ?></div>
                <div class="local-financourselist-stat-label"><?php echo get_string('average_rating', 'local_financourselist'); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="local-financourselist-filters" role="search" aria-label="<?php echo get_string('search_placeholder', 'local_financourselist'); ?>">
        <form method="get" action="">
            <div class="local-financourselist-search">
                <label for="course-search" class="sr-only"><?php echo get_string('search_placeholder', 'local_financourselist'); ?></label>
                <input type="text" id="course-search" name="search" value="<?php echo s($search); ?>" 
                       placeholder="<?php echo get_string('search_placeholder', 'local_financourselist'); ?>"
                       onchange="this.form.submit()" 
                       aria-describedby="search-help">
                <i class="fas fa-search local-financourselist-search-icon" aria-hidden="true"></i>
                <div id="search-help" class="sr-only"><?php echo get_string('search_placeholder', 'local_financourselist'); ?></div>
            </div>
            
            <div class="local-financourselist-category-filters">
                <a href="<?php echo new moodle_url('/local/financourselist/index.php'); ?>" 
                   class="local-financourselist-category-filter <?php echo ($category == 0) ? 'active' : ''; ?>">
                    <?php echo get_string('all_categories', 'local_financourselist'); ?>
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?php echo new moodle_url('/local/financourselist/index.php', ['category' => $cat->id]); ?>" 
                       class="local-financourselist-category-filter <?php echo ($category == $cat->id) ? 'active' : ''; ?>">
                        <?php echo format_string($cat->name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </form>
    </div>

    <!-- Courses Grid -->
    <div class="local-financourselist-courses-grid">
        <?php if (empty($coursesdata)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted"><?php echo get_string('no_courses_found', 'local_financourselist'); ?></h4>
                <p class="text-muted"><?php echo get_string('no_courses_message', 'local_financourselist'); ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($coursesdata as $course): ?>
                <div class="local-financourselist-course-card" role="article" aria-labelledby="course-title-<?php echo $course['id']; ?>">
                    <div class="local-financourselist-course-image">
                        <?php if ($imagemode == 'icon' || (!$course['courseimage'] && $imagemode != 'courseimage')): ?>
                            <i class="<?php echo $course['icon']; ?> local-financourselist-course-icon" aria-hidden="true"></i>
                        <?php elseif ($course['courseimage']): ?>
                            <img src="<?php echo $course['courseimage']; ?>" alt="<?php echo s($course['title']); ?>" loading="lazy">
                            <?php if ($imagemode == 'both'): ?>
                                <div class="local-financourselist-course-image-overlay">
                                    <i class="<?php echo $course['icon']; ?> local-financourselist-course-icon" aria-hidden="true"></i>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <i class="<?php echo $course['icon']; ?> local-financourselist-course-icon" aria-hidden="true"></i>
                        <?php endif; ?>
                        <div class="local-financourselist-course-category" aria-label="<?php echo get_string('category', 'local_financourselist'); ?>: <?php echo s($course['categoryname']); ?>"><?php echo s($course['categoryname']); ?></div>
                    </div>
                    <div class="local-financourselist-course-content">
                        <h3 id="course-title-<?php echo $course['id']; ?>" class="local-financourselist-course-title"><?php echo s($course['title']); ?></h3>
                        <p class="local-financourselist-course-description"><?php echo s(substr($course['description'], 0, 150)) . '...'; ?></p>
                        <div class="local-financourselist-course-bottom">
                            <div class="local-financourselist-course-meta">
                                <div class="local-financourselist-course-stats" role="group" aria-label="<?php echo get_string('course_statistics', 'local_financourselist'); ?>">
                                    <span aria-label="<?php echo get_string('enrolled_students', 'local_financourselist'); ?>: <?php echo $course['students']; ?>">
                                        <i class="fas fa-users" aria-hidden="true"></i> <?php echo $course['students']; ?>
                                    </span>
                                    <span aria-label="<?php echo $course['activities']; ?> <?php echo get_string('activities', 'local_financourselist'); ?>">
                                        <i class="fas fa-book" aria-hidden="true"></i> <?php echo $course['activities']; ?>
                                        <?php echo get_string('activities', 'local_financourselist'); ?>
                                    </span>
                                </div>
                            </div>
                            <a href="<?php echo $course['url']; ?>" class="local-financourselist-btn-finan" 
                               aria-label="<?php echo get_string('join_course', 'local_financourselist'); ?>: <?php echo s($course['title']); ?>">
                                <i class="fas fa-play me-1" aria-hidden="true"></i> <?php echo get_string('join_now', 'local_financourselist'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination Info -->
    <?php if ($totalcount > 0): ?>
        <div class="local-financourselist-pagination-info">
            <?php echo get_string('showing', 'local_financourselist'); ?>
            <?php echo ($page * $perpage + 1); ?> -
            <?php echo min(($page + 1) * $perpage, $totalcount); ?>
            <?php echo get_string('of_total', 'local_financourselist'); ?>
            <?php echo $totalcount; ?>
            <?php echo get_string('courses', 'local_financourselist'); ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalpages > 1): ?>
        <div class="local-financourselist-pagination">
            <?php if ($page > 0): ?>
                <a href="<?php echo new moodle_url('/local/financourselist/index.php', 
                    ['search' => $search, 'category' => $category, 'page' => $page - 1]); ?>" 
                   class="local-financourselist-page-link">
                    <i class="fas fa-chevron-left"></i> <?php echo get_string('previous', 'local_financourselist'); ?>
                </a>
            <?php else: ?>
                <span class="local-financourselist-page-link disabled">
                    <i class="fas fa-chevron-left"></i> <?php echo get_string('previous', 'local_financourselist'); ?>
                </span>
            <?php endif; ?>
            
            <?php 
            // Smart pagination - show first, last, current and surrounding pages
            $start = max(0, $page - 2);
            $end = min($totalpages - 1, $page + 2);
            
            // Always show first page
            if ($start > 0): ?>
                <a href="<?php echo new moodle_url('/local/financourselist/index.php', 
                    ['search' => $search, 'category' => $category, 'page' => 0]); ?>" 
                   class="local-financourselist-page-link">1</a>
                <?php if ($start > 1): ?>
                    <span class="local-financourselist-page-link disabled">...</span>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a href="<?php echo new moodle_url('/local/financourselist/index.php',
                    ['search' => $search, 'category' => $category, 'page' => $i]); ?>" 
                   class="local-financourselist-page-link <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i + 1; ?>
                </a>
            <?php endfor; ?>
            
            <?php 
            // Always show last page.
            if ($end < $totalpages - 1): ?>
                <?php if ($end < $totalpages - 2): ?>
                    <span class="local-financourselist-page-link disabled">...</span>
                <?php endif; ?>
                <a href="<?php echo new moodle_url('/local/financourselist/index.php',
                    ['search' => $search, 'category' => $category, 'page' => $totalpages - 1]); ?>" 
                   class="local-financourselist-page-link"><?php echo $totalpages; ?></a>
            <?php endif; ?>
            
            <?php if ($page < $totalpages - 1): ?>
                <a href="<?php echo new moodle_url('/local/financourselist/index.php',
                    ['search' => $search, 'category' => $category, 'page' => $page + 1]); ?>" 
                   class="local-financourselist-page-link">
                    <?php echo get_string('next', 'local_financourselist'); ?> <i class="fas fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="local-financourselist-page-link disabled">
                    <?php echo get_string('next', 'local_financourselist'); ?> <i class="fas fa-chevron-right"></i>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
echo $OUTPUT->footer();