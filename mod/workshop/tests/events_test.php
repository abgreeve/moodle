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
 * Unit tests for workshop events.
 *
 * @package    mod_workshop
 * @category   phpunit
 * @copyright  2013 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/workshop/lib.php'); // Include the code to test
require_once($CFG->dirroot . '/mod/workshop/locallib.php'); // Include the code to test
require_once($CFG->dirroot . '/lib/cronlib.php'); // Include the code to test


/**
 * Test cases for the internal workshop api
 */
class mod_workshop_events_testcase extends advanced_testcase {

    /** $workshop Basic workshop data stored in an object. */
    protected $workshop;
    /** $course Generated Random Course. */
    protected $course;
    /** $context Course module context. */
    protected $context;

    /**
     * Set up the testing environment.
     */
    protected function setUp() {
        parent::setUp();
        $this->setAdminUser();

        // Create a workshop activity.
        $this->course = $this->getDataGenerator()->create_course();
        $this->workshop = $this->getDataGenerator()->create_module('workshop', array('course' => $this->course));
        $this->context = context_module::instance($this->workshop->id);
    }

    protected function tearDown() {
        $this->workshop = null;
        parent::tearDown();
    }

    /**
     * This event is triggered in view.php and workshop/lib.php through the function workshop_cron().
     */
    function test_phase_switched_event() {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Add additional workshop information.
        $this->workshop->phase = 20;
        $this->workshop->phaseswitchassessment = 1;
        $this->workshop->submissionend = time() - 1;

        $event = \mod_workshop\event\phase_switched::create(array(
            'objectid' => $this->workshop->id,
            'context'  => $this->context,
            'courseid' => $this->course->id,
            'other'    => array('workshopphase' => $this->workshop->phase)
        ));

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the legacy log data is valid.
        $expected = array($this->course->id, 'workshop', 'update switch phase', 'view.php?id=' . $this->workshop->id,
            $this->workshop->phase, $this->workshop->id);
        $this->assertEventLegacyLogData($expected, $event);

        $sink->close();
    }

    function test_assessment_evaluated() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $cm = get_coursemodule_from_instance('workshop', $this->workshop->id, $this->course->id, false, MUST_EXIST);

        $workshop = new testable_workshop($this->workshop, $cm, $this->course);

        $assessments = array();
        $assessments[] = (object)array('reviewerid'=>2, 'gradinggrade'=>null, 'gradinggradeover'=>null, 'aggregationid'=>null,
            'aggregatedgrade'=>12);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $workshop->aggregate_grading_grades_process($assessments);
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\mod_workshop\event\assessment_evaluated', $event);
        $this->assertEquals('workshop_aggregations', $event->objecttable);
        $this->assertEquals(context_module::instance($workshop->id), $event->get_context());

        $sink->close();
    }

    function test_assessment_reevaluated() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $cm = get_coursemodule_from_instance('workshop', $this->workshop->id, $this->course->id, false, MUST_EXIST);

        $workshop = new testable_workshop($this->workshop, $cm, $this->course);

        $assessments = array();
        $assessments[] = (object)array('reviewerid'=>2, 'gradinggrade'=>null, 'gradinggradeover'=>null, 'aggregationid'=>2,
            'aggregatedgrade'=>12);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $workshop->aggregate_grading_grades_process($assessments);
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\mod_workshop\event\assessment_reevaluated', $event);
        $this->assertEquals('workshop_aggregations', $event->objecttable);
        $this->assertEquals(context_module::instance($workshop->id), $event->get_context());
        $expected = array($this->course->id, 'workshop', 'update aggregate grade', 'view.php?id=' . $event->get_context()->instanceid,
                $event->objectid, $event->get_context()->instanceid);
        $this->assertEventLegacyLogData($expected, $event);

        $sink->close();
    }

    /**
     * There is no api involved so the best we can do is test legacy data by triggering event manually.
     */
    function test_aggregate_grades_reset_event() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $event = \mod_workshop\event\assessment_evaluations_reset::create(array(
            'objectid' => $this->workshop->id,
            'context'  => $this->context,
            'courseid' => $this->course->id,
        ));

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the legacy log data is valid.
        $expected = array($this->course->id, 'workshop', 'update clear aggregated grade', 'view.php?id=' . $this->workshop->id,
            $this->workshop->id, $this->workshop->id);
        $this->assertEventLegacyLogData($expected, $event);

        $sink->close();
    }

    /**
     * There is no api involved so the best we can do is test legacy data by triggering event manually.
     */
    function test_instances_list_viewed_event() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $context = context_course::instance($this->course->id);

        $event = \mod_workshop\event\instances_list_viewed::create(array('context' => $context));

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the legacy log data is valid.
        $expected = array($this->course->id, 'workshop', 'view all', 'index.php?id=' . $this->course->id, '');
        $this->assertEventLegacyLogData($expected, $event);

        $sink->close();
    }

    /**
     * There is no api involved so the best we can do is test legacy data by triggering event manually.
     */
    function test_submission_created_event() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $submissionid = 48;

        $event = \mod_workshop\event\submission_created::create(array(
            'objectid' => $submissionid,
            'context'  => $this->context,
            'courseid' => $this->course->id,
            'relateduserid' => $user->id,
            'other' => array(
                'workshopid' => $this->workshop->id
                )
            )
        );

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the legacy log data is valid.
        $expected = array($this->course->id, 'workshop', 'add submission',
            'submission.php?cmid=' . $this->workshop->id . '&id=' . $submissionid, $submissionid, $this->workshop->id);
        $this->assertEventLegacyLogData($expected, $event);

        $sink->close();
    }

    /**
     * There is no api involved so the best we can do is test legacy data by triggering event manually.
     */
    function test_submission_updated_event() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $submissionid = 48;

        $event = \mod_workshop\event\submission_updated::create(array(
            'objectid' => $submissionid,
            'context'  => $this->context,
            'courseid' => $this->course->id,
            'relateduserid' => $user->id,
            'other' => array(
                'workshopid' => $this->workshop->id
                )
            )
        );

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the legacy log data is valid.
        $expected = array($this->course->id, 'workshop', 'update submission',
            'submission.php?cmid=' . $this->workshop->id . '&id=' . $submissionid, $submissionid, $this->workshop->id);
        $this->assertEventLegacyLogData($expected, $event);

        $sink->close();
    }

    /**
     * There is no api involved so the best we can do is test legacy data by triggering event manually.
     */
    function test_submission_viewed_event() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $submissionid = 48;

        $event = \mod_workshop\event\submission_viewed::create(array(
            'objectid' => $submissionid,
            'context'  => $this->context,
            'courseid' => $this->course->id,
            'relateduserid' => $user->id,
            'other' => array(
                'workshopid' => $this->workshop->id
                )
            )
        );

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the legacy log data is valid.
        $expected = array($this->course->id, 'workshop', 'view submission',
            'submission.php?cmid=' . $this->workshop->id . '&id=' . $submissionid, $submissionid, $this->workshop->id);
        $this->assertEventLegacyLogData($expected, $event);

        $sink->close();
    }

}

/**
 * Test subclass that makes all the protected methods we want to test public.
 */
class testable_workshop extends workshop {

    public function aggregate_grading_grades_process(array $assessments, $timegraded = null) {
        parent::aggregate_grading_grades_process($assessments, $timegraded);
    }

}
