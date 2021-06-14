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
 * Output the action menu for this activity.
 *
 * @package   mod_book
 * @copyright 2021 Adrian Greeve <adrian@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_book\output;

use templatable;
use renderable;
use moodle_url;
use stdClass;

/**
 * Output the action menu for the book activity.
 *
 * @package   mod_book
 * @copyright 2021 Adrian Greeve <adrian@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main_action_menu implements templatable, renderable {

    /** @var int The course module ID. */
    protected $cmid;
    /** @var stdClass[] Chapters of the book. */
    protected $chapters;
    /** @var stdClass Current chapter of the book. */
    protected $chapter;
    /** @var stdClass The book object. */
    protected $book;

    /**
     * Constructor for this class.
     *
     * @param int      $cmid     The course module ID.
     * @param array    $chapters Chapters of this book.
     * @param stdClass $chapter  The current chapter.
     * @param stdClass $book     The book object.
     */
    public function __construct(int $cmid, array $chapters, stdClass $chapter, stdClass $book) {
        $this->cmid = $cmid;
        $this->chapters = $chapters;
        $this->chapter = $chapter;
        $this->book = $book;
    }

    /**
     * Get the next chapter in the book.
     *
     * @return ?stdClass The next chapter of the book.
     */
    public function get_next_chapter(): ?stdClass {
        $nextpageid = $this->chapter->pagenum + 1;
        return $this->get_chapter($nextpageid);
    }

    /**
     * Get the previous chapter in the book.
     *
     * @return ?stdClass The previous chapter of the book.
     */
    public function get_previous_chapter(): ?stdClass {
        $nextpageid = $this->chapter->pagenum - 1;
        return $this->get_chapter($nextpageid);
    }

    /**
     * Get the specific chapter of the book.
     *
     * @param int $id The chapter id to retrieve.
     * @return ?stdClass The requested chapter.
     */
    protected function get_chapter(int $id): ?stdClass {
        foreach ($this->chapters as $chapter) {
            if ($chapter->pagenum == $id) {
                return $chapter;
            }
        }
        return null;
    }

    /**
     * Exports the navigation buttons around the book.
     *
     * @param \renderer_base $output renderer base output.
     * @return array Data to render.
     */
    public function export_for_template(\renderer_base $output): array {
        $next = $this->get_next_chapter();
        $previous = $this->get_previous_chapter();

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

        return $data;
    }
}
