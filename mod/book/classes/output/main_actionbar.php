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

use templatable;
use renderable;
use moodle_url;

class main_actionbar implements templatable, renderable {

    private $cmid;
    private $chapters;
    private $chapter;
    private $book;

    public function __construct($cmid, $chapters, $chapter, $book) {
        $this->cmid = $cmid;
        $this->chapters = $chapters;
        $this->chapter = $chapter;
        $this->book = $book;
    }

    public function get_next_page() {
        $nextpageid = $this->chapter->pagenum + 1;
        return $this->get_page($nextpageid);
    }

    public function get_previous_page() {
        $nextpageid = $this->chapter->pagenum - 1;
        return $this->get_page($nextpageid);
    }

    private function get_page($id) {
        foreach ($this->chapters as $chapter) {
            if ($chapter->pagenum == $id) {
                return $chapter;
            }
        }
        return null;
    }

    public function export_for_template(\renderer_base $output) {
        // TODO Add a choice of how the button is displayed.
        $next = $this->get_next_page();
        $previous = $this->get_previous_page();

        $context = \context_module::instance($this->cmid);
        $data = [];

        if ($next) {
            $chaptertitle = book_get_chapter_title($next->id, $this->chapters, $this->book, $context);
            $nextdata = [
                'title' => get_string('navnexttitle', 'mod_book', $chaptertitle),
                'url' => new moodle_url('/mod/book/view.php', ['id' => $this->cmid, 'chapterid' => $next->id])
            ];
            $data['next'] = $nextdata;
        }
        if ($previous) {
            $chaptertitle = book_get_chapter_title($previous->id, $this->chapters, $this->book, $context);
            $previousdata = [
                'title' => get_string('navprevtitle', 'mod_book', $chaptertitle),
                'url' => new moodle_url('/mod/book/view.php', ['id' => $this->cmid, 'chapterid' => $previous->id])
            ];
            $data['previous'] = $previousdata;
        }

        // print_object($data);
        return $data;
    }

}
