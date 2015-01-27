function htmlEncodeHelper(value){
  return $('<div/>').text(value).html().replace('&amp;','&');
}

function widgets_textinput_save_success_delete_me(elementId, response) {
	deJSON = jQuery.parseJSON(response.responseText);
	if (deJSON) {
		data = deJSON.data;
		if (!(jQuery("#" + elementId).length === 0)) {
			var widgets_textinput = jQuery("#" + elementId).parent();
			if (data.error == "none") {
				jQuery("#" + elementId).val(htmlEncodeHelper(data.newValue));
				widgets_textinput.find(".widgets_textinput_ok").show();
				widgets_textinput.find(".widgets_textinput_error").hide();
				jQuery("#" + elementId).removeClass("changed");
				jQuery("#" + elementId).addClass("saved");
			} else {
				//case save hasn't worked'
                                //jQuery("#" + elementId).val(htmlEncodeHelper(data.oldValue));
				widgets_textinput.find(".widgets_textinput_ok").hide();
				widgets_textinput.find(".widgets_textinput_error").show();
				widgets_textinput.find(".widgets_textinput_error").title = data.error;
			}
			
		}
	}
        //test, prevent early dialog close
        window.ajaxSaving==false;
}