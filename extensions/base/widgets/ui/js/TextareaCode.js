/*function widgets_textareacode_changed_delete_me(elementId) {
	var widgets_textinput = jQuery("#" + elementId).parent();
	widgets_textinput.find(".widgets_textareacode_save_button").show();
	widgets_textinput.find(".widgets_textareacode_loader").hide();
	widgets_textinput.find(".widgets_textareacode_ok").hide();
	widgets_textinput.find(".widgets_textareacode_undo_button").hide();
	widgets_textinput.find(".widgets_textareacode_error").hide();
}

function widgets_textareacode_hide_buttons_delete_me(elementId) {
	var widgets_textinput = jQuery("#" + elementId).parent();
	widgets_textinput.find(".widgets_textareacode_save_button").hide();
	widgets_textinput.find(".widgets_textareacode_loader").hide();
	widgets_textinput.find(".widgets_textareacode_ok").hide();
	widgets_textinput.find(".widgets_textareacode_undo_button").hide();
	widgets_textinput.find(".widgets_textareacode_error").hide();
}

function widgets_textareacode_save_delete_me(elementId) {
	var widgets_textinput = jQuery("#" + elementId).parent();
	widgets_textinput.find(".widgets_textareacode_save_button").hide();
	widgets_textinput.find(".widgets_textareacode_loader").show();
	widgets_textinput.find(".widgets_textareacode_ok").hide();
	widgets_textinput.find(".widgets_textareacode_undo_button").hide();
	widgets_textinput.find(".widgets_textareacode_error").hide();
}

function widgets_textareacode_save_success_delete_me(elementId, response) {
	data = jQuery.parseJSON(response.responseText).data;
	var widgets_textinput = jQuery("#" + elementId).parent();
	widgets_textinput.find(".widgets_textareacode_save_button").hide();
	widgets_textinput.find(".widgets_textareacode_loader").hide();
	if (data.error == "none") {
		//jQuery("#" + elementId).val(data.newValue);
		editor.setValue(data.newValue);
		widgets_textinput.find(".widgets_textareacode_save_button").hide();
		widgets_textinput.find(".widgets_textareacode_ok").show();
		widgets_textinput.find(".widgets_textareacode_error").hide();
	} else {
		//jQuery("#" + elementId).val(data.oldValue);
		editor.setValue(data.oldValue);
		widgets_textinput.find(".widgets_textareacode_save_button").hide();
		widgets_textinput.find(".widgets_textareacode_ok").hide();
		widgets_textinput.find(".widgets_textareacode_error").show();
		widgets_textinput.find(".widgets_textareacode_error").title = data.error;
	}
	if (data.undo) {
		jQuery("#" + elementId).attr("undo", true);
		jQuery("#" + elementId).attr("oldValue", data.oldValue);
		widgets_textinput.find(".widgets_textareacode_undo_button").show();
	} else {
		jQuery("#" + elementId).attr("undo", false);
		widgets_textinput.find(".widgets_textareacode_undo_button").hide();
	}
}

function widgets_textareacode_undo_success_delete_me(elementId, response) {
	data = jQuery.parseJSON(response.responseText).data;
	var widgets_textinput = jQuery("#" + elementId).parent();
	widgets_textinput.find(".widgets_textareacode_save_button").hide();
	widgets_textinput.find(".widgets_textareacode_loader").hide();
	if (data.error == "none") {
		//jQuery("#" + elementId).val(data.newValue);
		editor.setValue(data.newValue);
		widgets_textinput.find(".widgets_textareacode_save_button").hide();
		widgets_textinput.find(".widgets_textareacode_ok").show();
		widgets_textinput.find(".widgets_textareacode_error").hide();
	} else {
		widgets_textinput.find(".widgets_textareacode_ok").hide();
		widgets_textinput.find(".widgets_textareacode_error").show();
		widgets_textinput.find(".widgets_textareacode_error").attr("title" ,data.error);
	}

	jQuery("#" + elementId).attr("undo", false);
	jQuery("#" + elementId).attr("oldValue", "");
	widgets_textinput.find(".widgets_textareacode_undo_button").hide();
}*/