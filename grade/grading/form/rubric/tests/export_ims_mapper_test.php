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
//

namespace gradingform_rubric;

use advanced_testcase;

/**
 * Unit tests for the rubric export manager;
 *
 * @package   gradingform_rubric
 * @category  test
 * @copyright 2022 Adrian Greeve <adrian@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class export_ims_mapper_test extends advanced_testcase {

    /**
     * Rubric data in a format taken from advanced grading: rubric.
     * This is setup as an array for ease of use rather than casting it to a stdClass and then back again.
     */
    protected function rubric_data(): array {
        $data = [
            'id' => 30,
            'name' => 'Test rubric',
            'description' => 'This is a test rubric',
            'timecreated' => time(),
            'timemodified' => time(),
            'options' => [],
            'rubric_criteria' => [
                3 => [
                    'id' => 3,
                    'sortorder' => 1,
                    'description' => 'Spelling',
                    'descriptionformat' => 0,
                    'levels' => [
                        334 => [
                            'id' => 334,
                            'score' => 0,
                            'definition' => '4+ spelling mistakes',
                            'definitionformat' => 0
                        ],
                        335 => [
                            'id' => 335,
                            'score' => 1.67,
                            'definition' => '3 spelling mistakes',
                            'definitionformat' => 0
                        ],
                        336 => [
                            'id' => 336,
                            'score' => 2,
                            'definition' => '2 spelling mistakes',
                            'definitionformat' => 0
                        ],
                        337 => [
                            'id' => 337,
                            'score' => 3,
                            'definition' => '1 spelling mistake',
                            'definitionformat' => 0
                        ],
                        338 => [
                            'id' => 338,
                            'score' => 4,
                            'definition' => 'No mistakes',
                            'definitionformat' => 0
                        ]
                    ]
                ],
                56 => [
                    'id' => 56,
                    'sortorder' => 2,
                    'description' => 'Pictures',
                    'descriptionformat' => 0,
                    'levels' => [
                        339 => [
                            'id' => 339,
                            'score' => 0,
                            'definition' => 'No pictures',
                            'definitionformat' => 0
                        ],
                        340 => [
                            'id' => 340,
                            'score' => 5,
                            'definition' => 'One picture',
                            'definitionformat' => 0,
                        ],
                        341 => [
                            'id' => 341,
                            'score' => 10,
                            'definition' => 'Two pictures',
                            'definitionformat' => 0
                        ]
                    ]
                ]
            ]
        ];
        return $data;
    }

    /**
     * Test that an array of data from the advanced grading method: rubric, can be transformed into a format acceptable by IMS.
     */
    public function test_translate_data(): void {
        $temp = $this->rubric_data();
        $manager = new \gradingform_rubric\local\export\ims_mapper();
        $result = $manager->translate_data($temp);
        // Check base level information.
        $resultobject = json_decode($result);
        $this->assertTrue(is_a($resultobject, 'stdClass'));
        $this->assertTrue(isset($resultobject->Title));
        $this->assertTrue(isset($resultobject->URI));
        $this->assertTrue(isset($resultobject->Identifier));
        $this->assertTrue(isset($resultobject->Description));
        $this->assertTrue(isset($resultobject->lastChangeDateTime));
        $this->assertTrue(isset($resultobject->CFRubricCriterion));

        // Check the format of the Identifier
        // @see https://www.imsglobal.org/sites/default/files/CASE/casev1p0/information_model/caseservicev1p0_infomodelv1p0.html#DerivedAttribute_UUID_pattern
        $expression = "/[0-9a-f]{8}-[0-9a-f]{4}-[1-5]{1}[0-9a-f]{3}-[8-9a-b]{1}[0-9a-f]{3}-[0-9a-f]{12}/s";
        $this->assertMatchesRegularExpression($expression, $resultobject->Identifier);

        foreach ($resultobject->CFRubricCriterion as $element) {
            $this->assertTrue(isset($element->URI));
            $this->assertTrue(isset($element->Identifier));
            $this->assertTrue(isset($element->Description));
            $this->assertTrue(isset($element->position));
            $this->assertTrue(isset($element->CFRubricCriterionLevels));
            $this->assertMatchesRegularExpression($expression, $element->Identifier);
            foreach ($element->CFRubricCriterionLevels as $criterionlevel) {
                $this->assertTrue(isset($criterionlevel->Description));
                $this->assertTrue(isset($criterionlevel->URI));
                $this->assertTrue(isset($criterionlevel->Identifier));
                $this->assertTrue(isset($criterionlevel->score));
                $this->assertTrue(isset($criterionlevel->position));
                $this->assertTrue(isset($criterionlevel->lastChangeDateTime));
                $this->assertMatchesRegularExpression($expression, $criterionlevel->Identifier);
            }
        }
    }
}
