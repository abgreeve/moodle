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
 * External service to remove the last attempt of a student
 *
 * @package    mod_assign/external
 * @copyright  2023 Adrian Greeve <adriangreeve.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_assign\external;

use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;

global $CFG;

require_once("$CFG->dirroot/mod/assign/locallib.php");

class remove_last_attempt extends external_api {

    /**
     * Describes the parameters for submission_start.
     *
     * @return external_function_parameters
     * @since Moodle 4.0
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters ([
                'assignid' => new external_value(PARAM_INT, 'Assignment instance id'),
                'userid' => new external_value(PARAM_INT, 'User id')
            ]
        );
    }

    /**
     * Call to start an assignment submission.
     *
     * @param int $assignid Assignment ID.
     * @return array
     * @since Moodle 4.0
     */
    public static function execute(int $assignid, int $userid): array {
        global $DB, $USER;

        $result = $warnings = [];

        ['assignid' => $assignid, 'userid' => $userid] = self::validate_parameters(self::execute_parameters(), [
            'assignid' => $assignid,
            'userid' => $userid
        ]);

        list($assignment, $course, $cm, $context) = self::validate_assign($assignid);
        $assignment->remove_last_attempt($userid);

        return $result;
    }

    /**
     * Describes the submission_start return value.
     *
     * @return external_single_structure
     * @since Moodle 4.0
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'warnings' => new external_warnings(),
        ]);
    }

}
