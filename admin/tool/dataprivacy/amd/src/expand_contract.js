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

    var expandedImage = $('<img alt="" src="' + url.imageUrl('t/expanded') + '"/>');
    var collapsedImage = $('<img alt="" src="' + url.imageUrl('t/collapsed') + '"/>');

    return /** @alias module:tool_dataprivacy/expand-collapse */ {
        /**
         * Expand or collapse a selected node.
         *
         * @param  {object} targetnode The node that we want to expand / collapse
         * @param  {object} thisnode The node that was clicked.
         * @return {null}
         */
        expandCollapse: function(targetnode, thisnode) {
            window.console.log(thisnode);
            // Section -- Attempt to open section with filtering.
            if (thisnode.attr('data-plugin')) {
                var tname = thisnode.data('plugin');
                var anoth = $('[data-plugintarget="' + tname + '"]').children();
                anoth.each(function() {
                    $(this).removeClass('hide');
                });
            }

            if (targetnode.hasClass('hide') || targetnode.hasClass('done')) {
                targetnode.removeClass('hide');
                targetnode.addClass('visible');
                targetnode.removeClass('done');
                targetnode.attr('aria-expanded', true);
                thisnode.find(':header i.fa').removeClass('fa-plus-square');
                thisnode.find(':header i.fa').addClass('fa-minus-square');
                thisnode.find(':header img.icon').attr('src', expandedImage.attr('src'));
            } else {
                targetnode.removeClass('visible');
                targetnode.addClass('hide');
                targetnode.attr('aria-expanded', false);
                thisnode.find(':header i.fa').removeClass('fa-minus-square');
                thisnode.find(':header i.fa').addClass('fa-plus-square');
                thisnode.find(':header img.icon').attr('src', collapsedImage.attr('src'));
            }
        },

        /**
         * Expand or collapse all nodes on this page.
         *
         * @param  {string} nextstate The next state to change to.
         * @return {null}
         */
        expandCollapseAll: function(nextstate) {
            var currentstate = (nextstate == 'visible') ? 'hide' : 'visible';
            var ariaexpandedstate = (nextstate == 'visible') ? true : false;
            var iconclassnow = (nextstate == 'visible') ? 'fa-plus-square' : 'fa-minus-square';
            var iconclassnext = (nextstate == 'visible') ? 'fa-minus-square' : 'fa-plus-square';
            var imagenow = (nextstate == 'visible') ? expandedImage.attr('src') : collapsedImage.attr('src');
            $('.' + currentstate).each(function() {
                $(this).removeClass(currentstate);
                $(this).addClass(nextstate);
                $(this).attr('aria-expanded', ariaexpandedstate);
            });
            $('.tool_dataprivacy-expand-all').data('visibilityState', currentstate);

            str.get_string(currentstate, 'tool_dataprivacy').then(function(langString) {
                $('.tool_dataprivacy-expand-all').html(langString);
            }).catch(Notification.exception);

            $(':header i.fa').each(function() {
                $(this).removeClass(iconclassnow);
                $(this).addClass(iconclassnext);
            });
            $(':header img.icon').each(function() {
                $(this).attr('src', imagenow);
            });
        },

        /**
         * Filter all node and show ones that require attention.
         */
        expandFilter: function(filtertype) {

            // Okay this needs to change to incorporate all filters.
            // Get activated filters.
            var filters = $('.tool_dataprivacy-filter');
            var filternodes = [];
            filters.each(function() {
                if ($(this).attr('data-display') == 'hide') {
                    var node = ($(this).attr('data-type') == 'api-issue') ? $('[data-compliant="false"]') : $('[data-thing="external"]');
                    // var node = ($(this).attr('data-type') == 'api-issue') ? $('[data-compliant="false"]') : $('.badge-notice');
                    filternodes.push(node);
                }
            });

            var activefiltercount = filternodes.length;

            // window.console.log('filternodes: ' + filternodes.length);
            // Okay this has to change, but I can at least get it working to start off with.
            if (activefiltercount == 3) {
                filternodes = $('[data-thing="external"][data-compliant="false"]');
            }

            var thiscount = 0;
            $.each(filternodes, function() {
                thiscount = thiscount + $(this).length;
                $(this).each(function() {
                    var parentNode = $(this).parents('[data-plugintarget]');
                    parentNode.removeClass('hide');
                    parentNode.attr('aria-expanded', true);

                    var componentname = $(this).attr('id');
                    var thing = $('[data-id="' + componentname + '"]');
                    thing.removeClass('hide');
                    thing.data('filtered', 'true');

                    var preNode = parentNode.prev();
                    preNode.find(':header img.icon').attr('src', expandedImage.attr('src'));
                    preNode.find(':header i.fa').removeClass('fa-plus-square');
                    preNode.find(':header i.fa').addClass('fa-minus-square');
                });
            });
            $('.tool_dataprivacy-element[aria-expanded="false"]').each(function() {
                if ($(this).parent('div').data('filtered') != 'true') {
                    $(this).parent('div').addClass('hide');
                }
            });

            // window.console.log('filternodes: ' + filternodes.length);

            if (activefiltercount == 0) {
                this.resetAll();
            }
            if (thiscount == 0) {
                // Display a message saying there are no results.
                window.console.log('this count: ' + thiscount);
            }

        },

        /**
         * Reset the filters back to normal.
         */
        resetAll: function() {
            $('div[class="hide"]').each(function() {
                $(this).removeClass('hide');
            });
            $('[data-filtered="false"]').each(function() {
                $(this).removeClass('hide');
            });
            $('.tool_dataprivacy-element[aria-expanded="true"]').each(function() {
                $(this).addClass('hide');
                $(this).attr('aria-expanded', false);
                var preNode = $(this).prev();
                preNode.find(':header img.icon').attr('src', collapsedImage.attr('src'));
                preNode.find(':header i.fa').removeClass('fa-minus-square');
                preNode.find(':header i.fa').addClass('fa-plus-square');
            });
            $('.tool_dataprivacy-component-count').addClass('hide');
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
            var parentNode = linkNode.parents('[data-plugintarget]');
            parentNode.removeClass('hide');
            parentNode.addClass('done');
            parentNode.attr('aria-expanded', true);
            parentNode.parent().removeClass('hide');
        },

        search: function() {

            var searchText = $('.tool_dataprivacy-search-box').val();
            var plugintypes = $('h3');
            var components = $('h4');

            // Components first then plugins.
            components.each(function() {
                var htmlstring = $(this).text();
                htmlstring = htmlstring.toLowerCase();
                if (htmlstring.indexOf(searchText.toLowerCase()) !== -1) {
                    // Expand this area.
                    var parentNode = $(this).parents('[data-plugintarget]');
                    parentNode.removeClass('hide');
                    parentNode.attr('aria-expanded', true);
                    parentNode.addClass('done');
                    if ($(this).data('compliant') == false) {
                        $(this).parent('div').parent('div').removeClass('hide');
                    } else {
                        $(this).parent('a').parent('div').parent('div').removeClass('hide');
                    }
                } else {
                    // Hide!
                    if ($(this).data('compliant') == false) {
                        $(this).parent('div').parent('div').addClass('hide');
                    } else {
                        $(this).parent('a').parent('div').parent('div').addClass('hide');
                    }
                }
            });

            plugintypes.each(function() {
                var stuff = $(this).text();
                stuff = stuff.toLowerCase();
                if (stuff.indexOf(searchText.toLowerCase()) !== -1) {
                    $(this).parent('a').parent('div').parent('div').removeClass('hide');
                } else {
                    // Check to see if all children are hidden.
                    var pluginname = $(this).parent('a').data('plugin');
                    var doesit = false;
                    $('[data-plugintarget="' + pluginname + '"]').each(function() {
                        $(this).children().each(function() {
                            if (!$(this).hasClass('hide')) {
                                doesit = true;
                            }
                        });
                    });
                    // Hide this node.
                    if (!doesit) {
                        $(this).parent('a').parent('div').parent('div').addClass('hide');
                        // Collapse as well.
                    }
                }
            });

            // If we have an empty search then collapse everything back to it's original state.
            if (searchText == '') {
                plugintypes.each(function() {
                    var pluginname = $(this).attr('id');
                    $('[data-plugintarget="' + pluginname + '"]').addClass('hide');
                    $('[data-plugintarget="' + pluginname + '"]').removeClass('done');
                    $('[data-plugintarget="' + pluginname + '"]').attr('aria-expanded', false);
                });
            }
        }
    };
});
