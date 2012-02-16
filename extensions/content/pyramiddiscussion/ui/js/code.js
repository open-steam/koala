/**
 * function to disable deadline fields in the configuration view
 * @param phases
 */
function deadlines_readonly(phases) {
	for (i = 1; i <= phases; i++) {
		document.getElementsByName("deadline["+i+"]")[0].disabled = true;
	}
}

/**
 * function to activate deadline fields in the configuration view
 * @param phases
 */
function deadlines_write(phases) {
	for (i = 1; i <= phases; i++) {
		document.getElementsByName("deadline["+i+"]")[0].disabled = false;
	}
}

/**
 * function to check if deadlines are used in the configuration view
 * @param phases
 */
function deadlines(phases) {
	if (document.getElementsByName("use_deadlines")[0].checked) deadlines_write(phases);
	else deadlines_readonly(phases);
}

/**
 * function to check the input of the deadline fields if the configuration view got submitted
 * @param phases
 * @returns {Boolean}
 */
function checkDeadlines(phases) {
	if (document.getElementsByName("use_deadlines")[0].checked) {
		for (i = 1; i <= phases; i++) {
			var date = document.getElementById("datepicker"+i).value;
			if (date.length < 16) {
				alert("Bitte Deadlines für alle Phasen eingeben.");
				return false;
			}
			date = date.substring(0,10);
			if (date.length != 10) {
				alert("Falsches Datumsformat");
				return false;
			}
			var time = date.substring(11);
			var t = time.split(/\D+/);
			t[0] = parseInt(t[0]);
			t[1] = parseInt(t[1]);
			if (t[0] < 0 || t[0] > 23 || t[1] < 0 || t[1] > 59) {
			    alert ("Falsches Uhrzeitsformat");
			    return false;
			}
		}
	}
	return true;
}

/**
 * function to let the create comment dialog appear (view position)
 */
function show_createcomment() {
	document.getElementById("createbutton").style.display = "none";
	document.getElementById("createform").style.display = "";
}

/**
 * function to let the create comment dialog disappear (view position)
 */
function hide_createcomment() {
	document.getElementById("createbutton").style.display = "";
	document.getElementById("createform").style.display = "none";
}

/**
 * function to check the create/edit comment input on submit (view position)
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
 * function to let the edit comment dialog appear, fill it with the right values (view position)
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
 * function to let the edit comment dialog disappear (view position)
 */
function hide_editcomment() {
	document.getElementById("createbutton").style.display = "";
	document.getElementById("editform").style.display = "none";
}

/**
 * function to create pyramid view
 * @param startfields
 * @param gap - gap height in px
 * @param startheight - height of elements in first row
 * @param deadlines - boolean (deadlines used or not)
 */
