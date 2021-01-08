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

import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';

/**
 * Shows the Grading method export Modal.
 *
 * @param {object} e A event object
 */
const showModal = async(e) => {
    e.preventDefault();
    let link = e.currentTarget;
    let rawdata = link.getAttribute('data-export-types');
    let interthing = JSON.parse(rawdata);
    const modal = await buildModal(interthing);

    displayModal(modal, link);
};

/**
 * @param {object} templatecontext The context data to be sent to the template.
 */
const buildModal = async(templatecontext) => {

    return ModalFactory.create({
        title: await getString('exportgradingmethod', 'grading'),
        body: await Templates.render('core_grades/type_selector', templatecontext),
        type: ModalFactory.types.SAVE_CANCEL,
    });

};

/**
 * { function_description }
 *
 * @param      {<type>}  modal   The modal
 * @param      {<type>}  link    The link
 */
const displayModal = async(modal, link) => {
    modal.setSaveButtonText(await getString('download', 'grading'));
    modal.getRoot().on(ModalEvents.save, () => {
        let radiobuttonnodes = document.querySelectorAll('[data-grading-method-export-type]');
        if (typeof radiobuttonnodes !== 'undefined') {
            radiobuttonnodes.forEach((nodeitem) => {
                if (nodeitem.checked == true) {
                    let format = nodeitem.getAttribute('value');
                    let url = link.getAttribute('href');
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

export const init = () => {
    let thing = document.querySelector('[data-thing]');
    thing.addEventListener('click', showModal);
};
