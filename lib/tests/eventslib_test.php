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
 * Tests events subsystem.
 *
 * @package    core_event
 * @subpackage phpunit
 * @copyright  2007 onwards Martin Dougiamas (http://dougiamas.com)
 * @author     Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


class core_eventslib_testcase extends advanced_testcase {

    const DEBUGGING_MSG = 'Events API using $handlers array has been deprecated in favour of Events 2 API, please use it instead.';

    /**
     * Create temporary entries in the database for these tests.
     * These tests have to work no matter the data currently in the database
     * (meaning they should run on a brand new site). This means several items of
     * data have to be artificially inseminated (:-) in the DB.
     */
    protected function setUp() {
        parent::setUp();
        // Set global category settings to -1 (not force).
        eventslib_sample_function_handler('reset');
        eventslib_sample_handler_class::static_method('reset');

        $this->resetAfterTest();
    }

    /**
     * Tests the installation of event handlers from file
     */
    public function test_events_update_definition__install() {
        global $DB;

        events_update_definition('unittest');
        $this->assertDebuggingCalledCount(4);

        $dbcount = $DB->count_records('events_handlers', array('component'=>'unittest'));
        $handlers = array();
        require(__DIR__.'/fixtures/events.php');
        $this->assertCount($dbcount, $handlers, 'Equal number of handlers in file and db: %s');
    }

    /**
     * Tests the uninstallation of event handlers from file.
     */
    public function test_events_update_definition__uninstall() {
        global $DB;

        events_update_definition('unittest');
        $this->assertDebuggingCalledCount(4);

        events_uninstall('unittest');
        $this->assertDebuggingCalledCount(4);
        $this->assertEquals(0, $DB->count_records('events_handlers', array('component'=>'unittest')), 'All handlers should be uninstalled: %s');
    }

    /**
     * Tests the update of event handlers from file.
     */
    public function test_events_update_definition__update() {
        global $DB;

        events_update_definition('unittest');
        $this->assertDebuggingCalledCount(4);

        // First modify directly existing handler.
        $handler = $DB->get_record('events_handlers', array('component'=>'unittest', 'eventname'=>'test_instant'));

        $original = $handler->handlerfunction;

        // Change handler in db.
        $DB->set_field('events_handlers', 'handlerfunction', serialize('some_other_function_handler'), array('id'=>$handler->id));

        // Update the definition, it should revert the handler back.
        events_update_definition('unittest');
        $this->assertDebuggingCalledCount(4);
        $handler = $DB->get_record('events_handlers', array('component'=>'unittest', 'eventname'=>'test_instant'));
        $this->assertSame($handler->handlerfunction, $original, 'update should sync db with file definition: %s');
    }

    /**
     * Tests events_trigger_is_registered() function.
     */
    public function test_events_is_registered() {

        events_update_definition('unittest');
        $this->assertDebuggingCalledCount(4);

        $this->assertTrue(events_is_registered('test_instant', 'unittest'));
        $this->assertDebuggingCalled('events_is_registered() has been deprecated along with all Events 1 API in favour of Events 2' .
            ' API, please use it instead.', DEBUG_DEVELOPER);
    }

    /**
     * Tests that events_trigger throws an exception.
     */
    public function test_events_trigger_exception() {
        $this->expectException('coding_exception');
        events_trigger('test_instant', 'ok');
    }
}

/**
 * Test handler function.
 */
function eventslib_sample_function_handler($eventdata) {
    static $called = 0;
    static $ignorefail = false;

    if ($eventdata == 'status') {
        return $called;

    } else if ($eventdata == 'reset') {
        $called = 0;
        $ignorefail = false;
        return;

    } else if ($eventdata == 'fail') {
        if ($ignorefail) {
            $called++;
            return true;
        } else {
            return false;
        }

    } else if ($eventdata == 'ignorefail') {
        $ignorefail = true;
        return;

    } else if ($eventdata == 'ok') {
        $called++;
        return true;
    }

    print_error('invalideventdata', '', '', $eventdata);
}


/**
 * Test handler class with static method.
 */
class eventslib_sample_handler_class {
    public static function static_method($eventdata) {
        static $called = 0;
        static $ignorefail = false;

        if ($eventdata == 'status') {
            return $called;

        } else if ($eventdata == 'reset') {
            $called = 0;
            $ignorefail = false;
            return;

        } else if ($eventdata == 'fail') {
            if ($ignorefail) {
                $called++;
                return true;
            } else {
                return false;
            }

        } else if ($eventdata == 'ignorefail') {
            $ignorefail = true;
            return;

        } else if ($eventdata == 'ok') {
            $called++;
            return true;
        }

        print_error('invalideventdata', '', '', $eventdata);
    }
}
