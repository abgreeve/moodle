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
 * Selector for getting the export type
 *
 * @module     core_grades/export_type_selector
 * @copyright  2021 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';

/**
 * Shows the Grading method export Modal.
 *
 * @param {object} e An event object
 */
const showModal = async(e) => {
    e.preventDefault();
    const link = e.currentTarget;
    const rawdata = link.getAttribute('data-export-types');
    const exportdata = JSON.parse(rawdata);
    const modal = await buildModal(exportdata);

    displayModal(modal, link);
};

/**
 * @param {object} templatecontext The context data to be sent to the template.
 * @return {promise} a modal object.
 */
const buildModal = async(templatecontext) => {

    return ModalSaveCancel.create({
        title: await getString('exportgradingmethod', 'grading'),
        body: await Templates.render('core_grades/type_selector', templatecontext),
    });

};

/**
 * Displays the export type selector modal.
 *
 * @param {promise} modal The modal
 * @param {object} link  The link
 */
const displayModal = async(modal, link) => {
    modal.setSaveButtonText(await getString('download', 'grading'));
    modal.getRoot().on(ModalEvents.save, () => {
        const radiobuttonnodes = document.querySelectorAll('[data-grading-method-export-type]');
        if (typeof radiobuttonnodes !== 'undefined') {
            radiobuttonnodes.forEach((nodeitem) => {
                if (nodeitem.checked == true) {
                    const format = nodeitem.getAttribute('value');
                    const url = link.getAttribute('href');
                    link.setAttribute('href', url + '&format=' + format);
                    window.location.href = link.getAttribute('href');
                }
            });
        }
    });

    modal.getRoot().on(ModalEvents.hidden, () => {
        modal.destroy();
    });

    modal.show();
};

/**
 * Kick off displaying a modal to select an export type.
 */
export const init = () => {
    document.querySelector('[data-thing]').addEventListener('click', showModal);
};
