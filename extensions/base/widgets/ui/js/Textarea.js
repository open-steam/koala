
function widgets_textarea_save_success_delete_me(elementId, response) {
    var widget = jQuery("#" + elementId);
    deJSON = jQuery.parseJSON(response.responseText);
    if (deJSON) {
        data = deJSON.data;
        if (widget.length !== 0) {
            widget.removeClass("saving");
            if (data.error === "none") {
                if (widget.find("textarea").hasClass("mce-full") || widget.find("textarea").hasClass("mce-small")) {
                    tinyMCE.activeEditor.isNotDirty = 1;
                    tinyMCE.activeEditor.setContent(data.newValue);
                    tinyMCE.activeEditor.undoManager.clear();
                    $(tinyMCE.activeEditor.getBody()).removeClass("mceNonEditable");
                    //tinyMCE.activeEditor.getBody().click();
                    tinyMCE.activeEditor.focus();
                } else if (widget.find("textarea").hasClass("plain")) {
                    widget.find("textarea").val(data.newValue);
                }
                widget.removeClass("dirty");
                //	widget.addClass("undo");
                widget.addClass("saved");
                widget.attr("data-oldValue", widget.attr("value"));
                widget.attr("value", data.newValue);
                $(window).unbind('beforeunload');
            } else {
                widget.addClass("error");
                widget.removeClass("dirty");
                widget.find(".icon.error").title = data.error;
            }
        }
    }
}

