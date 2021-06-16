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

namespace mod_data\output;

use moodle_url;

/**
 * Class responsible for generating the action bar elements in the database module pages.
 *
 * @package    mod_data
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class actionbar {

    /** @var int $id The database module id. */
    private $id;

    /** @var moodle_url $currenturl The URL of the current page. */
    private $currenturl;

    /**
     * The class constructor.
     *
     * @param int $id The database module id
     * @param \moodle_page $pageurl The URL of the current page
     */
    public function __construct(int $id, moodle_url $pageurl) {
        $this->id = $id;
        $this->currenturl = $pageurl;
    }

    /**
     * Generate the output for the field related action bar.
     *
     * @return string The HTML code for the action bar.
     */
    public function get_fields_action_bar(): string {
        global $OUTPUT;

        $createfieldlink = new moodle_url('/mod/data/field.php', ['d' => $this->id]);
        $presetslink = new moodle_url('/mod/data/preset.php', ['d' => $this->id]);

        $menu = [
            $createfieldlink->out(false) => get_string('createnewfields', 'mod_data'),
            $presetslink->out(false) => get_string('usepredefinedset', 'mod_data'),
        ];

        $urlselect = new \url_select($menu, $this->currenturl->out(false), null, 'fieldactionselect');

        return $OUTPUT->render($urlselect);
    }

    /**
     * Generate the output for the action selector.
     *
     * @param bool $hasentries Whether entries exist
     * @return string The HTML code for the action selector.
     */
    public function get_view_action_bar(bool $hasentries): string {
        global $PAGE;

        $viewlistlink = new \moodle_url('/mod/data/view.php', ['d' => $this->id]);
        $viewsinglelink = new \moodle_url('/mod/data/view.php', ['d' => $this->id, 'mode' => 'single']);

        $menu = [
            $viewlistlink->out(false) => get_string('listview', 'mod_data'),
            $viewsinglelink->out(false) => get_string('singleview', 'mod_data'),
        ];

        $activeurl = $this->currenturl;

        if ($this->currenturl->get_param('rid') || $this->currenturl->get_param('mode') == 'single') {
            $activeurl = $viewsinglelink;
        }

        $urlselect = new \url_select($menu, $activeurl->out(false), null, 'viewactionselect');
        $renderer = $PAGE->get_renderer('mod_data');
        $viewactionbar = new \mod_data\output\view_action_bar($this->id, $urlselect, $hasentries);

        return $renderer->render_view_action_bar($viewactionbar);
    }

    /**
     * Generate the output for the action selector.
     *
     * @return string The HTML code for the action selector.
     */
    public function get_templates_action_bar(): string {
        global $PAGE;

        $listtemplatelink = new moodle_url('/mod/data/templates.php', ['d' => $this->id,
            'mode' => 'listtemplate']);
        $singletemplatelink = new moodle_url('/mod/data/templates.php', ['d' => $this->id,
            'mode' => 'singletemplate']);
        $advancedsearchtemplatelink = new moodle_url('/mod/data/templates.php', ['d' => $this->id,
            'mode' => 'asearchtemplate']);
        $addtemplatelink = new moodle_url('/mod/data/templates.php', ['d' => $this->id, 'mode' => 'addtemplate']);
        $rsstemplatelink = new moodle_url('/mod/data/templates.php', ['d' => $this->id, 'mode' => 'rsstemplate']);
        $csstemplatelink = new moodle_url('/mod/data/templates.php', ['d' => $this->id, 'mode' => 'csstemplate']);
        $jstemplatelink = new moodle_url('/mod/data/templates.php', ['d' => $this->id, 'mode' => 'jstemplate']);

        $menu = [
            $listtemplatelink->out(false) => get_string('listtemplate', 'mod_data'),
            $singletemplatelink->out(false) => get_string('singletemplate', 'mod_data'),
            $advancedsearchtemplatelink->out(false) => get_string('asearchtemplate', 'mod_data'),
            $addtemplatelink->out(false) => get_string('addtemplate', 'mod_data'),
            $rsstemplatelink->out(false) => get_string('rsstemplate', 'mod_data'),
            $csstemplatelink->out(false) => get_string('csstemplate', 'mod_data'),
            $jstemplatelink->out(false) => get_string('jstemplate', 'mod_data'),
        ];

        $urlselect = new \url_select($menu, $this->currenturl->out(false), null, 'templatesactionselect');
        $renderer = $PAGE->get_renderer('mod_data');
        $singleviewactionbar = new \mod_data\output\templates_action_bar($urlselect);

        return $renderer->render_templates_action_bar($singleviewactionbar);
    }
}
