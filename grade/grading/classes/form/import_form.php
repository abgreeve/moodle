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
        $mform->addElement('hidden', 'gradingmethod', $this->_customdata['gradingmethod']);
        $mform->setType('gradingmethod', PARAM_RAW); // @TODO get off raw!

        $mform->addElement('header', 'settingsheader', 'Import JSON file.'); // @TODO Change this heading!

        $filetypes = $this->get_all_accepted_file_types();

        $filemanageroptions = [
            'accepted_types' => $filetypes,
            'maxbytes' => 0,
            'maxfiles' => 1,
            'subdirs' => 0
        ];

        $mform->addElement('filepicker', 'advancedgradingimport', 'Rubric json file upload', // @TODO lang string + may not be a json file nor a rubric.
            null, $filemanageroptions);
        $mform->addRule('advancedgradingimport', null, 'required');

        $typecount = count($this->_customdata['importtypes']);

        if ($typecount == 1) {
            $mform->addElement('hidden', 'importtype', array_keys($this->_customdata['importtypes'])[0]);
            $mform->setType('importtype', PARAM_RAW); // @TODO Needs to be changed for what is appropriate for an import type string.
        } else {
            // @TODO put this into a group so that the title isn't double upped.
            foreach ($this->_customdata['importtypes'] as $key => $value) {
                $mform->addElement('radio', 'importtype', 'Import type', $value['title'], $key);
            }
            $mform->setDefault('importtype', array_keys($this->_customdata['importtypes'])[0]);
        }

        $mform->addElement('submit', 'import stuff', 'Import');
    }

    /**
     * Get all of the combined file types accepted by the available import services.
     *
     * @return array An array of accepted file types.
     */
    private function get_all_accepted_file_types(): array {
        $filetypes = [];
        foreach ($this->_customdata['importtypes'] as $key => $value) {
            if (!isset($value['acceptedfiletypes'])) {
                throw new \moodle_exception('error:importfiletyperequired', 'grading', '', $key);
            }
            $filetypes = array_unique(array_merge($filetypes, $value['acceptedfiletypes']));
        }
        return $filetypes;
    }
}
