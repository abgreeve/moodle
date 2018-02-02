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

namespace core_rating\privacy\request;

use \core_privacy\request\exporter;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem implementation for core_ratings.
 *
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\request\subsystem_provider {
    public static function store_user_data(int $userid, \context $context, exporter $exporter, $component, $ratingarea, $itemid, $subcontext = null) {
        global $DB;

        $sql = "SELECT
                    r.*
                  FROM {rating} r
                 WHERE r.component = :component
                   AND r.ratingarea = :ratingarea
                   AND r.itemid = :itemid";

        $params = [
            'component'     => $component,
            'ratingarea'    => $ratingarea,
            'itemid'        => $itemid,
        ];

        $ratings = $DB->get_records_sql($sql, $params);

        static::store_ratings($userid, $context, $exporter, $ratings, $subcontext);
    }

    public static function store_ratings(int $userid, \context $context, exporter $exporter, $ratings, $subcontext = null, $searchuser = null) {
        foreach ($ratings as $rating) {
            // Do tidyup work?
            \core_user\privacy\request\transformation::user($userid, $rating, ['userid']);
        }
        if ($ratings) {
            $data = json_encode($ratings);
            $exporter->store_custom_file($context, $subcontext, 'rating.json', $data);
        }
    }

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

        return [
            implode(', ', $select),
            $join,
            $params,
            $userwhere,
        ];
    }
}
