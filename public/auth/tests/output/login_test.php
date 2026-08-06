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

namespace core_auth\output;

/**
 * Unit tests for the legacy login renderable.
 *
 * The ordinary Moodle login page continues to use this renderable, the
 * core_renderer::render_login() renderer method, and the core/loginform Mustache template.
 * The React login_form renderable, introduced for the OAuth login flow in MDL-88472, is a
 * separate, independently renderable, code path.
 *
 * @package    core_auth
 * @copyright  2026 Moodle Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(login::class)]
final class login_test extends \advanced_testcase {
    #[\Override]
    protected function setUp(): void {
        parent::setUp();

        // The login renderable's constructor reads the global $PAGE/$OUTPUT directly (matching
        // how login/index.php uses it, after $OUTPUT->header() has already forced it to
        // initialise). Force that same initialisation here so it is not still the bootstrapping
        // stand-in renderer when the tests construct a login renderable.
        global $PAGE, $OUTPUT;
        $PAGE->set_url('/login/index.php');
        $PAGE->set_context(\context_system::instance());
        $OUTPUT = $PAGE->get_renderer('core');
    }

    /**
     * Export a login renderable to its template context data.
     *
     * @param login $loginform
     * @return \stdClass
     */
    protected function export(login $loginform): \stdClass {
        global $OUTPUT;

        return $loginform->export_for_template($OUTPUT);
    }

    /**
     * The username used to construct the form is passed through to the template context.
     */
    public function test_username_is_prefilled(): void {
        $this->resetAfterTest();

        $loginform = new login([], 'someuser');

        $this->assertSame('someuser', $this->export($loginform)->username);
    }

    /**
     * A generic error message set via set_error() is passed through to the template context.
     */
    public function test_set_error_generic_message(): void {
        $this->resetAfterTest();

        $loginform = new login([]);
        // Error code 1 (cookies not enabled) does not trigger the AUTH_LOGIN_FAILED generic wording.
        $loginform->set_error('Something went wrong', 1);

        $this->assertSame('Something went wrong', $this->export($loginform)->error);
    }

    /**
     * For AUTH_LOGIN_FAILED, a generic error/title is shown regardless of the message passed in,
     * so as not to reveal whether the username or the password was incorrect.
     */
    public function test_set_error_invalid_login_uses_generic_wording(): void {
        $this->resetAfterTest();

        $loginform = new login([]);
        $loginform->set_error('some specific reason', AUTH_LOGIN_FAILED);

        $data = $this->export($loginform);
        $this->assertSame(get_string('logininvalidlogindetail'), $data->error);
        $this->assertSame(get_string('logininvalidlogintitle'), $data->errortitle);
    }

    /**
     * An info message set via set_info() is passed through to the template context.
     */
    public function test_set_info(): void {
        $this->resetAfterTest();

        $loginform = new login([]);
        $loginform->set_info('Please check your email.');

        $this->assertSame('Please check your email.', $this->export($loginform)->info);
    }

    /**
     * The ordinary login page renders using the legacy core/loginform Mustache template, and not
     * the React component placeholder used by the OAuth login flow's login_form renderable.
     */
    public function test_render_uses_legacy_mustache_template(): void {
        global $OUTPUT;

        $this->resetAfterTest();

        $loginform = new login([]);
        $loginform->set_error('');
        $loginform->set_info('');

        $html = $OUTPUT->render($loginform);

        $this->assertStringContainsString('class="loginform"', $html);
        $this->assertStringNotContainsString('data-react-component', $html);
    }

    /**
     * A theme's render_login() override continues to be invoked for the legacy login renderable,
     * exactly as it was prior to the introduction of the React login_form renderable. Existing
     * themes do not need to implement render_login_form() or call get_legacy_login_form() to
     * retain their current login behaviour.
     */
    public function test_render_login_theme_override_is_invoked(): void {
        $this->resetAfterTest();

        $loginform = new login([]);
        $loginform->set_error('');
        $loginform->set_info('');

        $renderer = new class (new \moodle_page(), 'general') extends \core_renderer {
            // phpcs:ignore moodle.NamingConventions.ValidFunctionName.LowercaseMethod, moodle.Commenting.MissingDocblock.MissingTestcaseMethodDescription
            public function render_login(login $form): string {
                return 'rendered by theme override';
            }
        };

        $this->assertSame('rendered by theme override', $renderer->render($loginform));
    }
}
