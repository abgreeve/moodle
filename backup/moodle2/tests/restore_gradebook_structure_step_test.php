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
     * Test processing a grade category on import.
     *
     * @dataProvider basic_category_provider
     * @param  stdClass $data A category to process
     * @param  int $expected The expected parent id for the category.
     */
    public function test_process_grade_category(stdClass $data, int $expected): void {
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

    /**
     * A data provider to test the process_grade_category method.
     *
     * @return array basic category information and the expected result.
     */
    public function basic_category_provider(): array {
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

    /**
     * Test updating grade items when importing a gradebook with categories.
     *
     * @dataProvider grade_item_provider
     * @param array $importgradeitems
     * @param array $categories
     * @param array $expected
     */
    public function test_update_grade_items(array $importgradeitems, array $categories, array $expected): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();

        $this->initial_grade_items($course->id);

        backup_controller_dbops::create_backup_ids_temp_table('testingid');

        $categorymapping = [];
        foreach ($categories as $category) {
            $oldid = $category->id;
            unset($category->id);
            $category->courseid = $course->id;
            $insertid = $DB->insert_record('grade_categories', $category);
            $categorymapping[$oldid] = (object) ['id' => $insertid, 'fullname' => $category->fullname];
        }

        foreach ($importgradeitems as $gradeitem) {
            $insertid = $DB->insert_record('grade_items', $gradeitem);

            $temprecorddata = (object) [
                'backupid' => 99,
                'itemname' => 'grade_item',
                'itemid' => $insertid,
                'newitemid' => $insertid,
                'parentitemid' => $gradeitem->categoryid
            ];

            $DB->insert_record('backup_ids_temp', $temprecorddata);
        }

        $restore = $this->getMockBuilder('\restore_gradebook_structure_step')
            ->setMethods(['get_restoreid', 'get_mappingid'])
            ->disableOriginalConstructor()
            ->getMock();

        $restore->method('get_restoreid')
            ->willReturn(99);

        $restore->method('get_mappingid')
            ->willReturnCallback(function($name, $itemid, $other) use ($categorymapping) {
                return $categorymapping[$itemid]->id;
            });

        $rc = new \ReflectionClass('\restore_gradebook_structure_step');
        $rcm = $rc->getMethod('update_grade_items');
        $rcm->setAccessible(true);
        list($basecategory, $othercategories) = $this->get_grade_categories($course->id);
        $mappings = $rcm->invoke($restore, $basecategory, $othercategories);
        $records = $DB->get_records_list('grade_items', 'id', $mappings);

        foreach ($expected as $key => $value) {
            $params = [
                'itemname' => $key,
                'categoryid' => ($value == 'basecategory') ? $basecategory->id : $categorymapping[$value]->id
            ];
            $sql = "SELECT gi.*, gc.*
                      FROM {grade_items} gi
                 LEFT JOIN {grade_categories} gc ON gi.categoryid = gc.id
                     WHERE gi.itemname = :itemname AND gc.id = :categoryid";
            $records = $DB->get_records_sql($sql, $params);
            $this->assertCount(1, $records);
        }
        // Clean up. Remove the backup ids temp table.
        backup_controller_dbops::drop_backup_ids_temp_table('testingid');
    }

    /**
     * Structure for this gradebook is:
     * COURSE
     *    L GRADE CATEGORY
     *    |     L test quiz in category
     *    L test assignment
     *
     * @param int $courseid The id of the course we are creating grade items in.
     */
    public function initial_grade_items(int $courseid): void {
        $data = (object) [
            'courseid' => $courseid,
            'itemname' => 'test assignment',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'iteminstance' => 1
        ];

        $gi = new grade_item($data);
        $gi->insert();

        list($basecategory, $othercategories) = $this->get_grade_categories($courseid);

        $data = (object) [
            'courseid' => $courseid,
            'parent' => $basecategory->id,
            'fullname' => 'first grade category',
            'aggregation' => 13
        ];
        $newgradecategory = new grade_category($data, false);
        $newgradecategory->insert();

        $data = (object) [
            'courseid' => $courseid,
            'itemname' => 'test quiz in category',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'iteminstance' => 1,
            'categoryid' => $newgradecategory->id
        ];

        $gi = new grade_item($data);
        $gi->insert();
    }

    /**
     * Returns more complex category and grade item structures to test.
     *
     * @return array Gradebook data and the expected result.
     */
    public function grade_item_provider(): array {
        return [
            'Import of grades no categories' => [
                [
                    (object) [
                        'categoryid' => null,
                        'itemname' => null,
                        'itemtype' => 'course',
                        'itemmodule' => null,
                        'iteminstance' => 3
                    ],
                    (object) [
                        'categoryid' => 3,
                        'itemname' => 'import assignment',
                        'itemtype' => 'mod',
                        'itemmodule' => 'assign',
                        'iteminstance' => 1
                    ],
                    (object) [
                        'categoryid' => 3,
                        'itemname' => 'import quiz',
                        'itemtype' => 'mod',
                        'itemmodule' => 'quiz',
                        'iteminstance' => 1
                    ]
                ],
                [
                    (object) [
                        'id' => 3,
                        'courseid' => 3,
                        'parent' => 0,
                        'depth' => 1,
                        'path' => '/3/',
                        'fullname' => '?',
                        'aggregation' => 13,
                        'timecreated' => time(),
                        'timemodified' => time()
                    ]
                ],
                ['import assignment' => 'basecategory', 'import quiz' => 'basecategory']
            ],
            'Import of grades one category' => [
                [
                    (object) [
                        'courseid' => 3,
                        'categoryid' => null,
                        'itemname' => null,
                        'itemtype' => 'course',
                        'itemmodule' => null,
                        'iteminstance' => 3
                    ],
                    (object) [
                        'courseid' => 3,
                        'categoryid' => null,
                        'itemname' => '',
                        'itemtype' => 'category',
                        'itemmodule' => null,
                        'iteminstance' => 4
                    ],
                    (object) [
                        'courseid' => 3,
                        'categoryid' => 4,
                        'itemname' => 'import assignment in category',
                        'itemtype' => 'mod',
                        'itemmodule' => 'assign',
                        'iteminstance' => 1
                    ],
                    (object) [
                        'courseid' => 3,
                        'categoryid' => 3,
                        'itemname' => 'import quiz',
                        'itemtype' => 'mod',
                        'itemmodule' => 'quiz',
                        'iteminstance' => 1
                    ]
                ],
                [
                    (object) [
                        'id' => 3,
                        'courseid' => 3,
                        'parent' => 0,
                        'depth' => 1,
                        'path' => '/3/',
                        'fullname' => '?',
                        'aggregation' => 13,
                        'timecreated' => time(),
                        'timemodified' => time()
                    ],
                    (object) [
                        'id' => 4,
                        'courseid' => 3,
                        'parent' => 3,
                        'depth' => 2,
                        'path' => '/3/4/',
                        'fullname' => 'import category',
                        'aggregation' => 13,
                        'timecreated' => time(),
                        'timemodified' => time()
                    ]
                ],
                ['import assignment in category' => 4, 'import quiz' => 'basecategory']
            ],
            'Import of grades duplicate category name' => [
                [
                    (object) [
                        'courseid' => 3,
                        'categoryid' => null,
                        'itemname' => null,
                        'itemtype' => 'course',
                        'itemmodule' => null,
                        'iteminstance' => 3
                    ],
                    (object) [
                        'courseid' => 3,
                        'categoryid' => null,
                        'itemname' => '',
                        'itemtype' => 'category',
                        'itemmodule' => null,
                        'iteminstance' => 4
                    ],
                    (object) [
                        'courseid' => 3,
                        'categoryid' => 4,
                        'itemname' => 'import assignment in category',
                        'itemtype' => 'mod',
                        'itemmodule' => 'assign',
                        'iteminstance' => 1
                    ],
                    (object) [
                        'courseid' => 3,
                        'categoryid' => 3,
                        'itemname' => 'import quiz',
                        'itemtype' => 'mod',
                        'itemmodule' => 'quiz',
                        'iteminstance' => 1
                    ]
                ],
                [
                    (object) [
                        'id' => 3,
                        'courseid' => 3,
                        'parent' => 0,
                        'depth' => 1,
                        'path' => '/3/',
                        'fullname' => '?',
                        'aggregation' => 13,
                        'timecreated' => time(),
                        'timemodified' => time()
                    ],
                    (object) [
                        'id' => 4,
                        'courseid' => 3,
                        'parent' => 3,
                        'depth' => 2,
                        'path' => '/3/4/',
                        'fullname' => 'first grade category',
                        'aggregation' => 13,
                        'timecreated' => time(),
                        'timemodified' => time()
                    ]
                ],
                ['import assignment in category' => 4, 'import quiz' => 'basecategory']
            ]
        ];
    }

    /**
     * Returns the grade categories that need import processing for a course.
     *
     * @param  int    $courseid The course id to return the categories for.
     * @return array Returns the basecategory that everything belongs to and other categories that need processing.
     */
    public function get_grade_categories(int $courseid): array {
        global $DB;

        $select = 'courseid = :courseid AND (parent IS NULL OR parent = 0)';
        $parentcategories = $DB->get_records_select('grade_categories', $select, ['courseid' => $courseid]);

        $basecategory = array_filter($parentcategories, function($category) {
            return !isset($category->parent);
        });
        $othercategories = array_filter($parentcategories, function($category) {
            return (isset($category->parent) && empty($category->parent));
        });

        $basecategory = array_shift($basecategory);
        return [$basecategory, $othercategories];
    }
}
