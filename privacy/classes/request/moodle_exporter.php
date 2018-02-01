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

    public function __construct() {
        $basedir = make_temp_directory('privacy');
        $this->path = make_unique_writable_directory($basedir, true);
    }

    public function store_data(\context $context, \stdClass $data, $subcontext = null) {
        $path = $this->get_path($context, 'data.json', $subcontext);
        $this->write_data($path, json_encode($data));
    }

    public function store_metadata(\context $context, $key, $value, $subcontext = null) {
        $path = $this->get_path($context, 'metadata.json', $subcontext);

        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path));
        } else {
            $data = (object) [];
        }

        $data->$key = $value;
        $this->write_data($path, json_encode($data));

        return $this;
    }

    public function store_custom_file(\context $context, $filename, $filecontent, $subcontext = null) {
        $path = $this->get_path($context, $filename, $subcontext);
        $this->write_data($path, $filecontent);
    }

    public function store_area_files(\context $context, $component, $filearea, $itemid, $subcontext = null) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, $component, $filearea, $itemid);
        foreach ($files as $file) {
            $this->store_file($context, $file, $subcontext);
        }

        return $this;
    }

    public function store_file(\context $context, \stored_file $file, $subcontext = null) {
        if (!$file->is_directory()) {
            $path = $this->get_path($context, implode(DIRECTORY_SEPARATOR, ['files', $file->get_filepath(), $file->get_filename()]), $subcontext);
            $path = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
            $path = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
            $path = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
            $path = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
            check_dir_exists(dirname($path), true, true);
            $file->copy_content_to($path);
        }

        return $this;
    }

    protected function get_path(\context $context, $name, $subcontext = null) {
        // TODO
        $path = [
            $this->path,
            $subcontext
        ];

        return implode(DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR . $name;
    }

    protected function write_data($path, $data) {
        check_dir_exists(dirname($path), true, true);
        file_put_contents($path, $data);
    }

    public function get_archive() {
        var_dump($this->path);
    }
}
