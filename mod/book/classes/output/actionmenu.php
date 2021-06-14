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
 * @package   mod_book
 * @copyright 2021 Adrian Greeve <adrian@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_book\output;

use moodle_url;

class actionbar {

    private $cmid;
    private $currenturl;
    private $chapterid;
    private $nextchapterid;
    private $previouschapterid;

    public function __construct(int $cmid, moodle_url $pageurl, int $chapterid, int $nextchapterid = null, int $previouschapterid = null) {
        $this->cmid = $cmid;
        $this->currenturl = $pageurl;
        $this->chapterid = $chapterid;
        $this->nextchapterid = $nextchapterid;
        $this->previouschapterid = $previouschapterid;
    }

    public function get_main_actionbar() {
        global $PAGE;




    }


}
