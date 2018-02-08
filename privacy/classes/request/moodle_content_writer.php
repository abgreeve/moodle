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
 * This file contains the moodle format implementation of the content writer.
 *
 * @package core_privacy
 * @copyright 2018 Jake Dallimore <jrhdallimore@gmail.com>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\request;

class moodle_content_writer implements content_writer {
    protected $path = null;

    protected $context = null;

    /**
     * Constructor for the content writer.
     *
     * Note: The writer_factory must be passed.
     * @param   writer          $factory    The factory.
     */
    public function __construct(writer $writer) {
        $basedir = make_temp_directory('privacy');
        $this->path = make_unique_writable_directory($basedir, true);
    }

    /**
     * Set the context for the current item being processed.
     *
     * @param   \context        $context    The context to use
     */
    public function set_context(\context $context) : content_writer {
        $this->context = $context;

        return $this;
    }

    /**
     * Store the supplied data within the current context, at the supplied subcontext.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   \stdClass       $data       The data to be stored
     */
    public function store_data(array $subcontext, \stdClass $data) : content_writer {
        $path = $this->get_path($subcontext, 'data.json');

        $this->write_data($path, json_encode($data));

        return $this;
    }

    /**
     * Store metadata about the supplied subcontext.
     *
     * Metadata consists of a key/value pair and a description of the value.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   string          $name       The metadata name.
     * @param   string          $value      The metadata value.
     * @param   string          $description    The description of the value.
     */
    public function store_metadata(array $subcontext, String $key, $value, String $description) : content_writer{
        $path = $this->get_path($subcontext, 'metadata.json');

        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path));
        } else {
            $data = (object) [];
        }

        $data->$key = (object) [
            'value' => $value,
            'description' => $description,
        ];
        $this->write_data($path, json_encode($data));

        return $this;
    }

    /**
     * Store a piece of data in a custom format.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   string          $filename   The name of the file to be stored.
     * @param   string          $filecontent    The content to be stored.
     */
    public function store_custom_file(array $subcontext, $filename, $filecontent) : content_writer {
        $path = $this->get_path($subcontext, $filename);
        $this->write_data($path, $filecontent);

        return $this;
    }

    /**
     * Prepare a text area by processing pluginfile URLs within it.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   string          $component  The name of the component that the files belong to.
     * @param   string          $filearea   The filearea within that component.
     * @param   string          $itemid     Which item those files belong to.
     * param    string          $text       The text to be processed
     * @return  string                      The processed string
     */
    public function rewrite_pluginfile_urls(array $subcontext, $component, $filearea, $itemid, $text) : String {
        return str_replace('@@PLUGINFILE@@/', 'files/', $text);
    }

    /**
     * Store all files within the specified component, filearea, itemid combination.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   string          $component  The name of the component that the files belong to.
     * @param   string          $filearea   The filearea within that component.
     * @param   string          $itemid     Which item those files belong to.
     */
    public function store_area_files(array $subcontext, $component, $filearea, $itemid) : content_writer  {
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id, $component, $filearea, $itemid);
        foreach ($files as $file) {
            $this->store_file($subcontext, $file);
        }

        return $this;
    }

    /**
     * Store the specified file in the target location.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   \stored_file    $file       The file to be stored.
     */
    public function store_file(array $subcontext, \stored_file $file) : content_writer  {
        if (!$file->is_directory()) {
            $subcontextextra = [
                'files',
                $file->get_filepath(),
            ];
            $path = $this->get_path(array_merge($subcontext, $subcontextextra), $file->get_filename());
            check_dir_exists(dirname($path), true, true);
            $file->copy_content_to($path);
        }

        return $this;
    }

    /**
     * Determine the path for the current context.
     *
     * @return  array                       The context path.
     */
    protected function get_context_path() : Array {
        $path = [];
        $contexts = array_reverse($this->context->get_parent_contexts(true));
        foreach ($contexts as $context) {
            $path[] = clean_param($context->get_context_name(), PARAM_SAFEDIR);
        }

        return $path;
    }

    /**
     * Get the fully-qualified file path within the current context, and
     * subcontext, using the specified filename.
     *
     * @param   String[]        $subcontext The location within the current context to store this data.
     * @param   String          $name       The intended filename, including any extensions.
     * @return  String                      The fully-qualfiied file path.
     */
    protected function get_path(array $subcontext, String $name) : String {
        // Combine the base path of this exporter instance, with the context path, and the subcontext data.
        $path = array_merge(
            [
                $this->path,
            ],
            $this->get_context_path(),
            $subcontext
        );

        // Join the directory together with the name.
        return implode(DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * Write the data to the specified path.
     *
     * @param   String          $path       The path to store the data at.
     * @param   String          $data       The data to be stored.
     */
    protected function write_data(String $path, String $data) {
        check_dir_exists(dirname($path), true, true);
        file_put_contents($path, $data);
    }

    public function get_archive_location() {
        debugging('This is not part of the API - use with caution');
        return $this->path;
    }
}
