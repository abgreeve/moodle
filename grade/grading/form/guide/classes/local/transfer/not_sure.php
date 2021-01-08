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
 * The guide definition transfer. This communicates with core to import and export guides.
 *
 * @package    gradingform_guide
 * @copyright  2022 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_guide\local\transfer;

use core_grading\definition_transfer;
use core_grading\transfer_formats;
use stdClass;
use lang_string;
use moodle_url;
use moodle_exception;

class not_sure implements definition_transfer {

    /**
     * This defines and returns information about what the grading guide will export.
     * Currently only support exporting a JSON file in a Moodle basic format.
     *
     * @return transfer_formats A class which contains all of the details regarding exporting this grading method.
     */
    public function report_export_formats(): transfer_formats {
        $exportformats = new transfer_formats();
        $exportformats->add_format(
            'moodlebasic',
            new lang_string('basicexporttitle', 'gradingform_guide'),
            new lang_string('basicexporthelp', 'gradingform_guide'),
            ['.json']
        );
        return $exportformats;
    }

    /**
     * This defines and returns information about what the guide can import.
     * Currently only support importing a JSON file in a basic moodle format.
     *
     * @return transfer_formats A class which contains all of the details regarding exporting this grading method.
     */
    public function report_import_formats(): transfer_formats {
        $importformats = new transfer_formats();
        $importformats->add_format(
            'moodlebasic',
            new lang_string('basicimporttitle', 'gradingform_guide'),
            new lang_string('basicimporthelp', 'gradingform_guide'),
            ['.json']
        );
        return $importformats;
    }

    /**
     * This method just sets the stdClass to a JSON encoded string.
     *
     * @param stdClass $data The data that comes from grading method controller.
     * @param string $format The chosen format to export the grading method into.
     * @return string The converted data in a string format for exporting.
     */
    public function convert_to_export_format(stdClass $data, string $format): string {
        unset($data->id);
        $datastring = json_encode($data, JSON_PRETTY_PRINT);
        return $datastring;
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
        $dataobject = json_decode($datastring);
        $url = new moodle_url('/grade/grading/manage.php', ['areaid' => $areaid]);
        if (is_null($dataobject)) {
            throw new moodle_exception('jsonimporterror', 'gradingform_guide', $url);
        }

        // Check that this is the file type we are looking for.
        if (!isset($dataobject->method) || $dataobject->method !== "guide") {
            throw new moodle_exception('notmoodlebasicfile', 'gradingform_guide', $url);
        }

        $dataobject->areaid = $areaid;
        $dataobject->description_editor = [
            'text' => $dataobject->description,
            'format' => $dataobject->descriptionformat,
            'itemid' => \file_get_unused_draft_itemid()
        ];
        $dataobject->guide = ['criteria' => [], 'comments' => [], 'options' => (array) json_decode($dataobject->options)];
        foreach ($dataobject->guide_criteria as $key => $value) {
            $dataobject->guide['criteria']['NEWID' . $key] = (array) $value;
        }
        foreach ($dataobject->guide_comments as $key => $value) {
            $dataobject->guide['comments']['NEWID' . $key] = (array) $value;
        }

        return $dataobject;
    }

}
