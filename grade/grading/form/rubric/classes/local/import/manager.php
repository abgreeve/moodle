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
 * Export of advanced grading method to an IMS standard format.
 *
 * @package    core_grading
 * @copyright  2020 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_rubric\local\import;

use gradingform_rubric\local\import\ims_mapper;

class manager {

    public $data;
    public $areaid;

    public function __construct($data, $areaid) {
        $this->data = $data;
        $this->areaid = $areaid;
    }

    public function translate_data() {
        $imsmapper = new ims_mapper();

        $basemap = $imsmapper->map_base();
        // This is setting the status to "Ready for use". Perhaps should be set as a draft first?
        $imsmapper->set_number(20);
        $imsmapper->set_areaid($this->areaid);

        $tmepthing = (array) $this->data;

        $good = $this->convert_data_section($tmepthing, $basemap, $imsmapper);

        $criteriamap = $imsmapper->map_criteria();
        $levelmap = $imsmapper->map_levels();
        foreach ($good['rubric']['criteria'] as $key => $criteria) {

            foreach ($criteria['CFRubricCriterionLevels'] as $levelkey => $level) {
                $criteria['CFRubricCriterionLevels'][$levelkey] = $this->convert_data_section($level, $levelmap, $imsmapper);
            }

            $good['rubric']['criteria'][$key] = $this->convert_data_section($criteria, $criteriamap, $imsmapper);
        }

        return (object) $good;
    }

    protected function convert_data_section($datasection, $mapper, $imsthing) {
        $mappercheck = array_map(function($value) {
            if ($value == ims_mapper::NOT_IMPORTED) {
                return $value = true;
            }
            return $value = false;
        }, $mapper['base']);
        foreach ($datasection as $key => $value) {
            if (isset($mapper['base'][$key])) {
                if ($mapper['base'][$key] == ims_mapper::NOT_IMPORTED) {
                    $mappercheck[$key] = true;
                    unset($datasection[$key]);
                } else {
                    $function = $mapper['base'][$key]['function'];
                    $fieldname = $mapper['base'][$key]['field'];
                    $datasection[$fieldname] = call_user_func([$imsthing, $function], $value);
                    if ($key != $fieldname) {
                        unset($datasection[$key]);
                    }
                    $mappercheck[$key] = true;
                }
            }
            // Add target fields.
            foreach ($mapper['target'] as $index => $function) {
                $datasection[$index] = call_user_func([$imsthing, $function]);
            }
        }
        // Find the required fields in Moodle that need to be filled in, that are optional in the IMS spec.
        foreach ($mappercheck as $key => $value) {
            if (!$value) {
                $function = $mapper['base'][$key]['function'];
                $fieldname = $mapper['base'][$key]['field'];
                $datasection[$fieldname] = call_user_func([$imsthing, $function], '');
            }
        }

        return $datasection;
    }

}