(function($) {
    var sendFunction;

    var methods = {
        init: function(options) {

            //we also could use 'this' but then we would have to make it a jQuery object [$(this)] each time
            
            var textarea = this;
            var identifier = options.id;
            textarea.toggleClass("dirty");
            var value = decodeURIComponent(options.value);
            textarea.attr("value", value);
            textarea.attr("data-oldValue", value);
            sendFunction = options.sendFunction;
            var mce_defaults = {
                mode: "specific_textareas",
                // General options
                theme: "advanced",
                content_css: "{PATH_URL}widgets/css/tinymce.css",
                skin: "o2k7",
                remove_linebreaks: false,
                convert_urls: false,
                verify_html: "false",
                language: "de",
                // Theme options
                theme_advanced_buttons3: "",
                theme_advanced_buttons4: "",
                theme_advanced_toolbar_location: "top",
                theme_advanced_toolbar_align: "left",
                theme_advanced_statusbar_location: "none",
                theme_advanced_resizing: false,
                onchange_callback: function(e) {
                    //executed each time the contend is modified
                    eval(identifier + " = " + 'tinyMCE.activeEditor.getContent()');
                },
                handle_event_callback: function(e) {
                    if (e.type === "keyup" && tinyMCE.activeEditor.isDirty()) {
                        textarea.addClass("changed");
                        $(window).bind('beforeunload', function() {
                            return textarea.attr("data-leaveMessage");
                        });
                    }
                    ;
                    if (!tinyMCE.activeEditor.isDirty()) {
                        textarea.removeClass("changed");
                    }

                },
                oninit: function(e) {
                    //if the editor is loaded set the value and set it to notDirty
                    tinyMCE.activeEditor.setContent(value), tinyMCE.activeEditor.isNotDirty = 1
                },
                setup: function(e) {
                    setInterval(function() {
                        
                        value = "";
                        if (textarea.find("textarea").hasClass("mce-full") || textarea.find("textarea").hasClass("mce-small")) {
                           $(tinyMCE.activeEditor.getBody()).addClass("mceNonEditable");
                            var value = tinyMCE.activeEditor.getContent();
                        } else if (textarea.find("textarea").hasClass("plain")) {
                            var value = textarea.find("textarea").val();
                        }
                        var oldValue = textarea.attr("data-oldValue");
                        if (tinyMCE.activeEditor && tinyMCE.activeEditor.isDirty()) {
                            if (oldValue !== value) {
                                textarea.addClass("dirty");

                              //  $(window).bind('beforeunload', function() {
                                  //  return 'LeaveMessage';
                               // });
                            }

                        }
                        ;
                        if (tinyMCE.activeEditor && !tinyMCE.activeEditor.isDirty()) {
                            textarea.removeClass("dirty");
                        }
                    }, 1000);
                }
            };

            if (textarea.hasClass("mce-small")) {

                load("mce", function() {
                    tinyMCE.init($.extend({
                        editor_selector: "mce-small",
                        plugins: "emotions,paste,noneditable",
                        // Theme options
                        theme_advanced_buttons1: "bold,italic,underline,|,bullist,numlist,|,image,link,unlink,|,forecolor,removeformat,|,undo,redo,pasteword",
                        theme_advanced_buttons2: "",
                        readonly: tinymceReadOnly
                    }, mce_defaults));
                });

            } else if (textarea.hasClass("mce-full")) {

                //probably not needed
                //protect unsaved text
                /*
                $(window).unload(function() {
                    var dirtyTextareas = jQuery('#content').find('.widget.textarea.dirty');
                    if (textarea.hasClass("mce-full") || textarea.hasClass("mce-small")) {
                        $(tinyMCE.activeEditor.getBody()).addClass("mceNonEditable");
                        var value = tinyMCE.activeEditor.getContent();
                    } else if (textarea.hasClass("plain")) {
                        var value = textarea.find("textarea").val();
                    }
                    var oldValue = textarea.attr("data-oldValue");
                    if (oldValue.trim() !== value.trim()) {
                        //$(window).unbind( “unload” );
                        //window.onbeforeunload = state ? function() { return "text1"; } : null;
                        //return false;

                        //if (confirm('Sollen alle nicht gespeicherten Daten gesichert werden?')) {

                        $(dirtyTextareas[0]).textarea('save');
                        $(dirtyTextareas[0]).addClass('saved');
                        $(dirtyTextareas[0]).removeClass('dirty');

                        //saveing

                        //this.addClass("saving"); 

                        sendFunction(value);

                    }
                    else {
                        //nicht speichern
                    }



                });*/


                //load tinymce
                load("mce", function() {
                    tinyMCE.init($.extend({
                        editor_selector: "mce-full",
                        plugins: 'searchreplace,paste,table,emotions,asciimath,asciisvg,media,noneditable,bid_tooltip',
                        // Theme options
                        theme_advanced_buttons1: "formatselect,fontsizeselect,|, bold,italic,underline,sub,sup,|,bullist,numlist,table,tablecontrols,visualaid,|,justifyleft,justifycenter,justifyright,justifyfull,hr,|,forecolor,backcolor",
                        theme_advanced_buttons2: "undo,redo,pasteword,|,removeformat,|,search,|,charmap,|,emotions,image,media,link,unlink,|,asciimathcharmap,asciisvg,|,bid_tooltip",
                        theme_advanced_fonts: "Times New Roman=times new roman,times,serif;Arial=arial,helvetica,sans-serif;Courier New=courier new,courier,monospace;AkrutiKndPadmini=Akpdmi-n",
                        theme_advanced_blockformats: "p,pre,h1,h2,h3,h4",
                        //plugins
                        fix_table_elements: true,
                        readonly: tinymceReadOnly,
                        verify_html: false,
                        //test cases
                        //AScgiloc : 'http://www.imathas.com/imathas/filter/graph/svgimg.php', //orginalquelle
                        //AScgiloc: 'http://www.bid-owl.de/tools/asciisvg/svgimg.php', //aus bid2, läuft
                        //AScgiloc : 'https://steam.lspb.de/tools/asciisvg/svgimg.php', //??
                        //AScgiloc : '/tools/asciisvg/svgimg.php',
                        AScgiloc: '/asciisvg/svgimg.php',
                        ASdloc: pathUrl + '/styles/standard/javascript/tinymce-jquery/jscripts/tiny_mce/plugins/asciisvg/js/d.svg',
                        fullscreen_new_window: true,
                        extended_valid_elements: "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name|style]",
                        fullscreen_settings: {
                            theme_advanced_path_location: "top"
                        }
                    }, mce_defaults));
                });
            } else if (textarea.find("textarea").hasClass("plain")) {
                textarea.find("textarea").val(value);
                textarea.find("textarea").bind('keyup', function() {
                    textarea.addClass("dirty");
                });
            }
        },
        save: function() {
            //alert('hier');
            var textarea = this;
            var value;
            
            if (textarea.hasClass("mce-full") || textarea.hasClass("mce-small")) {
            
                $(tinyMCE.activeEditor.getBody()).addClass("mceNonEditable");
            
                value = tinyMCE.activeEditor.getContent();
                
            } else if (textarea.hasClass("plain")) {
                value = textarea.val();
            }
            
            var oldValue = textarea.attr("data-oldValue");
            textarea.addClass("saving");
            
            sendFunction(value);
            
            textarea.removeClass("dirty");
            


        }
    };

    //define a new function 'textarea' that can be called with parameters and forwards the call to the methods above
    $.fn.textarea = function(method) {
        // Method calling logic
        if (methods[method]) {
            return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.textarea');
        }

    };

})(jQuery);	