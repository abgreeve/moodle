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
 * Privacy Subsystem implementation for core_tag.
 *
 * @package    core_tag
 * @copyright  2018 Zig Tan <zig@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_tag\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem implementation for core_tag.
 *
 * @copyright  2018 Zig Tan <zig@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\metadata\provider, \core_privacy\request\subsystem\plugin_provider {

    /**
     * @inheritdoc
     */
    public static function get_metadata(\core_privacy\metadata\item_collection $items)  {
    }

    /**
     * Store all tags which match the specified component, itemtype, and itemid.
     *
     * @param   int         $userid The user whose information is to be stored
     * @param   array       $subcontext The subcontext within the context to store this information
     * @param   string      $component The component to fetch data from
     * @param   string      $itemtype The itemtype that the data was stored in within the component
     * @param   int         $itemid The itemid within that tag
     * @param   bool        $onlyuser Whether to only store ratings that the current user has made, or all tags
     */
    public static function store_item_tags(int $userid, \context $context, array $subcontext, string $component, string $itemtype, int $itemid, bool $onlyuser = false) {
        global $DB;

        $sql = "SELECT
                    t.*
                  FROM {tag} t 
            INNER JOIN {tag_instance} ti ON ti.tagid = t.id
                 WHERE ti.component = :component
                   AND ti.itemtype = :itemtype
                   AND ti.itemid = :itemid
                   ";

        if ($onlyuser) {
            $sql .= "AND ti.tiuserid = :userid";
        } else {
            $sql .= "AND (ti.tiuserid = 0 OR ti.tiuserid = :userid)";
        }

        $params = [
            'component' => $component,
            'itemtype' => $itemtype,
            'itemid' => $itemid,
            'userid' => $userid,
        ];

        $tags = $DB->get_records_sql($sql, $params);
        static::store_tag_list($context, $subcontext, $tags);
    }

    protected static function store_tag_list(\context $context, array $subcontext, $tags) {
        // TODO - do not include the user's details by userid due to bug with re-using existing tags by other users.
        if ($tags) {
            $data = json_encode($tags);
            $writer = \core_privacy\request\writer::with_context($context)
                        ->store_custom_file($subcontext, 'tags.json', $data);
        }
    }

}