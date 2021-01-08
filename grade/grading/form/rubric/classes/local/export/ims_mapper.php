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
 * @package    gradingform_rubric
 * @copyright  2023 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_rubric\local\export;

use stdClass;

/**
 * Export of advanced grading method to an IMS standard format.
 *
 * @package    gradingform_rubric
 * @copyright  2023 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ims_mapper {

    /** These fields are not exported to the IMS standard. */
    const NOT_EXPORTED = 1;

    /** @var The current datetime to use for get_datetime(). */
    protected $datetime = null;
    /** @var The current number to use for get_number(). */
    protected $number = null;

    /**
     * Translates our Moodle rubric into an object that conforms with IMS.
     *
     * @param array $data All of the rubric information to be translated.
     * @return string The translated data formatted as a json string.
     */
    public function translate_data(array $data): string {
        $timemodified = $data['timemodified'];
        $basemap = $this->map_base();
        $basedata = $this->convert_data_section($data, $basemap);

        // Deal with criteria.
        $criteriamap = $this->map_criteria();
        foreach ($basedata->CFRubricCriterion as $index => $criterion) {
            $this->set_datetime($timemodified);
            $basedata->CFRubricCriterion[$index] = $this->convert_data_section($criterion, $criteriamap);
            $this->convert_levels($criterion, $index, $basedata);
        }

        $temp = array_values($basedata->CFRubricCriterion);
        $basedata->CFRubricCriterion = $temp;
        $datastring = json_encode($basedata, JSON_PRETTY_PRINT);

        return $datastring;
    }

    /**
     * Converts the basedata levels to the desired format.
     *
     * @param array $criterion
     * @param int $index
     * @param stdClass $basedata
     */
    protected function convert_levels(array $criterion, int $index, stdClass $basedata): void {
        $levelmap = $this->map_levels();
        $i = 0; // Position number for our levels.
        foreach ($criterion['levels'] as $ckey => $level) {
            // Set the position for this level.
            $this->set_number($i);
            $i++;
            $basedata->CFRubricCriterion[$index]->CFRubricCriterionLevels[$ckey] = $this->convert_data_section($level, $levelmap);
        }
        // The format for IMS must have the array start at 0, so we re-index the array.
        $temp = array_values($basedata->CFRubricCriterion[$index]->CFRubricCriterionLevels);
        $basedata->CFRubricCriterion[$index]->CFRubricCriterionLevels = $temp;
    }

    /**
     * Converts the current data into the desired format.
     *
     * @param  array $datasection The section of data to be converted.
     * @param  array $mapper The map section used to convert this section of data from Moodle into IMS format.
     * @return stdClass The converted data section.
     */
    protected function convert_data_section(array $datasection, array $mapper): stdClass {
        foreach ($datasection as $key => $value) {
            if (isset($mapper['base'][$key])) {
                if ($mapper['base'][$key] == self::NOT_EXPORTED) {
                    unset($datasection[$key]);
                } else {
                    $function = $mapper['base'][$key]['function'];
                    $fieldname = $mapper['base'][$key]['fieldname'];
                    $datasection[$fieldname] = call_user_func([$this, $function], $value);
                    if ($key != $fieldname) {
                        unset($datasection[$key]);
                    }
                }
            }
            // Add target fields.
            foreach ($mapper['target'] as $key => $value) {
                $function = $value;
                $datasection[$key] = call_user_func([$this, $function]);
            }
        }
        return (object) $datasection;
    }

    /**
     * The list of fields from the base (Moodle) to the target (IMS).
     *
     * @return array The details of the fields.
     */
    protected function map_base(): array {
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
                    'fieldname' => 'Description'
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

    /**
     * A map for the criteria of the rubric.
     *
     * @return array information about rubric criteria.
     */
    protected function map_criteria(): array {
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

    /**
     * Provides level information for conversion from Moodle into IMS specification format.
     *
     * @return array level information
     */
    protected function map_levels(): array {
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

    /**
     * Creates a UUID
     *
     * @param  string  $value  The value
     * @return string  The uuid.
     */
    protected function get_uuid(string $value): string {
        return \core\uuid::generate();
    }

    /**
     * @see https://www.php.net/manual/en/normalizer.normalize.php
     *
     * @param string $value A string to be normalised.
     * @return string the normalised string.
     */
    protected function get_text(string $value): string {
        $value = normalizer_normalize($value);
        return strval($value);
    }

    /**
     * Sets the number.
     *
     * @param int $value The value for the get_number function to use.
     */
    protected function set_number(int $value): void {
        $this->number = $value;
    }

    /**
     * Returns a number that is provided, or set earlier.
     *
     * @param int $value The value to be used for exporting.
     * @return int Either the existing number or the one provided.
     */
    protected function get_number(int $value = null): int {
        $value = $value ?? $this->number;
        return $value;
    }

    /**
     * Set the date time to be used next.
     *
     * @param string $value A datetime is string format.
     */
    protected function set_datetime(string $value): void {
        $this->datetime = $value;
    }

    /**
     * Get the datetime in 'c' format.
     *
     * @param string $value The string to format appropriately
     * @return string The properly formated date string
     */
    protected function get_datetime($value = null): string {
        $value = $value ?? $this->datetime;
        $time = new \DateTime('@' . $value);
        return $time->format('c');
    }

    /**
     * Return the location of the rubric.
     *
     * @param string $value Not used.
     * @return string The location that this rubric can be found.
     */
    protected function get_uri($value = null): string {
        $url = new \moodle_url('/grade/grading/manage.php');
        return $url->out(false);
    }

    /**
     * Returns the criteria
     *
     * @param array $value The criteria.
     * @return array Returns the criteria.
     */
    protected function get_criteria($value): array {
        return $value;
    }

    /**
     * Gets levels
     *
     * @param array $value The levels.
     * @return array Returns the levels.
     */
    protected function get_levels($value): array {
        return $value;
    }
}
