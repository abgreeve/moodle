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
 * Privacy class for requesting user data.
 *
 * @package    core_plagiarism
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_plagiarism\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Provider for the plagiarism API.
 *
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // The Plagiarism subsystem does not store any data itself.
    // It has no database tables, and it purely acts as a conduit to the various plagiarism plugins.
    \core_privacy\metadata\null_provider,

    // The Plagiarism subsystem will be called by other components and will forward requests to each plagiarism plugin implementing its APIs.
    \core_privacy\request\subsystem\plugin_provider
{

    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return  string
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }

    /**
     * Export all plagiarism data from each plagiarism plugin for the specified userid and context.
     *
     * @param   int         $userid The user to export.
     * @param   \context    $context The context to export.
     * @param   array       $subcontext The subcontext within the context to export this information to.
     * @param   array       $linkarray The weird and wonderful link array used to display information for a specific item
     */
    public static function export_plagiarism_user_data(int $userid, \context $context, array $subcontext, array $linkarray) {
        // Note: Even if plagiarism is _now_ disabled, there may be legacy data to export.
        $plugins = \core_component::get_plugin_list('plagiarism');
        foreach (array_keys($plugins) as $plugin) {
            $component = "plagiarism_{$plugin}";
            $classname = manager::get_provider_classname_for_component($component);
            if (static::provider_implements($classname, plagiarism_provider::class)) {
                // This plagiarism plugin implements the plagiarism_provider.
                $classname::export_plagiarism_user_data($userid, $context, $subcontext, $linkarray);
            }
        }
    }

    /**
     * Checks whether the component's provider class implements the specified interface.
     * This can either be implemented directly, or by implementing a descendant (extension) of the specified interface.
     *
     * @param string $component the frankenstyle component name.
     * @param string $interface the name of the interface we want to check.
     * @return bool True if an implementation was found, false otherwise.
     */
    protected static function component_implements(string $providerclass, string $interface) : bool {
        if (class_exists($providerclass)) {
            $rc = new \ReflectionClass($providerclass);
            return $rc->implementsInterface($interface);
        }

        return false;
    }

}
