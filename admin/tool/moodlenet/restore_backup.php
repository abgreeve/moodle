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
 * This page does some checking and loads the file in preparation of the moodle restore process.
 *
 * @package     tool_moodlenet
 * @copyright   2020 Adrian Greeve <adrian@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_moodlenet\local\remote_resource;
use tool_moodlenet\local\import_backup_helper;
use tool_moodlenet\local\url;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot .'/course/lib.php');

$resourceurl = required_param('resourceurl', PARAM_RAW);
$resourceurl = urldecode($resourceurl);
$course = optional_param('course', null, PARAM_INT);
$section = optional_param('section', null, PARAM_INT);
$cancel = optional_param('cancel', null, PARAM_TEXT);

require_login();

if ($cancel) {
    if (!empty($course)) {
        redirect(new \moodle_url('/course/view.php', ['id' => $course]));
    } else {
        redirect(new \moodle_url('/'));
    }
}
confirm_sesskey(); // This must be confirmed, otherwise scripts could pass 'continue' and get around the user confirmation.

$remoteresource = new remote_resource(new curl(), new url($resourceurl));

if (!isset($course)) {
    // Find a course that the user has permission to upload a backup file.
    // This is likely to be very slow on larger sites.
    $context = import_backup_helper::get_context_for_user($USER->id);

    if (is_null($context)) {
        print_error('nopermissions', 'error', '', get_string('restore:uploadfile', 'core_role'));
    }
} else {
    $context = context_course::instance($course);
}

$importbackuphelper = new import_backup_helper($remoteresource, $USER, $context);
$storedfile = $importbackuphelper->get_stored_file();

$url = new \moodle_url('/backup/restorefile.php', [
    'component' => $storedfile->get_component(),
    'filearea' => $storedfile->get_filearea(),
    'itemid' => $storedfile->get_itemid(),
    'filepath' => $storedfile->get_filepath(),
    'filename' => $storedfile->get_filename(),
    'filecontextid' => $storedfile->get_contextid(),
    'contextid' => $context->id,
    'action' => 'choosebackupfile'
]);
redirect($url);