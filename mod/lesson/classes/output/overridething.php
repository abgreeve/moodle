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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Output the actionbar for this activity.
 *
 * @package   mod_lesson
 * @copyright 2021 Adrian Greeve <adrian@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_lesson\output;

use moodle_url;
use templatable;
use renderable;

class overridething implements templatable, renderable {

    private $urlselect;
    private $currenturl;

    public function __construct($urlselect, $currenturl) {
        $this->urlselect = $urlselect;
        $this->currenturl = $currenturl;
    }

    public function export_for_template(\renderer_base $output) {
        // TODO check if there are users or groups and don't show the button if those don't exist.
        $type = $this->currenturl->get_param('mode');
        $text = ($type == 'user') ? get_string('addnewuseroverride', 'mod_lesson') : get_string('addnewgroupoverride', 'mod_lesson');
        $action = ($type == 'user') ? 'adduser' : 'addgroup';
        $data = [
            'addoverride' => [
                'text' => $text,
                'link' => new moodle_url('/mod/lesson/overrideedit.php', ['cmid' => $this->currenturl->get_param('cmid'), 'action' => $action])
            ],
            'urlselect' => $this->urlselect->export_for_template($output)
        ];
        return $data;
    }

}
