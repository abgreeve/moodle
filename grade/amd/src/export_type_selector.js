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
 * @package    core_grades
 * @copyright  2021 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';

const showthing = async(e) => {
    e.preventDefault();
    let link = e.currentTarget;

    let rawdata = link.getAttribute('data-stuff');
    let interthing = JSON.parse(rawdata);
    window.console.log(interthing);
    // let awesomedata = {"types": interthing};
    // window.console.log(awesomedata);

    const modal = await buildModal(interthing);

    displayModal(modal, link);
};


const buildModal = async(templatecontext) => {

    return ModalFactory.create({
        // title: await getString('delexternalbackpack', 'core_badges'),
        title: 'Add title here',
        body: await Templates.render('core_grades/type_selector', templatecontext),
        // body: 'Add body here',
        type: ModalFactory.types.SAVE_CANCEL,
    });

};

const displayModal = async(modal, link) => {
    // modal.setSaveButtonText(await getString('delete', 'core'));

    modal.getRoot().on(ModalEvents.save, function() {
        let format = 'imsspecification';
        let url = link.getAttribute('href');
        link.setAttribute('href', url + '&format=' + format);
        window.location.href = link.getAttribute('href');
    });

    modal.getRoot().on(ModalEvents.hidden, function() {
        modal.destroy();
    });

    modal.show();
};

export const init = () => {
    let thing = document.querySelector('[data-thing]');
    thing.addEventListener('click', showthing);
};
