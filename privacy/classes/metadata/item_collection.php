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
 * This file defines the core_privacy\metadata\item_collection class object.
 *
 * The item_collection class is used to organize a collection of item_record
 * objects, which contains the privacy field details of a component.
 *
 * @package core_privacy
 * @copyright 2018 Jake Dallimore <jrhdallimore@gmail.com>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\metadata;

/**
 * Class item_collection
 * @package core_privacy\metadata
 */
class item_collection {

    // Item collection component reference.
    protected $component;

    // Item collection of item_records.
    protected $itemcollection;

    /**
     * Constructor for a component's privacy item collection class.
     *
     * @param string $component component name.
     */
    public function __construct($component) {
        $this->component = $component;
        $this->itemcollection = [];
    }

    /**
     * Function to add an object that implements item_record interface to the current item collection.
     *
     * @param item_record $itemrecord to add to item collection.
     */
    public function add_item_record(item_record $itemrecord) {
        $this->itemcollection[] = $itemrecord;
    }

    /**
     * Function to add a datastore item_record to the current item collection.
     *
     * @param string $name the name of the datastore.
     * @param array $privacyfields is an associative array of the component's privacy fields.
     * @param string $summary (optional) language string identifier within specified component describing this field.
     */
    public function add_datastore($name, array $privacyfields, $summary = '') {
        $this->add_item_record(new datastore_item_record($name, $privacyfields, $summary));
    }

    /**
     * Function to link a subsystem item_record to the current item collection.
     *
     * @param string $name the name of the data store.
     * @param string $summary (optional) language string identifier within specified component describing this field.
     */
    public function link_subsystem($name, $summary = '') {
        $this->add_item_record(new subsystem_item_record($name, $summary));
    }

    /**
     * Function to return the current component name.
     *
     * @return string $component
     */
    public function get_component() {
        return $this->component;
    }

    /**
     * Function to return the current item collection.
     *
     * @return array $itemcollection
     */
    public function get_item_collection() {
        return $this->itemcollection;
    }

}
