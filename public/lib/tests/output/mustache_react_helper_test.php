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

declare(strict_types=1);

namespace core\output;

use Mustache_LambdaHelper;

/**
 * Unit tests for mustache_react_helper.
 *
 * @package    core
 * @copyright  Meirza <meirza.arson@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \core\output\mustache_react_helper
 */
class mustache_react_helper_test extends \advanced_testcase {

    /** @var Mustache_LambdaHelper|null Helper to handle lambda rendering. */
    private $lambdahelper = null;
    /** @var mustache_react_helper|null Instance of the React mustache helper under test. */
    private $helper = null;

    /**
     * Sets up the test environment before each test case is run.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->lambdahelper = new \Mustache_LambdaHelper(new \Mustache_Engine(), new \Mustache_Context());
        $this->helper = new mustache_react_helper();
    }

    /**
     * Cleans up the test environment after each test case has run.
     */
    public function tearDown(): void {
        $this->lambdahelper = null;
        $this->helper = null;
        parent::tearDown();
    }

    /**
     * Test basic component with props.
     */
    public function test_basic_component_with_props(): void {

        $input = '{"component":"@core/ds/button","props":{"label":"Save"}}';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString('data-react-component="@core/ds/button"', $output);
        $this->assertStringContainsString('data-react-props=\'{"label":"Save"}\'', $output);
        $this->assertStringStartsWith('<div', $output);
        $this->assertStringEndsWith('</div>', $output);
    }

    /**
     * Test component without props.
     */
    public function test_component_without_props(): void {

        $input = '{"component":"@core/ds/loader"}';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString('data-react-component="@core/ds/loader"', $output);
        $this->assertStringNotContainsString('data-react-props', $output);
    }

    /**
     * Test props without component.
     */
    public function test_props_without_component(): void {

        $input = '{"props":{"user":"John","role":"admin"}}';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString('data-react-props=\'{"user":"John","role":"admin"}\'', $output);
        $this->assertStringNotContainsString('data-react-component', $output);
    }

    /**
     * Test custom HTML attributes.
     */
    public function test_custom_attributes(): void {

        $input = '{"component":"@core/ds/modal","id":"test-modal","class":"large"}';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString('id="test-modal"', $output);
        $this->assertStringContainsString('class="large"', $output);
    }

    /**
     * Test boolean attributes.
     */
    public function test_boolean_attributes(): void {

        $input = '{"component":"@core/ds/input","disabled":true,"hidden":false}';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString(' disabled', $output);
        $this->assertStringNotContainsString('hidden', $output);
    }

    /**
     * Test array values in attributes.
     */
    public function test_array_attributes(): void {

        $input = '{"component":"@core/ds/chart","data-values":[10,20,30]}';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString('data-values="[10,20,30]"', $output);
    }

    /**
     * Test inner content.
     */
    public function test_inner_content(): void {

        $input = '{"component":"@core/ds/card"}<p>Loading...</p>';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString('<p>Loading...</p>', $output);
        $this->assertStringContainsString('data-react-component="@core/ds/card"', $output);
    }

    /**
     * Test multiline JSON with content.
     */
    public function test_multiline_json_with_content(): void {

        $input = '{
    "component": "@core/ds/modal",
    "props": {
    "title": "Confirm"
    }
    }
    <div class="skeleton"></div>';

        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString('data-react-component="@core/ds/modal"', $output);
        $this->assertStringContainsString('<div class="skeleton"></div>', $output);
    }

    /**
     * Test trailing comma handling.
     */
    public function test_trailing_comma_auto_fix(): void {

        $input = '{"component":"@core/ds/button","class":"primary",}';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString('data-react-component="@core/ds/button"', $output);
        $this->assertStringContainsString('class="primary"', $output);
    }

    /**
     * Test nested JSON objects in props.
     */
    public function test_nested_props(): void {

        $input = '{"component":"@core/ds/form","props":{"user":{"name":"John","role":"admin"}}}';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString('data-react-component="@core/ds/form"', $output);
        $this->assertStringContainsString('"user":{"name":"John","role":"admin"}', $output);
    }

    /**
     * Test empty input.
     */
    public function test_empty_input(): void {

        $output = $this->helper->react('', $this->lambdahelper);
        $this->assertSame('', $output);
    }

    /**
     * Test invalid JSON with content fallback.
     */
    public function test_invalid_json_with_content(): void {
        $this->resetAfterTest();

        $input = '{invalid json}<p>Content</p>';
        $output = $this->helper->react($input, $this->lambdahelper);

        // Should still render div with content.
        $this->assertStringContainsString('<div><p>Content</p></div>', $output);

        // Assert debugging was called.
        $this->assertDebuggingCalled();
    }

    /**
     * Test invalid JSON without content.
     */
    public function test_invalid_json_without_content(): void {
        $this->resetAfterTest();

        $input = '{invalid json}';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertSame('', $output);

        // Assert debugging was called.
        $this->assertDebuggingCalled();
    }

    /**
     * Test plain div without component or props.
     */
    public function test_plain_div(): void {

        $input = '{"id":"wrapper","class":"container"}<h1>Title</h1>';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString('id="wrapper"', $output);
        $this->assertStringContainsString('class="container"', $output);
        $this->assertStringContainsString('<h1>Title</h1>', $output);
        $this->assertStringNotContainsString('data-react-component', $output);
        $this->assertStringNotContainsString('data-react-props', $output);
    }

    /**
     * Test XSS protection in attributes.
     */
    public function test_xss_protection(): void {

        $input = '{"component":"@core/ds/button","class":"<script>alert(1)</script>"}';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    /**
     * Test data attributes with special characters.
     */
    public function test_data_attributes(): void {

        $input = '{"component":"@core/ds/button","data-course-id":"123","data-action":"save"}';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString('data-course-id="123"', $output);
        $this->assertStringContainsString('data-action="save"', $output);
    }

    /**
     * Test aria attributes for accessibility.
     */
    public function test_aria_attributes(): void {

        $input = '{"component":"@core/ds/modal","aria-label":"Dialog","role":"dialog"}';
        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString('aria-label="Dialog"', $output);
        $this->assertStringContainsString('role="dialog"', $output);
    }

    /**
     * Test complex real-world scenario.
     */
    public function test_complex_scenario(): void {

        $input = '{
    "component": "@core/ds/datatable",
    "props": {
    "columns": ["Name", "Email", "Role"],
    "data": [
    {"name": "John", "email": "john@example.com", "role": "admin"}
    ],
    "sortable": true
    },
    "id": "users-table",
    "class": "table-responsive",
    "data-page": "1",
    "aria-label": "Users table"
    }
    <div class="loading-spinner"></div>';

        $output = $this->helper->react($input, $this->lambdahelper);

        $this->assertStringContainsString('data-react-component="@core/ds/datatable"', $output);
        $this->assertStringContainsString('"columns":["Name","Email","Role"]', $output);
        $this->assertStringContainsString('id="users-table"', $output);
        $this->assertStringContainsString('class="table-responsive"', $output);
        $this->assertStringContainsString('data-page="1"', $output);
        $this->assertStringContainsString('aria-label="Users table"', $output);
        $this->assertStringContainsString('<div class="loading-spinner"></div>', $output);
    }
}
