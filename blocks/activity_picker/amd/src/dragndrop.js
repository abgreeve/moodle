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
 *
 */
define(['jquery'], function($) {

    var activityname;
    var initialised = false;
    var lastmodule = null;

    function handleDragStart(e) {
        // window.console.log(this.dataset.moduleType);
        activityname = this.dataset.moduleType;
        this.style.opacity = '5.0';
        // window.console.log('yep good');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    }

    function handleDragEnter(e) {

    }

    function handleModEnter(e) {
        this.setAttribute('style', 'border-top: 1px solid blue');
    }

    function handleModLeave(e) {
        this.setAttribute('style', 'border-top: none');
    }

    function handleDragLeave(e) {
        // window.console.log('thinged');
    }

    function handleModDrop(e) {
        // window.console.log(this);
        lastmodule = this;
    }

    function handleDrag(e) {
        window.console.log(e);
    }

    function handleDragEnd(e) {
        // this.style.border = 'none';
    }

    function handleDragOver(e) {
        e.preventDefault();
    }

    function handleDrop(e) {
        // window.console.log(lastmodule);

        // Get the icondata for later.
        var moditem = document.querySelectorAll('.module_item[data-module-type=\"' + activityname + '\"]');
        var icon = moditem[0].getElementsByClassName('icon');
        var iconsrc = icon[0].getAttribute('src');

        var expression = /\/(\d+)/;


        var thing = iconsrc.match(expression);
        // window.console.log(thing[1]);

        // e.stopPropagation();

        var activitysection = this.getElementsByClassName('section');
        // window.console.log(activitysection[0]);

        // Create the activity.
        var lineitem = document.createElement('li');
        lineitem.className = 'activity ' + activityname + ' modtype_' + activityname;
        var firstdiv = document.createElement('div');
        var seconddiv = document.createElement('div');
        seconddiv.className = 'mod-indent-outer';
        var thirddiv = document.createElement('div');
        thirddiv.className = 'mod-indent';
        seconddiv.append(thirddiv);
        var fourthdiv = document.createElement('div');
        var fifthdiv = document.createElement('div');
        fifthdiv.className = ' activityinstance';
        var link = document.createElement('a');
        link.setAttribute('href', 'view.php');

        var iconelement = document.createElement('img');
        iconelement.className = 'iconlarge activityicon';
        iconelement.setAttribute('src', 'http://localhost/stable_master_development/theme/image.php/classic/' + activityname + '/' + thing[1] + '/icon');
        iconelement.setAttribute('role', 'presentation');
        iconelement.setAttribute('aria-hidden', 'true');

        var span = document.createElement('span');
        span.className = 'instancename';
        span.innerHTML = activityname;

        // Add them all together
        link.append(iconelement);
        link.append(span);
        fifthdiv.append(link);
        fourthdiv.append(fifthdiv);
        seconddiv.append(fourthdiv);
        firstdiv.append(seconddiv);
        lineitem.append(firstdiv);
        if (lastmodule !== 'null') {
            activitysection[0].insertBefore(lineitem, lastmodule);
        } else {
            activitysection[0].append(lineitem);
        }

        lastmodule = null;
        refreshHandlers();

        // this.style.background = 'white';
    }

    function refreshHandlers() {
        var mainregion = document.getElementById("region-main-box");
        var modelements = mainregion.getElementsByClassName('activity');
        for (var modelement of modelements) {
            modelement.setAttribute('style', 'border-top: none');
            modelement.addEventListener('dragenter', handleModEnter, false);
            modelement.addEventListener('dragleave', handleModLeave, false);
            modelement.addEventListener('drop', handleModDrop, false);
        }
    }

    return {
        // Public variables and functions.

        'init': function() {
            if (initialised) {
                return;
            }
            initialised = true;
            // Register event listeners.
            var modules = document.getElementsByClassName("module_item");

            var mainregion = document.getElementById("region-main-box");
            // var sections = document.querySelectorAll(".itemstable");
            var sections = document.querySelectorAll(".section[role=\"region\"]");
            // window.console.log(sections);
            var modelements = mainregion.getElementsByClassName('activity');
            // window.console.log(modelements);

            for (var mod of modules) {
                mod.addEventListener('dragstart', handleDragStart, false);
            }

            for (var section of sections) {
                // section.style.border = '1px dashed red';
                // window.console.log(section);
                section.addEventListener('dragenter', handleDragEnter, false);
                section.addEventListener('dragleave', handleDragLeave, false);
                section.addEventListener('drag', handleDrag, false);
                section.addEventListener('dragend', handleDragEnd, false);
                section.addEventListener('dragover', handleDragOver, false);
                // section.removeEventListener('drop');
                section.addEventListener('drop', handleDrop, false);
            }

            for (var modelement of modelements) {
                modelement.addEventListener('dragenter', handleModEnter, false);
                modelement.addEventListener('dragleave', handleModLeave, false);
                modelement.addEventListener('drop', handleModDrop, false);
            }

            // window.console.log(modules);
        }
    };
});