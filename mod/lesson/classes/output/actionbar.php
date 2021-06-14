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

class actionbar {

    private $cmid;
    private $currenturl;

    public function __construct($cmid, moodle_url $pageurl) {
        $this->cmid = $cmid;
        $this->currenturl = $pageurl;
    }

    private function create_select_menu() {
        $previewlink = new moodle_url('/mod/lesson/view.php', ['id' => $this->cmid]);
        $editlink = new moodle_url('/mod/lesson/edit.php', ['id' => $this->cmid, 'mode' => 'collapsed']);
        $fulleditlink = new moodle_url('/mod/lesson/edit.php', ['id' => $this->cmid, 'mode' => 'full']);
        $overviewlink = new moodle_url('/mod/lesson/report.php', ['id' => $this->cmid, 'action' => 'reportoverview']);
        $fulllink = new moodle_url('/mod/lesson/report.php', ['id' => $this->cmid, 'action' => 'reportdetail']);
        $essaylink = new moodle_url('/mod/lesson/essay.php', ['id' => $this->cmid]);
        $menu = [
            $previewlink->out(false) => get_string('preview', 'mod_lesson'),
            [
                get_string('edit', 'mod_lesson') => [
                    $editlink->out(false) => get_string('collapsed', 'mod_lesson'),
                    $fulleditlink->out(false) => get_string('full', 'mod_lesson')
                ]
            ],
            [
                get_string('reports', 'mod_lesson') => [
                    $overviewlink->out(false) => get_string('overview', 'mod_lesson'),
                    $fulllink->out(false) => get_string('detailedstats', 'mod_lesson')
                ]
            ],
            $essaylink->out(false) => get_string('manualgrading', 'mod_lesson')
        ];

        $urlselect = new \url_select($menu, $this->currenturl->out(false), null, 'lessonselectthing');
        return $urlselect;
    }

    private function create_override_select_menu() {
        $userlink = new moodle_url('/mod/lesson/overrides.php', ['cmid' => $this->cmid, 'mode' => 'user']);
        $grouplink = new moodle_url('/mod/lesson/overrides.php', ['cmid' => $this->cmid, 'mode' => 'group']);
        $menu = [
            $userlink->out(false) => get_string('useroverrides', 'mod_lesson'),
            $grouplink->out(false) => get_string('groupoverrides', 'mod_lesson'),
        ];
        return new \url_select($menu, $this->currenturl->out(false), null, 'lessonselectthing');
    }


    public function get_main_select_menu() {
        global $OUTPUT;
        $urlselect = $this->create_select_menu();
        return $OUTPUT->render($urlselect);
    }

    public function get_override_bar() {
        global $PAGE;

        $renderer = $PAGE->get_renderer('mod_lesson');
        $urlselect = $this->create_override_select_menu();

        $overrideclass = new \mod_lesson\output\overridething($urlselect, $this->currenturl);
        return $renderer->override_actionbar($overrideclass);
    }
}
