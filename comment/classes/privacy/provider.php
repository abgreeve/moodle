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
 * @package    core_comment
 * @copyright  2018 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_comment\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\metadata\item_collection;
use \core_privacy\metadata\provider as metadataprovider;
use \core_privacy\request\resultset;
use \core_privacy\request\subsystem\plugin_provider as subsystemprovider;
use \core_privacy\request\writer;
use \lang_string;

/**
 * Privacy class for requesting user data.
 *
 * @package    core_comment
 * @copyright  2018 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements metadataprovider, subsystemprovider {

    /**
     * Returns meta data about this system.
     *
     * @return item_collection A listing of user data stored through this system.
     */
    public static function get_metadata(item_collection $itemcollection) : item_collection {
        $comments = [
                'content' => 'commenttextpurpose',
                'userid' => 'useridpurpose',
                'timecreated' => 'timecreatedcommentpurpose'
        ];
        $itemcollection->add_datastore('core_comment', $comments, 'commenttablepurpose');
        return $itemcollection;
    }

    /**
     * Writes user data to the writer for the user to download.
     *
     * @param  array  $contexts Contexts to run through and return data.
     * @param  string $component The component that is calling this function
     * @param  string $commentarea The comment area related to the component
     * @param  int    $itemid An identifier for a group of comments
     * @param  array  $path The directory path to store these comments.
     * @param  int    $onlyforthisuser  Only return the comments this user made.
     */
    public static function store_comments($context, $component, $commentarea, $itemid, $path, $onlyforthisuser = null) {

        $data = new \stdClass;
        $data->context   = $context;
        $data->area      = $commentarea;
        $data->itemid    = $itemid;
        $data->component = $component;

        $commentobject = new \comment($data);
        $commentobject->set_view_permission(true);
        $comments = $commentobject->get_comments(0);
        $path[] = new \lang_string('commentpath');
        if ($onlyforthisuser) {
            $comments = array_filter($comments, function($comment) use ($onlyforthisuser) {
                if ($comment->userid == $onlyforthisuser) {
                    return $comment;
                }
            });
        }
        writer::with_context($context)
                ->store_data($path, (object)$comments);
    }
}
