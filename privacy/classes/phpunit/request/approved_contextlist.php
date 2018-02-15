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
 * Approved result set for unit testing.
 *
 * @package    privacy
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_privacy\phpunit\request;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Fetch Result Set.
 *
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class approved_contextlist implements \core_privacy\request\approved_contextlist {
    // TODO: Possibly make this class implement Iterator.
    protected $contextids = [];

    protected $user;

    protected $iteratorposition = 0;

    /**
     * Specify the user which owns this request.
     *
     * @param   \stdClass       $user The user record.
     * @return  $this
     */
    public function set_user(\stdClass $user) : \core_privacy\request\approved_contextlist {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the user which requested their data.
     *
     * @return  \stdClass
     */
    public function get_user() : \stdClass {
        return $this->user;
    }

    /**
     * Get the list of context IDs that relate to this request.
     *
     * @return  int[]
     */
    public function get_contextids() : array {
        return array_unique($this->contextids);
    }

    /**
     * Get the complete list of context objects that relate to this
     * request.
     *
     * @return  \contect[]
     */
    public function get_contexts() : array {
        $contexts = [];
        foreach ($this->contextids as $contextid) {
            $contexts[] = \context::instance_by_id($contextid);
        }

        return $contexts;
    }

    public function add_context(\context $context) {
        $this->contextids[] = $context->id;
    }

    public function add_context_by_id($contextid) {
        $this->contextids[] = $contextid;
    }

    public function add_contexts(array $contexts) {
        foreach ($contexts as $context) {
            $this->add_context($context);
        }
    }

    public function add_contexts_by_id(array $contexts) {
        foreach ($contexts as $contextid) {
            $this->add_context_by_id($contextid);
        }
    }

    /**
     * Return the current context.
     *
     * @return  \context
     */
    public function current() {
        return \context::instance_by_id($this->contextids[$this->iteratorposition]);
    }

    /**
     * Return the key of the current element.
     *
     * @return  mixed
     */
    public function key() {
        return $this->iteratorposition;
    }

    /**
     * Move to the next context in the list.
     */
    public function next() {
        ++$this->iteratorposition;
    }

    /**
     * Check if the current position is valid.
     *
     * @return  bool
     */
    public function valid() {
        return isset($this->contextids[$this->iteratorposition]);
    }

    /**
     * Rewind to the first found context.
     */
    public function rewind() {
        $this->iteratorposition = 0;
    }

    public function count() {
        return count($this->contextids);
    }
}
