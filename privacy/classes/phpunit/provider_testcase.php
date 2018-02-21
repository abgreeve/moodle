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
 * Testcase for providers implementing parts of the core_privacy subsystem.
 *
 * @package    core_privacy
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_privacy\phpunit;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Testcase for providers implementing parts of the core_privacy subsystem.
 *
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider_testcase extends \advanced_testcase {

    /**
     * Test tearDown.
     */
    public function tearDown() {
        \core_privacy\request\writer::reset();
    }

    /**
     * Export all data for a plugin for the specified user.
     */
    public function get_contexts_for_userid(int $userid, string $plugin) {
        $classname = "\\${plugin}\\privacy\\provider";

        if (!class_exists($classname)) {
            throw new \coding_exception("{$plugin} does not implement any provider");
        }

        $rc = new \ReflectionClass($classname);
        if (!$rc->implementsInterface(\core_privacy\metadata\provider::class)) {
            throw new \coding_exception("{$plugin} does not implement metadata provider");
        }
        if (!$rc->implementsInterface(\core_privacy\request\core_user_data_provider::class)) {
            throw new \coding_exception("{$plugin} does not declare that it provides any user data");
        }

        return $classname::get_contexts_for_userid($userid);
    }

    /**
     * Export all data for a plugin for the specified user.
     */
    public function export_all_data_for_user(int $userid, string $plugin) {
        $classname = "\\${plugin}\\privacy\\provider";

        if (!class_exists($classname)) {
            throw new \coding_exception("{$plugin} does not implement any provider");
        }

        $rc = new \ReflectionClass($classname);
        if (!$rc->implementsInterface(\core_privacy\metadata\provider::class)) {
            throw new \coding_exception("{$plugin} does not implement metadata provider");
        }
        if (!$rc->implementsInterface(\core_privacy\request\core_user_data_provider::class)) {
            throw new \coding_exception("{$plugin} does not declare that it provides any user data");
        }

        if ($contextlist = $classname::get_contexts_for_userid($userid)) {
            $contextlist->set_user(\core_user::get_user($userid));
            $classname::store_user_data($contextlist);
        }
    }

    /**
     * Export all data within a context for a plugin for the specified user.
     */
    public function export_context_data_for_user(int $userid, \context $context, string $plugin) {
        $classname = "\\${plugin}\\privacy\\provider";

        if (!class_exists($classname)) {
            throw new \coding_exception("{$plugin} does not implement any provider");
        }

        $rc = new \ReflectionClass($classname);
        if (!$rc->implementsInterface(\core_privacy\metadata\provider::class)) {
            throw new \coding_exception("{$plugin} does not implement metadata provider");
        }
        if (!$rc->implementsInterface(\core_privacy\request\core_user_data_provider::class)) {
            throw new \coding_exception("{$plugin} does not declare that it provides any user data");
        }

        $cl = new \core_privacy\phpunit\request\approved_contextlist();
        $cl->set_user(\core_user::get_user($userid));
        $cl->add_context($context);

        $classname::store_user_data($cl);
    }
}
