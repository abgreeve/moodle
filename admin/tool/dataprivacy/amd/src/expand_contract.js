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

    var filterlist;
    var currentlist;

    var filtering = false;
    var searchlength = 0;
    var filterlength = 0;

    var expandedImage = $('<img alt="" src="' + url.imageUrl('t/expanded') + '"/>');
    var collapsedImage = $('<img alt="" src="' + url.imageUrl('t/collapsed') + '"/>');

    var updatedisplay = function() {
        // Check each component and see if it is already hidden. Hide if necessary.
        var pluginstohide = $.extend(true, {}, filterlist);
        if (Object.keys(filterlist).length != Object.keys(currentlist).length || searchlength > 0) {
            for (key in currentlist) {
                delete pluginstohide[key];
            }

            // Hide all plugins.
            for (key in pluginstohide) {
                // Collapse previously expanded areas.
                var node = $('[data-plugintarget="' + key + '"]');
                node.addClass('hide');
                node.attr('aria-expanded', false);
                $('[data-plugin="' + key + '"]').addClass('hide');
            }

            // Expand and unhide the remaining plugins.
            for (key in currentlist) {
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
                    $('[data-id="' + plugin['raw_component'] + '"]').removeClass('hide');
                });
            }
        } else {
            // Reset back to normal.
            resetNodes();
        }
    };

    var resetNodes = function() {
        for (key in filterlist) {
            var node = $('[data-plugintarget="' + key + '"]');
            node.addClass('hide');
            node.attr('aria-expanded', false);
            $('[data-plugin="' + key + '"]').removeClass('hide');
        }
    };

    var hidenode = function(basenode, parentnode) {
        basenode.addClass('hide');
        basenode.attr('aria-expanded', false);
        parentnode.find(':header i.fa').removeClass('fa-minus-square');
        parentnode.find(':header i.fa').addClass('fa-plus-square');
        parentnode.find(':header img.icon').attr('src', collapsedImage.attr('src'));
    };

    var shownode = function(basenode, parentnode) {
        basenode.removeClass('hide');
        basenode.attr('aria-expanded', true);
        parentnode.find(':header i.fa').removeClass('fa-plus-square');
        parentnode.find(':header i.fa').addClass('fa-minus-square');
        parentnode.find(':header img.icon').attr('src', expandedImage.attr('src'));
    }

    return /** @alias module:tool_dataprivacy/expand-collapse */ {
        init: function(data) {
            filterlist = data;
            currentlist = $.extend(true, {}, filterlist);
        },

        /**
         * Expand or collapse a selected node.
         *
         * @param  {object} thisnode The node that was clicked.
         * @return {null}
         */
        expandCollapse: function(thisnode) {
            // Section -- Attempt to open section with filtering.
            var partiallyopen = false;
            var cantthinkofaname;
            if (thisnode.attr('data-plugin')) {
                var tname = thisnode.attr('data-plugin');
                var cantthinkofaname = $('[data-plugintarget="' + tname + '"]');
                var pluginchildren = cantthinkofaname.children();
                var childcount = pluginchildren.length;
                var hidecount = childcount;
                pluginchildren.each(function() {
                    hidecount = ($(this).hasClass('hide')) ? hidecount - 1 : hidecount;
                    $(this).removeClass('hide');
                });
                partiallyopen = (childcount !== hidecount) ? true : false;
            }

            if (typeof(cantthinkofaname) !== 'undefined') {

                if (!partiallyopen && cantthinkofaname.attr('aria-expanded') == 'true') {
                    hidenode(cantthinkofaname, thisnode);
                } else {
                    shownode(cantthinkofaname, thisnode);
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
         * @return {null}
         */
        expandCollapseAll: function(nextstate) {

            // $('.expand').each(function() {
            //     var infosection;
            //     if ($(this).attr('data-plugin')) {
            //         var name = $(this).attr('data-plugin');
            //         infosection = $('[data-plugintarget="' + name + '"]');
            //     } else {
            //         var name = $(this).attr('data-component');
            //         infosection = $('[data-section="' + name + '"]');
            //     }
            //     if (nextstate == 'visible') {
            //         shownode(infosection, $(this));
            //         } else {
            //         hidenode(infosection, $(this));
            //     }
            // });
            // 
            var showall = (nextstate == 'visible') ? true : false;
            var state = (nextstate == 'visible') ? 'hide' : 'visible';
            // 
            $('.tool_dataprivacy-element').each(function() {
                $(this).addClass(nextstate);
                $(this).removeClass(state);
                $(this).attr('aria-expanded', showall);
            });

            $('.tool_dataprivacy-expand-all').attr('data-visibility-state', state);

            str.get_string(nextstate, 'tool_dataprivacy').then(function(langString) {
                $('.tool_dataprivacy-expand-all').html(langString);
            }).catch(Notification.exception);


            // var ariaexpandedstate = (nextstate == 'visible') ? true : false;
            // var iconclassnow = (nextstate == 'visible') ? 'fa-plus-square' : 'fa-minus-square';
            // var iconclassnext = (nextstate == 'visible') ? 'fa-minus-square' : 'fa-plus-square';
            // var imagenow = (nextstate == 'visible') ? expandedImage.attr('src') : collapsedImage.attr('src');
            // $('.' + currentstate).each(function() {
            //     $(this).removeClass(currentstate);
            //     $(this).addClass(nextstate);
            //     $(this).attr('aria-expanded', ariaexpandedstate);
            // });
            // $('.tool_dataprivacy-expand-all').data('visibilityState', currentstate);


            // $(':header i.fa').each(function() {
            //     $(this).removeClass(iconclassnow);
            //     $(this).addClass(iconclassnext);
            // });
            // $(':header img.icon').each(function() {
            //     $(this).attr('src', imagenow);
            // });
        },

        filterList: function() {

            var filterfunctions = {
                "api-issue": function(test) {
                    return (test.compliant) ? false : true;
                },
                "additional": function(test) {
                    return (test.hasOwnProperty('external')) ? true : false;
                }
            };

            var selectedfilters = $('.tool_dataprivacy-filter[data-display="hide"]');
            if (selectedfilters.length < filterlength && searchlength == 0) {
                currentlist = $.extend(true, {}, filterlist);
            }
            filterlength = selectedfilters.length;

            selectedfilters.each(function() {
                var filtertype = $(this).attr('data-type');
                var newlist = {};

                // Reduce current list.
                for (listitem in currentlist) {
                    var newpluginlist = [];
                    currentlist[listitem].plugins.forEach(function(component) {
                        if (filterfunctions[filtertype](component)) {
                            newpluginlist.push(component);
                        }
                    });
                    if (newpluginlist.length > 0) {
                        var item = currentlist[listitem];
                        item.plugins = newpluginlist;
                        newlist[listitem] = item;
                    }
                };

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
            var parentNode = linkNode.parents('[data-plugintarget]');
            parentNode.removeClass('hide');
            parentNode.addClass('done');
            parentNode.attr('aria-expanded', true);
            parentNode.parent().removeClass('hide');
        },

        search: function() {
            var searchText = $('.tool_dataprivacy-search-box').val();
            if (searchlength > searchText.length) {
                currentlist = $.extend(true, {}, filterlist);
            }
             searchlength = searchText.length;

            for (key in currentlist) {
                var matchingplugins = [];
                currentlist[key].plugins.forEach(function(plugin) {
                    var componentstring = plugin.component.toLowerCase();
                    if (componentstring.indexOf(searchText.toLowerCase()) !== -1) {
                        // Add to the matching list.
                        matchingplugins.push(plugin);
                    }
                });
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
