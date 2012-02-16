/**
 * function to hide the upload part and to display the text input part (create command)
 */
function hideUpload() {
	document.getElementById("tcr_upload").style.display = "none";
	document.getElementById("tcr_content").style.display = "";
}
/**
 * function to hide the text input part and to display the upload part (create command)
 */
function hideContent() {
	document.getElementById("tcr_upload").style.display = "";
	document.getElementById("tcr_content").style.display = "none";
}
/**
 * function to hide the upload part and to display the text input part (edit command)
 */
function showTextarea() {
	document.getElementById("new_upload").style.display = "none";
	document.getElementById("new_text").style.display = "";
	document.getElementById("tcr_warning").style.display = "";
}
/**
 * function to hide the text input part and to display the upload part (edit command)
 */
function showUpload() {
	document.getElementById("new_upload").style.display = "";
	document.getElementById("new_text").style.display = "none";
	document.getElementById("tcr_warning").style.display = "";
}
/**
 * function to hide the upload part (edit command)
 */
function hideUploadEdit() {
	document.getElementById("new_upload").style.display = "none";
	document.getElementById("tcr_warning").style.display = "none";
}
/**
 * function to hide upload and text input part (edit command)
 */
function hideEverything() {
	document.getElementById("new_upload").style.display = "none";
	document.getElementById("new_text").style.display = "none";
	document.getElementById("tcr_warning").style.display = "none";
}
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
 * function to let the create comment dialog appear (release command)
 */
function show_createcomment() {
	document.getElementById("createbutton").style.display = "none";
	document.getElementById("createform").style.display = "";
}

/**
 * function to let the create comment dialog disappear (release command)
 */
function hide_createcomment() {
	document.getElementById("createbutton").style.display = "";
	document.getElementById("createform").style.display = "none";
}

/**
 * function to check the create/edit comment input on submit (release command)
 * @param createoredit
 * @returns {Boolean}
 */
function check_comment(createoredit) {
	var title = document.getElementsByName("title")[createoredit].value;
	var content = document.getElementsByName("content")[createoredit].value;
	if (title.length == 0) {
		alert("Bitte Titel eingeben.");
		return false;
	} else {
		if (content.length == 0) {
			alert("Bitte Kommentar eingeben.");
			return false;
		} else return true;
	}
}

/**
 * function to let the edit comment dialog appear, fill it with the right values (release command)
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
 * function to let the edit comment dialog disappear (release command)
 */
function hide_editcomment() {
	document.getElementById("createbutton").style.display = "";
	document.getElementById("editform").style.display = "none";
}