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
 * Potential user selector module.
 *
 * @module     tool_dataprivacy/expand_contract
 * @class      page-expand-contract
 * @package    tool_dataprivacy
 * @copyright  2018 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/url', 'core/str'], function($, url, str) {

    var SELECTORS = {
        ELEMENTS: '.tool_dataprivacy-element',
        EXPANDALL: '.tool_dataprivacy-expand-all',
        AWESOMEICONS: ':header i.fa',
        OLDICONS: ':header img.icon',
        ACTIVEFILTERS: '.tool_dataprivacy-filter[data-display="hide"]',
        SEARCHBOX: '.tool_dataprivacy-search-box',
    };


    /** {object} filterlist An object that represents all plugin types and components in the system.  */
    var filterlist;

    /**
     * {object} currentlist An object that represents the filtered (via filters and search box) list
     * of plugin types and component.
     */
    var currentlist;

    /** {integer} searchlength The length of the string in the searchbox. */
    var searchlength = 0;

    /** {integer} filterlength The number of filters currently active. */
    var filterlength = 0;

    /** {object} expandedImage The image node (clean) for the expanded image. */
    var expandedImage = $('<img alt="" src="' + url.imageUrl('t/expanded') + '"/>');

    /** {object} collapsedImage The image node (clean) for the collapsed image. */
    var collapsedImage = $('<img alt="" src="' + url.imageUrl('t/collapsed') + '"/>');

    /**
     * Update the page to show or hide the relevant nodes.
     */
    var updatedisplay = function() {
        // Check each component and see if it is already hidden. Hide if necessary.
        var pluginstohide = $.extend(true, {}, filterlist);
        if (Object.keys(filterlist).length != Object.keys(currentlist).length || searchlength > 0) {
            for (var key in currentlist) {
                delete pluginstohide[key];
            }

            // Hide all plugins.
            for (var key in pluginstohide) {
                // Collapse previously expanded areas.
                var node = $('[data-plugintarget="' + key + '"]');
                node.addClass('hide');
                node.attr('aria-expanded', false);
                $('[data-plugin="' + key + '"]').addClass('hide');
            }

            // Expand and unhide the remaining plugins.
            for (var key in currentlist) {
                $('[data-plugin="' + key + '"]').removeClass('hide');
                var node = $('[data-plugintarget="' + key + '"]');
                node.attr('aria-expanded', true);
                if (currentlist[key].plugins.length == 0) {
                    node.attr('aria-expanded', false);
                    node.addClass('hide');
                    // Set children to unhidden.
                    node.children().each(function() {
                        $(this).removeClass('hide');
                    });
                } else {
                    node.removeClass('hide');
                    // Hide all components then just unhide from our list.
                    node.children().each(function() {
                        $(this).addClass('hide');
                    });
                }

                currentlist[key].plugins.forEach(function(plugin) {
                    $('[data-id="' + plugin.raw_component + '"]').removeClass('hide');
                });
            }
        } else {
            // Reset back to normal.
            resetNodes();
        }
    };

    /**
     * Reset the all of the nodes back to the original state.
     */
    var resetNodes = function() {
        for (var key in filterlist) {
            var node = $('[data-plugintarget="' + key + '"]');
            node.addClass('hide');
            node.attr('aria-expanded', false);
            $('[data-plugin="' + key + '"]').removeClass('hide');
        }
    };

    /**
     * Hides nodes and also changes the icons to collapsed.
     *
     * @param  {object} basenode The node to hide.
     * @param  {object} parentnode The parent node to use to find the appropriate icons to collapse.
     */
    var hidenode = function(basenode, parentnode) {
        basenode.addClass('hide');
        basenode.attr('aria-expanded', false);
        parentnode.find(SELECTORS.AWESOMEICONS).removeClass('fa-minus-square');
        parentnode.find(SELECTORS.AWESOMEICONS).addClass('fa-plus-square');
        parentnode.find(SELECTORS.OLDICONS).attr('src', collapsedImage.attr('src'));
    };

    /**
     * Shows nodes and also changes the icons to expand.
     *
     * @param  {object} basenode The node to show.
     * @param  {object} parentnode The parent node to use to find the appropriate icons to expand.
     */
    var shownode = function(basenode, parentnode) {
        basenode.removeClass('hide');
        basenode.attr('aria-expanded', true);
        parentnode.find(SELECTORS.AWESOMEICONS).removeClass('fa-plus-square');
        parentnode.find(SELECTORS.AWESOMEICONS).addClass('fa-minus-square');
        parentnode.find(SELECTORS.OLDICONS).attr('src', expandedImage.attr('src'));
    };

    return /** @alias module:tool_dataprivacy/expand-collapse */ {
        /**
         * Initialises The page to be ready for returning a filtered list.
         *
         * @param  {object} data All of the plugin and component information.
         */
        init: function(data) {
            filterlist = data;
            currentlist = $.extend(true, {}, filterlist);
        },

        /**
         * Expand or collapse a selected node.
         *
         * @param  {object} thisnode The node that was clicked.
         */
        expandCollapse: function(thisnode) {
            // Section -- Attempt to open section with filtering.
            var partiallyopen = false;
            var section;
            if (thisnode.attr('data-plugin')) {
                var tname = thisnode.attr('data-plugin');
                section = $('[data-plugintarget="' + tname + '"]');
                var pluginchildren = section.children();
                var childcount = pluginchildren.length;
                var hidecount = childcount;
                pluginchildren.each(function() {
                    hidecount = ($(this).hasClass('hide')) ? hidecount - 1 : hidecount;
                    $(this).removeClass('hide');
                });
                partiallyopen = (childcount !== hidecount) ? true : false;
            }

            if (typeof (section) !== 'undefined') {

                if (!partiallyopen && section.attr('aria-expanded') == 'true') {
                    hidenode(section, thisnode);
                } else {
                    shownode(section, thisnode);
                }
            }

            // Move onto child nodes.
            if (thisnode.attr('data-component')) {
                var componentname = thisnode.attr('data-component');
                var infosection = $('[data-section="' + componentname + '"]');
                if (infosection.attr('aria-expanded') == 'false') {
                    // Open up the section.
                    shownode(infosection, thisnode);
                } else {
                    // Hide the section.
                    hidenode(infosection, thisnode);
                }
            }
        },

        /**
         * Expand or collapse all nodes on this page.
         *
         * @param  {string} nextstate The next state to change to.
         */
        expandCollapseAll: function(nextstate) {

            var showall = (nextstate == 'visible') ? true : false;
            var state = (nextstate == 'visible') ? 'hide' : 'visible';

            $(SELECTORS.ELEMENTS).each(function() {
                $(this).addClass(nextstate);
                $(this).removeClass(state);
                $(this).attr('aria-expanded', showall);
            });

            $(SELECTORS.EXPANDALL).attr('data-visibility-state', state);

            str.get_string(nextstate, 'tool_dataprivacy').then(function(langString) {
                $('.tool_dataprivacy-expand-all').html(langString);
                return;
            }).catch(Notification.exception);

            var iconclassnow = (nextstate == 'visible') ? 'fa-plus-square' : 'fa-minus-square';
            var iconclassnext = (nextstate == 'visible') ? 'fa-minus-square' : 'fa-plus-square';
            var imagenow = (nextstate == 'visible') ? expandedImage.attr('src') : collapsedImage.attr('src');

            $(SELECTORS.AWESOMEICONS).each(function() {
                $(this).removeClass(iconclassnow);
                $(this).addClass(iconclassnext);
            });
            $(SELECTORS.OLDICONS).each(function() {
                $(this).attr('src', imagenow);
            });
        },

        /**
         * Applies the selected filters to the plugin list.
         */
        filterList: function() {

            var filterfunctions = {
                /**
                 *  Returns if the component is compliant with the API.
                 * 
                 * @param  {object} component The component object that we are filtering.
                 * @return {boolean} If the object is compliant with the API.
                 */
                "api-issue": function(component) {
                    return (component.compliant) ? false : true;
                },
                /**
                 * Is this component and external / third party plugin.
                 *
                 * @param  {object} component The component object that we are filtering.
                 * @return {boolean} If the component is an external plugin.
                 */
                "additional": function(component) {
                    return (component.hasOwnProperty('external')) ? true : false;
                }
            };

            /**
             * Returns a list of nodes that match all of the filters.
             *
             * @param  {object} listitem The list item to filter on.
             * @param  {object} filtertype The filter type to filter with.
             * @return {array} An array of plugin objects.
             */
            var getfilteredlist = function(listitem, filtertype) {
                var newpluginlist = [];
                currentlist[listitem].plugins.forEach(function(component) {
                    if (filterfunctions[filtertype](component)) {
                        newpluginlist.push(component);
                    }
                });
                return newpluginlist;
            };

            var selectedfilters = $(SELECTORS.ACTIVEFILTERS);
            if (selectedfilters.length < filterlength && searchlength == 0) {
                currentlist = $.extend(true, {}, filterlist);
            }
            filterlength = selectedfilters.length;

            selectedfilters.each(function() {
                var filtertype = $(this).attr('data-type');
                var newlist = {};

                // Reduce current list.
                for (var listitem in currentlist) {
                    var newpluginlist = getfilteredlist(listitem, filtertype);
                    if (newpluginlist.length > 0) {
                        var item = currentlist[listitem];
                        item.plugins = newpluginlist;
                        newlist[listitem] = item;
                    }
                }

                currentlist = newlist;
            });
            updatedisplay();
        },

        /**
         * Expands node when following a hyper link.
         *
         * @param  {string} link The hyperlink that we are following.
         */
        followLink: function(link) {
            var linkContainer = $('[data-id="' + link + '"]');
            linkContainer.removeClass('hide');
            var linkNode = $('[data-section="' + link + '"]');
            linkNode.removeClass('hide');
            linkNode.attr('aria-expanded', true);
            var parentNode = linkNode.parents();
            parentNode.removeClass('hide');
            parentNode.attr('aria-expanded', true);
        },

        /**
         * Searches the plugin list for a string matching the search box. This is compared against the plugin type and
         * component name.
         */
        search: function() {
            var searchText = $(SELECTORS.SEARCHBOX).val();

            /**
             * Returns an array of plugins matching the search criteria.
             * 
             * @param  {object} plugins The plugins to filter on
             * @return {array} An array of plugins after they have been filtered.
             */
            var getmatchingplugins = function(plugins) {
                var matchingplugins = [];
                plugins.forEach(function(plugin) {
                    var componentstring = plugin.component.toLowerCase();
                    if (componentstring.indexOf(searchText.toLowerCase()) !== -1) {
                        // Add to the matching list.
                        matchingplugins.push(plugin);
                    }
                });
                return matchingplugins;
            };

            if (searchlength > searchText.length) {
                currentlist = $.extend(true, {}, filterlist);
            }
             searchlength = searchText.length;

            for (var key in currentlist) {
                var matchingplugins = getmatchingplugins(currentlist[key].plugins);
                currentlist[key].plugins = matchingplugins;
                var pluginstring = currentlist[key].plugin_type.toLowerCase();
                if (pluginstring.indexOf(searchText.toLowerCase()) == -1 && matchingplugins.length == 0) {
                    delete currentlist[key];
                }
            }
            updatedisplay();
        },
    };
});
