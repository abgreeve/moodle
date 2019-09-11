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
 * Testing the H5PFrameworkInterface interface implementation.
 *
 * @package    core_h5p
 * @category   test
 * @copyright  2019 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use \core_h5p\framework;

defined('MOODLE_INTERNAL') || die();

/**
 *
 * Test class covering the H5PFrameworkInterface interface implementation.
 *
 * @package    core_h5p
 * @copyright  2019 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class framework_testcase extends advanced_testcase {

    public function test_getPlatformInfo() {
        global $CFG;

        $this->resetAfterTest();

        $CFG->version = "2019083000.05";

        $interface = framework::instance('interface');
        $platforminfo = $interface->getPlatformInfo();

        $expected = array(
            'name' => 'Moodle',
            'version' => '2019083000.05',
            'h5pVersion' => '2019083000.05'
        );

        $this->assertEquals($expected, $platforminfo);
    }

    public function test_fetchExternalData() {
//        $url = "https://h5p.org/sites/default/files/h5p/exports/arithmetic-quiz-22-57860.h5p";
//      //  $url = "https://example.com/download/trt.h5p";
//
//        $interface = framework::instance('interface');
//        print_r($interface->fetchExternalData($url, null, true, 'dsadsadas'));
    }

    public function test_setErrorMessage() {
        $message = "Error message";
        $code = '404';

        $interface = framework::instance('interface');
        // Set an error message.
        $interface->setErrorMessage($message, $code);

        // Get the error messages.
        $errormessages = framework::messages('error');

        $expected = new stdClass();
        $expected->code = 404;
        $expected->message = 'Error message';

        $this->assertEquals($expected, $errormessages[0]);
    }

    public function test_setInfoMessage() {
        $message = "Info message";

        $interface = framework::instance('interface');
        // Set an info message.
        $interface->setInfoMessage($message);

        // Get the info messages.
        $infomessages = framework::messages('info');

        $expected = 'Info message';

        $this->assertEquals($expected, $infomessages[0]);
    }

    public function test_create() {
        global $DB;

        $this->resetAfterTest();

        $this->save_h5p();

        $libraries = $DB->get_records('h5p_libraries');

        print_r($libraries);

    }

    private function save_h5p() {
        global $CFG;

        $url = "https://h5p.org/sites/default/files/h5p/exports/arithmetic-quiz-22-57860.h5p";
        $storage = framework::instance('storage');
        $interface = framework::instance('interface');
        $validator = framework::instance('validator');

        // Add file so that core framework can find it.
        $path = $CFG->tempdir . uniqid('/h5p-');
        $interface->getUploadedH5pFolderPath($path);
        $path .= '.h5p';
        $interface->getUploadedH5pPath($path);


        $response = download_file_content($url, null, null, true, 300, 20,
            false, $path);

        $this->assertFileExists($path);
        //
        if($validator->isValidPackage(false, true)) {
            $storage->savePackage(null, null, false);
        }

    }


}