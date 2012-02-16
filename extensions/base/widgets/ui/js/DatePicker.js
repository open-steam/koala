function widgets_datepicker_changed(elementId) {
	jQuery("#" + elementId).parent().children("div")[0].style.display = "block";
	jQuery("#" + elementId).parent().children("div")[1].style.display = "none";
	jQuery("#" + elementId).parent().children("div")[2].style.display = "none";
	jQuery("#" + elementId).parent().children("div")[3].style.display = "none";
	jQuery("#" + elementId).parent().children("div")[4].style.display = "none";
}

function widgets_datepicker_save(elementId) {
	jQuery("#" + elementId).parent().children("div")[0].style.display = "none";
	jQuery("#" + elementId).parent().children("div")[1].style.display = "block";
	jQuery("#" + elementId).parent().children("div")[2].style.display = "none";
	jQuery("#" + elementId).parent().children("div")[3].style.display = "none";
	jQuery("#" + elementId).parent().children("div")[4].style.display = "none";
}

function widgets_datepicker_save_success(elementId, response) {
	data = jQuery.parseJSON(response.responseText).data;
	jQuery("#" + elementId).parent().children("div")[0].style.display = "none";
	jQuery("#" + elementId).parent().children("div")[1].style.display = "none";
	if (data.error == "none") {
		document.getElementById(elementId).value = data.newValue;
		jQuery("#" + elementId).parent().children("div")[2].style.display = "block";
		jQuery("#" + elementId).parent().children("div")[4].style.display = "none";
	} else {
		document.getElementById(elementId).value = data.oldValue;
		jQuery("#" + elementId).parent().children("div")[2].style.display = "none";
		jQuery("#" + elementId).parent().children("div")[4].style.display = "block";
		jQuery("#" + elementId).parent().children("div")[4].title = data.error;
	}
	if (data.undo) {
		jQuery("#" + elementId).undo = true;
		document.getElementById(elementId).oldValue = data.oldValue;
		jQuery("#" + elementId).parent().children("div")[3].style.display = "block";
	} else {
		jQuery("#" + elementId).undo = false;
		jQuery("#" + elementId).parent().children("div")[3].style.display = "none";
	}
}

function widgets_datepicker_undo_success(elementId, response) {
	data = jQuery.parseJSON(response.responseText).data;
	jQuery("#" + elementId).parent().children("div")[0].style.display = "none";
	jQuery("#" + elementId).parent().children("div")[1].style.display = "none";
	if (data.error == "none") {
		document.getElementById(elementId).value = data.newValue;
		jQuery("#" + elementId).parent().children("div")[2].style.display = "block";
		jQuery("#" + elementId).parent().children("div")[4].style.display = "none";
	} else {
		jQuery("#" + elementId).parent().children("div")[2].style.display = "none";
		jQuery("#" + elementId).parent().children("div")[4].style.display = "block";
		jQuery("#" + elementId).parent().children("div")[4].title = data.error;
	}

	jQuery("#" + elementId).undo = false;
	document.getElementById(elementId).oldValue = "";
	jQuery("#" + elementId).parent().children("div")[3].style.display = "none";
}