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
 * Import advanced grading json file.
 *
 * @package    core_grading
 * @copyright  2020 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_grading\form;

require_once($CFG->dirroot.'/lib/formslib.php');

class import_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'areaid', $this->_customdata['areaid']);
        $mform->setType('areaid', PARAM_INT);

        $mform->addElement('header', 'settingsheader', 'Import JSON file.');

        $filemanageroptions = [
            'accepted_types' => ['.json'],
            'maxbytes' => 0,
            'maxfiles' => 1,
            'subdirs' => 0
        ];

        $mform->addElement('filepicker', 'advancedgradingimport', 'Rubric json file upload',
            null, $filemanageroptions);
        $mform->addRule('advancedgradingimport', null, 'required');

        foreach ($this->_customdata['importtypes'] as $key => $value) {
            $mform->addElement('radio', 'importtype', 'Import type', $value['title'], $key);
        }

        $mform->addElement('submit', 'import stuff', 'Import');
    }
}