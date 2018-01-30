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

namespace core\privacy\request;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Fetch Result Set.
 *
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class resultset {
    // TODO: Possibly make this class implement Iterator.
    protected $contextids = [];

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
            $contextids[] = $context->id;
            \context_helper::preload_from_record($context);
        }

        $this->contextids += $contextids;

        return $this;
    }
}
