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
 * Contains class \core\output\language_menu
 *
 * @package    core
 * @category   output
 * @copyright  2021 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core\output;

class language_menu implements \renderable, \templatable {

    protected $page;

    protected $currentlang;

    protected $langs;

    public function __construct($page) {
        $this->page = $page;
        $this->currentlang = \current_language();
        $this->langs = \get_string_manager()->get_list_of_translations();
    }

    protected function show_language_menu() {
        global $CFG;

        if (empty($CFG->langmenu)) {
            return false;
        }

        if ($this->page->course != SITEID and !empty($this->page->course->lang)) {
            // do not show lang menu if language forced
            return false;
        }

        if (count($this->langs) < 2) {
            return false;
        }
        return true;
    }

    public function export_for_template(\renderer_base $output) {
        // Early return if a lang menu does not exists.
        if (!$this->show_language_menu()) {
            return [];
        }

        $nodes = [];
        $activelanguage = '';

        // Add the lang picker if needed.
        foreach ($this->langs as $langtype => $langname) {
            $isactive = $langtype == $this->currentlang;
            $node = [
                'title' => $langname,
                'text' => $langname,
                'link' => true,
                'isactive' => $isactive,
                'url' => $isactive ? new \moodle_url('#') : new \moodle_url($this->page->url, ['lang' => $langtype]),
            ];

            $nodes[] = $node;

            if ($isactive) {
                $activelanguage = $langname;
            }
        }

        return [
            'title' => $activelanguage,
            'items' => $nodes,
        ];
    }

    public function export_for_action_menu(\renderer_base $output) {
        $languagedata = $this->export_for_template($output);
        if (empty($languagedata)) {
            return [];
        }
        $langmenu = new \action_menu();
        $menuname = \get_string('language');
        if (!empty($languagedata['title'])) {
            $menuname = $languagedata['title'];
        }
        $langmenu->set_menu_trigger($menuname);
        foreach ($languagedata['items'] as $node) {
            $lang = new \action_menu_link_secondary($node['url'], null, $node['title'], ['data-lang' => $node['url']->get_param('lang')]);
            $langmenu->add($lang);
        }
        return $langmenu->export_for_template($output);
    }

    public function export_for_single_select(\renderer_base $output) {
        if (!$this->show_language_menu()) {
            return '';
        }
        $singleselect = new \single_select($this->page->url, 'lang', $this->langs, $this->currentlang, null);
        $singleselect->label = get_accesshide(\get_string('language'));
        $singleselect->class = 'langmenu';
        return $singleselect->export_for_template($output);
    }

}
