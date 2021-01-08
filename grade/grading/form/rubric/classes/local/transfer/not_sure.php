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
 * The rubric definition transfer. This communicates with core to import and export rubrics.
 *
 * @package    gradingform_rubric
 * @copyright  2023 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_rubric\local\transfer;

use core_grading\definition_transfer;
use core_grading\transfer_formats;
use stdClass;
use lang_string;

class not_sure implements definition_transfer {

    /**
     * This defines and returns information about what the rubric will export.
     * Currently rubrics only support exporting a JSON file in an IMS specification.
     *
     * @return transfer_formats A class which contains all of the details regarding exporting this grading method.
     */
    public function report_export_formats(): transfer_formats {
        $exportformats = new transfer_formats();
        $exportformats->add_format(
            'imsspecification',
            new lang_string('imsspectitle', 'gradingform_rubric'),
            new lang_string('imsspechelp', 'gradingform_rubric'),
            ['.json']
        );
        return $exportformats;
    }

    /**
     * This defines and returns information about what the rubric can import.
     * Currently rubrics only support importing a JSON file in an IMS specificaion format.
     *
     * @return transfer_formats A class which contains all of the details regarding exporting this grading method.
     */
    public function report_import_formats(): transfer_formats {
        $importformats = new transfer_formats();
        $importformats->add_format(
            'imsspecification',
            new lang_string('imsspectitle', 'gradingform_rubric'),
            new lang_string('imsspechelp', 'gradingform_rubric'),
            ['.json']
        );
        return $importformats;
    }

    /**
     * This method handles converting the data in Moodle to an IMS specification format and then producing a string that can be
     * written to a file for export.
     *
     * @param stdClass $data The data that comes from grading method controller.
     * @param string $format The chosen format to export the grading method into.
     * @return string The converted data in a string format for exporting.
     */
    public function convert_to_export_format(stdClass $data, string $format): string {
        $imsmapper = new \gradingform_rubric\local\export\ims_mapper();
        return $imsmapper->translate_data((array) $data);
    }

    /**
     * Takes a string read from a file and then converts it into a stdClass that can be sent to the grading method controller to be
     * imported into Moodle.
     *
     * @param string $datastring The uploaded files contents.
     * @param int $areaid The id related to the area that this grading method is being used.
     * @param string $format The chose format that the file is in.
     * @param context $context The context of the activity that is using this advanced grading method.
     * @return stdClass The data converted from the uploaded format to be inserted into Moodle.
     */
    public function import_from_file(string $datastring, int $areaid, string $format, \context $context): stdClass {
        // Currently we only have imsspecification, but if we have more, then we need to check the format to decide on what to do.
        $dataobject = json_decode($datastring);
        if (is_null($dataobject)) {
            throw new \moodle_exception('jsonimporterror', 'gradingform_rubric');
        }

        $imsimportmapper = new \gradingform_rubric\local\import\ims_mapper($dataobject, $areaid);
        return $imsimportmapper->translate_data();
    }
}
