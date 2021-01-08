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
 * @copyright  2023 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_rubric\local\import;

use stdClass;

/**
 * Import of an IMS standard JSON file to our advanced grading method - Rubric.
 *
 * @package    gradingform_rubric
 * @copyright  2023 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ims_mapper {

    /** A constant to signify that this item is not imported. */
    const NOT_IMPORTED = 1;

    /** @var stdClass The file data in object form to be converted. */
    protected $data;
    /** @var int The area id that we are importing this rubric into. */
    protected $areaid;
    /** int A number used for numeric values when importing. */
    protected $number = null;

    /**
     * Constructs a new instance.
     *
     * @param stdClass  $data    File data.
     * @param int  $areaid  The areaid for this grading method.
     */
    public function __construct(stdClass $data, int $areaid) {
        $this->data = $data;
        $this->areaid = $areaid;
        if (!isset($this->data->CFRubricCriterion)) {
            throw new \moodle_exception('notimsspecfile', 'gradingform_rubric');
        }
    }

    /**
     * Translate the IMS spec file into a format that our API recognises.
     *
     * @return stdClass A converted object similar to what a form returns.
     */
    public function translate_data(): stdClass {
        $basemap = $this->map_base();
        // This is setting the status to "Ready for use". Perhaps should be set as a draft first?
        $this->set_number(20);
        $this->set_areaid($this->areaid);

        $dataarray = (array) $this->data;

        $converteddata = $this->convert_data_section($dataarray, $basemap);

        $criteriamap = $this->map_criteria();
        $levelmap = $this->map_levels();
        foreach ($converteddata['rubric']['criteria'] as $key => $criteria) {

            foreach ($criteria['CFRubricCriterionLevels'] as $levelkey => $level) {
                $criteria['CFRubricCriterionLevels'][$levelkey] = $this->convert_data_section($level, $levelmap);
            }
            $converteddata['rubric']['criteria'][$key] = $this->convert_data_section($criteria, $criteriamap);
        }

        return (object) $converteddata;
    }

    /**
     * Convert this section of data with the use of the mapped criteria.
     *
     * @param  array $datasection The section of data we are converting.
     * @param  array $mappedsection The mapped section we are dealing with.
     * @return array The datasection with the translated data.
     */
    protected function convert_data_section(array $datasection, array $mappedsection): array {
        $mappercheck = array_map(function($value) {
            if ($value == self::NOT_IMPORTED) {
                return $value = true;
            }
            return $value = false;
        }, $mappedsection['base']);

        foreach ($datasection as $key => $value) {
            if (isset($mappedsection['base'][$key])) {
                if ($mappedsection['base'][$key] == self::NOT_IMPORTED) {
                    $mappercheck[$key] = true;
                    unset($datasection[$key]);
                } else {
                    $function = $mappedsection['base'][$key]['function'];
                    $fieldname = $mappedsection['base'][$key]['field'];
                    $datasection[$fieldname] = call_user_func([$this, $function], $value);
                    if ($key != $fieldname) {
                        unset($datasection[$key]);
                    }
                    $mappercheck[$key] = true;
                }
            }
            // Add target fields.
            foreach ($mappedsection['target'] as $index => $function) {
                $datasection[$index] = call_user_func([$this, $function]);
            }
        }

        // Find the required fields in Moodle that need to be filled in, that are optional in the IMS spec.
        foreach ($mappercheck as $key => $value) {
            if (!$value) {
                $function = $mappedsection['base'][$key]['function'];
                $fieldname = $mappedsection['base'][$key]['field'];
                $datasection[$fieldname] = call_user_func([$this, $function], '');
            }
        }

        return $datasection;
    }

    /**
     * Lists all the fields possible from the base (IMS standard) and the required fields in the target (Moodle) with
     * functions to achieve the translation.
     *
     * @return array Details about the different fields and respective functions.
     */
    protected function map_base(): array {
        return [
            'base' => [
                'Identifier' => self::NOT_IMPORTED,
                'URI' => self::NOT_IMPORTED,
                'Title' => [
                    'function' => 'get_text',
                    'field' => 'name'
                ],
                'description' => [
                    'function' => 'get_editor',
                    'field' => 'description_editor'
                ],
                'lastChangeDateTime' => self::NOT_IMPORTED,
                'CFRubricCriterion' => [
                    'function' => 'get_criteria',
                    'field' => 'rubric'
                ]
            ],
            'target' => [
                'areaid' => 'get_areaid',
                'status' => 'get_number'
            ]
        ];
    }

    /**
     * This is a list of the possible fields in the criteria section of the file. This outlines what is not imported, and for
     * other elements, the function required to translate the values.
     *
     * @return array A mapping for data translation.
     */
    protected function map_criteria(): array {
        return [
            'base' => [
                'Identifier' => self::NOT_IMPORTED,
                'URI' => self::NOT_IMPORTED,
                'category' => self::NOT_IMPORTED,
                'Description' => [
                    'function' => 'get_text',
                    'field' => 'description'
                ],
                'CFItemURI' => self::NOT_IMPORTED,
                'weight' => self::NOT_IMPORTED,
                'position' => [
                    'function' => 'get_number',
                    'field' => 'sortorder'
                ],
                'rubricId' => self::NOT_IMPORTED,
                'lastChangeDateTime' => self::NOT_IMPORTED,
                'CFRubricCriterionLevels' => [
                    'function' => 'get_levels',
                    'field' => 'levels'
                ]
            ],
            'target' => [
            ]
        ];
    }

    /**
     * This is a list of the possible fields in the levels section of the file. This outlines what is not imported, and for
     * other elements, the function required to translate the values.
     *
     * @return array A mapping for data translation.
     */
    protected function map_levels(): array {
        return [
            'base' => [
                'Identifier' => self::NOT_IMPORTED,
                'URI' => self::NOT_IMPORTED,
                'Description' => [
                    'function' => 'get_text',
                    'field' => 'definition'
                ],
                'quality' => self::NOT_IMPORTED,
                'score' => [
                    'function' => 'get_number',
                    'field' => 'score'
                ],
                'feedback' => self::NOT_IMPORTED,
                'position' => self::NOT_IMPORTED,
                'rubricCriterionId' => self::NOT_IMPORTED,
                'lastChangeDateTime' => self::NOT_IMPORTED
            ],
            'target' => [
            ]
        ];
    }

    /**
     * Returns the supplied value
     *
     * @param string $value Text to be returned
     * @return string The value provided.
     */
    protected function get_text(string $value): string {
        return $value;
    }

    /**
     * Set the number to be used instead of the imported value.
     *
     * @param int $value The number to be set to be used later.
     */
    protected function set_number(int $value): void {
        $this->number = $value;
    }

    /**
     * Get the number. If it hasn't been overriden this will use the value provided by the import document.
     *
     * @param float $value The number being imported.
     * @return float The value provided.
     */
    protected function get_number(float $value = null): float {
        return $value ?? $this->number;
    }

    /**
     * Get the criteria and options for this import.
     *
     * @param array $value criteria to be imported
     * @return array criteria translated
     */
    protected function get_criteria(array $value): array {

        // Add data to array with index of 'criteria'
        // Add options array.
        return [
            'criteria' => $this->index_newids($value),
            'options' => [
                'sortlevelsasc' => 1,
                'lockzeropoints' => 1,
                'alwaysshowdefinition' => 1,
                'showdescriptionteacher' => 1,
                'showdescriptionstudent' => 1,
                'showscoreteacher' => 1,
                'showscorestudent' => 1,
                'enableremarks' => 1,
                'showremarksstudent' => 1
            ]
        ];
    }

    /**
     * Returns editor information
     *
     * @param string $value The text information
     * @return array Editor information
     */
    protected function get_editor(string $value): array {
        return [
            'text' => $value,
            'format' => FORMAT_MOODLE,
            'itemid' => file_get_unused_draft_itemid()
        ];
    }

    /**
     * Returns the value provided.
     *
     * @param array $value The levels value
     * @return array The $value provided
     */
    protected function get_levels($value): array {
        return $value;
    }

    /**
     * Sets the areaid value in get_areaid()
     *
     * @param int $value $the number to use.
     */
    protected function set_areaid(int $value): void {
        $this->areaid = $value;
    }

    /**
     * Get the areaid
     *
     * @return int The area ID
     */
    protected function get_areaid(): int {
        return clean_param($this->areaid, PARAM_INT);
    }

    /**
     * Creates and returns an index with new IDs for import.
     *
     * @param array $data information to create criterion levels
     * @return array The criteria ready for import.
     */
    protected function index_newids(array $data): array {
        $newidnumber = 1;
        $newlevelid = 1;
        $newarray = [];
        foreach ($data as $key => $value) {
            $levelarray = [];
            $valuearray = (array) $value;

            foreach ($valuearray['CFRubricCriterionLevels'] as $lkey => $level) {
                $larray = (array) $level;
                $index = 'NEWID' . $newlevelid;
                $newlevelid++;
                $levelarray[$index] = $larray;
            }

            $valuearray['CFRubricCriterionLevels'] = $levelarray;
            $index = 'NEWID' . $newidnumber;
            $newidnumber++;
            $newarray[$index] = $valuearray;
        }
        return $newarray;
    }

}
