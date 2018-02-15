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
 * This file defines the core_privacy\request\contextlist_collection class object.
 *
 * The contextlist_collection is used to organize a collection of contextlists.
 *
 * @package core_privacy
 * @copyright 2018 Jake Dallimore <jrhdallimore@gmail.com>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\request;

/**
 * Class contextlist_collection
 * @package core_privacy\request
 */
class contextlist_collection {
    /**
     * @var array $contextlists the internal array of contextlist objects.
     */
    protected $contextlists;

    public function __construct() {
        $this->contextlists = [];
    }

    /**
     * @param string $component the frankenstyle name of the component to which the contextlist applies. E.g. core_comment.
     * @param contextlist $contextlist the contextlist to store.
     */
    public function add_contextlist($component, contextlist $contextlist) {
        $this->contextlists[$component] = $contextlist;
    }

    /**
     * Get the contextlists in this collection.
     *
     * @return array the associative array of contextlists stored in this collection, indexeb by component name.
     * E.g. mod_assign => contextlist, core_comment => contextlist.
     */
    public function get_contextlists() : array {
        return $this->contextlists;
    }
}
