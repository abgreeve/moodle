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

namespace core_privacy\phpunit;

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
}
