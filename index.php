<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

require_once('../../config.php');

// Require login
require_login();

// Check capability
require_capability('local/financourselist:view', context_system::instance());

// Set up the page
$PAGE->set_url('/local/financourselist/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Danh Sách Khóa Học - Finan');
$PAGE->set_heading(''); // Hide default page heading
$PAGE->set_pagelayout('standard'); // Use standard layout

// Get parameters
$search = optional_param('search', '', PARAM_TEXT);
$category = optional_param('category', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

// Get settings
$perpage = get_config('local_financourselist', 'coursesperpage') ?: 15;
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

// Get courses data
$courses = array();
$totalcount = 0;

// Build SQL query
$params = array();
$whereclauses = array('c.visible = 1', 'c.id != 1'); // Exclude site course

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

// Get total count
$countsql = "SELECT COUNT(c.id) FROM {course} c WHERE " . $where;
$totalcount = $DB->count_records_sql($countsql, $params);

// Get courses
$sql = "SELECT c.*, cat.name as categoryname 
        FROM {course} c 
        LEFT JOIN {course_categories} cat ON c.category = cat.id 
        WHERE " . $where . " 
        ORDER BY c.sortorder ASC";

$courses = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);

// Process courses data
$coursesdata = array();
foreach ($courses as $course) {
    // Get enrollment count
    $enrolledsql = "SELECT COUNT(DISTINCT ue.userid) 
                    FROM {user_enrolments} ue 
                    JOIN {enrol} e ON ue.enrolid = e.id 
                    WHERE e.courseid = ? AND ue.status = 0";
    $enrolledcount = $DB->count_records_sql($enrolledsql, array($course->id));
    
    // Get activities count
    $activitiescount = $DB->count_records('course_modules', array('course' => $course->id, 'visible' => 1));
    
    // Determine category type and icon
    $categorytype = 'business';
    $categoryname = strtolower($course->categoryname ?: '');
    
    if (strpos($categoryname, 'tài chính') !== false || strpos($categoryname, 'finance') !== false) {
        $categorytype = 'finance';
        $icon = 'fas fa-chart-line';
    } elseif (strpos($categoryname, 'kế toán') !== false || strpos($categoryname, 'accounting') !== false) {
        $categorytype = 'accounting';
        $icon = 'fas fa-calculator';
    } elseif (strpos($categoryname, 'đầu tư') !== false || strpos($categoryname, 'investment') !== false) {
        $categorytype = 'investment';
        $icon = 'fas fa-trending-up';
    } elseif (strpos($categoryname, 'công nghệ') !== false || strpos($categoryname, 'technology') !== false) {
        $categorytype = 'technology';
        $icon = 'fas fa-microchip';
    } elseif (strpos($categoryname, 'marketing') !== false) {
        $categorytype = 'marketing';
        $icon = 'fas fa-bullhorn';
    } else {
        $icon = 'fas fa-briefcase';
    }
    
    // Get course image
    $courseimage = null;
    if ($imagemode == 'courseimage' || $imagemode == 'both') {
        require_once($CFG->libdir . '/filelib.php');
        $context = context_course::instance($course->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', 0, 'filename', false);
        if (!empty($files)) {
            $file = reset($files);
            // Use the correct URL generation for course overview files
            $courseimage = file_encode_url($CFG->wwwroot . '/pluginfile.php', 
                '/' . $context->id . '/course/overviewfiles/' . $file->get_filename());
        }
    }
    
    $coursesdata[] = array(
        'id' => $course->id,
        'title' => format_string($course->fullname),
        'description' => strip_tags(format_text($course->summary, $course->summaryformat)),
        'category' => $categorytype,
        'categoryname' => $course->categoryname ?: 'Khác',
        'icon' => $icon,
        'courseimage' => $courseimage,
        'students' => $enrolledcount,
        'activities' => $activitiescount,
        'url' => new moodle_url('/course/view.php', array('id' => $course->id))
    );
}

// Get categories for filter
$categories = $DB->get_records('course_categories', array('visible' => 1), 'name ASC');

// Calculate pagination
$totalpages = ceil($totalcount / $perpage);

echo $OUTPUT->header();
?>

<style>
:root {
    --finan-primary: <?php echo $primarycolor; ?>;
    --finan-secondary: <?php echo $secondarycolor; ?>;
    --finan-dark-green: <?php echo $darkgreen; ?>;
    --finan-light-green: <?php echo $lightgreen; ?>;
    --finan-teal: #005258;
    --finan-orange: #FC9000;
    --finan-gradient: linear-gradient(135deg, <?php echo $primarycolor; ?> 0%, <?php echo $secondarycolor; ?> 100%);
}

.finan-header {
    background: var(--finan-gradient);
    color: <?php echo $headertextcolor; ?>;
    padding: 3rem 0 2rem;
    margin: 0 0 2rem 0;
    text-align: center;
}

/* Override Moodle's default page header */
#page-header {
    display: none !important;
}

