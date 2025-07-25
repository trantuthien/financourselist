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
 * Version details for Finan Course List.
 *
 * @package    local_financourselist
 * @copyright  2025 Orwell <thien.trantu@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_financourselist';
$plugin->version = 2025011704; // Complete code style fixes.
$plugin->requires = 2022112800; // Moodle 4.1.
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.1.2';
$plugin->supported = [401, 404]; // Moodle 4.1-4.4.

// Plugin URLs for Moodle.org directory.
$plugin->dependencies = [];
$plugin->cron = 0;
