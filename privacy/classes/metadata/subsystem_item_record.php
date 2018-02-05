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
 * This file defines the core_privacy\metadata\item_record class object.
 *
 * The subsystem_item_record object implements the item_record interface and is
 * used to add references to subsystems associated with a component.
 *
 * The subsystem_item_record object DOES NOT store the privacy field details
 * from the subsystems as they are derived directly from them.
 *
 * The subsystem_item_record is organized into an item_collection defined in the
 * core_privacy\metadata\item_collection class for a given component.
 *
 * @package core_privacy
 * @copyright 2018 Zig Tan <zig@moodle.com>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_privacy\metadata;

class subsystem_item_record implements item_record {

    // Subsystem item record name.
    protected $name;

    // Subsystem item record summary.
    protected $summary;

    /**
     * subsystem_item_record constructor.
     *
     * @param string $name name of subsystem.
     * @param string $summary (optional) language identifier within specified component describing this item record.
     */
    public function __construct($name, $summary = '') {
        $this->name = $name;
        $this->summary = $summary;
    }

    /**
     * Function to return the name of this subsystem item record.
     *
     * @return string $name
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Function to return privacy fields of this subsystem item record.
     *
     * NOTE: For subsystems, the privacy fields are derived directly so return null here.
     *
     * @return null
     */
    public function get_privacy_fields() {
        return null;
    }

    /**
     * Function to return the summary of this subsystem item record.
     *
     * @return string $summary
     */
    public function get_summary() {
        return $this->summary;
    }

}