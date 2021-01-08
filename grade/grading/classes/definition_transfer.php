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
 * Definition transfer interface
 *
 * @package    core_grading
 * @copyright  2022 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_grading;

use core_grading\transfer_formats;
use stdClass;
use context;

interface definition_transfer {

    /**
     * This defines and returns information about what this grading method exports.
     * The transfer_formats contains a title or string key for internal use, A title language string to describe the format, A help
     * language string to provide additional information, and an array of extensions that the file will export in. Note that you
     * can only provide one export extension
     *
     * @return transfer_formats A class which contains all of the details regarding exporting this grading method.
     */
    public function report_export_formats(): transfer_formats;

    /**
     * This defines and returns information about what this grading method imports.
     * The transfer_formats contains a title or string key for internal use, A title language string to describe the format, A help
     * language string to provide additional information, and an array of extensions that this grading method can handle importing.
     *
     * @return transfer_formats A class which contains all of the details regarding exporting this grading method.
     */
    public function report_import_formats(): transfer_formats;

    /**
     * This method handles converting the data in Moodle to a format and then producing a string that can be written to a file for
     * export.
     *
     * @param stdClass $data The data that comes from grading method controller.
     * @param string $format The chosen format to export the grading method into.
     * @return string The converted data in a string format for exporting.
     */
    public function convert_to_export_format(stdClass $data, string $format): string;

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
    public function import_from_file(string $datastring, int $areaid, string $format, context $context): stdClass;

}
