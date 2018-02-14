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
 * Helpers for the core_rating subsystem implementation of privacy.
 *
 * @package    core_rating
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_rating\phpunit;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\phpunit\content_writer;

global $CFG;

/**
 * Helpers for the core_rating subsystem implementation of privacy.
 *
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait privacy_helper {
    /**
     * Fetch all ratings on a subcontext.
     *
     * @param   content_writer      $writer     The contextualised writer to fetch ratings for.
     * @param   array               $subcontext The subcontext path to check.
     * @return  array
     */
    protected function get_ratings_on_subcontext(\context $context, array $subcontext) {
        $writer = \core_privacy\request\writer::with_context($context);
        $content = $writer->get_custom_file($subcontext, 'rating.json');

        return json_decode($content);
    }

    /**
     * Check that all included ratings belong to the specified user.
     *
     * @param   content_writer      $writer     The contextualised writer to fetch ratings for.
     * @param   int                 $userid     The ID of the user being rated.
     * @param   array               $subcontext The subcontext path to check.
     */
    protected function assert_all_own_ratings_on_context(int $userid, \context $context, array $subcontext, $component, $ratingarea, $itemid) {
        $writer = \core_privacy\request\writer::with_context($context);
        $rm = new \rating_manager();
        $dbratings = $rm->get_all_ratings_for_item((object) [
            'context' => $context,
            'component' => $component,
            'ratingarea' => $ratingarea,
            'itemid' => $itemid,
        ]);

        $exportedratings = $this->get_ratings_on_subcontext($context, $subcontext);

        foreach ($exportedratings as $rating) {
            $ratingid = $rating->id;
            $this->assertTrue(isset($dbratings[$ratingid]));
            $this->assertEquals($user, $rating->author);
            $this->assert_rating_matches($userid, $dbratings[$ratingid], $rating);
            $ratingid = $rating->id;
        }

        foreach ($dbratings as $rating) {
            if ($rating->userid == $userid) {
                $this->assertEquals($rating->id, $ratingid);
            }
        }
    }

    /**
     * Check that all included ratings are valid. They may belong to any user.
     *
     * @param   content_writer      $writer     The contextualised writer to fetch ratings for.
     * @param   int                 $userid     The ID of the user being rated.
     * @param   array               $subcontext The subcontext path to check.
     */
    protected function assert_all_ratings_on_context(int $userid, \context $context, array $subcontext, $component, $ratingarea, $itemid) {
        $writer = \core_privacy\request\writer::with_context($context);
        $rm = new \rating_manager();
        $dbratings = $rm->get_all_ratings_for_item((object) [
            'context' => $context,
            'component' => $component,
            'ratingarea' => $ratingarea,
            'itemid' => $itemid,
        ]);

        $exportedratings = $this->get_ratings_on_subcontext($context, $subcontext);

        foreach ($exportedratings as $rating) {
            $ratingid = $rating->id;
            $this->assertTrue(isset($dbratings[$ratingid]));
            $this->assert_rating_matches($userid, $dbratings[$ratingid], $rating);
        }

        foreach ($dbratings as $rating) {
            $this->assertTrue(isset($exportedratings->{$rating->id}));
        }
    }

    /**
     * Assert that the rating matches.
     */
    protected function assert_rating_matches(int $userid, $expected, $stored) {
        $this->assertEquals($expected->rating, $stored->rating);
        $this->assertEquals($expected->userid, $stored->author);
    }
}
