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
 * Test for restore_stepslib.
 *
 * @package core_backup
 * @copyright 2016 Andrew Nicols <andrew@nicols.co.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->libdir . '/completionlib.php');

/**
 * Test for restore_stepslib.
 *
 * @package core_backup
 * @copyright 2016 Andrew Nicols <andrew@nicols.co.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_backup_restore_gradebook_structure_step_testcase extends advanced_testcase {

    /**
     * Provide tests for rewrite_step_backup_file_for_legacy_freeze based upon fixtures.
     *
     * @return array
     */
    public function rewrite_step_backup_file_for_legacy_freeze_provider() {
        $fixturesdir = realpath(__DIR__ . '/fixtures/rewrite_step_backup_file_for_legacy_freeze/');
        $tests = [];
        $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fixturesdir),
                \RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($iterator as $sourcefile) {
            $pattern = '/\.test$/';
            if (!preg_match($pattern, $sourcefile)) {
                continue;
            }

            $expectfile = preg_replace($pattern, '.expectation', $sourcefile);
            $test = array($sourcefile, $expectfile);
            $tests[basename($sourcefile)] = $test;
        }

        return $tests;
    }

    /**
     * @dataProvider rewrite_step_backup_file_for_legacy_freeze_provider
     * @param   string  $source     The source file to test
     * @param   string  $expected   The expected result of the transformation
     */
    public function test_rewrite_step_backup_file_for_legacy_freeze($source, $expected) {
        $restore = $this->getMockBuilder('\restore_gradebook_structure_step')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock()
            ;

        // Copy the file somewhere as the rewrite_step_backup_file_for_legacy_freeze will write the file.
        $dir = make_request_directory(true);
        $filepath = $dir . DIRECTORY_SEPARATOR . 'file.xml';
        copy($source, $filepath);

        $rc = new \ReflectionClass('\restore_gradebook_structure_step');
        $rcm = $rc->getMethod('rewrite_step_backup_file_for_legacy_freeze');
        $rcm->setAccessible(true);
        $rcm->invoke($restore, $filepath);

        // Check the result.
        $this->assertFileEquals($expected, $filepath);
    }

    /**
     * @dataProvider stuff_provider
     * @param  [type] $data     [description]
     * @param  [type] $expected [description]
     */
    public function test_my_stuff($data, $expected) {
        global $DB;
        $this->resetAfterTest();
        $restore = $this->getMockBuilder('\restore_gradebook_structure_step')
            ->setMethods(['get_courseid', 'set_mapping'])
            ->disableOriginalConstructor()
            ->getMock();

        $restore->method('get_courseid')
                ->willReturn(5);

        $restore->method('set_mapping')
                ->willReturn(0);

        $rc = new \ReflectionClass('\restore_gradebook_structure_step');
        $rcm = $rc->getMethod('process_grade_category');
        $rcm->setAccessible(true);
        $rcm->invoke($restore, $data);

        $records = $DB->get_records('grade_categories');
        $record = array_shift($records);
        $this->assertEquals($expected, $record->parent);
    }

    public function stuff_provider() {
        $generaldata = [
            'courseid' => 42,
            'aggregation' => 13,
            'keephigh' => 0,
            'droplow' => 0,
            'aggregateonlygraded' => 1,
            'aggregateoutcomes' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
            'hidden' => 0
        ];

        $cat1 = [
            'id' => 1,
            'parent' => null,
            'depth' => 1,
            'path' => '/1/',
            'fullname' => '?'
        ];
        $cat1 = array_merge($cat1, $generaldata);

        $cat2 = [
            'id' => 2,
            'parent' => 1,
            'depth' => 2,
            'path' => '/1/2/',
            'fullname' => 'test cat 2'
        ];
        $cat2 = array_merge($cat2, $generaldata);

        $cat3 = [
            'id' => 3,
            'parent' => 2,
            'depth' => 3,
            'path' => '/1/2/3/',
            'fullname' => 'test cat 3'
        ];
        $cat3 = array_merge($cat3, $generaldata);
        return [
            'parent category' => [(object) $cat1, 0],
            'sub category' => [(object) $cat2, 1],
            'sub sub category' => [(object) $cat3, 2],
        ];
    }
}
