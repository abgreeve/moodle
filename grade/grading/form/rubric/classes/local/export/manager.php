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

namespace gradingform_rubric\local\export;

use gradingform_rubric\local\export\ims_mapper;

/**
 * Export of advanced grading method to an IMS standard format.
 *
 * @package    core_grading
 * @copyright  2020 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** @var \stdClass The full rubric data to be translated into IMS standard format. */
    public $data;
    /** @var int The timestamp for when the rubric was last modified. Required in differnt places in the IMS format. */
    protected $timemodified;

    /**
     * Constructor method that takes in a rubric for translation.
     *
     * @param \stdClass $data All of the rubric information to be translated.
     */
    public function __construct(\stdClass $data) {
        $this->data = $data;
        $this->timemodified = $data->timemodified;
    }

    /**
     * Translates our Moodle rubric into an object that conforms with IMS.
     *
     * @return \stdClass The translated data.
     */
    public function translate_data(): \stdClass {
        $imsmapper = new ims_mapper();

        $basemap = $imsmapper->map_base();

        $basedata = (array) $this->data;
        $basedata = (object) $this->convert_data_section($basedata, $basemap, $imsmapper);

        // Deal with criteria.
        $criteriamap = $imsmapper->map_criteria();
        $levelmap = $imsmapper->map_levels();
        foreach ($basedata->CFRubricCriterion as $index => $criterion) {
            $imsmapper->set_datetime($this->timemodified);
            $basedata->CFRubricCriterion[$index] = (object) $this->convert_data_section($criterion, $criteriamap, $imsmapper);
              // Let's do levels as well.
            $i = 0; // Position number for our levels.
            foreach ($criterion['levels'] as $ckey => $level) {
                // Set the position for this level.
                $imsmapper->set_number($i);
                $i++;
                $basedata->CFRubricCriterion[$index]->CFRubricCriterionLevels[$ckey] =
                        (object) $this->convert_data_section($level, $levelmap, $imsmapper);
            }
            // The format for IMS must have the array start at 0, so we re-index the array.
            $temp = array_values($basedata->CFRubricCriterion[$index]->CFRubricCriterionLevels);
            $basedata->CFRubricCriterion[$index]->CFRubricCriterionLevels = $temp;
        }

        $tmpe = array_values($basedata->CFRubricCriterion);

        $basedata->CFRubricCriterion = $tmpe;

        return $basedata;
    }

    /**
     * Converts the current data into the desired format.
     *
     * @param  array $datasection The section of data to be converted.
     * @param  array $mapper The map section used to convert this section of data from Moodle into IMS format.
     * @param  imsmapper $imsmapper The mapper object. Used to call conversion functions.
     * @return array The converted data section.
     */
    protected function convert_data_section(array $datasection, array $mapper, ims_mapper $imsmapper): array {
        foreach ($datasection as $key => $value) {
            if (isset($mapper['base'][$key])) {
                if ($mapper['base'][$key] == ims_mapper::NOT_EXPORTED) {
                    unset($datasection[$key]);
                } else {
                    $function = $mapper['base'][$key]['function'];
                    $fieldname = $mapper['base'][$key]['fieldname'];
                    $datasection[$fieldname] = call_user_func([$imsmapper, $function], $value);
                    if ($key != $fieldname) {
                        unset($datasection[$key]);
                    }
                }
            }
            // Add target fields.
            foreach ($mapper['target'] as $key => $value) {
                $function = $value;
                $datasection[$key] = call_user_func([$imsmapper, $function]);
            }
        }
        return $datasection;
    }
}
