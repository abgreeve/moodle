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
 * Page to select WHAT to do with a given resource stored on MoodleNet.
 *
 * This collates and presents the same options as a user would see for dndupload.
 * That is, it leverages the dndupload_register() hooks and delegates the resource handling to the dndupload_handle hooks.
 *
 * This page requires a course, section an resourceurl.
 *
 * @package     tool_moodlenet
 * @copyright   2020 Jake Dallimore <jrhdallimore@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot .'/course/lib.php');

use \tool_moodlenet\local\import_handler_registry;
use \tool_moodlenet\local\import_processor;
use \tool_moodlenet\local\remote_resource;
use \tool_moodlenet\local\url;

/*
The basic logic for this page is as follows:
1. Try to get the extension of the remote resource, defaulting to ''.
2. Get plugins handling that extension from the dnd_register hooks. If the extension is '', the only option is a file resource.
3. Present the various import options in a form.
4. Handle form submit, which includes processing the import and then redirecting to the course page with a notice.
*/

$course = required_param('course', PARAM_INT);
$section = required_param('section', PARAM_INT);
$resourceurl = required_param('resourceurl', PARAM_RAW);
// TODO if receiving a POST, this URL can be a real PARAM_URL (recommended).
$resourceurl = urldecode($resourceurl);
$modhandler = optional_param('modhandler', null, PARAM_TEXT);
$import = optional_param('import', null, PARAM_TEXT);
$cancel = optional_param('cancel', null, PARAM_TEXT);

$course = get_course($course);
require_course_login($course, false);
global $USER;

confirm_sesskey();

if ($cancel) {
    redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
}

$handlerregistry = new import_handler_registry($course, $USER);

if ($modhandler && $import) {
    // TODO check capability to process import in this course.

    $remoteresource = new remote_resource(new curl(), new url($resourceurl));
    $importproc = new import_processor($course, $section, $remoteresource, $modhandler, $handlerregistry, $USER);
    $importproc->process();
    redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
}

// Find the handlers supporting this extension.
$resource = new remote_resource(new curl(), new url($resourceurl));
$handlers = $handlerregistry->get_file_handlers_for_extension($resource->get_extension());
$handlercontext = [];
foreach ($handlers  as $handler) {
    $handlercontext[] = ['module' => $handler->get_module_name(), 'message' => $handler->get_description()];
}

// Setup the page and display the form.
$PAGE->set_context(context_course::instance($course->id));
$PAGE->set_pagelayout('base');
$PAGE->set_title("Import options");
$PAGE->set_heading("Import option select");
$url = new moodle_url('/admin/tool/moodlenet/options.php');
$PAGE->set_url($url);
$renderer = $PAGE->get_renderer('core');
$context = [
    'resourcename' => sprintf('%s.%s', $resource->get_name(), $resource->get_extension()),
    'resourceurl' => urlencode($resourceurl),
    'course' => $course->id,
    'section' => $section,
    'sesskey' => sesskey(),
    'handlers' => $handlercontext
];

echo $OUTPUT->header();
echo $renderer->render_from_template('tool_moodlenet/import_options_select', $context);
echo $OUTPUT->footer();
