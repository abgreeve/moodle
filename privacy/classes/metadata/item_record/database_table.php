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
 * This file defines an item of metadata which encapsulates a database table.
 *
 * @package core_privacy
 * @copyright 2018 Zig Tan <zig@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\metadata\item_record;

/**
 * The database_table item record.
 *
 * @copyright 2018 Zig Tan <zig@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class database_table implements type {

    // Database table name.
    protected $name;

    // Fields which contain user information within the table.
    protected $privacyfields;

    // A description of what this table is used for.
    protected $summary;

    /**
     * Constructor to create a new database_table item_record.
     *
     * @param   string  $name The name of the database table being described.
     * @param   array   $privacyfields A list of fields iwth their description.
     * @param   string  $summary A description of what the table is used for.
     */
    public function __construct($name, array $privacyfields = [], $summary = '') {
        $this->name = $name;
        $this->privacyfields = $privacyfields;
        $this->summary = $summary;
    }

    /**
     * The name of the database table.
     *
     * @return  string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * The list of fields within the table which contain user data, with a description of each field.
     *
     * @return  array
     */
    public function get_privacy_fields() {
        return $this->privacyfields;
    }

    /**
     * A summary of what this table is used for.
     *
     * @return  string
     */
    public function get_summary() {
        return $this->summary;
    }
}
