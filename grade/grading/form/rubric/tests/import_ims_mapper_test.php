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
 * Unit tests for the rubric import manager;
 *
 * @package   gradingform_rubric
 * @category  test
 * @copyright 2022 Adrian Greeve <adrian@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class import_ims_mapper_test extends advanced_testcase {

    /**
     * Test that a file in IMS specification can be imported and transformed into a format that Moodle will accept.
     */
    public function test_translate_data() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $importstring = file_get_contents(__DIR__ . '/fixtures/rubric-import.json');
        $data = json_decode($importstring);
        $manager = new \gradingform_rubric\local\import\ims_mapper($data, 1);
        $result = $manager->translate_data();

        $this->assertTrue(isset($result->areaid));
        $this->assertTrue(isset($result->status));
        $this->assertTrue(isset($result->name));
        $this->assertTrue(isset($result->rubric));
        $this->assertCount(4, $result->rubric['criteria']);
        foreach ($result->rubric['criteria'] as $criteria) {
            $this->assertTrue(isset($criteria['sortorder']));
            $this->assertTrue(isset($criteria['description']));
            $this->assertTrue(isset($criteria['levels']));
            foreach ($criteria['levels'] as $level) {
                $this->assertTrue(isset($level['score']));
                $this->assertTrue(isset($level['definition']));
            }
        }
    }

}
