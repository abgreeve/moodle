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
 * Landing page for all imports from MoodleNet.
 *
 * This page asks the user to confirm the import process, and takes them to the relevant next step.
 *
 * @package     tool_moodlenet
 * @copyright   2020 Jake Dallimore <jrhdallimore@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_moodlenet\local\remote_resource;
use tool_moodlenet\local\url;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot .'/course/lib.php');

$resourceurl = required_param('resourceurl', PARAM_RAW);
$resourceurl = urldecode($resourceurl);
$course = optional_param('course', null, PARAM_INT);
$section = optional_param('section', null, PARAM_INT);
$cancel = optional_param('cancel', null, PARAM_TEXT);
$continue = optional_param('continue', null, PARAM_TEXT);

require_login();

// Handle the form submits.
// This page POSTs to self to verify the sesskey for the confirm action,
// which was included in the confirmation form on render.
// The next page will either be:
// - 1. The restore process for a course or module, if the file is an mbz file.
// - 2. The 'select a course' tool page, if course and section are not provided.
// - 3. The 'select what to do with the content' tool page, provided course and section are present.
// - 4. The dashboard, if the user decides to cancel and course or section is not found.
// - 5. The course home, if the user decides to cancel but the course and section are found.
if ($cancel) {
    if (!empty($course)) {
        redirect(new \moodle_url('/course/view.php', ['id' => $course]));
    } else {
        redirect(new \moodle_url('/'));
    }
} else if ($continue) {
    confirm_sesskey(); // This must be confirmed, otherwise scripts could pass 'continue' and get around the user confirmation.

    // Course and section data present and confirmed. Redirect to the option select view.
    if (!is_null($course) && !is_null($section)) {
        redirect(new \moodle_url('/admin/tool/moodlenet/options.php', [
            'resourceurl' => urlencode($resourceurl),
            'course' => $course,
            'section' => $section,
            'sesskey' => sesskey()
        ]));
    } else {
        \core\notification::warning("
            This will redirect to the course select page."
        );
    }
}


$remoteresource = new remote_resource(new curl(), new url($resourceurl));
$extension = $remoteresource->get_extension();

// Display the page.
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('base');
$PAGE->set_title("Import resources from MoodleNet");
$PAGE->set_heading("Import resources from MoodleNet");
$url = new moodle_url('/admin/tool/moodlenet/index.php');
$PAGE->set_url($url);

echo $OUTPUT->header();

/*\core\notification::info("
    Resource: " . $resourceurl . "
    <br> Course: $course
    <br> Section $section"
);
*/

$renderer = $PAGE->get_renderer('core');

// Relevant confirmation form.
// TODO: check extension case - might be uppercase.
if ($extension == 'mbz') {
    // TODO: Ask the user to confirm, and take them to the restore process.
    $next = '/admin/tool/moodlenet/index.php'; // Place holder only.
} else if (!is_null($course) && !is_null($section)) {
    // Course and section are provided, so ask the user to confirm, and take them to the 'option select' view.
    $course = get_course($course);
    $context = [
        'action' => 'index.php',
        'resourceurl' => $resourceurl,
        'resourcename' => $remoteresource->get_name() . '.' . $remoteresource->get_extension(),
        'course' => $course->id,
        'coursename' => $course->shortname,
        'section' => $section,
        'sesskey' => sesskey()
    ];
    echo $renderer->render_from_template('tool_moodlenet/import_index', $context);

} else {
    // Ask the user to confirm, and take them to the 'course select' view.
    $context = [
        'action' => 'index.php',
        'resourceurl' => $resourceurl,
        'resourcename' => $remoteresource->get_name() . '.' . $remoteresource->get_extension(),
        'sesskey' => sesskey()
    ];
    echo $renderer->render_from_template('tool_moodlenet/import_index', $context);
}

echo $OUTPUT->footer();
