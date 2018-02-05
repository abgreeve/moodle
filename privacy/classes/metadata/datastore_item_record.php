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
 * This file defines the core_privacy\metadata\datastore_item_record class object.
 *
 * The datastore_item_record object implements the item_record interface and is
 * used to store a component's database table(s) privacy field details.
 *
 * The datastore_item record is organized into an item_collection defined in the
 * core_privacy\metadata\item_collection class for a given component.
 *
 * @package core_privacy
 * @copyright 2018 Zig Tan <zig@moodle.com>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\metadata;

class datastore_item_record implements item_record {

    // Datastore item record name.
    protected $name;

    // Datastore item record privacy fields.
    protected $privacyfields;

    // Datastore item record summary.
    protected $summary;

    /**
     * datastore_item_record constructor.
     * @param string $name name of datastore.
     * @param array $privacyfields is an associative array.
     * @param string $summary (optional) language identifier within specified component describing this item record.
     */
    public function __construct($name, array $privacyfields = [], $summary = '') {
        $this->name = $name;
        $this->privacyfields = $privacyfields;
        $this->summary = $summary;
    }

    /**
     * Function to return the name of this datastore item record.
     *
     * @return string $name
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Function to return the name of this datastore item record.
     *
     * @return array $privacyfields
     */
    public function get_privacy_fields() {
        return $this->privacyfields;
    }

    /**
     * Function to return the summary of this datastore item record.
     *
     * @return string $summary
     */
    public function get_summary() {
        return $this->summary;
    }

}