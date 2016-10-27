var elementCounter;
var singleChoiceCounter = 1;
var multipleChoiceCounter = 1;
var matrixColumnCounter = 1;
var matrixRowCounter = 1;
var gradingCounter = 1;
var tendencyCounter = 1;

var enterFunction = (function(event) {
    if (navigator.appName == "Microsoft Internet Explorer") {
        if (event.keyCode == 13) {
            return false;
        }
    } else {
        if (event.which == 13) {
            return false;
        }
    }
});

/*
 * function to initiate the datepicker
 */
function initiateDatepicker() {
    $("#datepicker_begin").datetimepicker();
    $("#datepicker_end").datetimepicker();
    $.datepicker.regional['de'] = {clearText: 'löschen', clearStatus: 'aktuelles Datum löschen',
        closeText: 'schließen', closeStatus: 'ohne Änderungen schließen',
        prevText: '&#x3c;Zurück', prevStatus: 'letzten Monat zeigen',
        nextText: 'Vor&#x3e;', nextStatus: 'nächsten Monat zeigen',
        currentText: 'heute', currentStatus: '',
        monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
            'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
        monthNamesShort: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun',
            'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
        monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',
        weekHeader: 'Wo', weekStatus: 'Woche des Monats',
        dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
        dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
        dayNamesMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
        dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'Wähle D, M d',
        dateFormat: 'dd.mm.yy', firstDay: 1,
        initStatus: 'Wähle ein Datum', isRTL: false};
    $.datepicker.setDefaults($.datepicker.regional['de']);
}

/*
 * function to show the create dialog
 */
function showCreateDialog(type) {
    resetCreateDialog();
    if (document.getElementById('editID').value != '-1') {
        $('#rfelement' + document.getElementById('editID').value).show();
        document.getElementById('editID').value = '-1';
    }
    changeQuestionDialog(type.toString(), false);
    $('#newquestion').show();
    $('#newlayout').hide();
    //$('#newquestion_button').hide();
    $("form").each(function() {
        $(this).keypress(enterFunction)
    });


}

/*
 * function to hide the create dialog
 */
