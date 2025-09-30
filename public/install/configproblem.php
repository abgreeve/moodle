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
 * This is a quick page to let the user know that they have their web address incorrectly set up.
 *
 * @package    core
 * @subpackage install
 * @copyright  2025 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_MOODLE_COOKIES', true);

require_once(__DIR__ . '/../config.php');

@header('Content-Type: text/html; charset=UTF-8');
@header('X-UA-Compatible: IE=edge');
@header('Cache-Control: no-store, no-cache, must-revalidate');
@header('Cache-Control: post-check=0, pre-check=0', false);
@header('Pragma: no-cache');
@header('Expires: Mon, 20 Aug 1969 09:23:00 GMT');
@header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

$css = new \core\url('/public/install/css.php');

$problemdescription = get_string('webserverconfigproblemdescription', 'install', $CFG->wwwroot);

$data = [
    'dir' => right_to_left() ? 'rtl' : 'ltr',
    'css' => $css->out(),
    'webserverconfigproblemdescription' => $problemdescription,
];

echo $OUTPUT->render_from_template('core/configproblem', $data);
