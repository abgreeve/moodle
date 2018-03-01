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
 * This file defines the contextlist_collection class object.
 *
 * The contextlist_collection is used to organize a collection of contextlists.
 *
 * @package core_privacy
 * @copyright 2018 Jake Dallimore <jrhdallimore@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\request;

defined('MOODLE_INTERNAL') || die();

/**
 * A collection of contextlist items.
 *
 * @copyright 2018 Jake Dallimore <jrhdallimore@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contextlist_collection {

    /**
     * @var array $contextlists the internal array of contextlist objects.
     */
    protected $contextlists = [];

    /**
     * Add a contextlist to this collection.
     *
     * @param   contextlist $contextlist the contextlist to export.
     * @return  $this
     */
    public function add_contextlist(contextlist_base $contextlist) {
        $component = $contextlist->get_component();
        if (empty($component)) {
            throw new \moodle_exception("The contextlist must have a component set");
        }
        if (isset($this->contextlists[$component])) {
            throw new \moodle_exception("A contextlist has already been added for the '{$component}' component");
        }

        $this->contextlists[$component] = $contextlist;

        return $this;
    }

    /**
     * Get the contextlists in this collection.
     *
     * @return  array   the associative array of contextlists in this collection, indexed by component name.
     * E.g. mod_assign => contextlist, core_comment => contextlist.
     */
    public function get_contextlists() : array {
        return $this->contextlists;
    }

    /**
     * Get the contextlist for the specified component.
     *
     * @param   string      $component the frankenstyle name of the component to fetch for.
     * @return  contextlist_base|null
     */
    public function get_contextlist_for_component(string $component) {
        if (isset($this->contextlists[$component])) {
            return $this->contextlists[$component];
        }

        return null;
    }
}
