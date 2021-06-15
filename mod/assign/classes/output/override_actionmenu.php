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
 * @package   mod_assign
 * @copyright 2021 Adrian Greeve <adrian@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_assign\output;

use moodle_url;
use templatable;
use renderable;
use url_select;

class override_actionmenu implements templatable, renderable {

    private $currenturl;

    public function __construct($currenturl) {
        $this->currenturl = $currenturl;
    }

    private function get_select_menu() {
        $cmid = $this->currenturl->get_param('cmid');
        $userlink = new moodle_url('/mod/assign/overrides.php', ['cmid' => $cmid, 'mode' => 'user']);
        $grouplink = new moodle_url('/mod/assign/overrides.php', ['cmid' => $cmid, 'mode' => 'group']);
        $menu = [
            $userlink->out(false) => get_string('useroverrides', 'mod_assign'),
            $grouplink->out(false) => get_string('groupoverrides', 'mod_assign'),
        ];
        return new url_select($menu, $this->currenturl->out(false), null, 'assignselectthing');
    }

    public function export_for_template(\renderer_base $output) {
        // TODO check if there are users or groups and don't show the button if those don't exist.
        $type = $this->currenturl->get_param('mode');
        $text = ($type == 'user') ? get_string('addnewuseroverride', 'mod_assign') : get_string('addnewgroupoverride', 'mod_assign');
        $action = ($type == 'user') ? 'adduser' : 'addgroup';
        $urlselect = $this->get_select_menu();
        $data = [
            'addoverride' => [
                'text' => $text,
                'link' => new moodle_url('/mod/assign/overrideedit.php', ['cmid' => $this->currenturl->get_param('cmid'), 'action' => $action])
            ],
            'urlselect' => $urlselect->export_for_template($output)
        ];
        return $data;
    }

}
