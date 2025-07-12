<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_financourselist';
$plugin->version = 2024071213;
$plugin->requires = 2022112800; // Moodle 4.1
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.0.10';
$plugin->supported = [401, 404]; // Moodle 4.1-4.4

// Plugin URLs for Moodle.org directory
$plugin->dependencies = array();
$plugin->cron = 0;