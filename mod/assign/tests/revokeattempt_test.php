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
            // Attempt single submission no additional grading, no feedback.
            'Single attempt' => [
                'type' => 'single',
                'grade' => false,
                'feedback' => false,
                'activitycompletion' => 'none'
            ],
            // Attempt single submission, additional grade, no feedback.
            'Single attempt with grade' => [
                'type' => 'single',
                'grade' => true,
                'feedback' => false,
                'activitycompletion' => 'none'
            ],
            // Attempt single submission, additional grade, feedback.
            'Single attempt with grade and feedback' => [
                'type' => 'single',
                'grade' => true,
                'feedback' => true,
                'activitycompletion' => 'none'
            ],
            // Attempt single submission no additional grading, no feedback, activity was complete, now is not.
            'Single attempt with past completed activity' => [
                'type' => 'single',
                'grade' => false,
                'feedback' => false,
                'activitycompletion' => 'past'
            ],
            // Attempt single submission, additional grade, no feedback, activity was not complete, now is.
            'Single attempt with future completed activity' => [
                'type' => 'single',
                'grade' => true,
                'feedback' => false,
                'activitycompletion' => 'future'
            ],
            // Group attempt, no additional grading, no feedback.
            'Group attempt' => [
                'type' => 'group',
                'grade' => false,
                'feedback' => false,
                'activitycompletion' => 'none'
            ],
            // Group attempt, additional grade, no feedback.
            'Group attempt with grade' => [
                'type' => 'group',
                'grade' => true,
                'feedback' => false,
                'activitycompletion' => 'none'
            ],
            // Group attempt, additional grade, feedback.
            'Group attempt with grade and feedback' => [
                'type' => 'group',
                'grade' => true,
                'feedback' => true,
                'activitycompletion' => 'none'
            ],
            // Group attempt, no additional grading, no feedback, activity was complete, now is not.
            'Group attempt with past completed activity' => [
                'type' => 'group',
                'grade' => false,
                'feedback' => false,
                'activitycompletion' => 'past'
            ],
            // Group attempt, additional grade, no feedback, activity was not complete, now is.
            'Group attempt with future completed activity' => [
                'type' => 'group',
                'grade' => true,
                'feedback' => false,
                'activitycompletion' => 'future'
            ],
        ];
    }

    /**
     * Test revoking an assignment attempt
     *
     * @dataProvider revoke_attempt_dataprovider
     */
    public function test_revoke_attempt($type, $grade, $feedback, $activitycompletion) {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
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
        $this->mark_submission($teacher, $assign, $student, 45.0, $data);

        $assign->testable_process_add_attempt($student->id);
        if ($grade) {
            if ($feedback) {
                $data = [
                    'assignfeedbackcomments_editor' => ['text' => 'Feedback!', 'format' => 1]
                ];
            } else {
                $data = [
                    'assignfeedbackcomments_editor' => ['text' => '', 'format' => 1]
                ];
            }
            $this->mark_submission($teacher, $assign, $student, 51.0, $data, 1);
            $gradeinfo = $assign->get_user_grade($student->id, true);
            $this->assertEquals(51.0, $gradeinfo->grade);
        }

        $assign->revoke_attempt($student->id);
        $gradeinfo = $assign->get_user_grade($student->id, true);
        $this->assertEquals(45.0, $gradeinfo->grade);
    }

    protected function create_assignment($generator, $type, $course) {
        $data = [
            'assignsubmission_onlinetext_enabled' => 1,
            'assignfeedback_comments_enabled' => 1,
        ];

        $assign = $this->create_instance($course, $data);
        return $assign;
    }

}
