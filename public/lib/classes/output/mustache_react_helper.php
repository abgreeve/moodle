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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace core\output;

use Mustache\LambdaHelper;

/**
 * Mustache helper for rendering React component mount points.
 *
 * This helper generates an element (DIV, for example) with data attributes that contain
 * the component reference and props.
 *
 * Usage in Mustache templates:
 * ```
 * {{#react}}
 * {
 *     "component": "@core/ds/modal",
 *     "props": {
 *         "title": "Confirm Action",
 *         "content": "Are you sure?",
 *         "buttons": ["cancel", "confirm"]
 *     },
 *     "id": "confirmation-modal",
 *     "class": "modal-wrapper",
 * }
 * <p>Loading...</p>
 * {{/react}}
 * ```
 *
 * You can use Moodle's built-in {{#str}} helper to generate localized strings:
 * ```
 * {{#react}}
 * {
 *   "component": "@core/ds/button",
 *   "props": {
 *     "label": "{{#str}}save, core{{/str}}",
 *     "cancelText": "{{#str}}cancel, core{{/str}}"
 *   },
 *   "aria-label": "{{#str}}savechanges, core{{/str}}",
 * }
 * {{/react}}
 *
 * @package    core
 * @copyright  Meirza <meirza.arson@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mustache_react_helper {

    /** @var string The HTML element tag for mount points */
    private const MOUNT_TAG = 'div';

    /**
     * Render React component mount point.
     *
     * @param string $text JSON config and optional inner content
     * @param LambdaHelper $helper Mustache lambda helper
     * @return string HTML output
     */
    public function react(string $text, LambdaHelper $helper): string {
        $text = trim($helper->render($text));

        if (empty($text)) {
            return '';
        }

        list($json, $content) = $this->split_json_content($text);
        $config = $this->decode_json($json);

        // Fallback to plain div if JSON invalid but has content.
        if ($config === null) {
            return $content ? $this->build_element($content) : '';
        }

        $attrs = $this->get_attributes($config);
        return $this->build_element($content, $attrs);
    }

    /**
     * Split input into JSON block and remaining content.
     *
     * @param string $text Input text
     * @return array [json_string, inner_content]
     */
    private function split_json_content(string $text): array {
        if ($text[0] !== '{') {
            return ['', $text];
        }

        $len = strlen($text);
        $depth = 0;
        $inquotes = false;
        $escaped = false;

        for ($i = 0; $i < $len; $i++) {
            $char = $text[$i];

            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $escaped = true;
                continue;
            }

            if ($char === '"') {
                $inquotes = !$inquotes;
                continue;
            }

            if ($inquotes) {
                continue;
            }

            if ($char === '{') {
                $depth++;
            } else if ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    return [
                        substr($text, 0, $i + 1),
                        trim(substr($text, $i + 1)),
                    ];
                }
            }
        }

        return [$text, ''];
    }

    /**
     * Decode JSON with automatic cleanup.
     *
     * @param string $json JSON string
     * @return array|null Decoded array or null on failure
     */
    private function decode_json(string $json): ?array {
        // Strip trailing commas - common mistake.
        $json = preg_replace('/,\s*([}\]])/', '$1', $json);

        $result = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            debugging('Invalid JSON in mustache react helper: ' . json_last_error_msg() . "\n" . $json, DEBUG_DEVELOPER);
            return null;
        }

        return is_array($result) ? $result : null;
    }

    /**
     * Build HTML attributes string from config.
     *
     * @param array $config Configuration array
     * @return string Attributes string with leading space
     */
    private function get_attributes(array $config): string {
        $out = '';

        // React data-react-component attribute.
        if (!empty($config['component'])) {
            $out .= ' data-react-component="' . s($config['component']) . '"';
        }

        // React data-react-props attribute.
        if (isset($config['props']) && is_array($config['props'])) {
            $props = json_encode($config['props'], JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
            $out .= ' data-react-props=\'' . $props . '\'';
        }

        // Custom attributes.
        foreach ($config as $name => $val) {
            if ($name === 'component' || $name === 'props' || $val === null || $val === '') {
                continue;
            }

            if (is_bool($val)) {
                if ($val) {
                    $out .= ' ' . s($name);
                }
            } else if (is_array($val)) {
                $encoded = json_encode($val, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
                $out .= ' ' . s($name) . '="' . $encoded . '"';
            } else {
                $out .= ' ' . s($name) . '="' . s($val) . '"';
            }
        }

        return $out;
    }

    /**
     * Wrap content in mount tag.
     *
     * @param string $content Inner content
     * @param string $attrs Attributes string (optional)
     * @return string Complete HTML element
     */
    private function build_element(string $content, string $attrs = ''): string {
        return "<" . self::MOUNT_TAG . $attrs . ">" . $content . "</" . self::MOUNT_TAG . ">";
    }
}
