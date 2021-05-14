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
 * Export of an internal rubric ultimately to JSON.
 *
 * @package    gradingform_rubric
 * @copyright  2020 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_rubric\local\import;

/**
 * Export of an internal rubric ultimately to JSON.
 *
 * @package    gradingform_rubric
 * @copyright  2020 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ims_mapper {

    /** A constant to signify that this item is not imported. */
    const NOT_IMPORTED = 1;

    /** int A number used for numeric values when importing. */
    protected $number = null;
    /** int The area ID that we are importing into. */
    protected $areaid = null;

    /**
     * Lists all the fields possible from the base (IMS standard) and the required fields in the target (Moodle) with
     * functions to achieve the translation.
     *
     * @return array Details about the different fields and respective functions.
     */
    public function map_base(): array {
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
    public function map_criteria(): array {
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
    public function map_levels(): array {
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

    public function get_text(string $value): string {
        return $value;
    }

    public function set_number(int $value): void {
        $this->number = $value;
    }

    public function get_number(float $value = null): float {
        return $value ?? $this->number;
    }

    public function get_criteria($value): array {

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

    public function get_editor(string $value): array {
        return [
            'text' => $value,
            'format' => FORMAT_MOODLE,
            'itemid' => file_get_unused_draft_itemid()
        ];
    }

    public function get_levels($value): array {
        return $value;
    }

    public function set_areaid(int $value): void {
        $this->areaid = $value;
    }

    public function get_areaid(): int {
        return clean_param($this->areaid, PARAM_INT);
    }

    protected function index_newids($data): array {
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
