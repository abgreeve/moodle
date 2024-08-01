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

namespace mod_assign;

use mod_assign_test_generator;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/../locallib.php');
require_once($CFG->dirroot . '/mod/assign/tests/generator.php');
require_once($CFG->libdir.'/completionlib.php');

use cm_info;
use completion_info;
use stdClass;

/**
 * Unit tests for revoking attempts in mod/assign/locallib.php.
 *
 * @package    mod_assign
 * @category   test
 * @copyright  2024 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class revokeattempt_test extends \advanced_testcase {
    use mod_assign_test_generator;

    protected function revoke_attempt_dataprovider() {
        return [
            // Attempt single submission no additional grading.
            'Single attempt' => [
                'type' => 'single',
                'firstgrade' => 45.0,
                'grade' => 0,
                'activitycompletion' => [],
                'result' => []
            ],
            // Attempt single submission, additional grade.
            'Single attempt with grade' => [
                'type' => 'single',
                'firstgrade' => 45.0,
                'grade' => 51.0,
                'activitycompletion' => [],
                'result' => []
            ],
            // Attempt single submission no additional grading, activity was complete, now is not.
            'Single attempt with past completed activity' => [
                'type' => 'single',
                'firstgrade' => 54.0,
                'grade' => 0,
                'activitycompletion' => [
                    'type' => 'past',
                    'thing' => ['requires_submission']
                ],
                'result' => ['completion' => 'incomplete']
            ],
            // Attempt single submission, additional grade, activity was not complete, now is.
            'Single attempt with future completed activity' => [
                'type' => 'single',
                'firstgrade' => 45.0,
                'grade' => 51.0,
                'activitycompletion' => [
                    'type' => 'future',
                    'thing' => ['requires_submission']
                ],
                'result' => ['completion' => 'complete']
            ],
            // Activity completion requires submission, requires grade, requires passing grade.
            // Group attempt, no additional grading.
            'Group attempt' => [
                'type' => 'group',
                'firstgrade' => 45.0,
                'grade' => 0,
                'activitycompletion' => [],
                'result' => []
            ],
            // Group attempt, additional grade.
            'Group attempt with grade' => [
                'type' => 'group',
                'firstgrade' => 45.0,
                'grade' => 51.0,
                'activitycompletion' => [],
                'result' => []
            ],
            // Group attempt, no additional grading, activity was complete, now is not.
            'Group attempt with past completed activity' => [
                'type' => 'group',
                'firstgrade' => 54.0,
                'grade' => 0,
                'activitycompletion' => [
                    'type' => 'past',
                    'thing' => ['requires_submission']
                ],
                'result' => ['completion' => 'incomplete']
            ],
            // Group attempt, additional grade, activity was not complete, now is.
            'Group attempt with future completed activity' => [
                'type' => 'group',
                'firstgrade' => 45.0,
                'grade' => 51.0,
                'activitycompletion' => [
                    'type' => 'future',
                    'thing' => ['requires_submission']
                ],
                'result' => ['completion' => 'complete']
            ],
        ];
    }

    /**
     * Test revoking an assignment attempt
     *
     * @dataProvider revoke_attempt_dataprovider
     */
    public function test_revoke_attempt($type, $initialgrade, $grade, $activitycompletion, $result) {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['enablecompletion' => 1]);
        // user creation to be moved.
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $assign = $this->create_assignment($generator, $type, $course);
        // Add a submission.
        $this->add_submission($student, $assign, $onlinetext = 'First attempt submission');
        $this->submit_for_grading($student, $assign);

        $teacher->ignoresesskey = true;
        $this->setUser($teacher);

        $initialfeedback = 'Feedback for first attempt';
        $data = [
            'assignfeedbackcomments_editor' => ['text' => $initialfeedback, 'format' => 1]
        ];
        $this->mark_submission($teacher, $assign, $student, $initialgrade, $data);

        $assign->testable_process_add_attempt($student->id);
        $submission = $assign->get_user_submission($student->id, false);
        $this->assertEquals(ASSIGN_SUBMISSION_STATUS_REOPENED, $submission->status);

        if ($grade != 0) {
            $data = [
                'assignfeedbackcomments_editor' => ['text' => 'Feedback!', 'format' => 1]
            ];
            $this->mark_submission($teacher, $assign, $student, $grade, $data, 1);
            $gradeinfo = $assign->get_user_grade($student->id, true);
            $this->assertEquals($grade, $gradeinfo->grade);
        }

        $assign->revoke_attempt($student->id);
        // submission
        $submission = $assign->get_user_submission($student->id, false);
        // print_object($submission->status);
        $gradeinfo = $assign->get_user_grade($student->id, true);
        $this->assertEquals($initialgrade, $gradeinfo->grade);

        $cm = cm_info::create($assign->get_course_module());
        $completioninfo = new completion_info($course);
        // $customcompletion = new custom_completion($cm, (int)$student->id);
        $current = new stdClass();
        // Try internal_get_grade_state instead.
        print_object($completioninfo->get_grade_completion($cm, $student->id));
    }

    protected function create_assignment($generator, $type, $course) {
        $data = [
            'maxattempts' => -1,
            'attemptreopenmethod' => 'manual',
            'assignsubmission_onlinetext_enabled' => 1,
            'assignfeedback_comments_enabled' => 1,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionsubmit' => COMPLETION_ENABLED,
            'completionpassgrade' => 1,
            'gradepass' => 50.0
        ];

        $assign = $this->create_instance($course, $data);
        return $assign;
    }

}
