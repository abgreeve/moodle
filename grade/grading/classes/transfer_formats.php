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
 * Transfer object for import and export formats
 *
 * @package    core_grading
 * @copyright  2022 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_grading;

use lang_string;
use coding_exception;

class transfer_formats {

    /** @var array A collection of information about different formats. */
    protected $formats;

    /**
     * Adds details about a format for either importing or exporting.
     *
     * @param string $name A title given to the import / export format such as 'imsspecificaion'
     * @param lang_string $title A language string detailing the name of the import format
     * @param lang_string $help Additional text to help the user decide if they want to use this format
     * @param array $extensions An array of extensions that this format supports. If this is an export format then it should only
     *                          contain one item.
     */
    public function add_format(string $name, lang_string $title, lang_string $help, array $extensions): void {
        if (empty($extensions)) {
            throw new coding_exception('You must provide an extension for the file.');
        }
        // TODO Test the extensions for the right format (.*).
        // foreach ($extensions as $ext) {
        //     if (substr($ext, '.') !== 0) {
        //         throw new coding_exception('The extension must start with a peridod (.)');
        //     }
        // }
        $this->formats[$name] = ['title' => $title, 'help' => $help, 'extensions' => $extensions];
    }

    /**
     * Returns the extensions for a given format.
     *
     * @param string $name The title given to the import / export format such as 'imsspecification'
     * @return array A simple array of just extensions.
     */
    public function get_extensions_for_type(string $name): array {
        return $this->formats[$name]['extensions'];
    }

    /**
     * Get the titles of the different formats.
     *
     * @return array A list of the different formats that this advanced grading method supports
     */
    public function get_format_types(): array {
        return array_keys($this->formats);
    }

    /**
     * Returns all information about the formats for this advanced grading method
     *
     * @return array A list of formats, title and help language strings, and extension information
     */
    public function get_formats(): array {
        return $this->formats;
    }
}
