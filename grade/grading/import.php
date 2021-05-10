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
 * Export of advanced grading method to XML.
 *
 * @package    core_grading
 * @copyright  2020 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

$areaid = optional_param('areaid', null, PARAM_INT);
$gradingmethod = optional_param('gradingmethod', 'gradingform_rubric', PARAM_PLUGIN);

$pluginlist = get_plugin_list_with_function('gradingform', 'report_import_formats');
$options = $pluginlist[$gradingmethod]();

// get all information about this rubric

$manager = get_grading_manager($areaid);

list($context, $course, $cm) = get_context_info_array($manager->get_context()->id);
require_login($course, true, $cm);

$PAGE->set_url(new \moodle_url('/grade/grading/import.php', ['areaid' => $areaid]));

$control = substr($gradingmethod, 12);
$controller = $manager->get_controller($control);

// Make a form to upload a file for processing.
$mform = new \core_grading\form\import_form('', ['areaid' => $areaid, 'importtypes' => $options, 'gradingmethod' => $gradingmethod]);

if ($data = $mform->get_data()) {

    require_sesskey();

    // Get the file from the users draft area.
    $usercontext = context_user::instance($USER->id);
    $fs = get_file_storage();
    $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data->advancedgradingimport, 'id', false);
    $file = reset($files);
    $importstring = $file->get_content();

    $formfunctions = get_plugin_list_with_function('gradingform', 'import_from_file');
    $translatedobject = $formfunctions[$gradingmethod]($importstring, $areaid, $data->importtype);
    $controller->update_definition($translatedobject);

    redirect(new \moodle_url('/grade/grading/manage.php', ['areaid' => $areaid]));

    exit();
}

echo $OUTPUT->header();
echo $OUTPUT->box_start();
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
