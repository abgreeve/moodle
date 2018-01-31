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
 * This file contains the \core\privacy\request\plugin_provider interface to describe
 * a class which provides data in some form for a plugin.
 *
 * Plugins should implement this if they store personal information.
 *
 * @package core_privacy
 * @copyright 2018 Jake Dallimore <jrhdallimore@gmail.com>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core\privacy\request;

interface plugin_provider extends data_provider {

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int         $userid The user to search
     * @return  resultset           The resultset containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : resultset;

    /**
     * Store all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param   int         $userid The user to store information for
     * @param   context[]   $contexts   The list of contexts to store information for
     * @param   exporter    $exporter   The exporter plugin used to write the user data
     */
    public static function store_user_data(int $userid, array $contexts, exporter $exporter);
}
