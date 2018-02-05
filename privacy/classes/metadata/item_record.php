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
 * This file defines the core_privacy\metadata\item_record interface.
 *
 * The item_record interface defines the standard functions expected
 * within class objects implementing this interface.
 *
 * The objects implementing the item_record interface are organized into
 * an item_collection defined in the core_privacy\metadata\item_collection
 * class for a given component.
 *
 * @package core_privacy
 * @copyright 2018 Zig Tan <zig@moodle.com>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\metadata;

/**
 * Interface item_record
 * @package core_privacy\metadata
 */
interface item_record {

    public function get_name();

    public function get_privacy_fields();

    public function get_summary();

}