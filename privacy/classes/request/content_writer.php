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
 * This file contains the interface required to implmeent a content writer.
 *
 * @package core_privacy
 * @copyright 2018 Jake Dallimore <jrhdallimore@gmail.com>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\request;

interface content_writer {

    /**
     * Constructor for the content writer.
     *
     * Note: The writer_factory must be passed.
     * @param   writer          $factory    The factory.
     */
    public function __construct(writer $writer);

    /**
     * Set the context for the current item being processed.
     *
     * @param   \context        $context    The context to use
     * @return  content_writer
     */
    public function set_context(\context $context) : content_writer ;

    /**
     * Store the supplied data within the current context, at the supplied subcontext.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   \stdClass       $data       The data to be stored
     * @return  content_writer
     */
    public function store_data(array $subcontext, \stdClass $data) : content_writer ;

    /**
     * Store metadata about the supplied subcontext.
     *
     * Metadata consists of a key/value pair and a description of the value.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   string          $name       The metadata name.
     * @param   string          $value      The metadata value.
     * @param   string          $description    The description of the value.
     * @return  content_writer
     */
    public function store_metadata(array $subcontext, String $name, $value, String $description) : content_writer ;

    /**
     * Store a piece of related data.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   string          $name       The name of the file to be stored.
     * @param   \stdClass       $data       The related data to store.
     * @return  content_writer
     */
    public function store_related_data(array $subcontext, $name, $data) : content_writer ;

    /**
     * Store a piece of data in a custom format.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   string          $filename   The name of the file to be stored.
     * @param   string          $filecontent    The content to be stored.
     * @return  content_writer
     */
    public function store_custom_file(array $subcontext, $filename, $filecontent) : content_writer ;

    /**
     * Prepare a text area by processing pluginfile URLs within it.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   string          $component  The name of the component that the files belong to.
     * @param   string          $filearea   The filearea within that component.
     * @param   string          $itemid     Which item those files belong to.
     * @param   string          $text       The text to be processed
     * @return  string                      The processed string
     */
    public function rewrite_pluginfile_urls(array $subcontext, $component, $filearea, $itemid, $text) : String;

    /**
     * Store all files within the specified component, filearea, itemid combination.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   string          $component  The name of the component that the files belong to.
     * @param   string          $filearea   The filearea within that component.
     * @param   string          $itemid     Which item those files belong to.
     * @return  content_writer
     */
    public function store_area_files(array $subcontext, $component, $filearea, $itemid) : content_writer ;

    /**
     * Store the specified file in the target location.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   \stored_file    $file       The file to be stored.
     * @return  content_writer
     */
    public function store_file(array $subcontext, \stored_file $file) : content_writer ;

    /**
     * Store the specified user preference.
     *
     * @param   string          $component  The name of the component.
     * @param   string          $key        The name of th key to be stored.
     * @param   string          $value      The value of the preference
     * @param   string          $description    A description of the value
     * @return  content_writer
     */
    public function store_user_preference(string $component, string $key, string $value, string $description) : content_writer ;

    /**
     * Perform any required finalisation steps.
     */
    public function finalise_content() ;
}
