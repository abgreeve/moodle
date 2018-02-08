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

class moodle_exporter implements exporter {
    protected $path = null;

    protected $context = null;

    public function __construct() {
        $basedir = make_temp_directory('privacy');
        $this->path = make_unique_writable_directory($basedir, true);
    }

    public function set_context(\context $context) {
        $this->context = $context;
    }

    public function store_data(array $subcontext, \stdClass $data) {
        $path = $this->get_path($subcontext, 'data.json');

        $this->write_data($path, json_encode($data));
    }

    public function store_metadata(array $subcontext, String $key, $value, String $description) {
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

    public function store_custom_file(array $subcontext, $filename, $filecontent) {
        $path = $this->get_path($subcontext, $filename);
        $this->write_data($path, $filecontent);
    }

    public function rewrite_pluginfile_urls(array $subcontext, $component, $filearea, $itemid, $text) : String {
        return str_replace('@@PLUGINFILE@@/', 'files/', $text);
    }

    public function store_area_files(array $subcontext, $component, $filearea, $itemid) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id, $component, $filearea, $itemid);
        foreach ($files as $file) {
            $this->store_file($subcontext, $file);
        }

        return $this;
    }

    public function store_file(array $subcontext, \stored_file $file) {
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

    protected function get_context_path() : Array {
        $path = [];
        $contexts = array_reverse($this->context->get_parent_contexts(true));
        foreach ($contexts as $context) {
            $path[] = clean_param($context->get_context_name(), PARAM_SAFEDIR);
        }

        return $path;
    }

    protected function get_path(array $subcontext, String $name) {
        // TODO
        $path = array_merge(
            [
                $this->path,
            ],
            $this->get_context_path(),
            $subcontext
        );

        return implode(DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR . $name;
    }

    protected function write_data($path, $data) {
        check_dir_exists(dirname($path), true, true);
        file_put_contents($path, $data);
    }

    public function get_archive_location() {
        return $this->path;
    }

    public function get_archive() {
        var_dump($this->get_archive_location());
    }
}
