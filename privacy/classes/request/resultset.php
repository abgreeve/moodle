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
 * Privacy Fetch Result Set.
 *
 * @package    privacy
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_privacy\request;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Fetch Result Set.
 *
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class resultset implements
    // This is currently an approved_contextlist until we decide upon implementation of that interface.
    approved_contextlist,

    // Implement an Iterator to fetch the Context objects.
    \Iterator,

    // Implement the Countable interface to allow the number of returned results to be queried easily.
    \Countable
{
    protected $contextids = [];

    protected $position = 0;

    public function get_contextids() {
        return array_unique($this->contextids);
    }

    public function get_contexts() {
        $contexts = [];
        foreach ($this->contextids as $contextid) {
            $contexts[] = \context::instance_by_id($contextid);
        }

        return $contexts;
    }

    /**
     * Add a set of contexts from  SQL.
     *
     * The SQL should only return a list of context IDs.
     *
     * @param   string  $sql    The SQL which will fetch the list of * context IDs
     * @param   array   $params The set of SQL parameters
     * @return  $this
     */
    public function add_from_sql($sql, $params) {
        global $DB;

        $fields = \context_helper::get_preload_record_columns_sql('ctx');
        $wrapper = "SELECT {$fields} FROM {context} ctx WHERE id IN ({$sql})";
        $contexts = $DB->get_recordset_sql($wrapper, $params);

        $contextids = [];
        foreach ($contexts as $context) {
            $contextids[] = $context->ctxid;
            \context_helper::preload_from_record($context);
        }

        $this->contextids += $contextids;

        return $this;
    }

    /**
     * Return the current context.
     *
     * @return  \context
     */
    public function current() {
        return \context::instance_by_id($this->contextids[$this->position]);
    }

    /**
     * Return the key of the current element.
     *
     * @return  mixed
     */
    public function key() {
        return $this->position;
    }

    /**
     * Move to the next context in the list.
     */
    public function next() {
        ++$this->position;
    }

    /**
     * Check if the current position is valid.
     *
     * @return  bool
     */
    public function valid() {
        return isset($this->contextids[$this->position]);
    }

    /**
     * Rewind to the first found context.
     */
    public function rewind() {
        $this->position = 0;
    }

    public function count() {
        return count($this->contextids);
    }
}
