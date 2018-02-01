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
 * This file contains the core_privacy\manager class.
 *
 * @package core_privacy
 * @copyright 2018 Jake Dallimore <jrhdallimore@gmail.com>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy;

/**
 * Class manager.
 * Provides the mechanisms to get and delete personal information across Moodle.
 * @package core_privacy
 */
class manager {
    //TODO: same functions as plugins implement

    /**
     * Get the privacy metadata for all components or for a subset of components.
     * @param array $components
     * @return array
     */
    public static function get_metadata_for_components(array $components = []) {
        // If empty, get the metadata for all components.

        // Else, provide only for those specified.
        return [];
    }

    public static function get_contexts_for_userid(int $userid) {

    }



    //TODO: plugins list
    //TODO: ss list (assumed mapped elsewhere for now.
}
