/**
 * function to filter documents by their round
 * @param round - which round is displayed (0 = all)
 * @param maxrounds - number of rounds
 */
function hideDocuments(round, maxrounds) {
	if (round == 0) {
		for (var i=1; i <= maxrounds; i++) {
			document.getElementById("round"+i).style.display = "";
		}
	} else {
		for (var i=1; i <= maxrounds; i++) {
			document.getElementById("round"+i).style.display = "none";
		}
		document.getElementById("round"+round).style.display = "";
	}
}

/**
 * function to let the create comment dialog appear (view command)
 */
function show_createcomment() {
	document.getElementById("createbutton").style.display = "none";
	document.getElementById("createform").style.display = "";
}

/**
 * function to let the create comment dialog disappear (view command)
 */
function hide_createcomment() {
	document.getElementById("createbutton").style.display = "";
	document.getElementById("createform").style.display = "none";
}

/**
 * function to check the create/edit comment input on submit (view command)
 * @param createoredit
 * @returns {Boolean}
 */
function check_comment(createoredit, id, tcr) {
	var title = document.getElementsByName("title")[createoredit].value;
	var content = document.getElementsByName("content")[createoredit].value;
	if (title.length == 0) {
		alert("Bitte Titel eingeben.");
		return false;
	} else {
		if (content.length == 0) {
			alert("Bitte Kommentar eingeben.");
			return false;
		} else {
			params = {};
			params.id = id;
			params.tcr = tcr;
			params.title = title;
			params.content = content;
			if (createoredit == 0) {
				params.action = "addComment";
			} else {
				params.edit = document.getElementById('edit_id').value;
				params.action = "editComment";
			}
			sendRequest('EditDialog', params, '', 'reload');
		}
	}
}

/**
 * function to let the edit comment dialog appear, fill it with the right values (view command)
 * @param title
 * @param id
 */
function show_editcomment(title, id) {
	if (document.getElementById("createbutton").style.display == "") {
		document.getElementById("createbutton").style.display = "none";
	}
	if (document.getElementById("createform").style.display == "") {
		document.getElementById("createform").style.display = "none";
	}
	if (document.getElementById("editform").style.display == "none") {
		document.getElementById("editform").style.display = "";
	}
	var content = document.getElementById("content" + id).value;
	
	document.getElementById("edit_title").value = title;
	document.getElementById("edit_content").value = content;
	document.getElementById("edit_id").value = id;
}

/**
 * function to let the edit comment dialog disappear (view command)
 */
function hide_editcomment() {
	document.getElementById("createbutton").style.display = "";
	document.getElementById("editform").style.display = "none";
}

/**
 * function to send release request depending on the document type
 * @param id
 * @param type
 * @param tcr
 */
function release(id, type, tcr) {
	var params = {};
	params.id = id;
	params.type = type;
	params.tcr = tcr;
	params.action = "release";
	if (type == 0) {
		sendRequest('EditDialog', params, '', 'popup');
	} else {
		var check = confirm('Sie können das Dokument nach dem Veröffentlichen nicht mehr bearbeiten.\n Wirklich veröffentlichen?');
		if (check == true) {
			sendRequest('EditDialog', params, '', 'reload');
		}
	}
}

/**
 * function to delete uploaded file (view command)
 * @param id
 * @param name
 */
function deleteUpload(id, name) {
	var check = confirm('Datei "'+name+'" wirklich löschen?');
	if (check == true) {
		var params = {};
		params.id = id;
		params.action = "delete";
		sendRequest('EditDialog', params, '', 'reload');
	}
}

/**
 * function to change the background of marked column/row (index command)
 */
function changeBackground(id, color) {
	if (color == 1) {
		document.getElementById(id).style.background = "#E3E3F3";
		document.getElementById(id).style.background = "#E3E3F3";
	} else if (color == 0) {
		document.getElementById(id).style.background = "white";
		document.getElementById(id).style.background = "white";
	}
}

/**
 * function to check the configuration (configuration command)
 * @returns {Boolean}
 */
function checkConfiguration() {
	var value = document.getElementById("rounds").value;
	if ((parseFloat(value) == parseInt(value)) && !isNaN(value) && value > 0){
		if (document.getElementById("title").value == "") {
			alert("Bitte einen Titel angeben.");
			return false;
		} else return true;
	} else {
		alert("Bitte eine gültige Rundenzahl angeben.");
		return false;
	}
}

/**
 * function to check the title
 * @returns {Boolean}
 */
function checkTitle() {
	if (document.getElementById("title").value == "") {
		alert("Bitte einen Titel angeben.");
		return false;
	} else return true;
}