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
 * Unit Tests for a the collection of contextlists class
 *
 * @package     core_privacy
 * @category    test
 * @copyright   2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

use \core_privacy\request\contextlist_collection;
use \core_privacy\request\contextlist;
use \core_privacy\request\approved_contextlist;

/**
 * Tests for the \core_privacy API's contextlist collection functionality.
 *
 * @copyright   2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contextlist_collection_test extends advanced_testcase {
    /**
     * A contextlist_collection should support the contextlist type.
     */
    public function test_supports_contextlist() {
        $uit = new contextlist_collection();
        $contextlist = new contextlist();
        $contextlist->set_component('core_privacy');
        $uit->add_contextlist($contextlist);

        $this->assertCount(1, $uit->get_contextlists());
    }

    /**
     * A contextlist_collection should support the approved_contextlist type.
     */
    public function test_supports_approved_contextlist() {
        $uit = new contextlist_collection();
        $testuser = \core_user::get_user_by_username('admin');
        $contextids = [3, 2, 1];
        $uit->add_contextlist(new approved_contextlist($testuser, 'core_privacy', $contextids));

        $this->assertCount(1, $uit->get_contextlists());
    }

    /**
     * Ensure that get_contextlist_for_component returns the correct contextlist.
     */
    public function test_get_contextlist_for_component() {
        $uit = new contextlist_collection();
        $coretests = new contextlist();
        $coretests->set_component('core_tests');
        $uit->add_contextlist($coretests);

        $coreprivacy = new contextlist();
        $coreprivacy->set_component('core_privacy');
        $uit->add_contextlist($coreprivacy);

        // Note: This uses assertSame rather than assertEquals.
        // The former checks the actual object, whilst assertEquals only checks that they look the same.
        $this->assertSame($coretests, $uit->get_contextlist_for_component('core_tests'));
        $this->assertSame($coreprivacy, $uit->get_contextlist_for_component('core_privacy'));
    }

    /**
     * Ensure that get_contextlist_for_component does not die horribly when querying a non-existent component.
     */
    public function test_get_contextlist_for_component_not_found() {
        $uit = new contextlist_collection();

        $this->assertNull($uit->get_contextlist_for_component('core_tests'));
    }

    /**
     * Ensure that a duplicate contextlist in the collection throws an Exception.
     */
    public function test_duplicate_addition_throws() {
        $uit = new contextlist_collection();

        $coretests = new contextlist();
        $coretests->set_component('core_tests');
        $uit->add_contextlist($coretests);

        $this->expectException('moodle_exception');
        $uit->add_contextlist($coretests);
    }
}
