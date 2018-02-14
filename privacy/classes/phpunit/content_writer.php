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
namespace core_privacy\phpunit;

class content_writer implements \core_privacy\request\content_writer {
    protected $context = null;

    protected $metadata = [];
    protected $data = [];
    protected $files = [];
    protected $customfiles = [];

    /**
     * Whether any data has been stored at all within the current context.
     */
    public function has_any_data() {
        $hasdata = !empty($this->data[$this->context->id]);
        $hasmetadata = !empty($this->metadata[$this->context->id]);
        $hasfiles = !empty($this->files[$this->context->id]);
        $hascustomfiles = !empty($this->customfiles[$this->context->id]);

        return $hasdata || $hasmetadata || $hasfiles || $hascustomfiles;
    }

    /**
     * Constructor for the content writer.
     *
     * Note: The writer_factory must be passed.
     * @param   writer          $factory    The factory.
     */
    public function __construct(\core_privacy\request\writer $writer) {
    }

    /**
     * Set the context for the current item being processed.
     *
     * @param   \context        $context    The context to use
     */
    public function set_context(\context $context) : \core_privacy\request\content_writer {
        $this->context = $context;

        if (empty($this->data[$this->context->id])) {
            $this->data[$this->context->id] = [];
        }

        if (empty($this->metadata[$this->context->id])) {
            $this->metadata[$this->context->id] = [];
        }

        if (empty($this->files[$this->context->id])) {
            $this->files[$this->context->id] = [];
        }

        if (empty($this->customfiles[$this->context->id])) {
            $this->customfiles[$this->context->id] = [];
        }

        return $this;
    }

    /**
     * Store the supplied data within the current context, at the supplied subcontext.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   \stdClass       $data       The data to be stored
     */
    public function store_data(array $subcontext, \stdClass $data) : \core_privacy\request\content_writer {
        array_push($subcontext, 'data');

        $finalcontent = $data;

        while ($pathtail = array_pop($subcontext)) {
            $finalcontent = [
                $pathtail => $finalcontent,
            ];
        }

        $this->data[$this->context->id] = array_replace_recursive($this->data[$this->context->id], $finalcontent);

        return $this;
    }

    /**
     * Get all data within the subcontext.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @return  array                       The metadata as a series of keys to value + descrition objects.
     */
    public function get_data(array $subcontext = []) {
        $basepath = $this->data[$this->context->id];
        while ($subpath = array_shift($subcontext)) {
            if (isset($basepath[$subpath])) {
                $basepath = $basepath[$subpath];
            } else {
                return [];
            }
        }

        if (isset($basepath['data'])) {
            return $basepath['data'];
        } else {
            return [];
        }
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
    public function store_metadata(array $subcontext, String $key, $value, String $description) : \core_privacy\request\content_writer {
        array_push($subcontext, 'metadata');

        $finalcontent = [
            $key => (object) [
                'value' => $value,
                'description' => $description,
            ],
        ];

        while ($pathtail = array_pop($subcontext)) {
            $finalcontent = [
                $pathtail => $finalcontent,
            ];
        }

        $this->metadata[$this->context->id] = array_replace_recursive($this->metadata[$this->context->id], $finalcontent);

        return $this;
    }

    /**
     * Get all metadata within the subcontext.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @return  array                       The metadata as a series of keys to value + descrition objects.
     */
    public function get_all_metadata(array $subcontext = []) {
        $basepath = $this->metadata[$this->context->id];
        while ($subpath = array_shift($subcontext)) {
            if (isset($basepath[$subpath])) {
                $basepath = $basepath[$subpath];
            }
        }

        if (isset($basepath['metadata'])) {
            return $basepath['metadata'];
        } else {
            return [];
        }
    }

    /**
     * Get the specified metadata within the subcontext.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   string          $name       The metadata to be fetched within the context + subcontext.
     * @param   boolean         $valueonly  Whether to fetch only the value, rather than the value + description.
     * @return  array                       The metadata as a series of keys to value + descrition objects.
     */
    public function get_metadata(array $subcontext = [], $key, $valueonly = true) {
        $data = $this->get_all_metadata($subcontext);

        $metadata = $data[$key];
        if ($valueonly) {
            return $metadata->value;
        } else {
            return $metadata;
        }
    }

    /**
     * Store a piece of data in a custom format.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   string          $filename   The name of the file to be stored.
     * @param   string          $filecontent    The content to be stored.
     */
    public function store_custom_file(array $subcontext, $filename, $filecontent) : \core_privacy\request\content_writer {
        $filename = clean_param($filename, PARAM_FILE);

        $finalcontent = [
            $filename => $filecontent,
        ];
        while ($pathtail = array_pop($subcontext)) {
            $finalcontent = [
                $pathtail => $finalcontent,
            ];
        }

        $this->customfiles[$this->context->id] = array_replace_recursive($this->customfiles[$this->context->id], $finalcontent);

        return $this;
    }

    /**
     * Get the specified custom file within the subcontext.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @param   string          $filename   The name of the file to be fetched within the context + subcontext.
     * @return  string                      The content of the file.
     */
    public function get_custom_file(array $subcontext = [], $filename = null) {
        if (!empty($filename)) {
            array_push($subcontext, $filename);
        }

        $basepath = $this->customfiles[$this->context->id];
        while ($subpath = array_shift($subcontext)) {
            if (isset($basepath[$subpath])) {
                $basepath = $basepath[$subpath];
            }
        }

        return $basepath;
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
    public function store_area_files(array $subcontext, $component, $filearea, $itemid) : \core_privacy\request\content_writer  {
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
    public function store_file(array $subcontext, \stored_file $file) : \core_privacy\request\content_writer  {
        if (!$file->is_directory()) {
            $subcontextextra = [
                'files',
                $file->get_filepath(),
            ];
            $newsubcontext = array_merge($subcontext, $subcontextextra);

            $finalcontent = [
                $file,
            ];
            while ($pathtail = array_pop($subcontext)) {
                $finalcontent = [
                    $pathtail => $finalcontent,
                ];
            }

            $this->customfiles[$this->context->id] = array_replace_recursive($this->customfiles[$this->context->id], $finalcontent);
        }

        return $this;
    }

    /**
     * Get all files in the specfied subcontext.
     *
     * @param   array           $subcontext The location within the current context that this data belongs.
     * @return  stored_file[]               The list of stored_files in this context + subcontext.
     */
    public function get_files(array $subcontext = []) {
        $basepath = $this->files[$this->context->id];
        while ($subpath = array_shift($subcontext)) {
            if (isset($basepath[$subpath])) {
                $basepath = $basepath[$subpath];
            }
        }

        return $basepath;
    }

    /**
     * Finalise content for this writer.
     */
    public function finalise_content() {
        // This plugin stores no actual content.
    }
}
