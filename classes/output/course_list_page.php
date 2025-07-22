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
 * Course list page output class.
 *
 * @package    local_financourselist
 * @copyright  2025 Orwell <thien.trantu@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_financourselist\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Course list page output class.
 *
 * @package    local_financourselist
 * @copyright  2025 Orwell <thien.trantu@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_list_page implements renderable, templatable {
    /** @var string The page title */
    protected $pagetitle;
    
    /** @var string The page subtitle */
    protected $pagesubtitle;
    
    /** @var bool Whether to show statistics */
    protected $showstats;
    
    /** @var array Statistics data */
    protected $stats;
    
    /** @var array Courses data */
    protected $courses;
    
    /** @var array Categories for filter */
    protected $categories;
    
    /** @var string Current search term */
    protected $search;
    
    /** @var int Current category filter */
    protected $categoryid;
    
    /** @var array Pagination data */
    protected $pagination;
    
    /**
     * Constructor.
     *
     * @param string $pagetitle The page title
     * @param string $pagesubtitle The page subtitle
     * @param bool $showstats Whether to show statistics
     * @param array $stats Statistics data
     * @param array $courses Courses data
     * @param array $categories Categories for filter
     * @param string $search Current search term
     * @param int $categoryid Current category filter
     * @param array $pagination Pagination data
     */
    public function __construct($pagetitle, $pagesubtitle, $showstats, $stats, $courses, 
                                $categories, $search, $categoryid, $pagination) {
        $this->pagetitle = $pagetitle;
        $this->pagesubtitle = $pagesubtitle;
        $this->showstats = $showstats;
        $this->stats = $stats;
        $this->courses = $courses;
        $this->categories = $categories;
        $this->search = $search;
        $this->categoryid = $categoryid;
        $this->pagination = $pagination;
    }
    
    /**
     * Export data for template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        
        // Page header data.
        $data->pagetitle = format_string($this->pagetitle);
        $data->pagesubtitle = format_string($this->pagesubtitle);
        
        // Statistics data.
        $data->showstats = $this->showstats;
        if ($this->showstats) {
            $data->stats = $this->stats;
        }
        
        // Search and filter data.
        $data->search = $this->search;
        $data->searchplaceholder = get_string('search_placeholder', 'local_financourselist');
        
        // Categories data.
        $data->categories = [];
        foreach ($this->categories as $category) {
            $cat = new stdClass();
            $cat->id = $category->id;
            $cat->name = format_string($category->name);
            $cat->active = ($this->categoryid == $category->id);
            $cat->url = new \moodle_url('/local/financourselist/index.php', ['category' => $category->id]);
            $data->categories[] = $cat;
        }
        
        // Add "All categories" option.
        $allcat = new stdClass();
        $allcat->id = 0;
        $allcat->name = get_string('all_categories', 'local_financourselist');
        $allcat->active = ($this->categoryid == 0);
        $allcat->url = new \moodle_url('/local/financourselist/index.php');
        array_unshift($data->categories, $allcat);
        
        // Courses data.
        $data->hascourses = !empty($this->courses);
        $data->courses = [];
        foreach ($this->courses as $course) {
            $coursedata = new stdClass();
            $coursedata->id = $course['id'];
            $coursedata->title = $course['title'];
            $coursedata->description = $course['description'];
            $coursedata->categoryname = $course['categoryname'];
            $coursedata->categoryclass = 'local-financourselist-' . $course['category'];
            $coursedata->icon = $course['icon'];
            $coursedata->courseimage = $course['courseimage'];
            $coursedata->hasimage = !empty($course['courseimage']);
            $coursedata->students = $course['students'];
            $coursedata->activities = $course['activities'];
            $coursedata->url = $course['url']->out();
            $coursedata->jointext = get_string('join_now', 'local_financourselist');
            $data->courses[] = $coursedata;
        }
        
        // Empty state.
        if (empty($this->courses)) {
            $data->emptytitle = get_string('no_courses_found', 'local_financourselist');
            $data->emptymessage = get_string('no_courses_message', 'local_financourselist');
        }
        
        // Pagination data.
        $data->haspagination = !empty($this->pagination['pages']) && count($this->pagination['pages']) > 1;
        if ($data->haspagination) {
            $data->pagination = $this->pagination;
        }
        
        // Strings for template.
        $data->strings = [
            'totalcourses' => get_string('total_courses', 'local_financourselist'),
            'totalstudents' => get_string('total_students', 'local_financourselist'),
            'completionrate' => get_string('completion_rate', 'local_financourselist'),
            'averagerating' => get_string('average_rating', 'local_financourselist'),
            'activities' => get_string('activities', 'local_financourselist'),
            'category' => get_string('category', 'local_financourselist'),
            'enrolledstudents' => get_string('enrolled_students', 'local_financourselist'),
            'coursestatistics' => get_string('course_statistics', 'local_financourselist'),
            'joincourse' => get_string('join_course', 'local_financourselist'),
        ];
        
        return $data;
    }
}