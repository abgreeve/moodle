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
 * Privacy Subsystem implementation for core_ratings.
 *
 * @package    core_ratings
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_rating\privacy;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/rating/lib.php');

/**
 * Privacy Subsystem implementation for core_ratings.
 *
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\request\subsystem\plugin_provider {

    /**
     * Store all ratings which match the specified component, areaid, and itemid.
     *
     * If requesting ratings for a users own content, and you wish to include all ratings of that content, specify $onlyuser as false.
     *
     * When requesting ratings for another users content, you should only export the ratings that the specified user made themselves.
     *
     * @param   int         $userid The user whose information is to be stored
     * @param   array       $subcontext The subcontext within the context to store this information
     * @param   string      $component The component to fetch data from
     * @param   string      $ratingarea The ratingarea that the data was stored in within the component
     * @param   int         $itemid The itemid within that ratingarea
     * @param   bool        $onlyuser Whether to only store ratings that the current user has made, or all ratings
     */
    public static function store_area_ratings(int $userid, \context $context, array $subcontext, string $component, string $ratingarea, int $itemid, bool $onlyuser = true) {
        global $DB;

        $rm = new \rating_manager();
        $ratings = $rm->get_all_ratings_for_item((object) [
            'context' => $context,
            'component' => $component,
            'ratingarea' => $ratingarea,
            'itemid' => $itemid,
        ]);

        if ($onlyuser) {
            $ratings = array_filter($ratings, function($rating) use ($userid){
                return ($rating->userid == $userid);
            });
        }

        if (empty($ratings)) {
            return;
        }

        $tostore = array_map(function($rating) {
            return (object) [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'author' => $rating->userid,
            ];
        }, $ratings);

        $writer = \core_privacy\request\writer::with_context($context)
            ->store_related_data($subcontext, 'rating', $tostore);
    }

    /**
     * Get the SQL required to find all submission items where this user has had any involvements.
     *
     * @param   int           $userid       The user to search.
     * @return  \stdClass

     */
    public static function get_sql_join($alias, $component, $ratingarea, $itemidjoin, $userid) {
        static $count = 0;
        $count++;

        $select = [
            "{$alias}.itemid        AS {$alias}_itemid",
            "{$alias}.scaleid       AS {$alias}_scaleid",
            "{$alias}.rating        AS {$alias}_rating",
            "{$alias}.userid        AS {$alias}_userid",
            "{$alias}.timecreated   AS {$alias}_timecreated",
            "{$alias}.timemodified  AS {$alias}_timemodified",
        ];
        $join = "LEFT JOIN {rating} {$alias} ON ";
        $join .= "{$alias}.component = :ratingcomponent{$count} AND ";
        $join .= "{$alias}.ratingarea = :ratingarea{$count} AND ";
        $join .= "{$alias}.itemid = {$itemidjoin}";

        $userwhere = "{$alias}.userid = :ratinguserid{$count}";

        $params = [
            'ratingcomponent' . $count  => $component,
            'ratingarea' . $count       => $ratingarea,
            'ratinguserid' . $count     => $userid,
        ];

        $return = (object) [
            'select' => implode(', ', $select),
            'join' => $join,
            'params' => $params,
            'userwhere' => $userwhere,
        ];
        return $return;
    }
}
