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

use core_component;
use Throwable;
use stdClass;
use context;

class transfer_factory {

    /**
     * Tries to grab the appropriate definition_transfer object from the advanced grading method.
     *
     * @param string $name Name of the advanced grading method e.g. 'rubric'
     * @return definition_transfer An object that implements the definition_transfer interface
     */
    protected static function get_advanced_grading_class(string $name): ?definition_transfer {
        try {
            $classname = "\\gradingform_{$name}\\local\\transfer\\not_sure";
            return new $classname();
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Gets all of the export types, strings, and extensions for an advanced grade.
     *
     * @param string $name Name of the advanced grading method e.g. 'rubric'
     * @return array information about the definition and extension data for transfer
     */
    public static function get_definition_and_extension_data(string $name): ?array {
        $classinstance = self::get_advanced_grading_class($name);
        if (is_null($classinstance)) {
            return null;
        }
        $exportformats = $classinstance->report_export_formats();
        $data = [];
        foreach ($exportformats->get_formats() as $type => $format) {
            $data['types'][] = [
                'title' => $format['title']->out(),
                'help' => $format['help']->out(),
                'fileextention' => $format['extensions'][0],
                'type' => $type
            ];
        }
        return $data;

    }

    /**
     * Gets the extension for the file to be exported.
     *
     * @param string $name Name of the advanced grading method e.g. 'rubric'
     * @param string $type the format to get the export extension for. This should match the name given in the
     *                     transfer_formats::add_format() method used in report_export_formats()
     * @return array Returns the extension for the file to be exported.
     */
    public static function get_extension_for_export(string $name, string $type): ?array {
        $classinstance = self::get_advanced_grading_class($name);
        if (is_null($classinstance)) {
            return null;
        }
        $exportformats = $classinstance->report_export_formats();
        return $exportformats->get_extensions_for_type($type);
    }

    /**
     * Gets all of the import types, strings, and extensions for an advanced grade.
     *
     * @param string $name Name of the advanced grading method e.g. 'rubric'
     * @return array Returns all import types that this definition accepts.
     */
    public static function get_import_definition_data(string $name): ?array {
        $classinstance = self::get_advanced_grading_class($name);
        if (is_null($classinstance)) {
            return null;
        }
        $importformats = $classinstance->report_import_formats();
        $data = [];
        foreach ($importformats->get_formats() as $type => $format) {
            $data[$type] = [
                'title' => $format['title'],
                'help' => $format['help'],
                'acceptedfiletypes' => $format['extensions']
            ];
        }
        return $data;
    }

    /**
     * Returns the export types for a definition
     *
     * @param string $name Name of the advanced grading method e.g. 'rubric'
     * @return array A simple array of export types
     */
    public static function get_export_definition_types(string $name) {
        $classinstance = self::get_advanced_grading_class($name);
        if (is_null($classinstance)) {
            return null;
        }
        $reportexportformat = $classinstance->report_export_formats();
        return $reportexportformat->get_format_types();
    }

    /**
     * Takes data that is in an object and converts it into the appropriate format and then returns it in one string
     *
     * @param string $name Name of the advanced grading method e.g. 'rubric'
     * @param stdClass $data The data from the advanced grading type in a standard class.
     * @param string $format The export format (type) e.g. 'imsspecification'
     * @return string The converted information into the expected format
     */
    public static function get_export_string(string $name, stdClass $data, string $format) {
        $classinstance = self::get_advanced_grading_class($name);
        if (is_null($classinstance)) {
            return null;
        }
        return $classinstance->convert_to_export_format($data, $format);
    }

    /**
     * Imports into the advanced grading method from a file string into a stdClass that can be imported into the advanced grading
     * method.
     *
     * @param string $name Name of the advanced grading method e.g. 'rubric'
     * @param string $importstring String from an uploaded file to be converted
     * @param int $areaid This is an id related to the activity the advanced grading method is being used in.
     * @param string $format This is the format or type of the file being converted e.g. 'imsspecification'
     * @param context $context The context of the calling page
     * @return stdClass returns a simple object that can be imported into the advanced grading method.
     */
    public static function import_from_file_string(
        string $name,
        string $importstring,
        int $areaid,
        string $format,
        context $context
    ): ?stdClass {
        $classinstance = self::get_advanced_grading_class($name);
        if (is_null($classinstance)) {
            return null;
        }
        return $classinstance->import_from_file($importstring, $areaid, $format, $context);
    }

}
