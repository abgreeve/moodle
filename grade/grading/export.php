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
 * Export of advanced grading method to the selected format.
 *
 * @package    core_grading
 * @copyright  2020 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

$areaid = required_param('areaid', PARAM_INT);
$gradingmethod = optional_param('gradingmethod', 'gradingform_rubric', PARAM_PLUGIN);
$format = optional_param('format', 'imsspecification', PARAM_ALPHANUMEXT);

$manager = get_grading_manager($areaid);
list($context, $course, $cm) = get_context_info_array($manager->get_context()->id);
require_login($course, true, $cm);
require_capability('moodle/grade:exportgradingforms', $context);

$control = substr($gradingmethod, 12);

// get all information about this grading method.
$controller = $manager->get_controller($control);
$definition = $controller->get_definition();
$data = clone($definition);

$exportstring = \core_grading\transfer_factory::get_export_string($control, $data, $format);
$extension = \core_grading\transfer_factory::get_extension_for_export($control, $format);

// TODO create a better name for the export file.
if (!is_null($exportstring)) {
    send_temp_file($exportstring, $control . 'export-' . $format . $extension[0], true);
}
