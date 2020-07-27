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
 * Smash out a module instance.
 *
 * @package    core
 * @copyright  2020 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/config.php');
require_once($CFG->dirroot . '/course/lib.php');

$courseid = required_param('id', PARAM_INT);
$section = required_param('section', PARAM_INT);

require_login();
$context = context_system::instance();

$PAGE->set_url('/lib/tests/other/jquerypage.php');
$PAGE->set_context($context);
$PAGE->set_title('Add a module');
$PAGE->set_heading('Add a module');

// We just need a call to each module to get these sensible default required values.
$moduleinfo = (object) [
	'modulename' => 'assign',
	'name' => 'My new assignment',
	'course' => $courseid,
	'section' => $section,
	'visible' => true,
	'introeditor' => ['text' => 'Sometext', 'format' => 1, 'itemid' => 1],
	'submissiondrafts' => 1,
	'requiresubmissionstatement' => 0,
	'sendnotifications' => 0,
	'sendlatenotifications' => 0,
	'duedate' => 0,
	'cutoffdate' => 0,
	'gradingduedate' => 0,
	'allowsubmissionsfromdate' => 0,
	'grade' => 0,
	'teamsubmission' => 0,
	'requireallteammemberssubmit' => 0,
	'blindmarking' => 0,
	'markingworkflow' => 0,
	'markingallocation' => 0
];

create_module($moduleinfo);

echo $OUTPUT->header();
echo $OUTPUT->footer();