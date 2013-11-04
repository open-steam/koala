var currentX = 0;
var currentY = 0;

function sendMultiRequest(command, paramsArray, elementIdArray, requestType, completeFunction, successFunction, namespace, message, loop, count) {
    if (paramsArray.length >= 1) {
        if ((jQuery("#progressbarwrapper").length == 0)) {
            createOverlay("white", null, "show");
            createDynamicWrapper("<div id=\"progressbarwrapper\" style=\"z-index: 255; position: fixed; top: 50%; left:50%; margin-left: -150px\"><div id=\"progressbar\" style=\"width:300px; height:10px\"></div><div id=\"message\">" + message + "</div></div>");
            $("#progressbar").progressbar({
                value: 0
            });
        }
        $("#progressbar").progressbar({
            value: Math.round((loop + 1) / count * 100)
        });
        $("#message").html(message + " (" + (loop + 1) + " von " + count + ")");
        var params = paramsArray[0];
        var elementId = elementIdArray[0];
        paramsArray.splice(0, 1);
        elementIdArray.splice(0, 1);
        sendRequest(command, params, elementId, requestType, function() {
            sendMultiRequest(command, paramsArray, elementIdArray, requestType, completeFunction, successFunction, namespace, message, loop + 1, count);
        }, successFunction, namespace);
    } else {
        closeDialog();
    }
}

//Sending the AJAX request
function sendRequest(command, params, elementId, requestType, completeFunction, successFunction, namespace) {
    try {
        path = window.location.href;
        if (!params || (typeof(params) != 'object') || $.isArray(params)) {
            params = {};
        }
        params.command = command;
        params.elementId = elementId;
        params.requestType = requestType;
        params.namespace = namespace;
        params.mouseX = currentX;
        params.mouseY = currentY;
        if (!completeFunction || completeFunction == "") {
            completeFunction = function(response) {
            };
        }

        if (!successFunction || successFunction == "") {
            successFunction = function(response) {
                if (checkResponseText(response, params)) {
                    responseData = jQuery.parseJSON(response);
                    if (responseData.status == "ok") {
                        if (requestType == "updater") {
                            jQuery.globalEval(responseData.js);
                            jQuery('#' + elementId).html("<style type=\"text/css\">" + responseData.css + "</style>" + responseData.html);
                            closeDialog();
                        } else if (requestType == "popup") {
                            jQuery.globalEval(responseData.js);
                            createDynamicWrapper("<style type=\"text/css\">" + responseData.css + "</style>" + responseData.html);
                            jQuery.globalEval(responseData.postjs);
                        } else if (requestType == "reload") {
                            window.location.reload();
                        } else if (requestType == "wizard") {
                            jQuery.globalEval(responseData.js);
                            jQuery('#' + elementId).fadeOut(1000, function() {
                                jQuery('#' + elementId).html("<style type=\"text/css\">" + responseData.css + "</style>" + responseData.html).fadeIn(1000);
                            });
                            jQuery.globalEval(responseData.postjs);
                        } else if (requestType == "data") {

                        } else {
                            handleError("Falscher Anfragetyp", response, params);
                        }
                    } else {
                        handleError("Server meldet Fehler(1)", response, params);
                    }
                }
            };
        }
        if (requestType != "data" && requestType != "wizard") {
            createOverlay("white", null, "show");
        }
        $.ajax({
            type: "POST",
            data: params,
            success: successFunction,
            complete: completeFunction,
            error: function(response) {
                if (DEVELOPER) {
                    handleError(response.status + " - Server meldet Fehler(2) (" + elementId + ")", response.responseText, params)
                }
            }
        });
    } catch (e) {
        handleError(e.name, e.message, params);
    }
}

function checkResponseText(responseText, params) {
    if (responseText == "") {
        handleError("Serverantwort ist leer", responseText, params);
        return false;
    } else if (responseText[0] != "{" || responseText[responseText.length - 1] != "}") {
        handleError("Serverantwort nicht erkannt", responseText, params);
        return false;
    } else {
        return true;
    }
}

function createOverlay(color, opacity, effect) {
    if ($("#overlay").length != 0) {
        return;
    }
    $overlay = "<div id=\"overlay\"></div>";
    jQuery('body').prepend($overlay);
    jQuery('#overlay').css({
        "position": "absolute",
        "width": jQuery("body").width(),
        "height": 0,
        "top": jQuery(window).scrollTop(),
        "left": jQuery(window).scrollLeft(),
        "opacity": opacity ? opacity : 0.8,
        "background-color": color ? color : white,
        "z-index": 200
    });
    jQuery(window).bind('resize scroll', function() {
        jQuery('#overlay').css({
            "width": jQuery(window).width(),
            "top": 0,
            "left": jQuery(window).scrollLeft()});
    });
    if (jQuery("body").height() > jQuery(window).height()) {
        height = jQuery("body").height();
    } else {
        height = jQuery(window).height();
    }
    if (effect == "fadeIn") {
        jQuery('#overlay').hide();
        jQuery('#overlay').css({"height": height});
        jQuery('#overlay').fadeIn("slow");
    } else if (effect == "show") {
        jQuery('#overlay').show();
        jQuery('#overlay').css({"height": height});
    } else {
        jQuery('#overlay').animate({"height": height}, {"duration": 1000});
    }
}

