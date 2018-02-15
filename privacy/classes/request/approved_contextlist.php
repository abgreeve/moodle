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
 * Privacy Approved Context List interface
 *
 * @package    privacy
 * @copyright  2018 Zig Tan <zig@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_privacy\request;

/**
 * Interface approved_contextlist
 * @package core_privacy\request
 */
interface approved_contextlist extends

    // Implement an Iterator to fetch the Context objects.
    \Iterator,

    // Implement the Countable interface to allow the number of returned results to be queried easily.
    \Countable
{

    /**
     * Specify the user which owns this request.
     *
     * @param   \stdClass       $user The user record.
     * @return  $this
     */
    public function set_user(\stdClass $user) : approved_contextlist ;

    /**
     * Get the user which requested their data.
     *
     * @return  \stdClass
     */
    public function get_user() : \stdClass;

    /**
     * Get the list of context IDs that relate to this request.
     *
     * @return  int[]
     */
    public function get_contextids() : array ;

    /**
     * Get the complete list of context objects that relate to this
     * request.
     *
     * @return  \contect[]
     */
    public function get_contexts() : array ;
}
