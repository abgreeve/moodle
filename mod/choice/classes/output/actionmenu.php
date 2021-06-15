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
 * @package   mod_choice
 * @copyright 2021 Adrian Greeve <adrian@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_choice\output;

use templatable;
use renderable;
use moodle_url;

class actionmenu implements templatable, renderable {

    private $cmid;
    private $allresponses;


    public function __construct($cmid, $allresponses) {
        $this->cmid = $cmid;
        $this->allresponses = $allresponses;
    }

    private function count_responses() {
        $userschosen = array();
        foreach($this->allresponses as $optionid => $userlist) {
            if ($optionid) {
                $userschosen = array_merge($userschosen, array_keys($userlist));
            }
        }
        return count(array_unique($userschosen));
    }

    public function export_for_template(\renderer_base $output) {
        return [
            'responsecount' => $this->count_responses(),
            'link' => new moodle_url('/mod/choice/report.php', ['id' => $this->cmid])
        ];
    }

}
