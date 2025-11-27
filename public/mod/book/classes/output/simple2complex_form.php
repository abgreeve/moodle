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

namespace mod_book\output;

use context_system;
use core_form\dynamic_form;
use core_text;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');

/**
 * Minimal example dynamic form for the calendar proof-of-concept UI.
 *
 * @package    mod_book
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class simple2complex_form extends dynamic_form {
    /** @var string File area identifier for uploaded attachments. */
    private const FILEAREA = 'simple2complex';
    #[\Override]
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('text', 'title', get_string('eventname', 'calendar'), ['maxlength' => 255, 'size' => 48]);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('required'), 'required', null, 'server');

        $mform->addElement(
            'editor',
            'description_editor',
            get_string('eventdescription', 'calendar'),
            null,
            $this->get_editor_options()
        );
        $mform->setType('description_editor', PARAM_RAW);

        $mform->addElement(
            'filepicker',
            'attachment',
            get_string('attachment', 'moodle'),
            null,
            $this->get_filepicker_options()
        );
        $mform->setType('attachment', PARAM_INT);

        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);
    }

    #[\Override]
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if (array_key_exists('title', $data) && trim((string)$data['title']) === '') {
            $errors['title'] = get_string('required');
        } else if (!empty($data['title']) && core_text::strlen($data['title']) > 255) {
            $errors['title'] = get_string('maximumchars', '', 255);
        }

        return $errors;
    }

    #[\Override]
    public function process_dynamic_submission(): array {
        $data = $this->get_data();
        $context = $this->get_context_for_dynamic_submission();
        $fs = get_file_storage();

        if (!empty($data->attachment)) {
            file_save_draft_area_files(
                $data->attachment,
                $context->id,
                'core_calendar',
                self::FILEAREA,
                0,
                $this->get_filepicker_options()
            );
        } else {
            $fs->delete_area_files($context->id, 'core_calendar', self::FILEAREA, 0);
        }

        $storedfiles = [];
        foreach ($fs->get_area_files($context->id, 'core_calendar', self::FILEAREA, 0, '', false) as $file) {
            $storedfiles[] = [
                'filename' => $file->get_filename(),
                'filesize' => $file->get_filesize(),
                'filepath' => $file->get_filepath(),
            ];
        }

        return [
            'title' => $data->title,
            'description' => $data->description_editor['text'] ?? '',
            'descriptionformat' => $data->description_editor['format'] ?? FORMAT_HTML,
            'files' => $storedfiles,
        ];
    }

    #[\Override]
    protected function get_context_for_dynamic_submission(): \context {
        $contextid = $this->optional_param('contextid', 0, PARAM_INT);

        if ($contextid > 0) {
            return \context::instance_by_id($contextid, MUST_EXIST);
        }

        return context_system::instance();
    }

    #[\Override]
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/calendar/index.php');
    }

    #[\Override]
    protected function check_access_for_dynamic_submission(): void {
        require_login();
    }

    #[\Override]
    public function set_data_for_dynamic_submission(): void {
        $data = [];

        $contextid = $this->optional_param('contextid', 0, PARAM_INT);
        if ($contextid > 0) {
            $data['contextid'] = $contextid;
        }

        $title = $this->optional_param('title', '', PARAM_TEXT);
        if ($title !== '') {
            $data['title'] = $title;
        }

        $description = $this->optional_param('description', '', PARAM_RAW);
        if ($description !== '') {
            $data['description_editor']['text'] = $description;
            $data['description_editor']['format'] = $this->optional_param('descriptionformat', FORMAT_HTML, PARAM_INT);
        }

        $draftitemid = file_get_submitted_draft_itemid('attachment');
        file_prepare_draft_area(
            $draftitemid,
            $this->get_context_for_dynamic_submission()->id,
            'core_calendar',
            self::FILEAREA,
            0,
            $this->get_filepicker_options()
        );
        $data['attachment'] = $draftitemid;

        $this->set_data($data);
    }

    /**
     * Editor options for the description field.
     *
     * @return array
     */
    protected function get_editor_options(): array {
        return [
            'maxfiles' => 0,
            'maxbytes' => 0,
            'context' => $this->get_context_for_dynamic_submission(),
        ];
    }

    /**
     * Filepicker options for the attachment field.
     *
     * @return array
     */
    protected function get_filepicker_options(): array {
        return [
            'accepted_types' => '*',
            'maxbytes' => 0,
            'maxfiles' => 1,
            'return_types' => FILE_INTERNAL,
            'context' => $this->get_context_for_dynamic_submission(),
        ];
    }
}
