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
 * This file defines the core_privacy\request\resultset_collection class object.
 *
 * The resultset_collection is used to organize a collection of resultsets.
 *
 * @package core_privacy
 * @copyright 2018 Jake Dallimore <jrhdallimore@gmail.com>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\request;

/**
 * Class resultset_collection
 * @package core_privacy\request
 */
class resultset_collection {
    /**
     * @var array $resultsets the internal array of resultset objects.
     */
    protected $resultsets;

    public function __construct() {
        $this->resultsets = [];
    }

    /**
     * @param string $component the frankenstyle name of the component to which the resultset applies. E.g. core_comment.
     * @param resultset $resultset the resultset to store.
     */
    public function add_resultset($component, resultset $resultset) {
        $this->resultsets[$component] = $resultset;
    }

    /**
     * Get the resultsets in this collection.
     *
     * @return array the associative array of resultsets stored in this collection, indexeb by component name.
     * E.g. mod_assign => resultset, core_comment => resultset.
     */
    public function get_resultsets() : array {
        return $this->resultsets;
    }
}