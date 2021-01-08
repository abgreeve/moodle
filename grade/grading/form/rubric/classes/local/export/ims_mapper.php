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
 * Export of advanced grading method to XML.
 *
 * @package    gradingform_rubric
 * @copyright  2020 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_rubric\local\export;

class ims_mapper {

    /** These fields are not exported to the IMS standard. */
    const NOT_EXPORTED = 1;

    /** @var $datetime The current datetime to use for get_datetime(). */
    protected $datetime = null;
    /** @var $number The current number to use for get_number(). */
    protected $number = null;

    /**
     * The list of fields from the base (Moodle) to the target (IMS).
     *
     * @return array The details of the fields.
     */
    public function map_base(): array {
        return [
            'base' => [
                'areaid' => self::NOT_EXPORTED,
                'id' => [
                    'function' => 'get_uuid',
                    'fieldname' => 'Identifier'
                ],
                'name' => [
                    'function' => 'get_text',
                    'fieldname' => 'Title'
                ],
                'description' => [
                    'function' => 'get_text',
                    'fieldname' => 'description'
                ],
                'descriptionformat' => self::NOT_EXPORTED,
                'status' => self::NOT_EXPORTED,
                'description_editor' => self::NOT_EXPORTED,
                'options' => self::NOT_EXPORTED,
                'copiedfromid' => self::NOT_EXPORTED,
                'timecreated' => self::NOT_EXPORTED,
                'usercreated' => self::NOT_EXPORTED,
                'timemodified' => [
                    'function' => 'get_datetime',
                    'fieldname' => 'lastChangeDateTime'
                ],
                'usermodified' => self::NOT_EXPORTED,
                'timecopied' => self::NOT_EXPORTED,
                'rubric_criteria' => [
                    'function' => 'get_criteria',
                    'fieldname' => 'CFRubricCriterion'
                ]
            ],
            'target' => [
                'URI' => 'get_uri',
            ]
        ];
    }

    public function map_criteria(): array {
        return [
            'base' => [
                'id' => [
                    'function' => 'get_uuid',
                    'fieldname' => 'Identifier'
                ],
                'sortorder' => [
                    'function' => 'get_number',
                    'fieldname' => 'position'
                ],
                'description' => [
                    'function' => 'get_text',
                    'fieldname' => 'Description'
                ],
                'descriptionformat' => self::NOT_EXPORTED,
                'levels' => [
                    'function' => 'get_levels',
                    'fieldname' => 'CFRubricCriterionLevels'
                ]
            ],
            'target' => [
                'URI' => 'get_uri',
                'lastChangeDateTime' => 'get_datetime'
            ]
        ];
    }

    public function map_levels(): array {
        return [
            'base' => [
                'id' => [
                    'function' => 'get_uuid',
                    'fieldname' => 'Identifier'
                ],
                'score' => [
                    'function' => 'get_text',
                    'fieldname' => 'score'
                ],
                'definition' => [
                    'function' => 'get_text',
                    'fieldname' => 'Description'
                ],
                'definitionformat' => self::NOT_EXPORTED
            ],
            'target' => [
                'URI' => 'get_uri',
                'lastChangeDateTime' => 'get_datetime',
                'position' => 'get_number'
            ]
        ];
    }

    public function get_uuid($value): string {
        return \core\uuid::generate();
    }

    public function get_text($value): string {
        $value = \Normalizer::normalize($value);
        return strval($value);
    }

    public function set_number($value): void {
        $this->number = $value;
    }

    public function get_number($value = null): int {
        $value = $value ?? $this->number;
        return $value;
    }

    public function set_datetime($value): void {
        $this->datetime = $value;
    }

    public function get_datetime($value = null): string {
        $value = $value ?? $this->datetime;
        $time = new \DateTime('@' . $value);
        return $time->format('c');
    }

    public function get_uri($value = null): string {
        return new \moodle_url('/grade/grading/manage.php');
    }

    public function get_criteria($value): array {
        return $value;
    }

    public function get_levels($value): array {
        return $value;
    }
}
