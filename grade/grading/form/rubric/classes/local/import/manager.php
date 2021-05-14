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
 * Import of an IMS standard JSON file to our advanced grading method - Rubric.
 *
 * @package    gradingform_rubric
 * @copyright  2020 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_rubric\local\import;

use gradingform_rubric\local\import\ims_mapper;

/**
 * Import of an IMS standard JSON file to our advanced grading method - Rubric.
 *
 * @package    gradingform_rubric
 * @copyright  2020 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** stdClass The file data in object form to be converted. */
    public $data;
    /** int The area id that we are importing this rubric into. */
    public $areaid;

    /**
     * Constructs a new instance.
     *
     * @param stdClass  $data    File data.
     * @param int  $areaid  The areaid for this grading method.
     */
    public function __construct(\stdClass $data, int $areaid) {
        $this->data = $data;
        $this->areaid = $areaid;
    }

    /**
     * Check that the data coming in contains rubric criterion. If not then it might be the wrong format.
     */
    public function check_file_contents(): void {
        if (!isset($this->data->CFRubricCriterion)) {
            $url = new \moodle_url('/grade/grading/manage.php', ['areaid' => $this->areaid]);
            print_error('notimsspecfile', 'gradingform_rubric', $url);
        }
    }

    /**
     * Translate the IMS spec file into a format that our API recognises.
     *
     * @return \stdClass A converted object similar to what a form returns.
     */
    public function translate_data(): \stdClass {
        $imsmapper = new ims_mapper();

        $basemap = $imsmapper->map_base();
        // This is setting the status to "Ready for use". Perhaps should be set as a draft first?
        $imsmapper->set_number(20);
        $imsmapper->set_areaid($this->areaid);

        $dataarray = (array) $this->data;

        $converteddata = $this->convert_data_section($dataarray, $basemap, $imsmapper);

        $criteriamap = $imsmapper->map_criteria();
        $levelmap = $imsmapper->map_levels();
        foreach ($converteddata['rubric']['criteria'] as $key => $criteria) {

            foreach ($criteria['CFRubricCriterionLevels'] as $levelkey => $level) {
                $criteria['CFRubricCriterionLevels'][$levelkey] = $this->convert_data_section($level, $levelmap, $imsmapper);
            }

            $converteddata['rubric']['criteria'][$key] = $this->convert_data_section($criteria, $criteriamap, $imsmapper);
        }

        return (object) $converteddata;
    }

    /**
     * Convert this section of data with the use of the mapped criteria.
     *
     * @param  array      $datasection The section of data we are converting.
     * @param  array      $mappedsection The mapped section we are dealing with.
     * @param  ims_mapper $imsmapper The ims_mapper class for running the required functions.
     * @return array      The datasection with the translated data.
     */
    protected function convert_data_section(array $datasection, array $mappedsection, ims_mapper $imsmapper): array {

        $mappercheck = array_map(function($value) {
            if ($value == ims_mapper::NOT_IMPORTED) {
                return $value = true;
            }
            return $value = false;
        }, $mappedsection['base']);

        foreach ($datasection as $key => $value) {
            if (isset($mappedsection['base'][$key])) {
                if ($mappedsection['base'][$key] == ims_mapper::NOT_IMPORTED) {
                    $mappercheck[$key] = true;
                    unset($datasection[$key]);
                } else {
                    $function = $mappedsection['base'][$key]['function'];
                    $fieldname = $mappedsection['base'][$key]['field'];
                    $datasection[$fieldname] = call_user_func([$imsmapper, $function], $value);
                    if ($key != $fieldname) {
                        unset($datasection[$key]);
                    }
                    $mappercheck[$key] = true;
                }
            }
            // Add target fields.
            foreach ($mappedsection['target'] as $index => $function) {
                $datasection[$index] = call_user_func([$imsmapper, $function]);
            }
        }

        // Find the required fields in Moodle that need to be filled in, that are optional in the IMS spec.
        foreach ($mappercheck as $key => $value) {
            if (!$value) {
                $function = $mappedsection['base'][$key]['function'];
                $fieldname = $mappedsection['base'][$key]['field'];
                $datasection[$fieldname] = call_user_func([$imsmapper, $function], '');
            }
        }

        return $datasection;
    }
}
