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
 * This file contains the core_privacy\request helper.
 *
 * @package core_privacy
 * @copyright 2018 Andrew Nicols <andrew@nicols.co.uk>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\request;

use \core_privacy\request\writer;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/modinfolib.php');
require_once($CFG->dirroot . '/course/modlib.php');

/**
 * The core_privacy\request\helper class with useful shared functionality.
 *
 * @package core_privacy
 * @copyright 2018 Andrew Nicols <andrew@nicols.co.uk>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Get all general data for this context.
     *
     * @param   \context        $context The context to retrieve data for.
     * @param   \stdClass       $user The user being written.
     * @return  \stdClass
     */
    public static function get_context_data(\context $context, \stdClass $user) : \stdClass {
        global $DB;

        $basedata = (object) [];
        if ($context instanceof \context_module) {
            return static::get_context_module_data($context, $user);
        }
        if ($context instanceof \context_block) {
            return static::get_context_block_data($context, $user);
        }

        return $basedata;
    }

    /**
     * Get all general data for the activity module at this context.
     *
     * @param   \context_module $context The context to retrieve data for.
     * @param   \stdClass       $user The user being written.
     * @return  \stdClass
     */
    protected static function get_context_module_data(\context_module $context, \stdClass $user) : \stdClass {
        global $DB;

        $coursecontext = $context->get_course_context();
        $modinfo = get_fast_modinfo($coursecontext->instanceid);
        $cm = $modinfo->cms[$context->instanceid];
        $component = "mod_{$cm->modname}";
        $course = $cm->get_course();
        $moduledata = $DB->get_record($cm->modname, ['id' => $cm->instance]);

        $basedata = (object) [
            'name' => $cm->get_formatted_name(),
        ];

        if (plugin_supports('mod', $cm->modname, FEATURE_MOD_INTRO, true)) {
            $intro = $moduledata->intro;

            $intro = writer::with_context($context)
                ->rewrite_pluginfile_urls([], $component, 'intro', 0, $intro);

            $options = [
                'noclean' => true,
                'para' => false,
                'context' => $context,
                'overflowdiv' => true,
            ];
            $basedata->intro = format_text($intro, $moduledata->introformat, $options);
        }

        // Completion tracking.
        $completioninfo = new \completion_info($course);
        $completion = $completioninfo->is_enabled($cm);
        if ($completion != COMPLETION_TRACKING_NONE) {
            $completiondata = $completioninfo->get_data($cm, true, $user->id);
            $basedata->completion = (object) [
                'state' => $completiondata->completionstate,
            ];
        }

        return $basedata;
    }

    /**
     * Get all general data for the block at this context.
     *
     * @param   \context_block $context The context to retrieve data for.
     * @param   \stdClass $user The user being written.
     * @return  \stdClass General data about this block instance.
     */
    protected static function get_context_block_data(\context_block $context, \stdClass $user) : \stdClass {
        global $DB;

        $block = $DB->get_record('block_instances', ['id' => $context->instanceid]);

        $basedata = (object) [
            'blocktype' => get_string('pluginname', 'block_' . $block->blockname)
        ];

        return $basedata;
    }

    /**
     * Export all files for this context.
     *
     * @param   \context        $context The context to export files for.
     * @param   \stdClass       $user The user being written.
     * @return  \stdClass
     */
    public static function export_context_files(\context $context, \stdClass $user) {
        if ($context instanceof \context_module) {
            return static::export_context_module_files($context, $user);
        }
    }

    /**
     * Get all general data for the activity module at this context.
     *
     * @param   \context_module $context The context to retrieve data for.
     * @param   \stdClass       $user The user being written.
     * @return  \stdClass
     */
    protected static function export_context_module_files(\context_module $context, \stdClass $user) {
        $coursecontext = $context->get_course_context();
        $modinfo = get_fast_modinfo($coursecontext->instanceid);
        $cm = $modinfo->cms[$context->instanceid];
        $component = "mod_{$cm->modname}";

        writer::with_context($context)
            // Export the files for the intro.
            ->export_area_files([], $component, 'intro', 0);
    }
}
