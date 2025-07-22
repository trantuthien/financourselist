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
 * Renderer for local_financourselist.
 *
 * @package    local_financourselist
 * @copyright  2025 Orwell <thien.trantu@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_financourselist\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

/**
 * Renderer for local_financourselist.
 *
 * @package    local_financourselist
 * @copyright  2025 Orwell <thien.trantu@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    
    /**
     * Render the course list page.
     *
     * @param course_list_page $page
     * @return string
     */
    public function render_course_list_page(course_list_page $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('local_financourselist/course_list_page', $data);
    }
}