var elementCounter;
var singleChoiceCounter = 1;
var multipleChoiceCounter = 1;
var matrixCounter = 1;
var gradingCounter = 1;
var tendencyCounter = 1;

/*
 * function to initiate the datepicker
 */
function initiateDatepicker() {
	$( "#datepicker_begin" ).datetimepicker();
	$( "#datepicker_end" ).datetimepicker();
	$.datepicker.regional['de'] = {clearText: 'löschen', clearStatus: 'aktuelles Datum löschen',
             closeText: 'schließen', closeStatus: 'ohne Änderungen schließen',
             prevText: '&#x3c;Zurück', prevStatus: 'letzten Monat zeigen',
             nextText: 'Vor&#x3e;', nextStatus: 'nächsten Monat zeigen',
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
}

/*
 * function to show the create dialog
 */
function showCreateDialog() {
	resetCreateDialog();
	if (document.getElementById('editID').value != '-1') {
		$('#element'+document.getElementById('editID').value).show();
		document.getElementById('editID').value = '-1';
	}
	changeCreateDialog("0");
	$('#newquestion').show();
	$('#newlayout').hide();
	$('#newquestion_button').hide();
}

/*
 * function to hide the create dialog
 */
function hideCreateDialog() {
	if (document.getElementById('editID').value != '-1') {
		$('#element'+document.getElementById('editID').value).show();
		document.getElementById('editID').value = '-1';
	}
	$('#textarea_preview').hide();
	$('#singlechoice_preview').hide();
	$('#multiplechoice_preview').hide();
	$('#matrix_preview').hide();
	$('#grading_preview').hide();
	$('#tendency_preview').hide();
	$('#newquestion').hide();
	$('#text_preview').show();
	$('#newquestion_button').show();
}

/*
 * function to show the layout element dialog
 */
function showLayoutDialog() {
	resetLayoutDialog();
	if (document.getElementById('editID').value != '-1') {
		$('#element'+document.getElementById('editID').value).show();
		document.getElementById('editID').value = '-1';
	}
	changeLayoutDialog("7");
	$('#newquestion').hide();
	$('#newlayout').show();
	$('#newquestion_button').hide();
}

/*
 * function to hide the layout element dialog
 */
function hideLayoutDialog() {
	if (document.getElementById('editID').value != '-1') {
		$('#element'+document.getElementById('editID').value).show();
		document.getElementById('editID').value = '-1';
	}
	$('#newlayout').hide();
	$('#newquestion_button').show();
}

/**
 * function to reset the layout element dialog
 */
function resetLayoutDialog() {
	document.getElementById("layout_description").value = "";
	document.getElementById("layout_headline").value = "";
	document.getElementById('layoutType').value = "7";
	$('#description_preview').show();
	$('#headline_preview').hide();
	$('#newlayout').appendTo('#sortable_rf');
}

/*
 * function to reset the create dialog
 */
function resetCreateDialog() {
	document.getElementById('questionText').value = '';
	document.getElementById('helpText').value = '';
	document.getElementById('required').checked = false;
	document.getElementById('questionType')[0].selected = true;
	
	document.getElementById('textquestionLength').value = 0;
	document.getElementById('textareaRows').value = 4;
	
	// remove single choice options
	document.getElementById('singleChoiceColumns').value = 1;
	for (var i=1; i < singleChoiceCounter; i++) {
		if (document.getElementsByName('singlechoice_' + i)[0] != undefined) {
			$('#singlechoice_'+i).remove();
		}
	}
	document.getElementsByName('singlechoice_0')[0].value = '';
	singleChoiceCounter = 1;
	
	// remove multiple choice options
	document.getElementById('multipleChoiceColumns').value = 1;
	for (var i=1; i < multipleChoiceCounter; i++) {
		if (document.getElementsByName('multiplechoice_' + i)[0] != undefined) {
			$('#multiplechoice_'+i).remove();
		}
	}
	document.getElementsByName('multiplechoice_0')[0].value = '';
	multipleChoiceCounter = 1;
	
	// reset matrix
	showMatrixColumn(1);
	document.getElementById('matrixColumns').value = 1;
	for (var i=1; i <= 5; i++) {
		document.getElementsByName('matrix_column_'+i)[0].value = '';
	}
	for (var i=1; i < matrixCounter; i++) {
		if (document.getElementsByName('matrix_' + i)[0] != undefined) {
			$('#matrix_'+i).remove();
		}
	}
	document.getElementsByName('matrix_0')[0].value = '';
	matrixCounter = 1;
	
	// reset grading
	for (var i=1; i < gradingCounter; i++) {
		if (document.getElementById('grading_' + i) != undefined) {
			$('#grading_'+i).remove();
		}
	}
	document.getElementsByName('grading_0')[0].value = '';
	gradingCounter = 1;
	
	// reset tendency
	for (var i=1; i < tendencyCounter; i++) {
		if (document.getElementById('tendency_' + i) != undefined) {
			$('#tendency_'+i).remove();
		}
	}
	document.getElementsByName('tendency_0_0')[0].value = '';
	document.getElementsByName('tendency_0_1')[0].value = '';
	document.getElementById('tendency_steps').value = 2;
	tendencyCounter = 1;
	
	// move newquestion dialog to the end of sortable
	$('#newquestion').appendTo('#sortable_rf');
}

/**
 * change the layout dialog to display the dialog corresponding to type
 * @param type
 */
function changeLayoutDialog(type) {
	switch(type) {
		case "7":
			$('#description_preview').show();
			$('#headline_preview').hide();
			break;
		case "8":
			$('#description_preview').hide();
			$('#headline_preview').show();
			break;
		case "9":
			$('#description_preview').hide();
			$('#headline_preview').hide();
			break;
	}
}

/*
 * change the create dialog to display the dialog corresponding to type
 */
function changeCreateDialog(type) {
	switch (type) {
		case "0":
			$('#text_preview').show();
			$('#textarea_preview').hide();
			$('#singlechoice_preview').hide();
			$('#multiplechoice_preview').hide();
			$('#matrix_preview').hide();
			$('#grading_preview').hide();
			$('#tendency_preview').hide();
			break;
		case "1":
			$('#text_preview').hide();
			$('#textarea_preview').show();
			$('#singlechoice_preview').hide();
			$('#multiplechoice_preview').hide();
			$('#matrix_preview').hide();
			$('#grading_preview').hide();
			$('#tendency_preview').hide();
			break;
		case "2":
			$('#text_preview').hide();
			$('#textarea_preview').hide();
			$('#singlechoice_preview').show();
			$('#multiplechoice_preview').hide();
			$('#matrix_preview').hide();
			$('#grading_preview').hide();
			$('#tendency_preview').hide();
			break;
		case "3":
			$('#text_preview').hide();
			$('#textarea_preview').hide();
			$('#singlechoice_preview').hide();
			$('#multiplechoice_preview').show();
			$('#matrix_preview').hide();
			$('#grading_preview').hide();
			$('#tendency_preview').hide();
			break;
		case "4":
			$('#text_preview').hide();
			$('#textarea_preview').hide();
			$('#singlechoice_preview').hide();
			$('#multiplechoice_preview').hide();
			$('#matrix_preview').show();
			$('#grading_preview').hide();
			$('#tendency_preview').hide();
			break;
		case "5":
			$('#text_preview').hide();
			$('#textarea_preview').hide();
			$('#singlechoice_preview').hide();
			$('#multiplechoice_preview').hide();
			$('#matrix_preview').hide();
			$('#grading_preview').show();
			$('#tendency_preview').hide();
			break;
		case "6":
			$('#text_preview').hide();
			$('#textarea_preview').hide();
			$('#singlechoice_preview').hide();
			$('#multiplechoice_preview').hide();
			$('#matrix_preview').hide();
			$('#grading_preview').hide();
			$('#tendency_preview').show();
			break;
	}
}

// initiate sortable
$(function() {
	$( "#sortable_rf" ).sortable({
		placeholder: "ui-state-placeholder-rf",
		axis: "y",
		forcePlaceholderSize: true
	});
});

/*
 * function to create single choice option
 */
function createSingleChoiceOption() {
	var asseturl = document.getElementById('asseturl').value;
	$('<li style="margin: 0px; padding: 0px 0px 2px 1px; border:none;" class="option_element" id="singlechoice_'+singleChoiceCounter+'"><input name="singlechoice_'+singleChoiceCounter+'" type="text" value="" style="width: 95%;">&nbsp<input type="image" onclick="deleteSingleChoiceOption('+singleChoiceCounter+')" src="'+asseturl+'/delete.png" title="Löschen" width="12px" height="12px"></li>').appendTo($('#singlechoice_options'));
	singleChoiceCounter++;
}

/*
 * function to delete single choice option
 */
function deleteSingleChoiceOption(id) {
	$('#singlechoice_'+id).remove();
}

/*
 * function to create multiple choice option
 */
function createMultipleChoiceOption() {
	var asseturl = document.getElementById('asseturl').value;
	$('<li style="border:none; margin: 0px; padding: 0px 0px 2px 1px" id="multiplechoice_'+multipleChoiceCounter+'"><input name="multiplechoice_'+multipleChoiceCounter+'" type="text" value="" style="width: 95%;">&nbsp<input type="image" onclick="deleteMultipleChoiceOption('+multipleChoiceCounter+')" src="'+asseturl+'/delete.png" title="Löschen" width="12px" height="12px"></li>').appendTo($('#multiplechoice_options'));
	multipleChoiceCounter++;
}

/*
 * function to delete multiple choice option
 */
function deleteMultipleChoiceOption(id) {
	$('#multiplechoice_'+id).remove();
}

/*
 * function to create matrix option
 */
function createMatrixOption() {
	var asseturl = document.getElementById('asseturl').value;
	$('<li style="border:none; margin: 0px; padding: 0px 0px 2px 1px;" id="matrix_'+matrixCounter+'"><input name="matrix_'+matrixCounter+'" type="text" value="" style="width: 95%;">&nbsp<input type="image" onclick="deleteMatrixOption('+matrixCounter+')" src="'+asseturl+'/delete.png" title="Löschen" width="12px" height="12px"></li>').appendTo($('#matrix_options'));
	matrixCounter++;
}

/*
 * function to delete matrix option
 */
function deleteMatrixOption(id) {
	$('#matrix_'+id).remove();
}

/*
 * function to show matrix column input fields (amount = count)
 */
function showMatrixColumn(count) {
	count = parseInt(count);
	for (var i=1; i <= count; i++) {
		document.getElementById('matrix_column_'+i).style.display = '';
	}
	for (var i=count+1; i <= 6; i++) {
		document.getElementById('matrix_column_'+i).style.display = 'none';
	}
}

/*
 * function to create grading option
 */
function createGradingOption() {
	var asseturl = document.getElementById('asseturl').value;
	$('<li style="border:none; margin: 0px; padding: 0px 0px 2px 1px;" id="grading_'+gradingCounter+'"><input name="grading_'+gradingCounter+'" type="text" value="" style="width: 95%;">&nbsp<input type="image" onclick="deleteGradingOption('+gradingCounter+')" src="'+asseturl+'/delete.png" title="Löschen" width="12px" height="12px"></li>').appendTo($('#grading_options'));
	gradingCounter++;
}

/*
 * function to delete grading option
 */
function deleteGradingOption(id) {
	$('#grading_'+id).remove();
}

/*
 * function to create tendency option
 */
function createTendencyOption() {
	var asseturl = document.getElementById('asseturl').value;
	$('<li style="border:none; margin: 0px; padding: 0px 0px 2px 1px;" id="tendency_'+tendencyCounter+'"><input type="text" name="tendency_'+tendencyCounter+'_0" value="" style="width: 30%;"> - - - <input type="text" name="tendency_'+tendencyCounter+'_1" value="" style="width: 30%;">&nbsp<input type="image" onclick="deleteTendencyOption('+tendencyCounter+')" src="'+asseturl+'/delete.png" title="Löschen" width="12px" height="12px"></li>').appendTo($('#tendency_options'));
	tendencyCounter++;
}

/*
 * function to delete tendency option
 */
function deleteTendencyOption(id) {
	$('#tendency_'+id).remove();
}

/*
 * function to initiate the element counter
 */
function initiateCounter() {
	elementCounter = document.getElementById("elementcounter").value;
}

/*
 * function to save the order in sortable
 */
function getSortables() {
	if (!checkTitle()) return false;
	var result = $('#sortable_rf').sortable('toArray');
	document.getElementById("sortable_array").value = result;
}

/*
 * function to delete a question
 */
function deleteElement(id) {
	var data = document.getElementsByName('rfelement'+id)[0].value;
	data = data.split(',');
	switch (data[0]) {
		case "7":
			var check = confirm('Beschreibung "'+decodeURIComponent(data[1])+'" wirklich löschen?');
			break;
		case "8":
			var check = confirm('Überschrift "'+decodeURIComponent(data[1])+'" wirklich löschen?');
			break;
		case "9":
			var check = confirm('Seitenumbruch wirklich löschen?');
			break;
		default:
			var check = confirm('Frage "'+decodeURIComponent(data[1])+'" wirklich löschen?');
			break;
	}
	if (check == true) {
		$('#element'+id).remove();
		$('input[name=element'+id+']').remove();
		$('input[name=element'+id+'_options]').remove();
		$('input[name=element'+id+'_rows]').remove();
		$('input[name=element'+id+'_columns]').remove();
	}
}

function editLayoutElement(id) {
	var data = document.getElementsByName('rfelement'+id)[0].value;
	data = data.split(',');
	resetLayoutDialog();
	resetCreateDialog();
	if (document.getElementById('editID').value != '-1') {
		$('#element'+document.getElementById('editID').value).show();
		document.getElementById('editID').value = '-1';
	}
	document.getElementById('newquestion_button').style.display = '';
	document.getElementById('editID').value = id;
	document.getElementById('layoutType').value = data[0];
	$('#newlayout').insertBefore($('#element'+id));
	$('#element'+id).hide();
	$('#newquestion').hide();
	changeLayoutDialog(data[0]);
	switch (data[0]) {
		case "7":
			document.getElementById('layout_description').value = decodeURIComponent(data[1]);
			break;
		case "8":
			document.getElementById('layout_headline').value = decodeURIComponent(data[1]);
			break;
		case "9":
			break;
	}
	$('#newlayout').show();
}

/*
 * function to open the edit question dialog for question id
 */
function editElement(id) {
	var data = document.getElementsByName('rfelement'+id)[0].value;
	data = data.split(',');
	resetCreateDialog();
	resetLayoutDialog();
	if (document.getElementById('editID').value != '-1') {
		$('#element'+document.getElementById('editID').value).show();
		document.getElementById('editID').value = '-1';
	}
	document.getElementById('newquestion_button').style.display = '';
	document.getElementById('editID').value = id;
	$('#newlayout').hide();
	$('#newquestion').insertBefore($('#element'+id));
	$('#element'+id).hide();
	changeCreateDialog(data[0]);
	document.getElementById('questionType').value = data[0];
	document.getElementById('questionText').value = decodeURIComponent(data[1]);
	document.getElementById('helpText').value = decodeURIComponent(data[2]);
	if (data[3] == 1) {
		document.getElementById('required').checked = true;
	}
	
	switch (data[0]) {
		case "0":
			document.getElementById('textquestionLength').value = data[4];
			$('#newquestion').show();
			break;
		case "1":
			document.getElementById('textareaRows').value = data[4];
			$('#newquestion').show();
			break;
		case "2":
			var options = document.getElementsByName('rfelement'+id+'_options')[0].value;
			options = options.split(',');
			for (var i=0; i < options.length; i++) {
				if (i != 0) {
					createSingleChoiceOption();
				}
				document.getElementsByName('singlechoice_' + i)[0].value = decodeURIComponent(options[i]);
			}
			document.getElementById('singleChoiceColumns').value = data[4];
			$('#newquestion').show();
			break;
		case "3":
			var options = document.getElementsByName('rfelement'+id+'_options')[0].value;
			options = options.split(',');
			for (var i=0; i < options.length; i++) {
				if (i != 0) {
					createMultipleChoiceOption();
				}
				document.getElementsByName('multiplechoice_' + i)[0].value = decodeURIComponent(options[i]);
			}
			document.getElementById('multipleChoiceColumns').value = data[4];
			$('#newquestion').show();
			break;
		case "4":
			var columns = document.getElementsByName('rfelement'+id+'_columns')[0].value;
			columns = columns.split(',');
			showMatrixColumn(columns.length);
			document.getElementById('matrixColumns').value = columns.length;
			for (var i=1; i <= columns.length; i++) {
				document.getElementsByName('matrix_column_'+i)[0].value = decodeURIComponent(columns[i-1]);
			}
			
			var rows = document.getElementsByName('rfelement'+id+'_rows')[0].value;
			rows = rows.split(',');
			for (var i=0; i < rows.length; i++) {
				if (i != 0) {
					createMatrixOption();
				}
				document.getElementsByName('matrix_' + i)[0].value = decodeURIComponent(rows[i]);
			}
			$('#newquestion').show();
			break;
		case "5":
			var options = document.getElementsByName('rfelement'+id+'_rows')[0].value;
			options = options.split(',');
			for (var i=0; i < options.length; i++) {
				if (i != 0) {
					createGradingOption();
				}
				document.getElementsByName('grading_' + i)[0].value = decodeURIComponent(options[i]);
			}
			$('#newquestion').show();
			break;
		case "6":
			var options = document.getElementsByName('rfelement'+id+'_options')[0].value;
			options = options.split(',');
			for (var i=0; i < options.length; i=i+2) {
				if (i != 0) {
					createTendencyOption();
				}
				document.getElementsByName('tendency_' + (i/2) + '_0')[0].value = decodeURIComponent(options[i]);
				document.getElementsByName('tendency_' + (i/2) + '_1')[0].value = decodeURIComponent(options[i+1]);
			}
			document.getElementById('tendency_steps').value = data[4];
			$('#newquestion').show();
			break;
		default:
			$('#newquestion').show();
			break;
	}
}

/*
 * function to copy question id
 */
function copyElement(id) {
	var data = document.getElementsByName('rfelement'+id)[0].value;
	data = data.split(',');
	switch(data[0]) {
		case "0":
			createTextQuestion(data, '#element'+id);
			break;
		case "1":
			createTextareaQuestion(data, '#element'+id);
			break;
		case "2":
			var options = document.getElementsByName('rfelement'+id+'_options')[0].value;
			options = options.split(',');
			createSingleChoiceQuestion(data, options, '#element'+id);
			break;
		case "3":
			var options = document.getElementsByName('rfelement'+id+'_options')[0].value;
			options = options.split(',');
			createMultipleChoiceQuestion(data, options, '#element'+id);
			break;
		case "4":
			var columns = document.getElementsByName('rfelement'+id+'_columns')[0].value;
			columns = columns.split(',');
			var rows = document.getElementsByName('rfelement'+id+'_rows')[0].value;
			rows = rows.split(',');
			createMatrixQuestion(data, columns, rows, '#element'+id);
			break;
		case "5":
			var options = document.getElementsByName('rfelement'+id+'_rows')[0].value;
			options = options.split(',');
			createGradingQuestion(data, options, '#element'+id);
			break;
		case "6":
			var options = document.getElementsByName('rfelement'+id+'_options')[0].value;
			options = options.split(',');
			createTendencyQuestion(data, options, '#element'+id);
			break;
		case "7":
			createDescriptionLayoutElement(data[1], '#element'+id);
			break;
		case "8":
			createHeadlineLayoutElement(data[1], '#element'+id);
			break;
		case "9":
			createPageBreakLayoutElement('#element'+id);
			break;
	}
}

/*
 * function to add a layout element
 */
function addLayoutElement() {
	if (document.getElementById('editID').value != '-1') {
		var deleteid = document.getElementById('editID').value;
		$('#element'+deleteid).remove();
		$('input[name=element'+deleteid+']').remove();
		$('input[name=element'+deleteid+'_options]').remove();
		$('input[name=element'+deleteid+'_rows]').remove();
		$('input[name=element'+deleteid+'_columns]').remove();
		document.getElementById('editID').value = '-1';
	}
	var type = document.getElementById('layoutType').value;
	switch (type) {
		case "7":
			var description = document.getElementById('layout_description').value;
			createDescriptionLayoutElement(description, '#newlayout');
			hideLayoutDialog();
			break;
		case "8":
			var headline = document.getElementById('layout_headline').value;
			createHeadlineLayoutElement(headline, '#newlayout');
			hideLayoutDialog();
			break;
		case "9":
			createPageBreakLayoutElement('#newlayout');
			hideLayoutDialog();
			break;
	}
}
	
function createDescriptionLayoutElement(description, insertPoint) {
	params = {};
	params.layoutType = "7";
	params.description = description;
	params.layoutID = elementCounter;	 
	var successFunction = function(response){
		responseData = jQuery.parseJSON(response); 
		$(responseData.html).insertBefore($(insertPoint));
	}
	var response = sendRequest('AddLayoutElement', params, 0, 'data', '', successFunction);
	elementCounter++;
}

function createHeadlineLayoutElement(headline, insertPoint) {
	params = {};
	params.layoutType = "8";
	params.headline = headline;
	params.layoutID = elementCounter;	 
	var successFunction = function(response){
		responseData = jQuery.parseJSON(response); 
		$(responseData.html).insertBefore($(insertPoint));
	}
	var response = sendRequest('AddLayoutElement', params, 0, 'data', '', successFunction);
	elementCounter++;	
}

function createPageBreakLayoutElement(insertPoint) {
	params = {};
	params.layoutType = "9";
	params.layoutID = elementCounter;	 
	var successFunction = function(response){
		responseData = jQuery.parseJSON(response); 
		$(responseData.html).insertBefore($(insertPoint));
	}
	var response = sendRequest('AddLayoutElement', params, 0, 'data', '', successFunction);
	elementCounter++;
}

/*
 * function to add a new question
 */
function addElement() {
	var question = encodeURIComponent(document.getElementById('questionText').value);
	if (jQuery.trim(question) == '') {
		alert("Bitte einen Fragetext eingeben.");
		return;
	}
	var type = document.getElementById('questionType').value;
	if (document.getElementById('editID').value != '-1') {
		var deleteid = document.getElementById('editID').value;
		$('#element'+deleteid).remove();
		$('input[name=element'+deleteid+']').remove();
		$('input[name=element'+deleteid+'_options]').remove();
		$('input[name=element'+deleteid+'_rows]').remove();
		$('input[name=element'+deleteid+'_columns]').remove();
		document.getElementById('editID').value = '-1';
	}
	var helpText = encodeURIComponent(document.getElementById('helpText').value);
	if (document.getElementById('required').checked == true) {
		var required_text = '*';
		var required = 1;
	} else {
		var required_text = '';
		var required = 0;
	}
	switch (type) {
		case "0":
			var inputLength = document.getElementById('textquestionLength').value;
			var data = new Array(type, question, helpText, required, inputLength);
			createTextQuestion(data, '#newquestion');
			hideCreateDialog();
			break;
		case "1":
			var rows = document.getElementById('textareaRows').value;
			var data = new Array(type, question, helpText, required, rows);
			createTextareaQuestion(data, '#newquestion');
			hideCreateDialog();
			break;
		case "2":
			var options = new Array();
			var arrangement = document.getElementById('singleChoiceColumns').value;
			for (var i=0; i <= singleChoiceCounter; i++) {
				if (document.getElementsByName('singlechoice_' + i)[0] != undefined) {
					options.push(encodeURIComponent(document.getElementsByName('singlechoice_' + i)[0].value));
				}
			}
			var data = new Array(type, question, helpText, required, arrangement);
			createSingleChoiceQuestion(data, options, '#newquestion');
			hideCreateDialog();
			break;
		case "3":
			var options = new Array();
			var arrangement = document.getElementById('multipleChoiceColumns').value;
			for (var i=0; i <= multipleChoiceCounter; i++) {
				if (document.getElementsByName('multiplechoice_' + i)[0] != undefined) {
					options.push(encodeURIComponent(document.getElementsByName('multiplechoice_' + i)[0].value));
				}
			}
			var data = new Array(type, question, helpText, required, arrangement);
			createMultipleChoiceQuestion(data, options, '#newquestion');
			hideCreateDialog();
			break;
		case "4":
			var countRows = document.getElementById('matrixColumns').value;
			var columns = new Array();
			for (var i=1; i <= countRows; i++) {
				columns.push(encodeURIComponent(document.getElementsByName('matrix_column_'+i)[0].value));
			}
			var rows = new Array();
			for (var i=0; i <= matrixCounter; i++) {
				if (document.getElementsByName('matrix_' + i)[0] != undefined) {
					rows.push(encodeURIComponent(document.getElementsByName('matrix_' + i)[0].value));
				}
			}
			var data = new Array(type, question, helpText, required);
			createMatrixQuestion(data, columns, rows, '#newquestion');
			hideCreateDialog();
			break;
		case "5":
			var options = new Array();
			for (var i=0; i <= gradingCounter; i++) {
				if (document.getElementsByName('grading_' + i)[0] != undefined) {
					options.push(encodeURIComponent(document.getElementsByName('grading_' + i)[0].value));
				}
			}
			var data = new Array(type, question, helpText, required);
			createGradingQuestion(data, options, '#newquestion');
			hideCreateDialog();
			break;
		case "6":
			var options = new Array();
			var steps = document.getElementById('tendency_steps').value;
			for (var i=0; i <= tendencyCounter; i++) {
				if (document.getElementById('tendency_' + i) != undefined) {
					options.push(encodeURIComponent(document.getElementsByName('tendency_' + i + '_0')[0].value));
					options.push(encodeURIComponent(document.getElementsByName('tendency_' + i + '_1')[0].value));
				}
			}
			var data = new Array(type, question, helpText, required, steps);
			createTendencyQuestion(data, options, '#newquestion');
			hideCreateDialog();
	}
}

/*
 * function to create textquestion
 */
function createTextQuestion(data, insertPoint) {
	params = {};
	params.questionType = data[0];
	params.questionText = data[1];
	params.questionHelp = data[2];
	params.questionRequired = data[3];
	params.questionInputLength = data[4];
	params.questionRows = data[4];
	params.questionID = elementCounter;	 
	var successFunction = function(response){
		responseData = jQuery.parseJSON(response); 
		$(responseData.html).insertBefore($(insertPoint));
	}
	var response = sendRequest('AddQuestion', params, 0, 'data', '', successFunction);
	elementCounter++;   			
}

/*
 * function to create textareaquestion
 */
function createTextareaQuestion(data, insertPoint) {
	createTextQuestion(data, insertPoint);
}

/*
 * function to create singlechoice question
 */
function createSingleChoiceQuestion(data, options, insertPoint) {
	params = {};
	params.questionType = data[0];
	params.questionText = data[1];
	params.questionHelp = data[2];
	params.questionRequired = data[3];
	params.questionID = elementCounter;	 
	params.questionArrangement = data[4];
	params.options = options;
	var successFunction = function(response){
		responseData = jQuery.parseJSON(response); 
		$(responseData.html).insertBefore($(insertPoint));
	}
	var response = sendRequest('AddQuestion', params, 0, 'data', '', successFunction);
	elementCounter++;   
}

/*
 * function to create multiple choice question
 */
function createMultipleChoiceQuestion(data, options, insertPoint) {
	createSingleChoiceQuestion(data, options, insertPoint);
}

/*
 * function to create matrix question
 */
function createMatrixQuestion(data, columns, rows, insertPoint) {
	params = {};
	params.questionType = data[0];
	params.questionText = data[1];
	params.questionHelp = data[2];
	params.questionRequired = data[3];
	params.questionID = elementCounter;
	params.columns = columns;
	params.rows = rows;
	var successFunction = function(response){
		responseData = jQuery.parseJSON(response); 
		$(responseData.html).insertBefore($(insertPoint));
	}
	var response = sendRequest('AddQuestion', params, 0, 'data', '', successFunction);
	elementCounter++;   
	return;
}

/*
 * function to create grading question
 */
function createGradingQuestion(data, rows, insertPoint) {
	var columns = new Array('sehr gut', 'gut', 'befriedigend', 'ausreichend', 'mangelhaft', 'ungenügend');
	createMatrixQuestion(data, columns, rows, insertPoint);
}

/*
 * function to create tendency question
 */
function createTendencyQuestion(data, options, insertPoint) {
	createSingleChoiceQuestion(data, options, insertPoint);
}

/*
 * function to request admin actions
 */
function admin_action(action, id, name, rf) {
	params = {};
	params.id = id;
	params.action = action;
	params.rf = rf;
	if (action == 4) {
		var check = confirm('Umfrage "'+name+'" wirklich löschen?');
		if (check == true) {
			sendRequest('AdminAction', params, '', 'reload');
		}
	} else {
		sendRequest('AdminAction', params, '', 'reload');
	}
}

/*
 * function to delete a result
 */
function deleteResult(id, survey, rf) {
	params = {};
	params.id = id;
	params.survey = survey;
	params.rf = rf;
	var check = confirm('Ausgewählte Abgabe wirklich löschen?');
	if (check == true) {
		sendRequest('DeleteResult', params, '', 'reload');
	}
}

/*
 * function to check title input
 */
function checkTitle() {
	if ($.trim(document.getElementById("title").value) == "") {
		alert("Bitte einen Titel eingeben.");
		return false;
	} else return true;
}

/*
 * function to submit one page of a survey when clicking on the next page button
 */
function submitPrevious(url) {
	document.getElementById('action').value = 'previous';
	document.getElementById('form').action = url;
	document.getElementById('form').submit();
}

/*
 * function to submit one page of a survey when clicking on the next page button
 */
function submitNext(url) {
	document.getElementById('action').value = 'next';
	document.getElementById('form').action = url;
	document.getElementById('form').submit();
}