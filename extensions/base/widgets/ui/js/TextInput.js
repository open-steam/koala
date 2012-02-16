function widgets_textinput_changed(elementId) {
	var widgets_textinput = jQuery("#" + elementId).parent();
	widgets_textinput.find(".widgets_textinput_save_button").show();
	widgets_textinput.find(".widgets_textinput_loader").hide();
	widgets_textinput.find(".widgets_textinput_ok").hide();
	widgets_textinput.find(".widgets_textinput_undo_button").hide();
	widgets_textinput.find(".widgets_textinput_error").hide();
}

function widgets_textinput_changed_autosave(elementId) {
	if (jQuery("#" + elementId).val() !== jQuery("#" + elementId).attr("oldValue")) {
		jQuery("#" + elementId).addClass("changed");
	} else {
		jQuery("#" + elementId).removeClass("changed");
	}
}

function widgets_textinput_hide_buttons(elementId) {
	var widgets_textinput = jQuery("#" + elementId).parent();
	widgets_textinput.find(".widgets_textinput_save_button").hide();
	widgets_textinput.find(".widgets_textinput_loader").hide();
	widgets_textinput.find(".widgets_textinput_ok").hide();
	widgets_textinput.find(".widgets_textinput_undo_button").hide();
	widgets_textinput.find(".widgets_textinput_error").hide();
}

function widgets_textinput_save(elementId) {
	var widgets_textinput = jQuery("#" + elementId).parent();
	widgets_textinput.find(".widgets_textinput_save_button").hide();
	widgets_textinput.find(".widgets_textinput_loader").show();
	widgets_textinput.find(".widgets_textinput_ok").hide();
	widgets_textinput.find(".widgets_textinput_undo_button").hide();
	widgets_textinput.find(".widgets_textinput_error").hide();
}

function widgets_textinput_save_success(elementId, response) {
	deJSON = jQuery.parseJSON(response.responseText);
	if (deJSON) {
		data = deJSON.data;
		if (!(jQuery("#" + elementId).length === 0)) {
			var widgets_textinput = jQuery("#" + elementId).parent();
			widgets_textinput.find(".widgets_textinput_save_button").hide();
			widgets_textinput.find(".widgets_textinput_loader").hide();
			if (data.error == "none") {
				jQuery("#" + elementId).val(data.newValue);
				widgets_textinput.find(".widgets_textinput_ok").show();
				widgets_textinput.find(".widgets_textinput_error").hide();
				jQuery("#" + elementId).removeClass("changed");
				jQuery("#" + elementId).addClass("saved");
			} else {
				jQuery("#" + elementId).val(data.oldValue);
				widgets_textinput.find(".widgets_textinput_ok").hide();
				widgets_textinput.find(".widgets_textinput_error").show();
				widgets_textinput.find(".widgets_textinput_error").title = data.error;
			}
			if (data.undo) {
				jQuery("#" + elementId).attr("undo", true);
				if (data.oldValue != data.newValue) {
					jQuery("#" + elementId).attr("oldValue", data.oldValue);
				}
				widgets_textinput.find(".widgets_textinput_undo_button").show();
			} else {
				jQuery("#" + elementId).attr("undo", false);
				widgets_textinput.find(".widgets_textinput_undo_button").hide();
			}
		}
	}
}

function widgets_textinput_undo_success(elementId, response) {
	data = jQuery.parseJSON(response.responseText).data;
	var widgets_textinput = jQuery("#" + elementId).parent();
	widgets_textinput.find(".widgets_textinput_save_button").hide();
	widgets_textinput.find(".widgets_textinput_loader").hide();
	if (data.error == "none") {
		jQuery("#" + elementId).val(data.newValue);
		widgets_textinput.find(".widgets_textinput_ok").show();
		widgets_textinput.find(".widgets_textinput_error").hide();
	} else {
		widgets_textinput.find(".widgets_textinput_ok").hide();
		widgets_textinput.find(".widgets_textinput_error").show();
		widgets_textinput.find(".widgets_textinput_error").attr("title" ,data.error);
	}

	jQuery("#" + elementId).attr("undo", false);
	jQuery("#" + elementId).attr("oldValue", "");
	widgets_textinput.find(".widgets_textinput_undo_button").hide();
}