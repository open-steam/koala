	var wikiText;
	var selectionStart;
	var selectionEnd;
	var selectionLength;
	var selectionText;
    
    function wiki_markup(wiki_element, markupLeft, markupRight, noSelectWarning) {
    	selection = get_selection(wiki_element);
    	wikiText = document.getElementById(wiki_element);
    	selectionStart = selection.start;
    	selectionEnd = selection.end;
    	selectionLength = selection.length;
    	selectionText = selection.text;
    	
    	if (selectionLength > 0) {
    		wikiText.value = wikiText.value.substr(0, selectionStart) + markupLeft + selectionText + markupRight + wikiText.value.substr(selectionEnd);
    		set_selection(wiki_element, selectionStart + markupLeft.length, selectionEnd + markupLeft.length);
    	} else {
    		if (noSelectWarning != "") {
    			alert(noSelectWarning);
    		} else {
    			wikiText.value = wikiText.value.substr(0, selectionStart) + markupLeft + markupRight + wikiText.value.substr(selectionEnd);
    			set_selection(wiki_element, selectionStart, selectionEnd + markupLeft.length);
    		}
    	}
    }
    
    function wiki_dialog(wiki_element, type) {
    	selection = get_selection(wiki_element);
    	wikiText = document.getElementById(wiki_element);
    	selectionStart = selection.start;
    	selectionEnd = selection.end;
    	selectionLength = selection.length;
    	selectionText = selection.text;
    	
    	switch (type) {
    	case "image":
    		document.getElementById("popup_dialog_overlay").style.display = "block";
    		document.getElementById("popup_dialog_wiki_image").style.display = "block";
    		document.getElementById("popup_dialog_wiki_image").oldHTML = document.getElementById("popup_dialog_wiki_image").innerHTML;
    		break;
    	
    	case "link":
    		document.getElementById("popup_dialog_overlay").style.display = "block";
    		document.getElementById("popup_dialog_wiki_link").style.display = "block";
    		document.getElementById("popup_dialog_wiki_link").oldHTML = document.getElementById("popup_dialog_wiki_link").innerHTML;
    		document.getElementById("popup_dialog_wiki_link_description").value = selectionText;
    		break;
    	
    	case "coment":
    		if (selectionLength > 0) {
    			document.getElementById("popup_dialog_overlay").style.display = "block";
    			document.getElementById("popup_dialog_wiki_coment").style.display = "block";
    			document.getElementById("popup_dialog_wiki_coment").oldHTML = document.getElementById("popup_dialog_wiki_coment").innerHTML;
    			document.getElementById("popup_dialog_wiki_coment_text").innerHTML = selectionText;
    		} else {
    			alert("Bitte wählen sie Text aus, den sie kommentiern wollen.");
    		}
    		break;
    	case "table":
    		document.getElementById("popup_dialog_overlay").style.display = "block";
			document.getElementById("popup_dialog_wiki_table").style.display = "block";
			document.getElementById("popup_dialog_wiki_table").oldHTML = document.getElementById("popup_dialog_wiki_table").innerHTML;
    	default:
    		return;
    	
    	}
    }
    
    function closeDialog(type) {
    	switch (type) {
    	case "image":
    		document.getElementById("popup_dialog_wiki_image").innerHTML = document.getElementById("popup_dialog_wiki_image").oldHTML;
    		document.getElementById("popup_dialog_overlay").style.display = "none";
    		document.getElementById("popup_dialog_wiki_image").style.display = "none";
    		break;
    	
    	case "link":
    		document.getElementById("popup_dialog_wiki_link").innerHTML = document.getElementById("popup_dialog_wiki_link").oldHTML;
    		document.getElementById("popup_dialog_overlay").style.display = "none";
    		document.getElementById("popup_dialog_wiki_link").style.display = "none";
    		break;
    	
    	case "coment":
    		document.getElementById("popup_dialog_wiki_coment").innerHTML = document.getElementById("popup_dialog_wiki_coment").oldHTML;
    		document.getElementById("popup_dialog_overlay").style.display = "none";
    		document.getElementById("popup_dialog_wiki_coment").style.display = "none";
    		break;
    	
    	case "table":
    		document.getElementById("popup_dialog_wiki_table").innerHTML = document.getElementById("popup_dialog_wiki_table").oldHTML;
    		document.getElementById("popup_dialog_overlay").style.display = "none";
    		document.getElementById("popup_dialog_wiki_table").style.display = "none";
    		break;
    	
    	default:
    		break;
    	}
    }
    
    function insertImage() {
    	if (document.getElementById("popup_dialog_wiki_image_extern").checked){
    		var url = document.getElementById("popup_dialog_wiki_image_url").value;
        	wikiText.value = wikiText.value.substr(0, selectionStart) + "[[Image:" + url + "]]" + wikiText.value.substr(selectionEnd);
        	set_selection(wikiText.id, selectionStart + 8, selectionStart  + 8 + url.length);
    	} else if (document.getElementById("popup_dialog_wiki_image_intern").checked) {
    		var images = document.formular.images;
    		if (!images.length) images = [images];
    		for(var i=0; i < images.length; i++) {
    			if (images[i].checked) {
    	    		wikiText.value = wikiText.value.substr(0, selectionStart) + "{" + images[i].value + "}" + wikiText.value.substr(selectionEnd);
    	    		set_selection(wikiText.id, selectionStart + 1, selectionStart + 1 + images[i].value.length);
    			}
    		}
    	}
    	closeDialog("image");
    }
    
    function insertLink() {
        var i = 0;
        var description = document.getElementById("popup_dialog_wiki_link_description").value;
        if (document.getElementById("popup_dialog_wiki_link_extern").checked){
            var url = document.getElementById("popup_dialog_wiki_link_url").value;
            if (url != "") {
                description = "|" + description;
                i = 1;
            }
            wikiText.value = wikiText.value.substr(0, selectionStart) + "[" + url + description + "]" + wikiText.value.substr(selectionEnd);
            set_selection(wikiText.id, selectionStart + 1 + url.length + i, selectionStart  + 1 + url.length + description.length);
        } else if (document.getElementById("popup_dialog_wiki_link_intern").checked) {
            var name = "";
            if (document.getElementById("popup_dialog_wiki_link_options").options[document.getElementById("popup_dialog_wiki_link_options").selectedIndex].value == 'newEntry') {
                name = document.getElementById("popup_dialog_wiki_link_name").value;
            } else {
                name = document.getElementById("popup_dialog_wiki_link_options").options[document.getElementById("popup_dialog_wiki_link_options").selectedIndex].value;
                name = name.replace(/.wiki/g, "");
            }
            if (name != "") {
                description = "|" + description;
                i = 1;
            }
            wikiText.value = wikiText.value.substr(0, selectionStart) + "[[" + name + description + "]]" + wikiText.value.substr(selectionEnd);
            set_selection(wikiText.id, selectionStart + 2 + name.length + i, selectionStart + 2 + name.length + description.length);
        }
        closeDialog("link");
    }
    
    function insertComent() {
    	var name = document.getElementById("popup_dialog_wiki_coment_text").innerHTML;
    	var coment = document.getElementById("popup_dialog_wiki_coment_coment").value;
    	wikiText.value = wikiText.value.substr(0, selectionStart) + "[" + name + "[" + coment + "]]" + wikiText.value.substr(selectionEnd);
    	set_selection(wikiText.id, selectionStart + 1 + name.length + 1, selectionStart + 2 + name.length + 1 + coment.length);
    	closeDialog("coment");
    }
    
    function getInputSelection(el) {
        var start = 0, end = 0, normalizedValue, range, textInputRange, len, endRange;
        
        if (typeof el.selectionStart == "number" && typeof el.selectionEnd == "number") {
            start = el.selectionStart;
            end = el.selectionEnd;
        } else {
            range = document.selection.createRange();

            if (range && range.parentElement() == el) {
                len = el.value.length;
                normalizedValue = el.value.replace(/\r\n/g, "\n");

                // Create a working TextRange that lives only in the input
                textInputRange = el.createTextRange();
                textInputRange.moveToBookmark(range.getBookmark());

                // Check if the start and end of the selection are at the very end
                // of the input, since moveStart/moveEnd doesn't return what we want
                // in those cases
                endRange = el.createTextRange();
                endRange.collapse(false);

                if (textInputRange.compareEndPoints("StartToEnd", endRange) > -1) {
                    start = end = len;
                } else {
                    start = -textInputRange.moveStart("character", -len);
                    start += normalizedValue.slice(0, start).split("\n").length - 1;

                    if (textInputRange.compareEndPoints("EndToEnd", endRange) > -1) {
                        end = len;
                    } else {
                        end = -textInputRange.moveEnd("character", -len);
                        end += normalizedValue.slice(0, end).split("\n").length - 1;
                    }
                }
            }
        }
        return {
            start: start,
            end: end
        };
    }

    function get_selection(the_id) {
        var e = document.getElementById(the_id);

        //Mozilla and DOM 3.0
        if('selectionStart' in e) {
            e.focus();
            var result = getInputSelection(e);
            var l = result.end - result.start;
            return { start: result.start, end: result.end, length: l, text: e.value.substr(result.start, l) };
        }
        // old IE versions
        else if(document.selection) {
            e.focus();
            var r = document.selection.createRange();
            var tr = e.createTextRange();
            var tr2 = tr.duplicate();
            tr2.moveToBookmark(r.getBookmark());
            tr.setEndPoint('EndToStart',tr2);
            if (r == null || tr == null) return { start: e.value.length, end: e.value.length, length: 0, text: '' };
            var text_part = r.text.replace(/[\r\n]/g,'.'); //for some reason IE doesn't always count the \n and \r in the length
            var text_whole = e.value.replace(/[\r\n]/g,'.');
            var the_start = text_whole.indexOf(text_part,tr.text.length);
            return { start: the_start, end: the_start + text_part.length, length: text_part.length, text: r.text };
        }
        //Browser not supported
        else return { start: e.value.length, end: e.value.length, length: 0, text: '' };
    }

    function replace_selection(the_id,replace_str) {
        var e = document.getElementById(the_id);
        selection = get_selection(the_id);
        var start_pos = selection.start;
        var end_pos = start_pos + replace_str.length;
        e.value = e.value.substr(0, start_pos) + replace_str + e.value.substr(selection.end, e.value.length);
        set_selection(the_id,start_pos,end_pos);
        return {start: start_pos, end: end_pos, length: replace_str.length, text: replace_str};
    }

    function set_selection(the_id,start_pos,end_pos) {
        var e = document.getElementById(the_id);

        //Mozilla and DOM 3.0
        if('selectionStart' in e)
        {
            e.focus();
            e.selectionStart = start_pos;
            e.selectionEnd = end_pos;
        }
        //IE
        else if(document.selection)
        {
            e.focus();
            var tr = e.createTextRange();

            //Fix IE from counting the newline characters as two seperate characters
            var stop_it = start_pos;
            for (i=0; i < stop_it; i++) if( e.value[i].search(/[\r\n]/) != -1 ) start_pos = start_pos - .5;
            stop_it = end_pos;
            for (i=0; i < stop_it; i++) if( e.value[i].search(/[\r\n]/) != -1 ) end_pos = end_pos - .5;

            tr.moveEnd('textedit',-1);
            tr.moveStart('character',start_pos);
            tr.moveEnd('character',end_pos - start_pos);
            tr.select();
        }
        return get_selection(the_id);
    }

    function wrap_selection(the_id, left_str, right_str, sel_offset, sel_length) {
        var the_sel_text = get_selection(the_id).text;
        var selection =  replace_selection(the_id, left_str + the_sel_text + right_str );
        if(sel_offset !== undefined && sel_length !== undefined) selection = set_selection(the_id, selection.start +  sel_offset, selection.start +  sel_offset + sel_length);
        else if(the_sel_text == '') selection = set_selection(the_id, selection.start + left_str.length, selection.start + left_str.length);
        return selection;
    }
    
    function helpChangeState(){
    	if($('#wikiHelp').is(':visible')) {    		    		
    		$('#wikiHelp').hide();
    		$('.toolbar_icon_help').removeClass('toolbar_icon_pressed');
    	} else {
    		$('#wikiHelp').show();
    		$('.toolbar_icon_help').addClass('toolbar_icon_pressed');
    	}
    }
    
    function switchHelpText(id){
    	var label = (id.substr(1));
    	
    	$('.pages .current').addClass('disabled');
    	$('.pages .current').removeClass('current');
    	
    	$('.index .current').removeClass('current');
    	
    	$("#P"+label).addClass('current');
    	$("#S"+label).addClass('current');
    }
    
    function insertTable(){
    	var generatedTable ="";
    	var rows = document.getElementById("popup_dialog_wiki_table_rowsBox").value;
    	var columns = document.getElementById("popup_dialog_wiki_table_columnsBox").value;
    	var headers = document.getElementById("popup_dialog_wiki_table_headerbox").checked;
        
    	alert(rows + " " + columns + " " + headers);
    	
        var cellText = "Beispiel";
        var headerText = "Ueberschrift";
    	
        generatedTable += "{|";
        
        if(headers){
            generatedTable += "\n|-\n!";
            for(var i = 0 ;i< columns ;i++){
                generatedTable += headerText;
                    if(i != columns - 1){
                        generatedTable+=" !! ";
                    }
            }
        }
        
    	for(var i = 0; i< rows;i++){
                generatedTable += "\n|-\n|";
    		for(var j = 0;j< columns;j++){
                    generatedTable += cellText;
                    if(j != columns - 1){
                        generatedTable+="||";
                    }
    		}
    	}
        generatedTable += "\n|}";
        
		wikiText.value = wikiText.value.substr(0, selectionStart) +generatedTable+ wikiText.value.substr(selectionEnd);
		closeDialog("table");	
    }
    
    function insert(aTag, eTag, form, element) {
        var input = document.forms[form].elements[element];
        input.focus();
        if (typeof document.selection != 'undefined') {
            var range = document.selection.createRange();
            var insText = range.text;
            range.text = aTag + insText + eTag;
            range = document.selection.createRange();
            if (insText.length == 0) {
                range.move('character', - eTag.length);
            } else {
                range.moveStart('character', aTag.length + insText.length + eTag.length);
            }
            range.select();
        } else if (typeof input.selectionStart != 'undefined') {
            var start = input.selectionStart;
            var end = input.selectionEnd;
            var insText = input.value.substring(start, end);
            input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
            var pos;
            if (insText.length == 0) {
                pos = start + aTag.length;
            } else {
                pos = start + aTag.length + insText.length + eTag.length;
            }
            input.selectionStart = pos;
            input.selectionEnd = pos;
        } else {
            var pos;
            var re = new RegExp('^[0-9]{0,3}$');
            while (!re.test(pos)) {
                pos = prompt("Einfügen an Position (0.." + input.value.length + "):", "0");
            }
            if (pos > input.value.length) {
                pos = input.value.length;
            }
            var insText = prompt("Bitte geben Sie den zu formatierenden Text ein:");
            input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos);
        }
    }
     