function hideCreateDialog() {
    if (document.getElementById('editID').value != '-1') {
        $('#rfelement' + document.getElementById('editID').value).show();
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
    //$('#newquestion_button').show();
}

/*
 * function to show the layout element dialog
 */
function showLayoutDialog(type) {
    resetLayoutDialog();
    if (document.getElementById('editID').value != '-1') {
        $('#rfelement' + document.getElementById('editID').value).show();
        document.getElementById('editID').value = '-1';
    }
    changeLayoutDialog(type.toString(), false);
    $('#newquestion').hide();
    $('#newlayout').show();
    //$('#newquestion_button').hide();
}

/*
 * function to hide the layout element dialog
 */
function hideLayoutDialog() {
    if (document.getElementById('editID').value != '-1') {
        $('#rfelement' + document.getElementById('editID').value).show();
        document.getElementById('editID').value = '-1';
    }
    $('#newlayout').hide();
    //$('#newquestion_button').show();
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
    document.getElementById('singleChoiceColumns').value = "vertikal";
    for (var i = 1; i < singleChoiceCounter; i++) {
        if (document.getElementsByName('singlechoice_' + i)[0] != undefined) {
            $('#singlechoice_' + i).remove();
        }
    }
    document.getElementsByName('singlechoice_0')[0].value = '';
    singleChoiceCounter = 1;

    // remove multiple choice options
    document.getElementById('multipleChoiceColumns').value = "vertikal";
    for (var i = 1; i < multipleChoiceCounter; i++) {
        if (document.getElementsByName('multiplechoice_' + i)[0] != undefined) {
            $('#multiplechoice_' + i).remove();
        }
    }
    document.getElementsByName('multiplechoice_0')[0].value = '';
    multipleChoiceCounter = 1;

    //columns
    for (var i = 1; i < matrixColumnCounter; i++) {
        if (document.getElementsByName('matrix_column_' + i)[0] != undefined) {
            $('#matrix_column_' + i).remove();
        }
    }
    document.getElementsByName('matrix_column_0')[0].value = '';
    matrixColumnCounter = 1;

    //rows
    for (var i = 1; i < matrixRowCounter; i++) {
        if (document.getElementsByName('matrix_row_' + i)[0] != undefined) {
            $('#matrix_row_' + i).remove();
        }
    }
    document.getElementsByName('matrix_row_0')[0].value = '';
    matrixRowCounter = 1;

    // reset grading
    for (var i = 1; i < gradingCounter; i++) {
        if (document.getElementById('grading_' + i) != undefined) {
            $('#grading_' + i).remove();
        }
    }
    document.getElementsByName('grading_0')[0].value = '';
    gradingCounter = 1;

    // reset tendency
    for (var i = 1; i < tendencyCounter; i++) {
        if (document.getElementById('tendency_' + i) != undefined) {
            $('#tendency_' + i).remove();
        }
    }
    document.getElementsByName('tendency_input_0_0')[0].value = '';
    document.getElementsByName('tendency_input_0_1')[0].value = '';
    document.getElementById('tendency_steps').value = 2;
    tendencyCounter = 1;

    // move newquestion dialog to the end of sortable
    $('#newquestion').appendTo('#sortable_rf');
}

/**
 * change the layout dialog to display the dialog corresponding to type
 * @param type
 */
function changeLayoutDialog(type, edit) {
    $('#layoutType').val(type);

    if(edit){
      $('#rfb-new-element').val("Änderungen speichern");
    }
    else{
      $('#rfb-new-element').val("Element hinzufügen");
    }

    switch (type) {
        case "7":
            $('#description_preview').show();
            $('#headline_preview').hide();
            $('#jumplabel_preview').hide();
            $('#jumplabel_preview2').hide();
            if(edit){
              $('#layout_headline_left').html("Beschreibung bearbeiten");
            }
            else{
              $('#layout_headline_left').html("Neue Beschreibung erstellen");
            }
            break;
        case "8":
            $('#description_preview').hide();
            $('#headline_preview').show();
            $('#jumplabel_preview').hide();
            $('#jumplabel_preview2').hide();
            if(edit){
              $('#layout_headline_left').html("Überschrift bearbeiten");
            }
            else{
              $('#layout_headline_left').html("Neue Überschrift erstellen");
            }
            break;
        case "9":
            $('#description_preview').hide();
            $('#headline_preview').hide();
            $('#jumplabel_preview').hide();
            $('#jumplabel_preview2').hide();
            if(edit){
              $('#layout_headline_left').html("Seitenumbruch bearbeiten");
            }
            else{
              $('#layout_headline_left').html("Neuen Seitenumbruch erstellen");
            }
            break;
        case "10":
            $('#description_preview').hide();
            $('#headline_preview').hide();
            $('#jumplabel_preview').show();
            $('#jumplabel_preview2').show();
            if(edit){
              $('#layout_headline_left').html("Sprungmarke bearbeiten");
            }
            else{
              $('#layout_headline_left').html("Neue Sprungmarke erstellen");
            }
            break;
    }
}

/*
 * change the create dialog to display the dialog corresponding to type
 */
function changeQuestionDialog(type, edit) {
  $('#questionType').val(type);

  if(edit){
    $('#question_headline_left').html("Frage bearbeiten");
    $('#rfb-new-question').val("Änderungen speichern");
  }
  else{
    $('#question_headline_left').html("Neue Frage erstellen");
    $('#rfb-new-question').val("Frage hinzufügen");
  }

    switch (type) {
        case "0":
            $('#text_preview').show();
            $('#textarea_preview').hide();
            $('#singlechoice_preview').hide();
            $('#multiplechoice_preview').hide();
            $('#matrix_preview').hide();
            $('#grading_preview').hide();
            $('#tendency_preview').hide();
            $('#question_headline_right').html("Typ: kurzer Text");
            break;
        case "1":
            $('#text_preview').hide();
            $('#textarea_preview').show();
            $('#singlechoice_preview').hide();
            $('#multiplechoice_preview').hide();
            $('#matrix_preview').hide();
            $('#grading_preview').hide();
            $('#tendency_preview').hide();
            $('#question_headline_right').html("Typ: langer Text");
            break;
        case "2":
            $('#text_preview').hide();
            $('#textarea_preview').hide();
            $('#singlechoice_preview').show();
            $('#multiplechoice_preview').hide();
            $('#matrix_preview').hide();
            $('#grading_preview').hide();
            $('#tendency_preview').hide();
            $('#question_headline_right').html("Typ: Single Choice");
            break;
        case "3":
            $('#text_preview').hide();
            $('#textarea_preview').hide();
            $('#singlechoice_preview').hide();
            $('#multiplechoice_preview').show();
            $('#matrix_preview').hide();
            $('#grading_preview').hide();
            $('#tendency_preview').hide();
            $('#question_headline_right').html("Typ: Multiple Choice");
            break;
        case "4":
            $('#text_preview').hide();
            $('#textarea_preview').hide();
            $('#singlechoice_preview').hide();
            $('#multiplechoice_preview').hide();
            $('#matrix_preview').show();
            $('#grading_preview').hide();
            $('#tendency_preview').hide();
            $('#question_headline_right').html("Typ: Matrix");
            break;
        case "5":
            $('#text_preview').hide();
            $('#textarea_preview').hide();
            $('#singlechoice_preview').hide();
            $('#multiplechoice_preview').hide();
            $('#matrix_preview').hide();
            $('#grading_preview').show();
            $('#tendency_preview').hide();
            $('#question_headline_right').html("Typ: Benotung");
            break;
        case "6":
            $('#text_preview').hide();
            $('#textarea_preview').hide();
            $('#singlechoice_preview').hide();
            $('#multiplechoice_preview').hide();
            $('#matrix_preview').hide();
            $('#grading_preview').hide();
            $('#tendency_preview').show();
            $('#question_headline_right').html("Typ: Tendenz");
            break;
    }
}

$( document ).ready(function() {
  $("#sortable_rf").sortable({
    placeholder: "ui-state-placeholder-rf",
    axis: "y",
    forcePlaceholderSize: true,
    update: function(event, ui){updateNumbering()}
  });
  $("#sortable_rf").sortable("disable");
});

// initiate sortable
function initiateSortable(){
  $("#sortable_rf").sortable("enable");
  $("#sortable_rf").css('cursor', 'move');
  $("#sort-icon").parent().css("background-color", "#ff8300");
  $("#sort-icon").attr("name", "true");
}

// remove sortable
function removeSortable(){
  $("#sortable_rf").sortable("disable");
  $("#sortable_rf").css('cursor', 'auto');
  $("#sort-icon").parent().css("background-color", "#3a6e9f");
  $("#sort-icon").attr("name", "false");

  $('#save-que-button').click();
}

// update numbering of questions during sort or after creation/deletion/duplication
function updateNumbering(){
  var counter = 1;
  var sortedIDs = $('#sortable_rf').sortable('toArray');
  for(var i = 0; i<sortedIDs.length; i++){
    var question = sortedIDs[i];
    if(question != "newquestion" && question != "newlayout" && question != "") {
      var title = $("#"+question).find(".question_headline").find("b").html();
      var titleArray = title.split(".");
      if(!isNaN(titleArray[0])){
        var newTitle = counter + "." + titleArray[1];
        counter++;
        $("#"+question).find(".question_headline").find("b").html(newTitle);
      }
    }
  }
}

/*
 * function to create single choice option
 */
function createSingleChoiceOption() {
    var asseturl = document.getElementById('asseturl').value;
    $('<li style="margin: 0px; padding-bottom: 5px; border:none;" class="option_element" id="singlechoice_' + singleChoiceCounter + '"><input name="singlechoice_' + singleChoiceCounter + '" type="text" value="" style="width: 95%;"><div class="bidButton negative" style="margin-bottom:-8px; margin-left:5px; padding:4px;" onclick="deleteSingleChoiceOption(' + singleChoiceCounter + ')" title="Löschen"><svg style="height:16px; width:16px;"><use xlink:href="' + asseturl + '/trash.svg#trash"/></svg></div></li>').appendTo($('#singlechoice_options'));
    singleChoiceCounter++;
}

/*
 * function to delete single choice option
 */
function deleteSingleChoiceOption(id) {
    $('#singlechoice_' + id).remove();
}

/*
 * function to create multiple choice option
 */
function createMultipleChoiceOption() {
    var asseturl = document.getElementById('asseturl').value;
    $('<li style="border:none; margin: 0px; padding-bottom: 5px;" id="multiplechoice_' + multipleChoiceCounter + '"><input name="multiplechoice_' + multipleChoiceCounter + '" type="text" value="" style="width: 95%;"><div class="bidButton negative" style="margin-bottom:-8px; margin-left:5px; padding:4px;" onclick="deleteMultipleChoiceOption(' + multipleChoiceCounter + ')" title="Löschen"><svg style="height:16px; width:16px;"><use xlink:href="' + asseturl + '/trash.svg#trash"/></svg></div></li>').appendTo($('#multiplechoice_options'));
    multipleChoiceCounter++;
}

/*
 * function to delete multiple choice option
 */
function deleteMultipleChoiceOption(id) {
    $('#multiplechoice_' + id).remove();
}

/*
 * function to create matrix column option
 */
function createMatrixColumnOption() {
    var asseturl = document.getElementById('asseturl').value;
    $('<li style="border:none; margin: 0px; padding-bottom: 5px;" id="matrix_column_' + matrixColumnCounter + '"><input name="matrix_column_' + matrixColumnCounter + '" type="text" value="" style="width: 95%;"><div class="bidButton negative" style="margin-bottom:-8px; margin-left:5px; padding:4px;" onclick="deleteMatrixColumnOption(' + matrixColumnCounter + ')" title="Löschen"><svg style="height:16px; width:16px;"><use xlink:href="' + asseturl + '/trash.svg#trash"/></svg></div></li>').appendTo($('#matrix_column_options'));
    matrixColumnCounter++;
}

/*
 * function to delete matrix column option
 */
function deleteMatrixColumnOption(id) {
    $('#matrix_column_' + id).remove();
}

/*
 * function to create matrix row option
 */
function createMatrixRowOption() {
    var asseturl = document.getElementById('asseturl').value;
    $('<li style="border:none; margin: 0px; padding-bottom: 5px;" id="matrix_row_' + matrixRowCounter + '"><input name="matrix_row_' + matrixRowCounter + '" type="text" value="" style="width: 95%;"><div class="bidButton negative" style="margin-bottom:-8px; margin-left:5px; padding:4px;" onclick="deleteMatrixRowOption(' + matrixRowCounter + ')" title="Löschen"><svg style="height:16px; width:16px;"><use xlink:href="' + asseturl + '/trash.svg#trash"/></svg></div></li>').appendTo($('#matrix_row_options'));
    matrixRowCounter++;
}

/*
 * function to delete matrix row option
 */
function deleteMatrixRowOption(id) {
    $('#matrix_row_' + id).remove();
}

/*
 * function to show matrix column input fields (amount = count)

function showMatrixColumn(count) {
    count = parseInt(count);
    for (var i = 1; i <= count; i++) {
        document.getElementById('matrix_column_' + i).style.display = '';
    }
    for (var i = count + 1; i <= 6; i++) {
        document.getElementById('matrix_column_' + i).style.display = 'none';
    }
}
*/

/*
 * function to create grading option
 */
function createGradingOption() {
    var asseturl = document.getElementById('asseturl').value;
    $('<li style="border:none; margin: 0px; padding-bottom: 5px;" id="grading_' + gradingCounter + '"><input name="grading_' + gradingCounter + '" type="text" value="" style="width: 95%;"><div class="bidButton negative" style="margin-bottom:-8px; margin-left:5px; padding:4px;" onclick="deleteGradingOption(' + gradingCounter + ')" title="Löschen"><svg style="height:16px; width:16px;"><use xlink:href="' + asseturl + '/trash.svg#trash"/></svg></div></li>').appendTo($('#grading_options'));
    gradingCounter++;
}

/*
 * function to delete grading option
 */
function deleteGradingOption(id) {
    $('#grading_' + id).remove();
}

/*
 * function to create tendency option
 */
function createTendencyOption() {
    var asseturl = document.getElementById('asseturl').value;
    $('<li style="border:none; margin: 0px; padding-bottom: 5px;" id="tendency_' + tendencyCounter + '"><input type="text" name="tendency_input_' + tendencyCounter + '_0" value="" style="width:45%;"><div style="width:4%; display:inline-block;"> - - - </div><input type="text" name="tendency_input_' + tendencyCounter + '_1" value="" style="width:45%;"><div class="bidButton negative" style="margin-bottom:-8px; margin-left:5px; padding:4px;" onclick="deleteTendencyOption(' + tendencyCounter + ')" title="Löschen"><svg style="height:16px; width:16px;"><use xlink:href="' + asseturl + '/trash.svg#trash"/></svg></div></li>').appendTo($('#tendency_options'));
    tendencyCounter++;
}

/*
 * function to delete tendency option
 */
function deleteTendencyOption(id) {
    $('#tendency_' + id).remove();
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
    //if (!checkTitle()) return false;
    var result = $('#sortable_rf').sortable('toArray');
    document.getElementById("sortable_array").value = result;
}

/*
 * function to delete a question
 */
function deleteElement(id) {
    var data = document.getElementsByName('rfelement' + id)[0].value;
    data = data.split(',');
    switch (data[0]) {
        case "7":
            var check = confirm('Beschreibung "' + decodeURIComponent(data[1]) + '" wirklich löschen?');
            break;
        case "8":
            var check = confirm('Überschrift "' + decodeURIComponent(data[1]) + '" wirklich löschen?');
            break;
        case "9":
            var check = confirm('Seitenumbruch wirklich löschen?');
            break;
        default:
            var check = confirm('Frage "' + decodeURIComponent(data[1]) + '" wirklich löschen?');
            break;
    }
    if (check == true) {
        $('#rfelement' + id).remove();
        $('input[name=rfelement' + id + ']').remove();
        $('input[name=rfelement' + id + '_options]').remove();
        $('input[name=rfelement' + id + '_rows]').remove();
        $('input[name=rfelement' + id + '_columns]').remove();
    }
    setTimeout(function(){$('#save-que-button').click();},750);
}

function editLayoutElement(id) {
    var data = document.getElementsByName('rfelement' + id)[0].value;
    data = data.split(',');
    resetLayoutDialog();
    resetCreateDialog();
    if (document.getElementById('editID').value != '-1') {
        $('#element' + document.getElementById('editID').value).show();
        document.getElementById('editID').value = '-1';
    }
    //document.getElementById('newquestion_button').style.display = '';
    document.getElementById('editID').value = id;
    document.getElementById('layoutType').value = data[0];
    $('#newlayout').insertBefore($('#rfelement' + id));
    $('#rfelement' + id).hide();
    $('#newquestion').hide();
    changeLayoutDialog(data[0], true);
    switch (data[0]) {
        case "7":
            document.getElementById('layout_description').value = decodeURIComponent(data[1]);
            break;
        case "8":
            document.getElementById('layout_headline').value = decodeURIComponent(data[1]);
            break;
        case "9":
            break;
        case "10":
            document.getElementById('text-JL').value = decodeURIComponent(data[1]);
            document.getElementById('to-JL').value = decodeURIComponent(data[2]);
            break;
    }
    $('#newlayout').show();
}

/*
 * function to open the edit question dialog for question id
 */
function editQuestion(id) {
    var data = document.getElementsByName('rfelement' + id)[0].value;
    data = data.split(',');
    resetCreateDialog();
    resetLayoutDialog();
    if (document.getElementById('editID').value != '-1') {
        $('#rfelement' + document.getElementById('editID').value).show();
        document.getElementById('editID').value = '-1';
    }
    //document.getElementById('newquestion_button').style.display = '';
    document.getElementById('editID').value = id;
    $('#newlayout').hide();
    $('#newquestion').insertBefore($('#rfelement' + id));
    $('#rfelement' + id).hide();
    changeQuestionDialog(data[0], true);
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
            var options = document.getElementsByName('rfelement' + id + '_options')[0].value;
            options = options.split(',');
            for (var i = 0; i < options.length; i++) {
                if (i != 0) {
                    createSingleChoiceOption();
                }
                document.getElementsByName('singlechoice_' + i)[0].value = decodeURIComponent(options[i]);
            }
            document.getElementById('singleChoiceColumns').value = data[4];
            $('#newquestion').show();
            break;
        case "3":
            var options = document.getElementsByName('rfelement' + id + '_options')[0].value;
            options = options.split(',');
            for (var i = 0; i < options.length; i++) {
                if (i != 0) {
                    createMultipleChoiceOption();
                }
                document.getElementsByName('multiplechoice_' + i)[0].value = decodeURIComponent(options[i]);
            }
            document.getElementById('multipleChoiceColumns').value = data[4];
            $('#newquestion').show();
            break;
        case "4":
            var columns = document.getElementsByName('rfelement' + id + '_columns')[0].value;
            columns = columns.split(',');
            for (var i = 0; i < columns.length; i++) {
                if (i != 0) {
                    createMatrixColumnOption();
                }
                document.getElementsByName('matrix_column_' + i)[0].value = decodeURIComponent(columns[i]);
            }

            var rows = document.getElementsByName('rfelement' + id + '_rows')[0].value;
            rows = rows.split(',');
            for (var i = 0; i < rows.length; i++) {
                if (i != 0) {
                    createMatrixRowOption();
                }
                document.getElementsByName('matrix_row_' + i)[0].value = decodeURIComponent(rows[i]);
            }
            $('#newquestion').show();
            break;
        case "5":
            var options = document.getElementsByName('rfelement' + id + '_rows')[0].value;
            options = options.split(',');
            for (var i = 0; i < options.length; i++) {
                if (i != 0) {
                    createGradingOption();
                }
                document.getElementsByName('grading_' + i)[0].value = decodeURIComponent(options[i]);
            }
            $('#newquestion').show();
            break;
        case "6":
            var options = document.getElementsByName('rfelement' + id + '_options')[0].value;
            options = options.split(',');
            for (var i = 0; i < options.length; i = i + 2) {
                if (i != 0) {
                    createTendencyOption();
                }
                document.getElementsByName('tendency_input_' + (i / 2) + '_0')[0].value = decodeURIComponent(options[i]);
                document.getElementsByName('tendency_input_' + (i / 2) + '_1')[0].value = decodeURIComponent(options[i + 1]);
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
function copyElement(questionnaireId, id) {
    var data = document.getElementsByName('rfelement' + id)[0].value;
    data = data.split(',');
    switch (data[0]) {
        case "0":
            createTextQuestion(questionnaireId, data, '#rfelement' + id);
            break;
        case "1":
            createTextareaQuestion(questionnaireId, data, '#rfelement' + id);
            break;
        case "2":
            var options = document.getElementsByName('rfelement' + id + '_options')[0].value;
            options = options.split(',');
            createSingleChoiceQuestion(questionnaireId, data, options, '#rfelement' + id);
            break;
        case "3":
            var options = document.getElementsByName('rfelement' + id + '_options')[0].value;
            options = options.split(',');
            createMultipleChoiceQuestion(questionnaireId, data, options, '#rfelement' + id);
            break;
        case "4":
            var columns = document.getElementsByName('rfelement' + id + '_columns')[0].value;
            columns = columns.split(',');
            var rows = document.getElementsByName('rfelement' + id + '_rows')[0].value;
            rows = rows.split(',');
            createMatrixQuestion(questionnaireId, data, columns, rows, '#rfelement' + id);
            break;
        case "5":
            var options = document.getElementsByName('rfelement' + id + '_rows')[0].value;
            options = options.split(',');
            createGradingQuestion(questionnaireId, data, options, '#rfelement' + id);
            break;
        case "6":
            var options = document.getElementsByName('rfelement' + id + '_options')[0].value;
            options = options.split(',');
            createTendencyQuestion(questionnaireId, data, options, '#rfelement' + id);
            break;
        case "7":
            createDescriptionLayoutElement(questionnaireId, data[1], '#rfelement' + id);
            break;
        case "8":
            createHeadlineLayoutElement(questionnaireId, data[1], '#rfelement' + id);
            break;
        case "9":
            createPageBreakLayoutElement(questionnaireId, '#rfelement' + id);
            break;
    }
    setTimeout(function(){$('#save-que-button').click();},750);
}

/*
 * function to add a layout element
 */
function addLayoutElement(questionnaireId) {

    if (document.getElementById('editID').value != '-1') {
        var deleteid = document.getElementById('editID').value;
        $('#rfelement' + deleteid).remove();
        $('input[name=rfelement' + deleteid + ']').remove();
        $('input[name=rfelement' + deleteid + '_options]').remove();
        $('input[name=rfelement' + deleteid + '_rows]').remove();
        $('input[name=rfelement' + deleteid + '_columns]').remove();
        document.getElementById('editID').value = '-1';
    }
    var type = document.getElementById('layoutType').value;
    switch (type) {
        case "7":
            var description = document.getElementById('layout_description').value;
            createDescriptionLayoutElement(questionnaireId, description, '#newlayout');
            hideLayoutDialog();
            break;
        case "8":
            var headline = document.getElementById('layout_headline').value;
            createHeadlineLayoutElement(questionnaireId, headline, '#newlayout');
            hideLayoutDialog();
            break;
        case "9":
            createPageBreakLayoutElement(questionnaireId, '#newlayout');
            hideLayoutDialog();
            break;
        case "10":
            var text = $('#text-JL').val();
            var to = $('#to-JL').val();
            createJumpLabelLayoutElement(questionnaireId, text, to, '#newlayout');
            hideLayoutDialog();
            break;

    }

    setTimeout(function() {
        $('#save-que-button').click();
    }, 750);

}

function createJumpLabelLayoutElement(questionnaireId, text, to, insertPoint) {
    params = {};
    params.layoutType = "10";
    params.text = text;
    params.to = to;
    params.layoutID = elementCounter;
    params.id = questionnaireId;
    var successFunction = function(response) {
        responseData = jQuery.parseJSON(response);
        $(responseData.html).insertBefore($(insertPoint));
    }
    var response = sendRequest('AddLayoutElement', params, 0, 'data', '', successFunction);
    elementCounter++;
}

function createDescriptionLayoutElement(questionnaireId, description, insertPoint) {
    params = {};
    params.layoutType = "7";
    params.description = description;
    params.layoutID = elementCounter;
    params.id = questionnaireId;
    var successFunction = function(response) {
        responseData = jQuery.parseJSON(response);
        $(responseData.html).insertBefore($(insertPoint));
    }
    var response = sendRequest('AddLayoutElement', params, 0, 'data', '', successFunction);
    elementCounter++;
}

function createHeadlineLayoutElement(questionnaireId, headline, insertPoint) {
    params = {};
    params.layoutType = "8";
    params.headline = headline;
    params.layoutID = elementCounter;
    params.id = questionnaireId;
    var successFunction = function(response) {
        responseData = jQuery.parseJSON(response);
        $(responseData.html).insertBefore($(insertPoint));
    }
    var response = sendRequest('AddLayoutElement', params, 0, 'data', '', successFunction);
    elementCounter++;
}

function createPageBreakLayoutElement(questionnaireId, insertPoint) {
    params = {};
    params.layoutType = "9";
    params.layoutID = elementCounter;
    params.id = questionnaireId;
    var successFunction = function(response) {
        responseData = jQuery.parseJSON(response);
        $(responseData.html).insertBefore($(insertPoint));
    }
    var response = sendRequest('AddLayoutElement', params, 0, 'data', '', successFunction);
    elementCounter++;
}

/*
 * function to add a new question
 */
function addQuestion(questionnaireId) {
    var question = encodeURIComponent(document.getElementById('questionText').value);
    if (jQuery.trim(question) == '') {
        alert("Bitte geben Sie einen Fragetext ein.");
        return;
    }
    var type = document.getElementById('questionType').value;
    if (document.getElementById('editID').value != '-1') {
        var deleteid = document.getElementById('editID').value;
        $('#rfelement' + deleteid).remove();
        $('input[name=rfelement' + deleteid + ']').remove();
        $('input[name=rfelement' + deleteid + '_options]').remove();
        $('input[name=rfelement' + deleteid + '_rows]').remove();
        $('input[name=rfelement' + deleteid + '_columns]').remove();
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
            createTextQuestion(questionnaireId, data, '#newquestion');
            hideCreateDialog();
            break;
        case "1":
            var rows = document.getElementById('textareaRows').value;
            var data = new Array(type, question, helpText, required, rows);
            createTextareaQuestion(questionnaireId, data, '#newquestion');
            hideCreateDialog();
            break;
        case "2":
            var options = new Array();
            var arrangement = document.getElementById('singleChoiceColumns').value;
            for (var i = 0; i <= singleChoiceCounter; i++) {
                if (document.getElementsByName('singlechoice_' + i)[0] != undefined) {
                    options.push(encodeURIComponent(document.getElementsByName('singlechoice_' + i)[0].value));
                }
            }
            var data = new Array(type, question, helpText, required, arrangement);
            createSingleChoiceQuestion(questionnaireId, data, options, '#newquestion');
            hideCreateDialog();
            break;
        case "3":
            var options = new Array();
            var arrangement = document.getElementById('multipleChoiceColumns').value;
            for (var i = 0; i <= multipleChoiceCounter; i++) {
                if (document.getElementsByName('multiplechoice_' + i)[0] != undefined) {
                    options.push(encodeURIComponent(document.getElementsByName('multiplechoice_' + i)[0].value));
                }
            }
            var data = new Array(type, question, helpText, required, arrangement);
            createMultipleChoiceQuestion(questionnaireId, data, options, '#newquestion');
            hideCreateDialog();
            break;
        case "4":
            var columns = new Array();
            for (var i = 0; i <= matrixColumnCounter; i++) {
                if (document.getElementsByName('matrix_column_' + i)[0] != undefined) {
                    columns.push(encodeURIComponent(document.getElementsByName('matrix_column_' + i)[0].value));
                }
            }
            var rows = new Array();
            for (var i = 0; i <= matrixRowCounter; i++) {
                if (document.getElementsByName('matrix_row_' + i)[0] != undefined) {
                    rows.push(encodeURIComponent(document.getElementsByName('matrix_row_' + i)[0].value));
                }
            }
            var data = new Array(type, question, helpText, required);
            createMatrixQuestion(questionnaireId, data, columns, rows, '#newquestion');
            hideCreateDialog();
            break;
        case "5":
            var options = new Array();
            for (var i = 0; i <= gradingCounter; i++) {
                if (document.getElementsByName('grading_' + i)[0] != undefined) {
                    options.push(encodeURIComponent(document.getElementsByName('grading_' + i)[0].value));
                }
            }
            var data = new Array(type, question, helpText, required);
            createGradingQuestion(questionnaireId, data, options, '#newquestion');
            hideCreateDialog();
            break;
        case "6":
            var options = new Array();
            var steps = document.getElementById('tendency_steps').value;
            for (var i = 0; i <= tendencyCounter; i++) {
                if (document.getElementById('tendency_' + i) != undefined) {
                    options.push(encodeURIComponent(document.getElementsByName('tendency_input_' + i + '_0')[0].value));
                    options.push(encodeURIComponent(document.getElementsByName('tendency_input_' + i + '_1')[0].value));
                }
            }
            var data = new Array(type, question, helpText, required, steps);
            createTendencyQuestion(questionnaireId, data, options, '#newquestion');
            hideCreateDialog();
    }

    setTimeout(function() {
        $('#save-que-button').click();
    }, 750);

}

/*
 * function to create textquestion
 */
function createTextQuestion(questionnaireId, data, insertPoint) {
    params = {};
    params.id = questionnaireId;
    params.questionType = data[0];
    params.questionText = data[1];
    params.questionHelp = data[2];
    params.questionRequired = data[3];
    params.questionInputLength = data[4];
    params.questionRows = data[4];
    params.questionID = elementCounter;
    var successFunction = function(response) {
        responseData = jQuery.parseJSON(response);
        $(responseData.html).insertBefore($(insertPoint));
    }
    var response = sendRequest('AddQuestion', params, 0, 'data', '', successFunction);
    elementCounter++;
}

/*
 * function to create textareaquestion
 */
function createTextareaQuestion(questionnaireId, data, insertPoint) {
    createTextQuestion(questionnaireId, data, insertPoint);
}

/*
 * function to create singlechoice question
 */
function createSingleChoiceQuestion(questionnaireId, data, options, insertPoint) {
    params = {};
    params.id = questionnaireId;
    params.questionType = data[0];
    params.questionText = data[1];
    params.questionHelp = data[2];
    params.questionRequired = data[3];
    params.questionID = elementCounter;
    params.questionArrangement = data[4];
    params.options = options;
    var successFunction = function(response) {
        responseData = jQuery.parseJSON(response);
        $(responseData.html).insertBefore($(insertPoint));
    }
    var response = sendRequest('AddQuestion', params, 0, 'data', '', successFunction);
    elementCounter++;
}

/*
 * function to create multiple choice question
 */
function createMultipleChoiceQuestion(questionnaireId, data, options, insertPoint) {
    createSingleChoiceQuestion(questionnaireId, data, options, insertPoint);
}

/*
 * function to create matrix question
 */
function createMatrixQuestion(questionnaireId, data, columns, rows, insertPoint) {
    params = {};
    params.id = questionnaireId;
    params.questionType = data[0];
    params.questionText = data[1];
    params.questionHelp = data[2];
    params.questionRequired = data[3];
    params.questionID = elementCounter;
    params.columns = columns;
    params.rows = rows;
    var successFunction = function(response) {
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
function createGradingQuestion(questionnaireId, data, rows, insertPoint) {
    var columns = new Array('sehr gut', 'gut', 'befriedigend', 'ausreichend', 'mangelhaft', 'ungenügend');
    createMatrixQuestion(questionnaireId, data, columns, rows, insertPoint);
}

/*
 * function to create tendency question
 */
function createTendencyQuestion(questionnaireId, data, options, insertPoint) {
    createSingleChoiceQuestion(questionnaireId, data, options, insertPoint);
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
        var check = confirm('Umfrage "' + name + '" wirklich löschen?');
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
    } else
        return true;
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
