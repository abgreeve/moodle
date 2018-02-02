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
 * This file contains the core_privacy\user_data_provider interface.
 *
 * Plugins should implement this if they store personal information.
 *
 * @package core_privacy
 * @copyright 2018 Jake Dallimore <jrhdallimore@gmail.com>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\request;

interface exporter {
    public function store_data(\context $context, array $subcontext, \stdClass $data);
    public function store_metadata(\context $context, array $subcontext, $name, $content);
    public function store_custom_file(\context $context, array $subcontext, $filename, $filecontent);
    public function rewrite_pluginfile_urls(\context $context, array $subcontext, $component, $filearea, $itemid, $text) : String;
    public function store_area_files(\context $context, array $subcontext, $component, $filearea, $itemid);
    public function store_file(\context $context, array $subcontext, \stored_file $file);
}
