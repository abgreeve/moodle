define(['jqueryui', 'jquery', 'core/forms', 'core/templates', 'core/form', 'core/form-text', 'core/form-select', 'core/ajax'],
    function(jqui, $, formfactory, templates, form, text, selectElement, ajax) {

    var lessonobjects = null;
    var lessonobjectid = 9999;
    var lessonid = 0;
    var ajaxlocation = 'ajax.php';
    var lesson = null;

    var Lesson = function(data) {
        /**
         * Create and add lesson pages to the lesson.
         *
         * @param {int} pageid Page ID for the lesson page.
         * @param {object} pagedata Data relating to the lesson page.
         */
        this.add_lessonpage = function(pageid, pagedata) {
            // Would like a better way to do this.
            switch(parseInt(pagedata.qtype)) {
                case 31:
                    this.pages[pageid] = new endofcluster_lessonPage(pagedata);
                    break;
                case 30:
                    this.pages[pageid] = new cluster_lessonPage(pagedata);
                    break;
                case 21:
                    this.pages[pageid] = new endofbranch_lessonPage(pagedata);
                    break;
                case 20:
                    this.pages[pageid] = new branchtable_lessonPage(pagedata);
                    break;
                case 10:
                    this.pages[pageid] = new essay_lessonPage(pagedata);
                    break;
                case 8:
                    this.pages[pageid] = new numerical_lessonPage(pagedata);
                    break;
                case 5:
                    this.pages[pageid] = new matching_lessonPage(pagedata);
                    break;
                case 3:
                    this.pages[pageid] = new multichoice_lessonPage(pagedata);
                    break;
                case 2:
                    this.pages[pageid] = new truefalse_lessonPage(pagedata);
                    break;
                case 1:
                    this.pages[pageid] = new shortanswer_lessonPage(pagedata);
                    break;
                default:
                    this.pages[pageid] = new lessonPage(pagedata);
                    break;
            }
        }

        this.id = lessonid;
        this.pages = {};
        for (lessonpage in data) {
            this.add_lessonpage(lessonpage, data[lessonpage]);
        }

    };

    var lessonPage = function(lessonobjectdata) {
        this.id = lessonobjectdata.id;
        this.qtype = parseInt(lessonobjectdata.qtype);
        this.lessonid = lessonobjectdata.lessonid;
        this.title = lessonobjectdata.title;
        this.contents = lessonobjectdata.contents;
        this.positionx = lessonobjectdata.positionx;
        this.positiony = lessonobjectdata.positiony;
        this.qtypestring = lessonobjectdata.qtypestr;
        this.nextpageid = lessonobjectdata.nextpageid;
        this.previouspageid = lessonobjectdata.prevpageid;
        this.location = lessonobjectdata.location;

        if (lessonobjectdata.hasOwnProperty("clusterid")) {
            this.clusterid = lessonobjectdata.clusterid;
        }
        if (lessonobjectdata.hasOwnProperty("subclusterid")) {
            this.subclusterid = lessonobjectdata.subclusterid;
        }

        this.jumps = {};
        var i = 0;
        var jumpname = "jumpto[" + i + "]";
        var answereditor = "answer_editor[" + i + "]";
        var responseeditor = "response_editor[" + i + "]";
        var lessonscore = "score[" + i + "]";

        while (lessonobjectdata.hasOwnProperty(jumpname)) {
            this.jumps[i] = {
                id: parseInt(lessonobjectdata[jumpname]),
                answer: lessonobjectdata[answereditor].text,
                response: lessonobjectdata[responseeditor].text,
                score: lessonobjectdata[lessonscore] // Might need grade here as well.
            }
            i += 1;
            jumpname = "jumpto[" + i + "]";
            answereditor = "answer_editor[" + i + "]";
            responseeditor = "response_editor[" + i + "]";
            lessonscore = "score[" + i + "]";
        }

        if (Object.keys(this.jumps).length === 0) {
            // Create default jumps.
            this.jumps[0] = {
                id: -1,
                answer: "Next page",
                response: "",
                score: 0
            }
            if (this.qtype < 11) {
                this.jumps[1] = {
                    id: 0,
                    answer: "This page",
                    response: "",
                    score: 0
                }
            }
        }

        if ("subclusterchildrenids" in lessonobjectdata) {
            this.childrenids = lessonobjectdata["subclusterchildrenids"];
        } else {
            this.childrenids = [];
        }

    };

    lessonPage.prototype = {
        in_cluster: function() {
            if (this.location === "cluster") {
                return true;
            }
            return false;
        },
        in_subcluster: function() {
            if (this.location == "subcluster") {
                return true;
            }
            return false;
        },
        update_jumps: function(jumpdata) {
            // Remove existing jumps.
            for (index in this.jumps) {
                delete this.jumps[index];
            }
            // Create new jumps from data.
            var i = 0;
            for (jumpid in jumpdata) {
                this.jumps[i] = {
                    id: parseInt(jumpdata[jumpid].jumpto),
                    answer: jumpdata[jumpid].answer,
                    response: jumpdata[jumpid].response,
                    score: jumpdata[jumpid].score,
                }
                i++;
            }
        },
        /**
         * This needs to be extended by the child classes.
         */
        get_default_edit_form: function() {
            var editform = '<div class="mod_lesson_page_editor"><form action="">';
            editform += '<h3>Edit this ' + this.qtypestring + ' </h3>';
            editform += pageTitle(this.id, this.title);
            editform += pageContents(this.id, this.contents);
            return editform;
        },
        save_edit_form: function(formobject) {

            // Perhaps this could all be included in a save method for the form.
            var formdata = formfactory.getFormData();
            formdata["lessonid"] = lesson.id;
            formdata["pageid"] = this.id;
            delete formdata["Jump_1"];
            // console.log(formdata);

            var promises = ajax.call([{
                methodname: 'mod_lesson_add_page',
                args: formdata
            }]);

            $.when.apply($.when, promises).then(function(response) {
                // console.log(response);
                if (response.warnings.length != "0") {

                    // formobject.handleErrors(response.warnings);
                    formfactory.handleErrors(response.warnings, formobject);

                }
            });

            // var jumps = {};
            // var i = 1;
            // var j = 0;

            // var formdata = $('form').serializeArray();
            // // formfactory.populateForm(formdata);
            // // formfactory.handleErrors('stuff');
            // // console.log(formdata);

            // // Iterate over lesson page answers.
            // while ($('#mod_lesson_answer_' + i).length) {
            //     var jumpanswer = $('#mod_lesson_answer_' + i).val();
            //     var jumpto = $('#mod_lesson_jump_select_' + i).val();
            //     var response = '';
            //     var score = 0;
            //     if ($('#mod_lesson_response_' + i).length) {
            //         response = $('#mod_lesson_response_' + i).val();
            //         // console.log('responjse: ' + response);
            //     }
            //     if ($('#mod_lesson_score_' + i).length) {
            //         score = $('#mod_lesson_score_' + i).val();
            //     }

            //     if (Object.keys(this.jumps).length <= j) {
            //         // Need to add new jumps
            //         this.jumps[j] = {
            //             id: jumpto,
            //             answer: jumpanswer,
            //             response: response,
            //             score: score
            //         };
            //     } else {
            //         // Update old jumps
            //         this.jumps[j].id = jumpto;
            //         this.jumps[j].answer = jumpanswer;
            //         this.jumps[j].response = response;
            //         this.jumps[j].score = score;
            //     }
            //     jumps[i] = {
            //         answer: jumpanswer,
            //         jumpto: jumpto,
            //         response: response,
            //         score: score
            //     };
            //     i++;
            //     j++;
            // }

            // var pagetitle = $('#mod_lesson_title_' + this.id).val();
            // var pagecontent = $('#mod_lesson_contents_' + this.id).val();
            // this.title = pagetitle;
            // this.contents = pagecontent;

            // var record = {
            //     page: {
            //         id: this.id,
            //         title: pagetitle,
            //         contents: pagecontent
            //     },
            //     answer: {
            //         lessonid: lesson.id,
            //         pageid: this.id,
            //         jumps: jumps

            //     }
            // };

            // var json = JSON.stringify(record);
            // var pageid = this.id;

            // // More ajax to save us all.
            // $.ajax({
            //         method: "POST",
            //         url: ajaxlocation,
            //         dataType: "json",
            //         data: {
            //             action: "updatelessonpage",
            //             lessonid: lessonid,
            //             jsondata: json
            //         }
            // }).done(function(newobject) {
            //     $('#mod_lesson_page_element_' + pageid + '_body').html(pagetitle);
            //     $('.mod_lesson_page_editor').remove();
            //     $('#mod_lesson_editor_addjump_btn').unbind('click');
            //     // Need to Refresh the jumps.
            //     drawalllines();
            // });
        }
    };

    // Lesson page types
    // Cluster
    var cluster_lessonPage = function(lessonobjectdata) {
        lessonPage.call(this, lessonobjectdata);
        if ("clusterchildrenids" in lessonobjectdata) {
            this.childrenids = lessonobjectdata["clusterchildrenids"];
        } else {
            this.childrenids = [];
        }
    };

    cluster_lessonPage.prototype = {
        in_cluster: function() {
            return lessonPage.prototype.in_cluster.call(this);
        },
        update_jumps: function(jumpdata) {
            lessonPage.prototype.update_jumps.call(this, jumpdata);
        },
        get_edit_form: function(jumpoptions) {
            return branchtable_lessonPage.prototype.get_edit_form.call(this, jumpoptions);
        },
        save_edit_form: function(formobject) {
            lessonPage.prototype.save_edit_form.call(this, formobject);
        }
    }

    // End of Cluster
    var endofcluster_lessonPage = function(lessonobjectdata) {
        lessonPage.call(this, lessonobjectdata);
    };

    endofcluster_lessonPage.prototype = {
        in_cluster: function() {
            return lessonPage.prototype.in_cluster.call(this);
        },
        update_jumps: function(jumpdata) {
            lessonPage.prototype.update_jumps.call(this, jumpdata);
        }
    }

    // True / False
    var truefalse_lessonPage = function(lessonobjectdata) {
        lessonPage.call(this, lessonobjectdata);
    };

    truefalse_lessonPage.prototype = {
        in_cluster: function() {
            return lessonPage.prototype.in_cluster.call(this);
        },
        in_subcluster: function () {
            return lessonPage.prototype.in_subcluster.call(this);
        },
        update_jumps: function(jumpdata) {
            lessonPage.prototype.update_jumps.call(this, jumpdata);
        },
        get_edit_form: function(jumpoptions) {
            // return branchtable_lessonPage.prototype.get_edit_form.call(this, jumpoptions);
            var editform = lessonPage.prototype.get_default_edit_form.call(this);
            var i = 1;
            editform += '<div id="mod_lesson_editor_answers">';
            for (jumpid in this.jumps) {
                editform += '<div class="mod_lesson_editor_answer">';
                if (i == 1) {
                    editform += '<h4>Correct response</h4>';
                } else if (i == 2) {
                    editform += '<h4>Wrong response</h4>';
                } else {
                    editform += '<h4>Extra response that should be removed</h4>';
                }
                editform += '<div>Answer</div>';
                editform += '<div><input type="text" id="mod_lesson_answer_' + i + '" value="' + this.jumps[jumpid].answer + '"/></div>';
                editform += '<div>Response</div>';
                editform += '<div><textarea id="mod_lesson_response_' + i + '">' + this.jumps[jumpid].response + '</textarea></div>';
                editform += pageJump(this.jumps[jumpid].id, this.id, jumpoptions, i);
                editform += '<div>Score</div>';
                editform += '<div><input type="text" name="score_' + i + '" id="mod_lesson_score_' + i + '" value="' + this.jumps[jumpid].score + '"/></div>';
                editform += '</div>';
                i++;
            }
            editform += '</div>';
            editform += '<div><button type="button" id="mod_lesson_editor_save_btn">Save</button>';
            editform += '<button type="button" id="mod_lesson_editor_cancel_btn">Cancel</button>';
            editform += '</div></div></form>';
            return editform;
        },
        save_edit_form: function(formobject) {
            lessonPage.prototype.save_edit_form.call(this, formobject);
        }
    }

    // Numerical
    var numerical_lessonPage = function(lessonobjectdata) {
        lessonPage.call(this, lessonobjectdata);
    };

    numerical_lessonPage.prototype = {
        in_cluster: function() {
            return lessonPage.prototype.in_cluster.call(this);
        },
        in_subcluster: function () {
            return lessonPage.prototype.in_subcluster.call(this);
        },
        update_jumps: function(jumpdata) {
            lessonPage.prototype.update_jumps.call(this, jumpdata);
        },
        get_edit_form: function(jumpoptions) {
            // return branchtable_lessonPage.prototype.get_edit_form.call(this, jumpoptions);
            var editform = lessonPage.prototype.get_default_edit_form.call(this);
            var i = 1;
            editform += '<div id="mod_lesson_editor_answers">';
            for (jumpid in this.jumps) {
                editform += '<div class="mod_lesson_editor_answer">';
                editform += '<h4>Answer ' + i + '</h4>';
                editform += '<div>Answer</div>';
                editform += '<div><input type="text" id="mod_lesson_answer_' + i + '" value="' + this.jumps[jumpid].answer + '"/></div>';
                editform += '<div>Response</div>';
                editform += '<div><textarea id="mod_lesson_response_' + i + '">' + this.jumps[jumpid].response + '</textarea></div>';
                editform += pageJump(this.jumps[jumpid].id, this.id, jumpoptions, i);
                editform += '<div>Score</div>';
                editform += '<div><input type="text" name="score_' + i + '" id="mod_lesson_score_' + i + '" value="' + this.jumps[jumpid].score + '"/></div>';
                editform += '</div>';
                i++;
            }
            editform += '</div>';
            editform += '<div><button type="button" id="mod_lesson_editor_save_btn">Save</button>';
            editform += '<button type="button" id="mod_lesson_editor_cancel_btn">Cancel</button>';
            editform += '<button type="button" id="mod_lesson_editor_addjump_btn">Add another jump</button>';
            editform += '</div></div></form>';
            return editform;
        },
        save_edit_form: function(formobject) {
            lessonPage.prototype.save_edit_form.call(this, formobject);
        },
        add_additional_jump: function(event) {
            // console.log(event);
            var jumpoptions = event.data.jumpoptions;
            // Get the last jump count.
            var i = 1;
            while ($('#mod_lesson_answer_' + i).length) {
                i++;
            }
            // Check should be done for maximum number of jumps.
            var editform = '<div class="mod_lesson_editor_answer">';
            editform += '<h4>Answer ' + i + '</h4>';
            editform += '<div>Answer</div>';
            editform += '<div><input type="text" id="mod_lesson_answer_' + i + '" value=""/></div>';
            editform += '<div>Response</div>';
            editform += '<div><textarea id="mod_lesson_response_' + i + '"></textarea></div>';
            editform += pageJump(0, this.id, jumpoptions, i);
            editform += '<div>Score</div>';
            editform += '<div><input type="text" name="score_' + i + '" id="mod_lesson_score_' + i + '" value=""/></div>';
            editform += '</div>';
            $('#mod_lesson_editor_answers').append(editform);
            // return editform;
        },
    }

    // Short answer
    var shortanswer_lessonPage = function(lessonobjectdata) {
        lessonPage.call(this, lessonobjectdata);
    };

    shortanswer_lessonPage.prototype = {
        in_cluster: function() {
            return lessonPage.prototype.in_cluster.call(this);
        },
        in_subcluster: function () {
            return lessonPage.prototype.in_subcluster.call(this);
        },
        update_jumps: function(jumpdata) {
            lessonPage.prototype.update_jumps.call(this, jumpdata);
        },
        get_edit_form: function(jumpoptions) {
            return branchtable_lessonPage.prototype.get_edit_form.call(this, jumpoptions);
        },
        save_edit_form: function(formobject) {
            lessonPage.prototype.save_edit_form.call(this, formobject);
        }
    }

    // End of branch
    var endofbranch_lessonPage = function(lessonobjectdata) {
        lessonPage.call(this, lessonobjectdata);
    };

    endofbranch_lessonPage.prototype = {
        in_cluster: function() {
            return lessonPage.prototype.in_cluster.call(this);
        },
        update_jumps: function(jumpdata) {
            lessonPage.prototype.update_jumps.call(this, jumpdata);
        }
    }

    // Content page
    var branchtable_lessonPage = function(lessonobjectdata) {
        lessonPage.call(this, lessonobjectdata);
    };

    branchtable_lessonPage.prototype = {
        in_cluster: function() {
            return lessonPage.prototype.in_cluster.call(this);
        },
        in_subcluster: function() {
            return lessonPage.prototype.in_subcluster.call(this);
        },
        update_jumps: function(jumpdata) {
            lessonPage.prototype.update_jumps.call(this, jumpdata);
        },
        get_edit_form: function(jumpoptions) {
            var editform = lessonPage.prototype.get_default_edit_form.call(this);
            var i = 1;
            editform += '<div id="mod_lesson_editor_answers">';
            for (jumpid in this.jumps) {
                editform += '<div class="mod_lesson_editor_answer">';
                editform += '<h4>Content ' + i + '</h4>';
                editform += '<div>Jump name</div>';
                editform += '<div><input name="jump_name_' + i + '" type="text" id="mod_lesson_answer_' + i + '" value="' + this.jumps[jumpid].answer + '"/></div>';
                editform += pageJump(this.jumps[jumpid].id, this.id, jumpoptions, i);
                editform += '</div>';
                i++;
            }
            editform += '</div>';
            editform += '<div><button type="button" id="mod_lesson_editor_save_btn">Save</button>';
            editform += '<button type="button" id="mod_lesson_editor_cancel_btn">Cancel</button>';
            editform += '<button type="button" id="mod_lesson_editor_addjump_btn">Add another jump</button>';
            editform += '</div></div></form>';
            return editform;
        },
        add_additional_jump: function(event) {
            // console.log(event);
            var jumpoptions = event.data.jumpoptions;
            // Get the last jump count.
            var i = 1;
            while ($('#mod_lesson_answer_' + i).length) {
                i++;
            }
            // Check should be done for maximum number of jumps.
            var editform = '<div class="mod_lesson_editor_answer">';
            editform += '<h4>Content ' + i + '</h4>';
            editform += '<div>Jump name</div>';
            editform += '<div><input name="jump_name_' + i + '" type="text" id="mod_lesson_answer_' + i + '" value=""/></div>';
            editform += pageJump(0, this.id, jumpoptions, i);
            editform += '</div>';
            $('#mod_lesson_editor_answers').append(editform);
            // return editform;
        },
        save_edit_form: function(formobject) {
            lessonPage.prototype.save_edit_form.call(this, formobject);
        }
    }

    // Essay page
    var essay_lessonPage = function(lessonobjectdata) {
        lessonPage.call(this, lessonobjectdata);
    };

    essay_lessonPage.prototype = {
        in_cluster: function() {
            return lessonPage.prototype.in_cluster.call(this);
        },
        in_subcluster: function() {
            return lessonPage.prototype.in_subcluster.call(this);
        },
        update_jumps: function(jumpdata) {
            lessonPage.prototype.update_jumps.call(this, jumpdata);
        },
        get_edit_form: function(jumpoptions) {
            return branchtable_lessonPage.prototype.get_edit_form.call(this, jumpoptions);
        },
        save_edit_form: function(formobject) {
            lessonPage.prototype.save_edit_form.call(this, formobject);
        }
    }

    // Matching page
    var matching_lessonPage = function(lessonobjectdata) {
        lessonPage.call(this, lessonobjectdata);
    };

    matching_lessonPage.prototype = {
        in_cluster: function() {
            return lessonPage.prototype.in_cluster.call(this);
        },
        in_subcluster: function () {
            return lessonPage.prototype.in_subcluster.call(this);
        },
        update_jumps: function(jumpdata) {
            lessonPage.prototype.update_jumps.call(this, jumpdata);
        },
        get_edit_form: function(jumpoptions) {
            return branchtable_lessonPage.prototype.get_edit_form.call(this, jumpoptions);
        },
        save_edit_form: function(formobject) {
            lessonPage.prototype.save_edit_form.call(this, formobject);
        }
    }

    // Multichoice page
    var multichoice_lessonPage = function(lessonobjectdata) {
        lessonPage.call(this, lessonobjectdata);
    };

    multichoice_lessonPage.prototype = {
        in_cluster: function() {
            return lessonPage.prototype.in_cluster.call(this);
        },
        in_subcluster: function () {
            return lessonPage.prototype.in_subcluster.call(this);
        },
        update_jumps: function(jumpdata) {
            lessonPage.prototype.update_jumps.call(this, jumpdata);
        },
        get_edit_form: function(jumpoptions) {
            return numerical_lessonPage.prototype.get_edit_form.call(this, jumpoptions);
        },
        save_edit_form: function(formobject) {
            lessonPage.prototype.save_edit_form.call(this, formobject);
        },
        add_additional_jump: function(jumpoptions) {
            numerical_lessonPage.prototype.add_additional_jump.call(this, jumpoptions);
        }
    }

    // End Lesson page types

    // Lesson page edit elements

    var pageTitle = function(pageid, title) {
        var html;
        html = '<div>Page Title</div>';
        html += '<div><input type="input" name="title" class="mod_lesson_title" id="mod_lesson_title_' + pageid + '" value="' + title + '" /></div>';
        return html;
    };

    var pageContents = function(pageid, contents) {
        var html;
        html = '<div>Page Content</div>';
        html += '<div><textarea name="contents" id="mod_lesson_contents_' + pageid + '">' + contents + '</textarea></div>';
        return html;
    };

    var pageJump = function(jumpid, pageid, jumpoptions, number) {
        var html;
        html = '<div>Jump</div>';
        html += '<select name="jump_' + number + '" id="mod_lesson_jump_select_' + number + '">';
        // $.each(jumpoptions ,function(index, jumpoption) {
        for (index in jumpoptions) {
            if (jumpid == index) {
                html += '<option value="' + index + '" selected>' + jumpoptions[index] + '</option>';
            } else {
                html += '<option value="' + index + '">' + jumpoptions[index] + '</option>';
            }
        }
        html += '</select>';
        return html;
    }
    // End of Lesson page edit elements



    var drawline = function(pagefrom, pageto) {
        if (pageto === 0) {
            return;
        }
        if (!document.getElementById('mod_lesson_page_element_' + pagefrom)) {
            return;
        }
        if (!document.getElementById('mod_lesson_page_element_' + pageto)) {
            return;
        }

        var fromoffset = $('#mod_lesson_page_element_' + pagefrom).offset();
        var tooffset = $('#mod_lesson_page_element_' + pageto).offset();

        var fromx = fromoffset.left + $('#mod_lesson_page_element_' + pagefrom).width();
        var fromy = fromoffset.top + $('#mod_lesson_page_element_' + pagefrom).height();

        var length = Math.sqrt(((tooffset.left - fromx) * (tooffset.left - fromx)) +
                ((tooffset.top - fromy) * (tooffset.top - fromy)));
        var angle = Math.atan2((fromy - tooffset.top), (fromx - tooffset.left)) * (180 / Math.PI);
        var cx = ((fromx + tooffset.left) / 2) - (length / 2);
        var cy = ((fromy + tooffset.top) / 2) - 1;
        var htmlline = "<div class='lessonline' style='left:" + cx + "px; top:" + cy + "px; width:" + length + "px;";
        htmlline += " -moz-transform:rotate(" + angle + "deg); -webkit-transform:rotate(" + angle + "deg);";
        htmlline += " -o-transform:rotate(" + angle + "deg); -ms-transform:rotate(" + angle + "deg);";
        htmlline += " transform:rotate(" + angle + "deg);' />";
        $('body').append(htmlline);
    };

    var drawalllines = function() {
        $('.lessonline').remove();
        for (lpid in lesson.pages) {
            var currentobject = lesson.pages[lpid];
            for (jumpid in currentobject.jumps) {
                if (currentobject.jumps[jumpid].id == -1) {
                    nextpageid = currentobject.nextpageid;
                } else {
                    nextpageid = currentobject.jumps[jumpid].id;
                }

                if (currentobject.qtype == 31) {
                    drawline(currentobject.clusterid, nextpageid);
                } else if (nextpageid === "-9") {
                    drawline(currentobject.id, currentobject.id + '1');
                } else if (!currentobject.in_cluster()) {
                    drawline(currentobject.id, nextpageid);
                }
            }
        }
    };


    var attachElement = function(event, ui) {
        var elementid = ui.helper.attr('id');
        var pagesections = elementid.split('_');
        var pageid = pagesections[4];
        var ischild = $(this).find(ui.helper).length;
        if (ischild) {
            return;
        }

        // Need to get the cluster id as well.
        var parentpageid = $(this).attr('id');
        pagesections = parentpageid.split('_');
        var parentid = pagesections[4];

        lesson.pages[pageid].location = 'cluster';

        var extrasauce = null;

        // Checks for content page (difficulty starts here)
        if (lesson.pages[pageid].qtype == 20) {
            lesson.pages[pageid].location = 'subcluster';

            // Check first to see if there is already an end of branch record before creating another one.
            // Create the end of branch page.
            record = {
                qtype: "21",
                lessonid: lesson.id,
                title: "Default title",
                contents: "",
                positionx: 0,
                positiony: 0,
                prevpageid: pageid,
                nextpageid: 0
            };

            // ajax call
            var endofbranch = $.Deferred();


            var promise = $.ajax({
                method: "POST",
                url: ajaxlocation,
                dataType: "json",
                data: {
                    action: "createcontent",
                    lessonid: lessonid,
                    lessondata: record
                }
            });

            promise.done(function(lessonpage) {
                endofbranch.resolve(lessonpage);
            });

            promise.fail(function(e) {
                console.log(e);
            });
            // return endofbranch.promise();
            // console.log(endofbranch.promise());
            extrasauce = endofbranch.promise();
            // return;

            // return a promise
        }

        // Find location in cluster for item.
        // See if there are any childelements.
        var afterid = null;
        if (lesson.pages[parentid].childrenids.length > 0) {
            afterid = lesson.pages[parentid].childrenids[lesson.pages[parentid].childrenids.length -1];
        } else {
            afterid = lesson.pages[parentid].id;
        }

        // Put a when in here.
        $.when(pageid, extrasauce).done(function(var1, var2) {
            // console.log(var1);
            // console.log(var2);

            var pageids = [var1];
            if (var2 !== null) {

                // Create whole object for internal use.
                lesson.add_lessonpage(var2.id, var2);
                lesson.pages[var2.prevpageid].nextpageid = var2.id;
                lesson.pages[var2.id].qtypestr = "End of branch";
                lesson.pages[var2.id].location = "subcluster";
                lesson.pages[var2.id].subclusterid = var1;
                lesson.pages[var2.id].nextpageid = parentid;

                pageids.push(var2.id);
            }
            // console.log(pageids);

            for (index in pageids) {
                // Check that this item isn't already in the array.
                if ($.inArray(pageids[index], lesson.pages[parentid].childrenids) == -1) {
                    lesson.pages[parentid].childrenids.push(pageids[index]);
                }

                var previousid = lesson.pages[pageids[index]].previouspageid;
                var nextid = lesson.pages[pageids[index]].nextpageid;
                if (previousid !== "0") {
                    lesson.pages[previousid].nextpageid = nextid;
                }
                if (nextid !== "0") {
                    lesson.pages[nextid].previouspageid = previousid;
                }
            }

            movepageids = pageids.join();

            lessondata = {
                pageid: movepageids,
                after: afterid
            }

            // Try some ajax here.
            $.ajax({
                method: "POST",
                url: ajaxlocation,
                dataType: "json",
                data: {
                    action: "movepage",
                    lessonid: lesson.id,
                    lessondata: lessondata
                }
            })
                .done(function(newobject) {
                    formatClusters();
                    formatSubClusters();

                })
        });
        // console.log(lesson);
        ui.helper.detach();
        $(this).append(ui.helper);
        
    };

    var detachElement = function(event, ui) {
        var ischild = $(this).find(ui.helper).length;
        // var lastoffset = ui.helper.offset();
        if (ischild) {
            ui.helper.detach();
            $(".mod_lesson_pages").append(ui.helper);
            // ui.helper.offset({left: event.pageX, top: event.pageY});
            formatClusters();
        }
    };

    var formatClusters = function() {
        for (var lessonpageid in lesson.pages) {
            var currentobject = lesson.pages[lessonpageid];
            if (currentobject.qtype === 30) {
                // We have a cluster. Now count how many children it has.
                var childcount = currentobject.childrenids.length;
                
                var childwidth = 270;
                var childheight = 100;
                // var clusterwidth = $("#mod_lesson_page_element_" + lessonpageid).width();
                var clusterheight = $("#mod_lesson_page_element_" + lessonpageid).height();
                var newwidth = 0;
                var newheight = 0;
                
                if  (childcount > 3) {
                    newwidth = childwidth * 3;
                    additionalheight = (Math.ceil(childcount / 3) * childheight) + 10;
                    newheight = 100 + additionalheight;
                } else {
                    var newchildcount = childcount;
                    if (childcount == 0) {
                        newchildcount = 1;
                    }
                    newwidth = childwidth * newchildcount;
                    if (clusterheight == 200) {
                        // Height does not need to be adjusted.
                        newheight = clusterheight;
                    } else {
                        newheight = clusterheight + (childheight * 1);
                    }
                }
                

                // Adjust the cluster width.
                $("#mod_lesson_page_element_" + lessonpageid).width(newwidth);
                // Adjust the cluster height.
                $("#mod_lesson_page_element_" + lessonpageid).height(newheight);
                // Position children in the cluster.
                if (childcount) {
                    var originalx = $("#mod_lesson_page_element_" + lessonpageid).offset().left + 10;
                    var startx = originalx;
                    var starty = $("#mod_lesson_page_element_" + lessonpageid).offset().top + 80;
                    for (var key in currentobject.childrenids) {
                        if ((key % 3) === 0 && key !== "0") {
                            starty = starty + 110;
                            $("#mod_lesson_page_element_" + currentobject.childrenids[key]).offset({
                                top: starty,
                                left: originalx
                            });
                            startx = originalx + $("#mod_lesson_page_element_" + currentobject.childrenids[key]).width() + 30;
                        } else {
                            $("#mod_lesson_page_element_" + currentobject.childrenids[key]).offset({top: starty, left: startx});
                            startx = startx + $("#mod_lesson_page_element_" + currentobject.childrenids[key]).width() + 30;
                        }

                    }
                }
                $("#mod_lesson_page_element_" + lessonpageid).droppable({
                    drop: attachElement,
                    out: detachElement
                });
            }
        }
    };

    var formatSubClusters = function() {
        for (var lessonpageid in lesson.pages) {
            var currentpage = lesson.pages[lessonpageid];
            if (currentpage.qtype === 20 && currentpage.in_subcluster()) {
                // We have a subcluster. Now count how many children it has.
                var childcount = currentpage.childrenids.length;
                if (childcount === 0) {
                    childcount = 1;
                }
                var childwidth = 250;
                var childheight = 100;
                // var clusterwidth = $("#mod_lesson_page_element_" + lessonpageid).width();
                var subclusterheight = $("#mod_lesson_page_element_" + lessonpageid).height();
                var newwidth = 0;
                var newheight = 0;
                
                if  (childcount > 3) {
                    newwidth = childwidth * 3;
                    newheight = 100 + (Math.ceil(childcount / 3) * childheight) + 10;
                } else {
                    newwidth = childwidth * childcount;
                    newheight = 100 + (childheight * 1);
                }

                // Adjust the subcluster width.
                $("#mod_lesson_page_element_" + lessonpageid).width(newwidth);
                // Adjust the subcluster height.
                $("#mod_lesson_page_element_" + lessonpageid).height(newheight);
                // Position children in the subcluster.
                var originalx = $("#mod_lesson_page_element_" + lessonpageid).offset().left + 10;
                var startx = originalx;
                var starty = $("#mod_lesson_page_element_" + lessonpageid).offset().top + 80;
                for (var key in currentpage.childrenids) {
                    
                    if ((key % 3) === 0 && key !== "0") {
                        starty = starty + 110;
                        $("#mod_lesson_page_element_" + currentpage.childrenids[key]).offset({
                            top: starty,
                            left: originalx
                        });
                        startx = originalx + $("#mod_lesson_page_element_" + currentpage.childrenids[key]).width() + 30;
                    } else {
                        $("#mod_lesson_page_element_" + currentpage.childrenids[key]).offset({top: starty, left: startx});
                        startx = startx + $("#mod_lesson_page_element_" + currentpage.childrenids[key]).width() + 30;
                    }

                }
                // Change the title of the sub cluster from "Content" to "Sub cluster".
                var subclusterheader = "Sub cluster";
                subclusterheader += '<img src="../../theme/image.php?theme=clean&component=core&image=t%2Fedit" class="mod_lesson_page_object_menu"></div></header>';
                $("#mod_lesson_page_element_" + lessonpageid + "_header").html(subclusterheader);
                $("#mod_lesson_page_element_" + lessonpageid).droppable({
                    drop: attachElement,
                    out: detachElement
                });
            }
        }
    };

    var replacecontent = function(event, ui) {
        pagetype = ui.helper.text();
        var htmlelement = "<div class='mod_lesson_menu_item'>" + pagetype + "</div>";
        $('.mod_lesson_menu').append(htmlelement);
        $(".mod_lesson_menu_item").draggable({
             stop: createLessonObject
        });
    };

    var createLessonObject = function(event, ui) {
        var pagetype = ui.helper.text();
        var qtype,
            content,
            title,
            location;
        switch(pagetype) {
            case "Content":
                qtype = "20";
                content = "";
                title =  "Default title";
                location = "normal";
                break;
            case "True False":
                qtype = "2";
                content = "";
                title =  "Default title";
                location = "normal";
                break;
            case "Numerical":
                qtype = "8";
                content = "";
                title =  "Default title";
                location = "normal";
                break;
            case "Multiple Choice":
                qtype = "3";
                content = "";
                title =  "Default title";
                location = "normal";
                break;
            case "Cluster":
                qtype = "30";
                content = "Cluster";
                title =  "Cluster";
                location = "cluster";
                break;
            default:
                qtype = "0";
                content = "";
                title =  "Default title";
                location = "normal";
                break;
        }
        
        if (!ui.helper.hasClass('mod_lesson_page_element')) {
            var lastoffset = ui.helper.offset();
            ui.helper.addClass('mod_lesson_page_element');
            ui.helper.removeClass('mod_lesson_menu_item');

            var defaultdata = {
                title: title,
                contents: content,
                qtype: qtype,
                lessonid: lesson.id,
                location: location,
                previouspageid: "0",
                nextpageid: "0",
                positionx: Math.round(lastoffset.left),
                positiony: Math.round(lastoffset.top)
            };



            // Try some ajax here.
            $.ajax({
                method: "POST",
                url: ajaxlocation,
                dataType: "json",
                data: {
                    action: "createcontent",
                    lessonid: lesson.id,
                    lessondata: defaultdata
                }
            })
                .done(function(newobject) {
                    if (newobject.qtype !== "31") {
                        ui.helper.attr('id', 'mod_lesson_page_element_' + newobject.id);
                        var htmlelement = '<header id="mod_lesson_page_element_' + newobject.id + '_header">' + pagetype;
                        htmlelement += '<img src="../../theme/image.php?theme=clean&component=core&image=t%2Fedit" class="mod_lesson_page_object_menu"></div></header>';
                        htmlelement += '<div class="mod_lesson_page_element_body" id="mod_lesson_page_element_' + newobject.id + '_body">' + newobject.title + '</div>';
                        $("#mod_lesson_page_element_" + newobject.id).html(htmlelement);
                    }


                    lesson.add_lessonpage(newobject.id, newobject);
                    lesson.pages[newobject.prevpageid].nextpageid = newobject.id;
                    // This is really bad, need to figure out another way to do this.
                    if (newobject.qtype === "30") {
                        // Add the end of cluster object.
                        var endofclusterid = (parseInt(newobject.id)) + 1;
                        var endofclusterdata = {
                            clusterid: newobject.id,
                            contents: "End of cluster",
                            id: endofclusterid,
                            lessonid: lesson.id,
                            location: "normal",
                            nextpageid: "0", 
                            positionx: "0",
                            positiony: "0",
                            qtype: 31,
                            qtypestring: "End of cluster",
                            title: "End of cluster"
                        }
                        lesson.add_lessonpage(endofclusterid, endofclusterdata);
                        lesson.pages[newobject.id].nextpageid = endofclusterid;
                    }

                    if (newobject.qtype !== "31") {
                        ui.helper.detach();
                        $('.mod_lesson_pages').append(ui.helper);
                        $("#mod_lesson_page_element_" + newobject.id).offset(lastoffset);
                        if (pagetype === "Cluster") {
                            formatClusters();
                        }
                        resetListeners();
                        drawalllines();
                    }
                })

                .fail(function(e) {
                    console.log(e);
                })

        }
        drawalllines();
        resetListeners();

    };

    /**
     * May need to be expanded to return the full record for the lesson page.
     */
    var getJumpOptions = function(pageid) {
        var jumpoptions = $.Deferred();

        var promise = $.ajax({
            method: "POST",
            url: ajaxlocation,
            dataType: "json",
            data: {
                action: "getjumpoptions",
                lessonid: lessonid,
                pageid: pageid
            }
        });

        promise.done(function(options) {
            jumpoptions.resolve(options);
        });

        promise.fail(function(e) {
            console.log(e);
        });
        return jumpoptions.promise();
    };

    var openEditor = function(event) {
        event.preventDefault();
        event.stopPropagation();
        var elementid = $(this).parents('.mod_lesson_page_element').attr('id');
        var pageids = elementid.split('_');
        var pageid = pageids[4];
        var jumpselectoptions = '';

        closeObjectMenus();

        $.when(getJumpOptions(pageid)).done(function(joptions){
            // console.log(joptions);

            // var frm = new form();
            // frm.addFieldset('general', 'General');
            // textfield = new text();
            // textfield.set_name('title');
            // textfield.set_id('mod_lesson_title_' + pageid);
            // textfield.set_label('Page Title');
            // textfield.set_required();
            // textfield.set_text(lesson.pages[pageid].title);
            // frm.addElement(textfield);
            // selectfield = new selectElement();
            // selectfield.set_name('Jump_1');
            // selectfield.set_label('Jumps');
            // selectfield.set_id("mod_lesson_jump_1_" + pageid);
            // selectfield.set_options(joptions);
            // frm.addElement(selectfield);
            // $.when(frm.printForm()).then(function(tempformstuff) {
            //     $('.mod_lesson_pages').append(tempformstuff); 
            //     // $('#mod_lesson_editor_addjump_btn').click({jumpoptions: joptions}, lesson.pages[pageid].add_additional_jump);
            //     $('#mod_lesson_editor_save_btn').click(function() {
            //         // Could do client side validation here.
            //         if ($("#mod_lesson_title_" + pageid).val() == "") {
            //             var textelement = $("#fitem_mod_lesson_title_" + pageid);
            //             frm.handleError('title', textelement, 'text', 'Title should not be empty');
            //         } else {
            //             lesson.pages[pageid].save_edit_form(frm);
            //         }
            //     });
            //     $('#mod_lesson_editor_cancel_btn').click(function() {
            //         $('.mod_lesson_page_editor').remove();
            //     });
            // });

            // formfactory.create();
            // formfactory.addElement('text', 'title', lesson.pages[pageid].title, "Page Title");
            // formfactory.makeRequired('text');
            // formfactory.addElement('select', 'jump_1', 'Jumps', 'Jumps');
            // var tmep = formfactory.getFormData();
            // console.log(tmep);

            var formdata = {
                lessonpagetype: lesson.pages[pageid].qtypestring,
                pageid: pageid,
                // title: lesson.pages[pageid].title,
                title: {
                    id: "mod_lesson_title_" + pageid,
                    label: "Page Title",
                    name: "title",
                    text: lesson.pages[pageid].title,
                    required: 1
                },
                contents: lesson.pages[pageid].contents,
                select: {
                    id: pageid,
                    label: "Jumps",
                    name: "Jump_1",
                    options: formfactory.formatOptions(joptions),
                    // required: 1,
                    helpButton: "<button type=\"button\">Help is here</button>"

                }
            };
            $.when(templates.render('mod_lesson/page_editor', formdata)).done(function(pageeditor) {
                $('.mod_lesson_pages').append(pageeditor); 
                $('#mod_lesson_editor_addjump_btn').click({jumpoptions: joptions}, lesson.pages[pageid].add_additional_jump);
                // $('#mod_lesson_editor_save_btn').click(lesson.pages[pageid].save_edit_form());
                // $('#mod_lesson_editor_save_btn').click({pageid: pageid}, saveTheCheerleader);
                $('#mod_lesson_editor_save_btn').click(function() {
                    // Do client side validation.
                    var titleelement = $("#mod_lesson_title_" + pageid);
                    if (titleelement.val() == "") {
                        var errors = {
                            errors: {
                                name: "title",
                                message: "The lesson page title should not be empty"
                            }
                        };
                        formfactory.handleErrors(errors, formdata);
                    } else {
                        lesson.pages[pageid].save_edit_form(formdata);
                    }
                });
                $('#mod_lesson_editor_cancel_btn').click(function() {
                    $('.mod_lesson_page_editor').remove();
                });
            });
            // // Create a page for editing the content.
            // var pageeditor = lesson.pages[pageid].get_edit_form(joptions);
            // $('.mod_lesson_pages').append(pageeditor);
            // $('#mod_lesson_editor_addjump_btn').click({jumpoptions: joptions}, lesson.pages[pageid].add_additional_jump);
            // // $('#mod_lesson_editor_save_btn').click(lesson.pages[pageid].save_edit_form());
            // $('#mod_lesson_editor_save_btn').click({pageid: pageid}, saveTheCheerleader);
            // $('#mod_lesson_editor_cancel_btn').click(function() {
            //     $('.mod_lesson_page_editor').remove();
            // });
        });
    };

    /**
     * Save edited lesson page content.
     */
    var saveTheCheerleader = function(event) {
        var pageid = event.data.pageid;
        lesson.pages[pageid].save_edit_form();

    };

    var openObjectMenu = function(event) {

        if (!$(this).parent().children('.mod_lesson_page_object_menu_thing').length) {
            var menu = '<div class="mod_lesson_page_object_menu_thing">';
            menu += '<ul>';
            menu += '<li><a href="#" class="mod_lesson_page_edit">Edit</a></li>';
            menu += '<li><a href="#" class="mod_lesson_page_link">Link</a></li>';
            menu += '<li><a href="#" class="mod_lesson_page_delete">Delete</a></li>';
            menu += '</ul>';
            menu += '</div>';
            $(this).parent().append(menu);
            $('.mod_lesson_page_delete').on({
                click: deleteLessonPageObject
            });
            $('.mod_lesson_page_edit').on({
                click: openEditor
            });
            $('.mod_lesson_page_link').on({
                click: linkLessonPage
            });
        } else {
            $(this).parent().find('.mod_lesson_page_object_menu_thing').remove();
        }
    }

    var closeObjectMenus = function() {
        $('.mod_lesson_main').find('.mod_lesson_page_object_menu_thing').remove();
    };

    var deleteLessonPageObject = function(event) {
        event.preventDefault();
        var elementid = $(this).parents('.mod_lesson_page_element').attr('id');
        var pageids = elementid.split('_');
        var pageid = pageids[4];
        var pagedata = {};
        if (lesson.pages[pageid].qtype == 30) {
            // Find the matching end of cluster.
            for (index in lesson.pages) {
                if (parseInt(lesson.pages[index].clusterid) == pageid) {
                    pagedata.endofclusterid = lesson.pages[index].id;
                }
            }
        }
        if (lesson.pages[pageid].qtype == 20) {
            // Find the matching end of subcluster if it exists.
            for (index in lesson.pages) {
                if (parseInt(lesson.pages[index].subclusterid) == pageid) {
                    pagedata.endofclusterid = lesson.pages[index].id;
                }
            }
        }

        $.ajax({
            method: "POST",
            url: ajaxlocation,
            dataType: "json",
            data: {
                action: "deletelessonpage",
                lessonid: lessonid,
                pageid: pageid,
                lessondata: pagedata
            }
        })
            .done(function() {
                // if (lesson.pages[pageid].qtype == 30) {
                if (pagedata.endofclusterid.length) {
                    delete lesson.pages[pagedata.endofclusterid];
                }
                delete lesson.pages[pageid];

            })

            .fail(function(e) {
                console.log(e);
            });
        $(this).parents('.mod_lesson_page_element').remove();
        closeObjectMenus();
        drawalllines();
    };

    var linkLessonPage = function(event) {
        event.preventDefault();
        event.stopPropagation();
        // Should use parents instead of a lot of parent.
        var elementid = $(this).parent().parent().parent().parent().attr('id');
        var pageids = elementid.split('_');
        var pageid = pageids[4];
        $('.mod_lesson_page_element').click({pageid: pageid}, actuallyLinkLessonPages);
        $('.mod_lesson_page_element').hover(hoverin, hoverout);
    };

    var hoverin = function() {
        $(this).css('background-color', '#b8b8b8');
    };

    var hoverout = function() {
        $(this).css('background-color', 'white');
    };

    var actuallyLinkLessonPages = function(event) {
        var objectid = this.id;
        var jumpids = objectid.split('_');
        var jumpid = jumpids[4];
        var pageid = event.data.pageid;
        $('.mod_lesson_page_element').unbind('click');
        $(this).css('background-color', 'white');
        $('.mod_lesson_page_element').off("mouseenter mouseleave");

        var lessondata = {
            pageid: pageid,
            jumpid: jumpid
        };

        // Go go ajax link and stuff.
        $.ajax({
            method: "POST",
            url: ajaxlocation,
            dataType: "json",
            data: {
                action: "linklessonpages",
                lessonid: lessonid,
                lessondata: lessondata
            }
        })
            .done(function(response) {

                lesson.pages[pageid].update_jumps(response);
                drawalllines();
            })

            .fail(function(e) {
                console.log(e);
            });
        closeObjectMenus();

    };

    var editTitle = function(event) {
        var classdetail = event.currentTarget.id;
        var pageids = classdetail.split('_');
        var pageid = pageids[4];
        var innertext = event.currentTarget.innerText;
        var inputid = 'mod_lesson_inline_edit_' + pageid;
        event.currentTarget.innerHTML = '<input type="text" id="' + inputid + '" value="' + innertext + '"/>';
        $('#' + inputid).keydown(function(e) {
            if (e.which == 13) {
                alert('save this title');
            } 
            if (e.which == 27) {
                event.currentTarget.innerHTML = innertext;
            }
        });
    };

    var resetListeners = function() {

        $(".mod_lesson_page_element").draggable({
            drag: drawalllines,
            stop: saveLocation
        });

        // Remove handler so that we don't double up with other elements.
        $(".mod_lesson_page_element").unbind('dblclick');
        $(".mod_lesson_page_object_menu").unbind('click');

        // $(".mod_lesson_page_element").on({
        //     dblclick: openEditor
        // });

        $(".mod_lesson_page_object_menu").on({
            click: openObjectMenu
        });        

        $(".mod_lesson_menu_item").draggable({
            stop: createLessonObject
        });

        $('.mod_lesson_pages').scroll(function() {
            drawalllines();
        });

        $('.mod_lesson_page_element_body').on({
            dblclick: editTitle
        });

    };

    var saveLocation = function(event, ui) {
        var lastposition = ui.helper.position();
        var elementid = this.id;
        var pageids = elementid.split('_');
        var pageid = pageids[4];
        var lessonobjectdata = {
            pageid: pageid,
            positionx: Math.round(lastposition.left),
            positiony: Math.round(lastposition.top)
        };

        // Try some ajax here.
        $.ajax({
            method: "POST",
            url: ajaxlocation,
            dataType: "json",
            data: {
                action: "saveposition",
                lessonid: lessonid,
                lessondata: lessonobjectdata
            }
        })
            .done(function() {
                // Not doing anything at the moment.
            });
    };

    var setLessonPages = function() {
        for (elementid in lessonobjects) {
            // End of cluster elements have been removed from this form.
            if (lessonobjects[elementid].qtype !== "31" && lessonobjects[elementid].qtype !== "21") {
                var newx = lessonobjects[elementid].x;
                var newy = lessonobjects[elementid].y;
                var lessonelement = $("#mod_lesson_page_element_" + elementid);
                var parentelement = lessonelement.parent();
                lessonelement.position({
                    my: "left top",
                    at: "left+" + newx + " top+" + newy,
                    of: parentelement
                });
            }
        }
    };

    var setLessonData = function(lessonid, pageid) {
        var tmepthing = $.Deferred();

        var lessondata = {
            pageid: pageid
        };

        var promise = $.ajax({
            method: "POST",
            url: ajaxlocation,
            dataType: "json",
            data: {
                action: "getlessondata",
                lessonid: lessonid,
                lessondata: lessondata
            }
        });

        promise.done(function(lessonpages) {
            tmepthing.resolve(lessonpages);
        });

        promise.fail(function(e) {
            console.log(e);
        });
        return tmepthing.promise();
    };

    return {

        init: function(llessonid, pageid) {
            lessonid = llessonid;
            $.when(setLessonData(llessonid, pageid)).done(function(data) {
                lessonobjects = data;
                console.log(lessonobjects);

                lesson = new Lesson(lessonobjects);
                console.log(lesson);

                // Add end of lesson objects.
                // addEOL();
                // Format clusters.
                formatClusters();
                formatSubClusters();

                // Position all elements.
                setLessonPages();

                // Draw lines between all of the objects.
                drawalllines();

                // addMenu();
                resetListeners();

                $(".mod_lesson_menu").droppable({
                    out: replacecontent,
                    drop: function(event, ui) {
                        ui.draggable.remove();
                    }
                });
            });
        }
    };
});