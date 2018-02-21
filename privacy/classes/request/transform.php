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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains the core_privacy\request helper.
 *
 * @package core_privacy
 * @copyright 2018 Andrew Nicols <andrew@nicols.co.uk>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\request;

class transform {
    public static function user(int $userid) {
        // For the moment we do not think we should transform as this reveals information about other users.
        // However this function is implemented should the need arise in the future.
        return $userid;
    }

    public static function datetime($datetime) {
        if ($datetime) {
            return userdate($datetime, get_string('strftimedaydatetime', 'langconfig'));
        }
        return null;
    }

    public static function date($date) {
        if ($datetime) {
            return userdate($datetime, get_string('strftimetime', 'langconfig'));
        }
        return null;
    }
}
