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
 * Privacy tests for core_comment.
 *
 * @package    core_comment
 * @category   test
 * @copyright  2018 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/comment/locallib.php');
require_once($CFG->dirroot . '/comment/lib.php');

use \core_privacy\phpunit\provider_testcase;

/**
 * Unit tests for comment/classes/privacy/policy
 *
 * @copyright  2018 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_comment_privacy_testcase extends provider_testcase {

    /**
     * Check the exporting of comments for a user id in a context.
     */
    public function test_export_comments() {
        $this->resetAfterTest(true);
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

        // Comment on course page.
        $args = new stdClass;
        $args->context = $context;
        $args->course = $course;
        $args->area = 'page_comments';
        $args->itemid = 0;
        $args->component = 'block_comments';
        $comment = new comment($args);
        $comment->set_post_permission(true);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Add comments.
        $firstcomment = 'This is the first comment';
        $this->setUser($user1);
        $comment->add($firstcomment);
        $secondcomment = 'From the second user';
        $this->setUser($user2);
        $comment->add($secondcomment);

        $writer = \core_privacy\request\writer::with_context($context);
        // Retrieve comments only for this user.
        \core_comment\privacy\provider::export_comments($context, $args->component, $args->area, $args->itemid, [], $user1->id);

        $exportedcomments = (array)$writer->get_data([get_string('commentsubcontext', 'core_comment')]);
        // There is only one comment made by this user.
        $this->assertCount(1, $exportedcomments);
        $this->assertContains($firstcomment, $exportedcomments[0]->content);
        // Retrieve the whole conversation.
        \core_comment\privacy\provider::export_comments($context, $args->component, $args->area, $args->itemid, []);
        $exportedcomments = (array)$writer->get_data([get_string('commentsubcontext', 'core_comment')]);
        // The whole conversation is two comments.
        $this->assertCount(2, $exportedcomments);
        $this->assertContains($firstcomment, $exportedcomments[0]->content);
        $this->assertContains($secondcomment, $exportedcomments[1]->content);
    }
}
