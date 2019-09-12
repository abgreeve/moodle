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

    public function test_loadLibraries() {
        $this->resetAfterTest();

        $this->save_h5p();

        $interface = framework::instance('interface');
        $libraries = $interface->loadLibraries();

        $this->assertNotEmpty($libraries);
        $libdata = new stdClass();
        $libdata->machine_name = 'Drop';
        $libdata->major_version = '1';
        $libdata->minor_version = '0';
        $libdata->patch_version = '2';

        $expected['Drop'][] = $libdata;

        $this->assertEquals($expected['Drop'], $libraries['Drop']);

    }

    public function test_getLibraryId() {
        $this->resetAfterTest();

        $this->save_h5p();

        $interface = framework::instance('interface');

        $libraryid = $interface->getLibraryId('Drop');

        $this->assertNotFalse($libraryid);
        $this->assertIsNumeric($libraryid);

        $libraryid = $interface->getLibraryId('NonExistant');

        $this->assertFalse($libraryid);
    }

    public function test_isPatchedLibrary() {
        $this->resetAfterTest();

        $this->save_h5p();

        $interface = framework::instance('interface');

        $library = array(
            'machineName' => 'Drop',
            'majorVersion' => '1',
            'minorVersion' => '0',
            'patchVersion' => '2'
        );
        $ispatched = $interface->isPatchedLibrary($library);
        $this->assertFalse($ispatched);

        $library['patchVersion'] = 3;
        $ispatched = $interface->isPatchedLibrary($library);
        $this->assertTrue($ispatched);
    }

    public function test_isInDevMode() {
        $interface = framework::instance('interface');
        $isdevmode = $interface->isInDevMode();
        $this->assertFalse($isdevmode);
    }

    public function test_mayUpdateLibraries() {
        $interface = framework::instance('interface');
        $mayupdatelib = $interface->mayUpdateLibraries();
        $this->assertTrue($mayupdatelib);
    }

    public function test_saveLibraryData() {
        global $DB;

        $this->resetAfterTest();

        $interface = framework::instance('interface');
        $librarydata = array(
            'title' => 'Title',
            'machineName' => 'Name',
            'majorVersion' => '1',
            'minorVersion' => '0',
            'patchVersion' => '2',
            'runnable' => 1,
            'fullscreen' => 1,
            'preloadedJs' => array(
                'path' => 'js/name.min.js'
            ),
            'preloadedCss' => array(
                'path' => 'css/name.css'
            ),
            'dropLibraryCss' => array(
                'machineName' => 'Name2'
            )
        );

        // Create new library.
        $interface->saveLibraryData($librarydata);
        $library = $DB->get_record('h5p_libraries', ['machinename' => $librarydata['machineName']]);

        $this->assertNotEmpty($library);
        $this->assertNotEmpty($librarydata['libraryId']);
        $this->assertEquals($librarydata['title'], $library->title);
        $this->assertEquals($librarydata['machineName'], $library->machinename);
        $this->assertEquals($librarydata['majorVersion'], $library->majorversion);
        $this->assertEquals($librarydata['minorVersion'], $library->minorversion);
        $this->assertEquals($librarydata['patchVersion'], $library->patchversion);
        $this->assertEquals($librarydata['preloadedJs']['path'], $library->preloadedjs);
        $this->assertEquals($librarydata['preloadedCss']['path'], $library->preloadedcss);
        $this->assertEquals($librarydata['dropLibraryCss']['machineName'], $library->droplibrarycss);

        $librarydata['machineName'] = 'Name2';
        $interface->saveLibraryData($librarydata, false);
        $library = $DB->get_record('h5p_libraries', ['machinename' => $librarydata['machineName']]);
        $this->assertEquals($librarydata['machineName'], $library->machinename);
    }

    public function test_insertContent() {
        global $DB;
        $this->resetAfterTest();

        $interface = framework::instance('interface');

        $content = array(
            'params' => '{"param1": "Test"}',
            'library' => array(
                'libraryId' => 1
            )
        );
        $contentid = $interface->insertContent($content);

        $dbcontent = $DB->get_record('h5p', ['id' => $contentid]);

        $this->assertNotEmpty($dbcontent);
        $this->assertEquals($content['params'], $dbcontent->jsoncontent);
        $this->assertEquals($content['library']['libraryId'], $dbcontent->mainlibraryid);
    }

    public function test_updateContent() {
        global $DB;
        $this->resetAfterTest();

        $content = array(
            'jsoncontent' => '{"param1": "Test"}',
            'mainlibraryid' => 1
        );

        $contentid = $DB->insert_record('h5p', $content);

        $content = array(
            'params' => '{"param2": "Test2"}',
            'library' => array(
                'libraryId' => 1
            )
        );
        $interface = framework::instance('interface');
        $content['id'] = $contentid;
        $interface->updateContent($content);

        $dbcontent = $DB->get_record('h5p', ['id' => $contentid]);

        $this->assertNotEmpty($dbcontent);
        $this->assertEquals($content['params'], $dbcontent->jsoncontent);
        $this->assertEquals($content['library']['libraryId'], $dbcontent->mainlibraryid);
    }

    public function test_saveLibraryDependencies() {
        global $DB;

        $this->resetAfterTest();

        $library = $this->create_library_record('Name', 'Title');
        $dependency1 = $this->create_library_record('Dependency name 1', 'Dependency title 1');
        $dependency2 = $this->create_library_record('Dependency name 2', 'Dependency title 2');

        $dependencies = array(
            array(
                'machineName' => $dependency1->machinename,
                'majorVersion' => $dependency1->majorversion,
                'minorVersion' => $dependency1->minorversion
            ),
            array(
                'machineName' => $dependency2->machinename,
                'majorVersion' => $dependency2->majorversion,
                'minorVersion' => $dependency2->minorversion
            ),
        );
        $interface = framework::instance('interface');
        $interface->saveLibraryDependencies($library->id, $dependencies, 'preloaded');

        $libdependencies = $DB->get_records('h5p_library_dependencies', ['libraryid' => $library->id]);
        $this->assertEquals(2, count($libdependencies));
        $this->assertEquals($dependency1->id, reset($libdependencies)->requiredlibraryid);
        $this->assertEquals($dependency2->id, end($libdependencies)->requiredlibraryid);
    }

    public function test_saveLibraryUsage() {
        global $DB;

        $this->resetAfterTest();

        $library = $this->create_library_record('Name', 'Title');
        $dependency1 = $this->create_library_record('Dependency name 1', 'Dependency title 1');
        $dependency2 = $this->create_library_record('Dependency name 2', 'Dependency title 2');

        $dependencies = array(
            array(
                'machineName' => $dependency1->machinename,
                'majorVersion' => $dependency1->majorversion,
                'minorVersion' => $dependency1->minorversion
            ),
            array(
                'machineName' => $dependency2->machinename,
                'majorVersion' => $dependency2->majorversion,
                'minorVersion' => $dependency2->minorversion
            ),
        );
        $interface = framework::instance('interface');
        $interface->saveLibraryDependencies($library->id, $dependencies, 'preloaded');

        $libdependencies = $DB->get_records('h5p_library_dependencies', ['libraryid' => $library->id]);
        $this->assertEquals(2, count($libdependencies));
        $this->assertEquals($dependency1->id, reset($libdependencies)->requiredlibraryid);
        $this->assertEquals($dependency2->id, end($libdependencies)->requiredlibraryid);
    }

