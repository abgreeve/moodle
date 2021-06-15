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
 * Output the override actionbar for this activity.
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
use single_button;

/**
 * Output the override actionbar for this activity.
 *
 * @package   mod_assign
 * @copyright 2021 Adrian Greeve <adrian@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class override_actionmenu implements templatable, renderable {

    /** @var moodle_url The current url for this page. */
    protected $currenturl;
    /** @var cm_info course module information */
    protected $cm;

    /**
     * Constructor for this action menu.
     *
     * @param moodle_url $currenturl The current url for this page.
     * @param cm_info $cm course module information.
     */
    public function __construct(moodle_url $currenturl, \cm_info $cm) {
        $this->currenturl = $currenturl;
        $this->cm = $cm;
    }

    /**
     * Create a select menu for overrides.
     *
     * @return url_select A url select object.
     */
    protected function get_select_menu(): url_select {
        $userlink = new moodle_url('/mod/assign/overrides.php', ['cmid' => $this->cm->id, 'mode' => 'user']);
        $grouplink = new moodle_url('/mod/assign/overrides.php', ['cmid' => $this->cm->id, 'mode' => 'group']);
        $menu = [
            $userlink->out(false) => get_string('useroverrides', 'mod_assign'),
            $grouplink->out(false) => get_string('groupoverrides', 'mod_assign'),
        ];
        return new url_select($menu, $this->currenturl->out(false), null, 'assignselectthing');
    }

    /**
     * Whether to show groups or not.
     *
     * @return bool
     */
    protected function show_groups(): bool {
        $assigngroupmode = groups_get_activity_groupmode($this->cm);
        if ($assigngroupmode == NOGROUPS) {
            return false;
        }
        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $this->cm->context);
        $groups = $canaccessallgroups ? groups_get_all_groups($this->cm->course) : groups_get_activity_allowed_groups($this->cm);
        return !empty($groups);
    }

    /**
     * Data to be used in a template.
     *
     * @param \renderer_base $output renderer base output.
     * @return array The data to be used in a template.
     */
    public function export_for_template(\renderer_base $output): array {

        $type = $this->currenturl->get_param('mode');
        if ($type == 'user') {
            $text = get_string('addnewuseroverride', 'mod_assign');
        } else {
            $text = get_string('addnewgroupoverride', 'mod_assign');
        }
        $action = ($type == 'user') ? 'adduser' : 'addgroup';

        $params = ['cmid' => $this->currenturl->get_param('cmid'), 'action' => $action];
        $url = new moodle_url('/mod/assign/overrideedit.php', $params);

        $options = [];
        if ($action == 'addgroup' && !$this->show_groups()) {
            $options = ['disabled' => 'true'];
        }
        $overridebutton = new single_button($url, $text, 'post', true, $options);

        $urlselect = $this->get_select_menu();
        $data = [
            'addoverride' => $overridebutton->export_for_template($output),
            'urlselect' => $urlselect->export_for_template($output)
        ];
        return $data;
    }
}
