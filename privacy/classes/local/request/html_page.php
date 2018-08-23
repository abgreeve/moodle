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
 * This file contains the moodle format implementation of the content writer.
 *
 * @package core_privacy
 * @copyright 2018 Adrian Greeve <adriangreeve.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\local\request;

defined('MOODLE_INTERNAL') || die();

class html_page {

    protected $header;

    protected $footer;

    protected $maincontent = '';

    protected $navigation;

    public function __construct() {
        // Set header
        $this->set_header();
        $this->set_navigation();
        // Set footer
        $this->set_footer();
    }

    public function set_navigation($navdata = '') {
        $this->navigation = '<div id="export-navigation">';
        $this->navigation .= '<div>' . $navdata . '</div>';
        $this->navigation .= '</div>';
    }

    protected function set_header() {
        $this->header = '<html>';
        $this->header .= '<head>';
        $this->header .= '<title>Site name?</title>';
        $this->header .= '<link rel="stylesheet" type="text/css" href="#" />';
        $this->header .= '</head>';
        $this->header .= '<body>';
    }

    protected function set_footer() {
        $this->footer = '</body>';
        $this->footer .= '</html>';
    }

    public function htmlize_data($data) {
        $html = '';
        $html .= '<ul>';
        foreach ($data as $key => $value) {
            $html .= '<li>';
            // $html .= $key . ':&nbsp;&nbsp;&nbsp;&nbsp;' . $value;
            $html .= $key . ':&nbsp;&nbsp;&nbsp;&nbsp;';
            $html .= '</li>';
        }
        $html .= '</ul>';
        $this->maincontent = $html;
    }

    public function out() {
        // Return a full html page.
        $html = $this->header;
        $html .= $this->navigation;
        $html .= $this->maincontent;
        $html .= $this->footer;
        return $html;
    }
}