//    public function test_create() {
//        global $DB;
//
//        $this->resetAfterTest();
//
//        $this->save_h5p();
//
//        $libraries = $DB->get_records('h5p_libraries');
//
//        print_r($libraries);
//    }

//    private function save_h5p() {
//        global $CFG;
//
//        $originalpath = "$CFG->dirroot/h5p/tests/packages/essay.h5p";
//        $storage = framework::instance('storage');
//        $interface = framework::instance('interface');
//        $validator = framework::instance('validator');
//
//        // Add file so that core framework can find it.
//        $path = $CFG->tempdir . uniqid('/h5p-');
//        $interface->getUploadedH5pFolderPath($path);
//        $path .= '.h5p';
//        $interface->getUploadedH5pPath($path);
//
//        copy($originalpath, $path);
//
//        $this->assertFileExists($path);
//        if ($validator->isValidPackage(true, false)) {
//            $storage->savePackage(null, null, true);
//        }
//    }

    private function generate_h5p() {

    }

    private function create_library_record($machinename, $title, $majorversion = 1, $minorversion = 0,
                                           $patchversion = 1) {
        global $DB;

        $content = array(
            'machinename' => $machinename,
            'title' => $title,
            'majorversion' => $majorversion,
            'minorversion' => $minorversion,
            'patchversion' => $patchversion,
            'runnable' => 1,
            'fullscreen' => 1,
            'preloadedjs' => 'js/example.js',
            'preloadedcss' => 'css/example.css'
        );

        $libraryid = $DB->insert_record('h5p_libraries', $content);

        return $DB->get_record('h5p_libraries', ['id' => $libraryid]);
    }


}