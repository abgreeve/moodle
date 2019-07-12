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
 * This file contains the Activity picker block.
 *
 * @package    block_activity_picker
 * @copyright  2019 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php');

class block_activity_picker extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_activity_picker');
    }

    function get_content() {
        global $CFG, $DB, $OUTPUT;

        $this->content = new stdClass();
        $this->content->items = [];
        $this->content->icons = [];
        $this->content->footer = '';

        $course = $this->page->course;
        $this->page->requires->js_call_amd('block_activity_picker/dragndrop', 'init'); //,$arguments.
        // Get activities and resources.
        $modules = $DB->get_records('modules');

        // core_collator::asort($modfullnames);

        foreach ($modules as $module) {
            if ($module->name === 'resources') {
                $icon = $OUTPUT->pix_icon('icon', '', 'mod_page', array('class' => 'icon'));
                $this->content->items[] = '<a href="'.$CFG->wwwroot.'/course/resources.php?id='.$course->id.'">'.$icon.$module->name.'</a>';
            } else {
                $icon = $OUTPUT->image_icon('icon', get_string('pluginname', $module->name), $module->name);
                $thing = '<div class="module_item" draggable="true" data-module-type="'.$module->name.'">' . $icon . $module->name . '</div>';
                $this->content->items[] = $thing;
            }
        }
        return $this->content;
    }

    /**
     * Returns the role that best describes this blocks contents.
     *
     * This returns 'navigation' as the blocks contents is a list of links to activities and resources.
     *
     * @return string 'navigation'
     */
    public function get_aria_role() {
        return 'navigation';
    }

    function applicable_formats() {
        return array('all' => true, 'mod' => false, 'my' => false, 'admin' => false,
                     'tag' => false);
    }
}


