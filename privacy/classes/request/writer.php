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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains the interface required to implmeent a content writer.
 *
 * @package core_privacy
 * @copyright 2018 Andrew Nicols <andrew@nicols.co.uk>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_privacy\request;

/**
 * The writer factory class.
 *
 * @package core_privacy
 * @copyright 2018 Andrew Nicols <andrew@nicols.co.uk>
 */
class writer {

    protected static $instance = null;

    protected $realwriter = null;

    /**
     * Constructor for the content writer.
     *
     * Protected to prevent direct instantiation.
     */
    protected function __construct() {}

    protected function get_writer_instance() {
        if (null === $this->realwriter) {
            if (PHPUNIT_TEST) {
                $this->realwriter = new \core_privacy\phpunit\request\content_writer(static::instance());
            } else {
                $this->realwriter = new moodle_content_writer(static::instance());
            }
        }

        return $this->realwriter;
    }

    protected static final function instance() {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public static final function reset() {
        static::$instance = null;
    }

    /**
     * Provide an instance of the writer with the specified context applied.
     *
     * @param   \context        $context    The context to apply
     * @return  content_writer              The content_writer
     */
    public static function with_context(\context $context) : content_writer {
        return static::instance()
            ->get_writer_instance()
            ->set_context($context);
    }

    /**
     * Store the specified user preference.
     *
     * @param   string          $component  The name of the component.
     * @param   string          $key        The name of th key to be stored.
     * @param   string          $value      The value of the preference
     * @param   string          $description    A description of the value
     * @return  content_writer
     */
    public static function store_user_preference(string $component, string $key, string $value, string $description) : content_writer {
        return static::with_context(\context_system::instance())
            ->store_user_preference($component, $key, $value, $description);
    }
}
