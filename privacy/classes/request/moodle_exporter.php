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
namespace core\privacy\request;

class moodle_exporter implements exporter {
    protected function get_path(\context $context, $name, $subcontext = null) {
        // TODO
        $path = [
            make_request_directory(),
            $subcontext
        ];

        return implode(DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR . $name;
    }

    public function store_metadata(\context $context, $key, $value, $subcontext = null) {
        $path = $this->get_path($context, 'metadata.json', $subcontext);

        // TODO
        // Fetch current file, json_decode() it, add the new $key => $value
        // pair, then save it.
        $this->write_data($path, $value);

        return $this;
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
        $path = $this->get_path($context, implode(DIRECTORY_SEPARATOR, [$file->get_filepath(), $file->get_filename()]), $subcontext);
        $file->copy_content_to($path);

        return $this;
    }

    public function store_data(\context $context, $data, $subcontext = null) {
        $path = $this->get_path($context, 'data.json', $subcontext);
        $this->write_data($path, json_encode($data));
    }

    public function store_custom_file(\context $context, $filename, $filecontent, $subcontext = null) {
        $path = $this->get_path($context, $filename, $subcontext);
        $this->write_data($path, $filecontent);
    }

    protected function write_data($path, $data) {
        file_put_contents($path, $data);
    }
}