function initiatePyramid(startfields, gap, startheight, deadlines) {
	var maxcol = Math.log(startfields) / Math.log(2) + 1;
	var height = new Array();
	var current_height = new Array();
	var deadline_height = 16;
	height[0] = gap; 
	height[1] = startheight;
	height[2] = 2 * height[1] - 0.5 * height[1] + 1 * height[0];
	height[3] = 3 * height[1] + 2 * height[0];
	height[4] = 4 * height[1] + 3 * height[0];
	height[5] = 8 * height[1] + 7 * height[0];
	height[6] = 16 * height[1] + 15 * height[0];
	height[7] = 32 * height[1] + 31 * height[0];
	
	for (count = 1; count <= maxcol; count++) {
		if (deadlines) {
			var position = $("#deadline" + count).position();
			top = position.top;
			left = position.left+135*(count-1)+10;
			switch (count) {
				case 2:
					top = top + 0.5 * height[1];
					break;
				case 3:
					top = top + height[1] + height[0];
					break;
				case 4:
					top = top + 2*height[1] + 2*height[0];
					break;
				case 5:
					top = top + 4*height[1] + 4*height[0];
					break;
				case 6:
					top = top + 8*height[1] + 8*height[0];
					break;
				case 7:
					top = top + 16*height[1] + 16*height[0];
					break;
				default:
					break;
			}
			$("#deadline" + count).css({ top: top, left: left });
		}
		for (count2 = 1; count2 <= startfields / Math.pow(2, count-1); count2++) {
			var position = $("#position" + count + count2).position();
			var top = position.top + deadline_height;

			var left = position.left+135*(count-1)+10;
			switch (count) {
				case 1:
					top = top + (height[0] + height[1])*(count2-1);
					break;
				case 2:
					if (count2 == 1) {
						current_height[count] = top + 0.5 * height[1];
					} else if (count2 % 2 == 0) {
						current_height[count] = current_height[count] + height[2] + height[0];
					} else {
						current_height[count] = current_height[count] + height[2] + height[0] + height[1];
					}
					top = current_height[count];
					break;
				case 3:
					if (count2 == 1) {
						current_height[count] = top + height[1] + height[0];
					} else if (count2 % 2 == 0) {
						current_height[count] = current_height[count] + height[3] + height[0];
					} else {
						current_height[count] = current_height[count] + height[3] + 3*height[0] + 2*height[1];
					}
					top = current_height[count];
					break;
				case 4:
					if (count2 == 1) {
						current_height[count] = top + 2*height[1] + 2*height[0];
					} else {
						current_height[count] = current_height[count] + height[4] + 4*height[1] + 5*height[0];
					}
					top = current_height[count];
					break;
				case 5:
					if (count2 == 1) {
						current_height[count] = top + 4*height[1] + 4*height[0];
					} else {
						current_height[count] = current_height[count] + height[5] + 8*height[1] + 9*height[0];
					}
					top = current_height[count];
					break;
				case 6:
					if (count2 == 1) {
						current_height[count] = top + 8*height[1] + 8*height[0];
					} else {
						current_height[count] = current_height[count] + height[6] + 16*height[1] + 17*height[0];
					}
					top = current_height[count];
					break;
				case 7:
					top = current_height[count] = top + 16*height[1] + 16*height[0];
					break;
			}
			// special case
			if (maxcol == 2 && count == 2) {
				top = top - 0.25 * height[1];
			}
			$("#position" + count + count2).css({ top: top, left: left });
		}
	}
	$("#pyramid").css({ visibility: 'visible' });
}

/**
 * function to show and hide previous position in editposition view
 * @param position
 */
function previousPosition(position) {
	if (document.getElementById(position).style.display == "none") {
		$('#'+position).show('blind');
		$('#'+position+'_button').hide('blind');
	} else {
		$('#'+position).hide('blind');
		$('#'+position+'_button').show('blind');
	}
}

//jquery: initialize the datepickers for the deadlines in the configuration view
$(document).ready(function() {
	window.onload = function(){ $("#pyramid").css({ visibility: 'visible' }); }
	if (document.getElementById("amountphases") != undefined) {
		var max = document.getElementById("amountphases").value;
		for (i = 1; i <= max; i++) {
			$("#datepicker" + i).datetimepicker({dateFormat: "dd.mm.yy", hourGrid: 4, minuteGrid: 10});
		}
	}
    $.datepicker.regional['de'] = {clearText: 'löschen', clearStatus: 'aktuelles Datum löschen',
            closeText: 'schließen', closeStatus: 'ohne Änderungen schließen',
            prevText: '<zurück', prevStatus: 'letzten Monat zeigen',
            nextText: 'Vor>', nextStatus: 'nächsten Monat zeigen',
            currentText: 'heute', currentStatus: '',
            monthNames: ['Januar','Februar','März','April','Mai','Juni',
            'Juli','August','September','Oktober','November','Dezember'],
            monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',
            'Jul','Aug','Sep','Okt','Nov','Dez'],
            monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',
            weekHeader: 'Wo', weekStatus: 'Woche des Monats',
            dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
            dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
            dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
            dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'Wähle D, M d',
            dateFormat: 'dd.mm.yy', firstDay: 1,
            initStatus: 'Wähle ein Datum', isRTL: false};
    $.datepicker.setDefaults($.datepicker.regional['de']);
});