.breadcrumb {
    display: none !important;
}

.finan-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: <?php echo $headertextcolor; ?> !important;
}

.finan-header h1 i {
    color: <?php echo $headertextcolor; ?> !important;
}

.finan-header p {
    color: <?php echo $headertextcolor; ?> !important;
    opacity: 0.9;
    font-size: 1.2rem;
    max-width: 600px;
    margin: 0 auto;
}

.finan-filters {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.finan-search {
    position: relative;
    margin-bottom: 1rem;
}

.finan-search input {
    border-radius: 25px;
    border: 2px solid #e9ecef;
    padding: 12px 50px 12px 20px;
    width: 100%;
    transition: all 0.3s ease;
}

.finan-search input:focus {
    border-color: var(--finan-primary);
    box-shadow: 0 0 0 0.2rem rgba(15, 212, 107, 0.25);
}

.finan-search .search-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--finan-primary);
}

.category-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 1rem;
}

.category-filter {
    background: var(--finan-light-green);
    color: var(--finan-dark-green);
    border: 2px solid transparent;
    border-radius: 20px;
    padding: 8px 16px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.category-filter:hover,
.category-filter.active {
    background: var(--finan-primary);
    color: white;
    text-decoration: none;
}

.courses-grid {
    display: grid;
    grid-template-columns: repeat(<?php echo $gridcolumns; ?>, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (max-width: 1200px) {
    .courses-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .courses-grid {
        grid-template-columns: 1fr;
    }
}

.course-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.course-image {
    height: 180px;
    background: var(--finan-gradient);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.course-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.course-icon {
    font-size: 3rem;
    color: white;
    opacity: 0.9;
}

.course-image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.course-category {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255,255,255,0.9);
    color: var(--finan-dark-green);
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.course-content {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.course-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
    line-height: 1.4;
    color: #212121;
}

.course-description {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.course-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 0.85rem;
    color: #6c757d;
}

.course-stats {
    display: flex;
    gap: 15px;
}

.course-bottom {
    margin-top: auto;
}

.btn-finan {
    background: var(--finan-gradient);
    color: white;
    border: none;
    border-radius: 25px;
    padding: 10px 20px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: block;
    text-align: center;
    width: 100%;
}

.btn-finan:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 212, 107, 0.3);
    color: white;
    text-decoration: none;
}

.pagination-finan {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 3rem;
    gap: 5px;
}

.pagination-finan .page-link {
    border: 2px solid #e9ecef;
    color: var(--finan-primary);
    padding: 10px 15px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    min-width: 45px;
    text-align: center;
}

.pagination-finan .page-link:hover {
    background: var(--finan-light-green);
    border-color: var(--finan-primary);
    color: var(--finan-dark-green);
    text-decoration: none;
    transform: translateY(-2px);
}

.pagination-finan .page-link.active {
    background: var(--finan-primary);
    border-color: var(--finan-primary);
    color: white;
    text-decoration: none;
}

.pagination-finan .page-link.disabled {
    background: #f8f9fa;
    color: #6c757d;
    border-color: #e9ecef;
    cursor: not-allowed;
}

.pagination-finan .page-link.disabled:hover {
    transform: none;
}

.pagination-info {
    text-align: center;
    margin: 1rem 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.stats-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--finan-primary);
}

.stat-label {
    color: #6c757d;
    font-weight: 500;
}

@media (max-width: 768px) {
    .category-filters {
        justify-content: center;
    }
    
    .pagination-finan {
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .pagination-finan .page-link {
        padding: 8px 12px;
        min-width: 40px;
    }
}
</style>

<div class="finan-header">
    <div class="container">
        <h1><i class="fas fa-graduation-cap me-3"></i><?php echo format_string($pagetitle); ?></h1>
        <p><?php echo format_string($pagesubtitle); ?></p>
    </div>
</div>

<div class="container">
    <!-- Statistics -->
    <?php if ($showstats): ?>
    <div class="stats-section">
        <div class="stats-grid">
            <div>
                <div class="stat-number"><?php echo $totalcount; ?></div>
                <div class="stat-label"><?php echo get_string('total_courses', 'local_financourselist'); ?></div>
            </div>
            <div>
                <div class="stat-number"><?php echo array_sum(array_column($coursesdata, 'students')); ?></div>
                <div class="stat-label"><?php echo get_string('total_students', 'local_financourselist'); ?></div>
            </div>
            <div>
                <div class="stat-number">94%</div>
                <div class="stat-label"><?php echo get_string('completion_rate', 'local_financourselist'); ?></div>
            </div>
            <div>
                <div class="stat-number">4.8</div>
                <div class="stat-label"><?php echo get_string('average_rating', 'local_financourselist'); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="finan-filters">
        <form method="get" action="">
            <div class="finan-search">
                <input type="text" name="search" value="<?php echo s($search); ?>" 
                       placeholder="<?php echo get_string('search_placeholder', 'local_financourselist'); ?>" onchange="this.form.submit()">
                <i class="fas fa-search search-icon"></i>
            </div>
            
            <div class="category-filters">
                <a href="<?php echo new moodle_url('/local/financourselist/index.php'); ?>" 
                   class="category-filter <?php echo ($category == 0) ? 'active' : ''; ?>">
                    <?php echo get_string('all_categories', 'local_financourselist'); ?>
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?php echo new moodle_url('/local/financourselist/index.php', array('category' => $cat->id)); ?>" 
                       class="category-filter <?php echo ($category == $cat->id) ? 'active' : ''; ?>">
                        <?php echo format_string($cat->name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </form>
    </div>

    <!-- Courses Grid -->
    <div class="courses-grid">
        <?php if (empty($coursesdata)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted"><?php echo get_string('no_courses_found', 'local_financourselist'); ?></h4>
                <p class="text-muted"><?php echo get_string('no_courses_message', 'local_financourselist'); ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($coursesdata as $course): ?>
                <div class="course-card">
                    <div class="course-image">
                        <?php if ($imagemode == 'icon' || (!$course['courseimage'] && $imagemode != 'courseimage')): ?>
                            <i class="<?php echo $course['icon']; ?> course-icon"></i>
                        <?php elseif ($course['courseimage']): ?>
                            <img src="<?php echo $course['courseimage']; ?>" alt="<?php echo s($course['title']); ?>">
                            <?php if ($imagemode == 'both'): ?>
                                <div class="course-image-overlay">
                                    <i class="<?php echo $course['icon']; ?> course-icon"></i>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <i class="<?php echo $course['icon']; ?> course-icon"></i>
                        <?php endif; ?>
                        <div class="course-category"><?php echo s($course['categoryname']); ?></div>
                    </div>
                    <div class="course-content">
                        <h3 class="course-title"><?php echo s($course['title']); ?></h3>
                        <p class="course-description"><?php echo s(substr($course['description'], 0, 150)) . '...'; ?></p>
                        <div class="course-bottom">
                            <div class="course-meta">
                                <div class="course-stats">
                                    <span><i class="fas fa-users"></i> <?php echo $course['students']; ?></span>
                                    <span><i class="fas fa-book"></i> <?php echo $course['activities']; ?> <?php echo get_string('activities', 'local_financourselist'); ?></span>
                                </div>
                            </div>
                            <a href="<?php echo $course['url']; ?>" class="btn-finan">
                                <i class="fas fa-play me-1"></i> <?php echo get_string('join_now', 'local_financourselist'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination Info -->
    <?php if ($totalcount > 0): ?>
        <div class="pagination-info">
            <?php echo get_string('showing', 'local_financourselist'); ?> <?php echo ($page * $perpage + 1); ?> - <?php echo min(($page + 1) * $perpage, $totalcount); ?> 
            <?php echo get_string('of_total', 'local_financourselist'); ?> <?php echo $totalcount; ?> <?php echo get_string('courses', 'local_financourselist'); ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalpages > 1): ?>
        <div class="pagination-finan">
            <?php if ($page > 0): ?>
                <a href="<?php echo new moodle_url('/local/financourselist/index.php', 
                    array('search' => $search, 'category' => $category, 'page' => $page - 1)); ?>" 
                   class="page-link">
                    <i class="fas fa-chevron-left"></i> <?php echo get_string('previous', 'local_financourselist'); ?>
                </a>
            <?php else: ?>
                <span class="page-link disabled">
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
                    array('search' => $search, 'category' => $category, 'page' => 0)); ?>" 
                   class="page-link">1</a>
                <?php if ($start > 1): ?>
                    <span class="page-link disabled">...</span>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a href="<?php echo new moodle_url('/local/financourselist/index.php', 
                    array('search' => $search, 'category' => $category, 'page' => $i)); ?>" 
                   class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i + 1; ?>
                </a>
            <?php endfor; ?>
            
            <?php 
            // Always show last page
            if ($end < $totalpages - 1): ?>
                <?php if ($end < $totalpages - 2): ?>
                    <span class="page-link disabled">...</span>
                <?php endif; ?>
                <a href="<?php echo new moodle_url('/local/financourselist/index.php', 
                    array('search' => $search, 'category' => $category, 'page' => $totalpages - 1)); ?>" 
                   class="page-link"><?php echo $totalpages; ?></a>
            <?php endif; ?>
            
            <?php if ($page < $totalpages - 1): ?>
                <a href="<?php echo new moodle_url('/local/financourselist/index.php', 
                    array('search' => $search, 'category' => $category, 'page' => $page + 1)); ?>" 
                   class="page-link">
                    <?php echo get_string('next', 'local_financourselist'); ?> <i class="fas fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="page-link disabled">
                    <?php echo get_string('next', 'local_financourselist'); ?> <i class="fas fa-chevron-right"></i>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
echo $OUTPUT->footer();
?>