function createDynamicWrapper(content) {
    $dynamic_wrapper = "<div id=\"dynamic_wrapper\"></div>";
    jQuery('body').prepend($dynamic_wrapper);
    jQuery('#dynamic_wrapper').html(content);
}

function isDefined(variable) {
    return (typeof(window[variable]) == "undefined") ? false : true;
}

function closeDialog() {
    console.log(window.ajaxSaving);
    if (window.closing) {
        return false;
    }

    //busy waiting, todo counter
    if (window.ajaxSaving) {
        window.setTimeout("closeDialog();", 250);
        return;
    }


    window.closing = true;

    if (!(jQuery('#dialog').length === 0)) {
        //textinput fields
        var textinput_save_buttons = jQuery('#dialog').find('.widgets_textinput_save_button:visible');
        if (!(textinput_save_buttons.length === 0)) {
           /* if (!confirm('Sollen alle nicht gespeicherten Daten gesichert werden?')) {
                window.closing = false;
                window.ajaxSaving = false;
                return false;
            }*/

            for (var i = 0; i < textinput_save_buttons.length; i++) {
                jQuery(textinput_save_buttons[i]).click();
            }
        }

        //textareas
        var dirtyTextareas = jQuery('#dialog').find('.widget.textarea.dirty');
        if (dirtyTextareas.length > 0) {
          //  if (confirm('Sollen alle nicht gespeicherten Daten gesichert werden?')) {
                window.ajaxSaving = true;
                $(dirtyTextareas[0]).textarea('save');
                $(dirtyTextareas[0]).addClass('saved');
                $(dirtyTextareas[0]).removeClass('dirty');
                window.closing = false;
                window.ajaxSaving = false;
                window.setTimeout("reloadHelper()", 250);
                return;
           // } else {
            //    window.closing = false;
            //    window.ajaxSaving = false;
           //     return false;
           // }
        }
        jQuery('#dialog').slideUp("slow", function() {
            jQuery('#dynamic_wrapper').remove();
            jQuery('#overlay').remove();
        });
        window.closing = false;
        window.ajaxSaving = false;

        return true;

    } else {
        jQuery('#dynamic_wrapper').remove();
        jQuery('#overlay').remove();
        window.closing = false;
        window.ajaxSaving = false;
        return true;
    }
}

function reloadHelper() {
    jQuery('#dialog').slideUp("slow", function() {
        jQuery('#dynamic_wrapper').remove();
        jQuery('#overlay').remove();
    });
    location.reload();
}
function handleError(errorTitle, errorDescription, requestParams) {
    if (DEVELOPER) {
        closeDialog();
        createOverlay("black");
        createDynamicWrapper("<div id=\"dialog\"><div id=\"dialog_title\"></div><center><h2>HTML:</h2><div class=\"dialog_content\" id=\"chtml\"></div><h2>RAW:</h2><div class=\"dialog_content\" id=\"ctext\"></div></center></div>");
        jQuery('#dialog_title').html("<a style=\"color:white\" onclick=\"if (closeDialog()) {return false;}; return false;\" href=\"#\">[x]</a> Ajax-Fehler. Fehlertitel: -->" + errorTitle + "<--");
        jQuery('#chtml').html("<b>request:</b><br>" + JSON.stringify(requestParams) + "<br><br><b><--Begin Error Description--></b><br><br><h2>Visit errors.log for error description!</h2><br>" + errorDescription + "<br><b><--End Error Description--></b><br>");
        jQuery('#ctext').text("request:\n" + JSON.stringify(requestParams) + "\n\n<--Begin Error Description-->\n" + errorDescription + "\n<--End Error Description-->\n");
        jQuery('#dialog').css({
            "position": "absolute",
            "top": "0px",
            "left": "0px",
            "width": "100%",
            "color": "white",
            "z-index": 255
        });
        jQuery('#dialog_title').css({
            "margin-top": "25px",
            "text-align": "center",
            "font-size": 24,
        });
        jQuery('.dialog_content').css({
            "margin-top": "20px",
            "width": "500px",
            "text-align": "left"
        });
        jQuery('#dialog').draggable();
    } else {
        createCookie("title", escape(errorTitle), 1);
        createCookie("description", escape(errorDescription), 1);
        createCookie("location", location.href, 1);
        createCookie("params", JSON.stringify(requestParams), 1);
        window.location = PATH_URL + "error/report/4400/";
    }
}

window.onerror = handleJSError;
jQuery.error = console.error;

function handleJSError(errorTitle, file, line) {
    if (DEVELOPER) {
        if (!document.body) {
            alert("JS Error" + errorTitle + " " + file + " " + line);
        } else {
            document.body.style.border = "20px solid red";
            document.body.style.width = "97%";
        }
        return false;
    } else {
        handleError("JS-Error: " + errorTitle, "file: " + file + " line: " + line, "empty");
        return true;
    }
}

function createCookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    }
    else
        var expires = "";
    document.cookie = name + "=" + value + expires + "; path=/";
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0)
            return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name, "", -1);
}