var sharejs_doc;
var PING_INTERVAL = 5; //in seconds
var connection;

/**
 * get the value of a cookie
 * @param {String} name the name of the cookie
 */
function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}


$(function() {

    //create the dialog which will be shown on disconnect
    var dialog_options = {
        modal: true,
        autoOpen: false,
        title: spreadsheetTexts.misc.disconnected,
        buttons: {}
    };
    dialog_options.buttons[spreadsheetTexts.misc.reload] = function() {
        location.reload();
    };
    $("#disconnectedDialog p").text(spreadsheetTexts.misc.disconnectMessage);
    $("#disconnectedDialog").dialog(dialog_options);

    //open the shareJS document and initiate the jQuery.sheet
    connection = new sharejs.Connection('http://'+rtServer+'/channel', readCookie("koala3-1"));
    connection.open(docID, 'json', function(error, doc) {
        sharejs_doc = doc;

        //Setup Ping to the RT-server to indicate that the connection is still alive
        setInterval(function () {
            var op = {p:['users', userName, 'connected'], oi:true};
            sharejs_doc.submitOp(op);
        }, PING_INTERVAL * 1000);

        //initialize the sheet contents
        initSpreadsheet(sharejs_doc);
        
        //register callback for remote changes on the document
        doc.on('remoteop', function(op) {
            for (var i=0; i<op.length; i++) {
                onDocChanged(op[i]);
            }
        });
    });

    //show a dialog when the connection to the RT-Server is lost
    connection.on("error", function(e) {
        $("#disconnectedDialog").dialog('open');
    });

    //register callbacks for events from jQuery.sheet
    $('.jQuerySheet').live("beforeCellEdit", beforeCellEditEvent);
    $('.jQuerySheet').live("afterCellEdit", afterCellEditEvent);
    $('.jQuerySheet').live("cellEditAbandon", cellEditAbandonEvent);
    $('.jQuerySheet').live("addRow", addRowEvent);
    $('.jQuerySheet').live("deleteRow", deleteRowEvent);
    $('.jQuerySheet').live("addColumn", addColumnEvent);
    $('.jQuerySheet').live("deleteColumn", deleteColumnEvent);
    $('.jQuerySheet').live("resizeColumn", resizeColumnEvent);
    $('.jQuerySheet').live("resizeRow", resizeRowEvent);
    $('.jQuerySheet').live("addSheet", addSheetEvent);
    $('.jQuerySheet').live("deleteSheet", deleteSheetEvent);
    $('.jQuerySheet').live("selectionChanged", selectionChangedEvent);
    $('.jQuerySheet').live("formulaRefreshed", formulaRefreshedEvent);

    $('.jQuerySheet').live("sheetOpened", function(event, a, b, c) {
        var functionList = [];
        var paramList = [];

        //set the localized texts in toolbar and jQuery.sheet
        for (var item in spreadsheetTexts.toolbar) {
            $('#toolbar_'+item).attr('title', spreadsheetTexts.toolbar[item]);
            $('#toolbar_'+item+' img').attr('alt', spreadsheetTexts.toolbar[item]);
        }
        $('#toolbar_export').text(spreadsheetTexts.toolbar['export']);

        //attach the user list and show it
        $('.jQuerySheet').append($("#userDialog"));
        $("#userDialog").show();

        //insert the formula button
        $('<td></td>').append($('#formula_button'))
            .css({'width': '16px', 'padding':'2px'})
            .insertBefore('.jSheetControls_formulaParent');

        for(var fn in spreadsheetTexts.functions) {
            fn_object = spreadsheetTexts.functions[fn];
            fn_string = '='+fn_object.name+'(';
            paramList = [];

            //add localized function to jQuery.sheet
            jQuery.sheet.fn[fn_object.name] = jQuery.sheet.fn[fn];

            //build a function description and add it to autocomplete list
            for(var param in fn_object.params) {
                var param_object = fn_object.params[param];
                
                if (param_object.optional) {
                    paramList.push('['+param_object.name+']');
                }
                else {
                    paramList.push(param_object.name);
                }
            }

            fn_string = fn_string + paramList.join(', ') + ')';
            functionList.push(fn_string);
        }

        functionList.sort();

        autoComplete = $('.jSheetControls_formula').autocomplete({
            source: functionList
        });

        $("#formula_button").click(function() {
            //show or hide the autocomlpete list
            if ($('ul.ui-autocomplete').is(':visible')) {
                $('.jSheetControls_formula').autocomplete('close');
            }
            else {
                $('.jSheetControls_formula').autocomplete('option', 'minLength', 0 );
                $('.jSheetControls_formula').autocomplete('search');
                $('.jSheetControls_formula').autocomplete('option', 'minLength', 1 );
            }
        });
    });
});

/**
 * initialize the sheet contents, register callbacks for doc changes
 *
 * @param{Object} doc the ShareJS document for the spreadsheet
 */
function initSpreadsheet(doc) {
    var sheet_count, 
        row_count, 
        col_count,
        sheets = doc.at('sheets'),
        rows = doc.at('sheets', 0, 'rows'),
        cols = doc.at('sheets', 0, 'columns'),
        users = doc.at('users'),
        cells,
        row,
        col,
        color,
        style,
        value;
        
    
    sheet_count = sheets.get().length;
    sheet_count = sheet_count ? sheet_count : 1;
    
    row_count = rows.get().length;
    row_count = row_count ? row_count : 15;
    
    col_count = cols.get().length;
    col_count = col_count ? col_count : 5;
    
    // Initiate jQuery.sheet
    $('#jQuerySheet').sheet({
        title : docTitle,
        buildSheet: col_count+'x'+row_count,
        editable : sheetEditable,
        resizable: sheetEditable,
        inlineMenu: $('#inlineMenu').html(),
        barMenus: false,
        menu: false,
        minSize: {rows: 3, cols: 3},
        numberFormatDE: true
    });

    //Hide the tabs for multiple sheets (multiple sheets are not supported yet)
    $('.jSheetTabContainer').hide();

    //set localized texts for jQuery.sheet
    $.sheet.instance[0].msg = spreadsheetTexts.msg;

    //Init the color-pickers
    $('.colorPickerCell').colorPicker().change(function(){
        cellSetStyle('background-color', $(this).val());
    });
    $('.colorPickerFont').colorPicker().change(function(){
        cellSetStyle('color', $(this).val());
    });

    //column sizes
    for (var col=0; col<col_count; col++) {
        var col_info = doc.at('sheets', 0, 'columns', col);
        $.sheet.instance[0].setColumnSize(col, col_info.get().size);
    }
    $("#jSheet_0_0").width(0); //Fixes a bug wich may cause incorrect column widths
    
    //put doc contents into the sheet
    sheet = 0;
    rows = doc.at('sheets', sheet, 'rows');
    row_count = rows.get().length;
    row_count = row_count ? row_count : 15;

    for (row=0; row<row_count; row++) {
        cells = doc.at('sheets', sheet, 'rows', row, 'cells');
        col_count = cells.get().length;
        col_count = col_count ? col_count : 5;
        var size = doc.at('sheets', sheet, 'rows', row, 'size');
        $.sheet.instance[0].setRowSize(row, size.get());

        for (col=0; col<col_count; col++) {
            var cell = doc.at('sheets', sheet, 'rows', row, 'cells', col);

            //set value and formatting of the cell
            value = cell.get().value;
            if (value.charAt(0) == '=') {
                //Translate funtion names into localized versions
                for (var fn in spreadsheetTexts.functions) {
                    if (spreadsheetTexts.functions.hasOwnProperty(fn)) {
                        value = value.replace(new RegExp(fn+'\\\('  ,'g'), spreadsheetTexts.functions[fn].name+'(');
                    }
                }
            }
            $.sheet.instance[0].setCellValue(row, col, sheet, value);
            
            for (style in cell.get().style) {

                if (style == 'font-size') {
                    var font_size = cell.get().style[style];
                    $.sheet.instance[0].setFontSize(sheet, row, col, font_size);
                }
                else if (style == 'background-color') {
                    color = cell.get().style[style];
                    $.sheet.instance[0].cellSetStyle(sheet, row, col, style, color);
                }
                else if (style == 'color') {
                    color = cell.get().style[style];
                    $.sheet.instance[0].cellSetStyle(sheet, row, col,style, color);
                }
                else if (style == 'flags') {
                    //style-flags
                    for (var flag in cell.get().style.flags) {
                        $.sheet.instance[0].addCellStyle(row, col, sheet, flag);
                    }
                }
            }

            if (cell.get().lockedBy) {
                //release lock if it is an old lock from the same user
                if (cell.get().lockedBy == userName) {
                    op = {p:['sheets', sheet, 'rows', row, 'cells', col, 'lockedBy'], od:userName};
                    sharejs_doc.submitOp(op);
                }
                else {
                    $.sheet.instance[0].lockCell(row, col, sheet);
                }
            }
        }
    }

    $.sheet.instance[0].sheetSyncSize();
    
    //mark the selection of other users
    for (var user in users.get()) {
        if (user && (user != userName)) {
            userColor = col = doc.at('users', user, 'color').get();
            row = doc.at('users', user, 'selection', 'row').get();
            col = doc.at('users', user, 'selection', 'col').get();
            if ((col != void 0) && (row != void 0) && (userColor != void 0)) {
                $.sheet.instance[0].setCellSelection(row, col, 0, userColor);
            }
        }
    }

    updateUserList();

    //select the last selected cell or A1 at the beginning
    if (sheetEditable) {
        setTimeout(function() {
            var selection = sharejs_doc.at('users', userName, 'selection').get(),
                row = 0,
                col = 0;
            if (selection) {
                row = selection.row;
                col = selection.col;
            }
            $.sheet.instance[0].cellEdit(jQuery($.sheet.instance[0].getTd(0, row, col)), 0);
        });
    }
}

/**
 * Gets called before the user starts editing a cell
 *
 * @param {Object} event jQuery event
 * @param {Object} source jQuery sheet which emits the event
 * @param {Object} data stores row, column, and content of the cell
 */
function beforeCellEditEvent(event, source, data) {
    var sheet = $.sheet.instance[0].i,
        cell = sharejs_doc.at('sheets', sheet, 'rows', data.row, 'cells', data.col),
        op;
    if (sheet >= 0 && data.row >=0 && data.col >=0) {
        if(!cell.get().lockedBy) {
            op = {p:['sheets', sheet, 'rows', data.row, 'cells', data.col, 'lockedBy'], oi:userName};
            sharejs_doc.submitOp(op);
        }
    }
}

/**
 * Gets called after a cell was edited by the user
 *
 * @param {Object} event jQuery event
 * @param {Object} source jQuery sheet which emits the event
 * @param {Object } data stores row, column, and content of the cell
 */
function afterCellEditEvent(event, source, data) {
    var sheet = $.sheet.instance[0].i,
        cell = sharejs_doc.at('sheets', sheet, 'rows', data.row, 'cells', data.col),
        user,
        old,
        value,
        op;

    if (sheet >= 0 && data.row >=0 && data.col >=0) {
        user = cell.get().lockedBy;
        old = cell.get().value;

        if(!old) {
            old = '';
        }
        value = $.sheet.instance[0].getCellValueOrFormula(data.row, data.col, sheet);
        if (value.charAt(0) == '=') {
            //Translate localized funtion names into original (English) versions
            for (var fn in spreadsheetTexts.functions) {
                if (spreadsheetTexts.functions.hasOwnProperty(fn)) {
                    value = value.replace(new RegExp(spreadsheetTexts.functions[fn].name+'\\\('  ,'g'), fn+'(');
                }
            }
        }
        op = {p:['sheets', sheet, 'rows', data.row, 'cells', data.col, 'value'], od:old, oi:value};
        sharejs_doc.submitOp(op);
        
        if(user) {
            //release lock
            op = {p:['sheets', sheet, 'rows', data.row, 'cells', data.col, 'lockedBy'], od:user};
            sharejs_doc.submitOp(op);
        }
    }
}

/**
 * Gets called after the user cancels a cell editing 
 *
 * @param {Object} event jQuery event
 * @param {Object} source jQuery sheet which emits the event
 * @param {Object} data stores row, column, and content of the cell
 */
function cellEditAbandonEvent(event, source, data) {
    if (data.col < 0 || data.row < 0) return;
    var sheet = $.sheet.instance[0].i,
        cell = sharejs_doc.at('sheets', sheet, 'rows', data.row, 'cells', data.col),
        user = cell.get().lockedBy,
        old = cell.get().value,
        value,
        op;
    
    if(!old) {
        old = '';
    }
    value = $.sheet.instance[0].getCellValueOrFormula(data.row, data.col, sheet);
    op = {p:['sheets', sheet, 'rows', data.row, 'cells', data.col, 'value'], od:old, oi:value};
    sharejs_doc.submitOp(op);
    
    if(cell.get().lockedBy) {
        //release lock
        op = {p:['sheets', sheet, 'rows', data.row, 'cells', data.col, 'lockedBy'], od:user};
        sharejs_doc.submitOp(op);
    }
}

/**
 * Gets called after a sheet was inserted by the user
 * 
 * @param {Object} event jQuery event
 * @param {Object} source jQuery sheet which emits the event
 * @param {number|string} [pos Position where the sheet has been inserted]
 */
function addSheetEvent(event, source, pos) {
    //not used yet
}
/**
 * Gets called after a sheet was deleted by the user
 * 
 * @param {Object} event jQuery event
 * @param {Object} source jQuery sheet which emits the event
 * @param {number|string} [pos Position where the sheet has been deleted]
 */
function deleteSheetEvent(event, source, pos) {
    //not used yet
}

/**
 * Gets called after jQuery.sheet upddates a formula (e.g. because a row affecting the fomula has been deleted)
 * 
 * @param {Object} event jQuery event
 * @param {Object} source jQuery sheet which emits the event
 * @param {Number} row Position of the formula
 * @param {Number} col Position of the formula
 * @param {String} formula the new value of the formula
 */
function formulaRefreshedEvent(event, source, row, col, formula) {
    var sheet = $.sheet.instance[0].i,
        cell = sharejs_doc.at('sheets', sheet, 'rows', row, 'cells', col),
        old = cell.get().value,
        op;

        old = old ? old : '';
        if (old !== formula) {
            op = {p:['sheets', sheet, 'rows', row, 'cells', col, 'value'], od:old, oi:formula};
            sharejs_doc.submitOp(op);
        }
}

/**
 * Gets called after a row was inserted by the user
 * 
 * @param {Object} event jQuery event
 * @param {Object} source jQuery sheet which emits the event
 * @param {number|string} [pos Position where the row has been inserted]
 * @param {boolean} [isBefore] true, iff the new row was iserted before the row at pos 
 */
function addRowEvent(event, source, pos, isBefore, rowCount) {
    var sheet = $.sheet.instance[0].i,
        row = pos ? pos : $.sheet.instance[0].rowLast,
        colCount = $.sheet.instance[0].sheetSize().width+1,
        //TODO: testen, ob die spalten anzahl auch mit merged cells richtig ist
        value = [],
        op;
    
    if (isBefore) {
        row--;
    }
    if (isNaN(row)) {
        row = $.sheet.instance[0].sheetSize().height-1;
    }
    row++;
    
    for (col=0; col<colCount; col++) {
        value.push({value: "", style: {flags: {styleLeft:true}}});
    }
    
    //insert new cells
    op = {p:['sheets', sheet, 'rows', row], li:{'cells': value, size: 20}};
    sharejs_doc.submitOp(op);
}

/**
 * Gets called after a row was deleted by the user
 * 
 * @param {Object} event jQuery event
 * @param {Object} source jQuery sheet which emits the event
 * @param {number|string} [pos Position where the row has been deleted]
 */
function deleteRowEvent(event, source, pos, isBefore, rowCount) {
    var sheet = $.sheet.instance[0].i,
        row = pos ? pos : $.sheet.instance[0].rowLast,
        colCount = $.sheet.instance[0].sheetSize().width+1,
        //TODO: testen, ob die spalten anzahl auch mit merged cells richtig ist
        value,
        op;
    
    if (isBefore) {
        row--;
    }                
    if (isNaN(row)) {
        row = $.sheet.instance[0].sheetSize().height-1;
    }
    if (row < 0) {
        row = 0;
    }
    
    value = sharejs_doc.at('sheets', sheet, 'rows', row).get();
    op = {p:['sheets', sheet, 'rows', row], ld:value};
    sharejs_doc.submitOp(op);
}

/**
 * Gets called after a column was inserted by the user
 * 
 * @param {Object} event jQuery event
 * @param {Object} source jQuery sheet which emits the event
 * @param {number|string} [pos Position where the column has been inserted]
 * @param {boolean} [isBefore] true, iff the new column was iserted before the column at pos 
 */
function addColumnEvent(event, source, pos, isBefore, number) {
    var sheet = $.sheet.instance[0].i,
        col = pos ? pos : $.sheet.instance[0].colLast,
        rowCount = $.sheet.instance[0].sheetSize().height+1,
        value = {value: "", style: {flags: {styleLeft:true}}},
        op;
    
    if (isBefore) {
        col--;
    }
    if (isNaN(col)) {
        col = $.sheet.instance[0].sheetSize().width-1;
    }
    col++;
    
    //add column
    op = {p:['sheets', sheet, 'columns', col], li:{size:120}};
    sharejs_doc.submitOp(op);
    //add cells into new column
    for (var row=0; row<rowCount; row++) {
        op = {p:['sheets', sheet, 'rows', row, 'cells', col], li:value};
        sharejs_doc.submitOp(op);
    }
}

/**
 * Gets called after a column was deleted by the user
 * 
 * @param {Object} event jQuery event
 * @param {Object} source jQuery sheet which emits the event
 * @param {number|string} [pos Position where the column has been deleted]
 */
function deleteColumnEvent(event, source, pos, isBefore, number) {
    var sheet = $.sheet.instance[0].i,
        col = pos ? pos : $.sheet.instance[0].colLast,
        rowCount = $.sheet.instance[0].sheetSize().height+1,
        value,
        op;
    
    if (isBefore) {
        col--;
    }
    if (isNaN(col)) {
        col = $.sheet.instance[0].sheetSize().width;
    }
    if (col < 0) {
        col = 0;
    }
    
    //remove column
    op = {p:['sheets', sheet, 'columns', col], ld:{size:120}};
    sharejs_doc.submitOp(op);
    //remove cells in column
    for (var row=0; row<rowCount; row++) {
        value = sharejs_doc.at('sheets', sheet, 'rows', row, 'cells', col).get();
        op = {p:['sheets', sheet, 'rows', row, 'cells', col], ld:value};
        sharejs_doc.submitOp(op);
    }
}

/**
 * Gets called after a column was resized by the user
 * 
 * @param {Object} event jQuery event
 * @param {Object} source jQuery sheet which emits the event
 * @param {number} col Position of the resized column
 * @param {number} size New size of the column
 */
function resizeColumnEvent(event, source, col, size) {
    var sheet = $.sheet.instance[0].i;
    
    op = {p:['sheets', sheet, 'columns', col, 'size'], oi:size , od:size};
    sharejs_doc.submitOp(op);
}

 /**
 * Gets called after a row was resized by the user
 * 
 * @param {Object} event jQuery event
 * @param {Object} source jQuery sheet which emits the event
 * @param {number} row Position of the resized column
 * @param {number} size New size of the column
 */
function resizeRowEvent(event, source, row, size) {
    var sheet = $.sheet.instance[0].i;
    
    op = {p:['sheets', sheet, 'rows', row, 'size'], oi:size , od:size};
    sharejs_doc.submitOp(op);
}

/**
 * Gets called after a cell was selected by the user
 * 
 * @param {Object} event jQuery event
 * @param {Object} source jQuery sheet which emits the event
 * @param {number} row Row of the selected cell
 * @param {number} row Column of the selected cell
 */
function selectionChangedEvent(event, source, row, col) {
    var sheet = $.sheet.instance[0].i,
        oldSelection = sharejs_doc.at('users', userName, 'selection').get(),
        selection = {'row':row, 'col':col, 'sheet':sheet};
    if ((oldSelection.row == selection.row) && (oldSelection.row == selection.row) && (oldSelection.row == selection.row)) {
        //same cell selected as before
    }
    op = {p:['users', userName, 'selection'], oi:selection , od:oldSelection};
    sharejs_doc.submitOp(op);
}

/**
 * In-/decreases the font size for the selected cells in the sheet 
 * and commits the change operation
 * 
 * @param {string} direction 'up' or 'down'
 */
 function resizeCellFont(direction) {
     var uiCell = $.sheet.instance[0].obj.cellHighlighted(),
         sheet = $.sheet.instance[0].i;
     
     //set the font-size on jQuery.Sheet
    $.sheet.instance[0].fontReSize(direction);
    
    uiCell.each(function(i) {
        var cell = jQuery(this),
            loc = $.sheet.instance[0].getTdLocation(cell),
            curr_size = (cell.css("font-size") + '').replace("px",""),
            op;
            
        op = {p:['sheets', sheet, 'rows', loc.row, 'cells', loc.col, 'style', 'font-size'], od:"", oi:curr_size};
        sharejs_doc.submitOp(op);
    });
 }

/**
 * toggles a style-flag (e.g. styleBold, styleCenter) for the selected cells in the sheet and commits
 * the change operation. The style-flags are CSS-Classes which are defined in jquery.sheet.css
 *
 * @param {string} style style-flag that will be applied to the cell
 * @param {string} [removeStyle] style-flag that will be removed from the cell
 */
function toggleCellStyle(style, removeStyle) {
    var cellRange = $.sheet.instance[0].highlightedLast;
    var tmp;
    var sheet = $.sheet.instance[0].i;
    var op, path;
    var flagsObject;
    
    //Get the start and end of the selection
    var rowStart = cellRange.rowStart == -1 ? cellRange.rowLast : cellRange.rowStart;
    var rowEnd = cellRange.rowEnd == -1 ? cellRange.rowLast : cellRange.rowEnd;
    var colStart = cellRange.colStart == -1 ? cellRange.colLast : cellRange.colStart;
    var colEnd = cellRange.colEnd == -1 ? cellRange.colLast : cellRange.colEnd;
    
    if (rowStart > rowEnd) {
        tmp = rowEnd;
        rowEnd = rowStart;
        rowStart = tmp;
    }                
    if (colStart > colEnd) {
        tmp = colEnd;
        colEnd = colStart;
        colStart = tmp;
    }
    
    //toggle the style on jQuery.Sheet
    $.sheet.instance[0].cellStyleToggle(style, removeStyle);
                    
    for (row=rowStart; row<=rowEnd; row++) {
        for (col=colStart; col<=colEnd; col++) {
            path = ['sheets', sheet, 'rows', row, 'cells', col, 'style', 'flags'];
            flagsObject = sharejs_doc.at(path).get();
            
            if (style in flagsObject) {
                //remove style
                op = {p:path.concat(style), od:"true"};
            }
            else {
                //add style
                op = {p:path.concat(style), oi:"true"};
            }
            
            sharejs_doc.submitOp(op);
            
            //apply removeStyle to remote clients
            if (removeStyle){
                removeStyle.split(' ').forEach(function(style) {
                    if (style in flagsObject) {
                        op = {p:path.concat(style), od:"true"};
                        sharejs_doc.submitOp(op);
                    }
                });
            }
        }
    }
}

/**
 * sets a style for the selected cells in the sheet and commits
 * the operation (currently only used for colors)
 *
 * @param {string} style HTML style attribute that will be applied to the cell
 * @param {string} value The value of the style attribute
 */
function cellSetStyle(style, value) {
    var cellRange = $.sheet.instance[0].highlightedLast;
    var tmp;
    var sheet = $.sheet.instance[0].i;
    var op, path, oldStyle;
    var styleObject;
    
    //Get the start and end of the selection
    var rowStart = cellRange.rowStart == -1 ? cellRange.rowLast : cellRange.rowStart;
    var rowEnd = cellRange.rowEnd == -1 ? cellRange.rowLast : cellRange.rowEnd;
    var colStart = cellRange.colStart == -1 ? cellRange.colLast : cellRange.colStart;
    var colEnd = cellRange.colEnd == -1 ? cellRange.colLast : cellRange.colEnd;
    
    if (rowStart > rowEnd) {
        tmp = rowEnd;
        rowEnd = rowStart;
        rowStart = tmp;
    }                
    if (colStart > colEnd) {
        tmp = colEnd;
        colEnd = colStart;
        colStart = tmp;
    }
    
    //set the style on jQuery.Sheet
    $.sheet.instance[0].cellChangeStyle(style, value);
                    
    for (row=rowStart; row<=rowEnd; row++) {
        for (col=colStart; col<=colEnd; col++) {
            path = ['sheets', sheet, 'rows', row, 'cells', col, 'style'];
            styleObject = sharejs_doc.at(path).get();
            
            if (style in styleObject) {
                //remove old style
                oldStyle = styleObject[style];
            }

            //add style
            op = {p:path.concat(style), oi:value, od: oldStyle};
            
            sharejs_doc.submitOp(op);
        }
    }
}

/**
 * update the list of active users of the document
 *
 * @param {string} name name of the user
 * @param {string} [color] color of the user
 */
function updateUserList() {
    var users, name, color, item, userCount;

    users = sharejs_doc.snapshot.users;
    userCount = 0;

    //clear the list
    $('#userList').children().remove();

    //diaplay the current user in the first item
    color = '#6089af';
    item = $('<a href="../../user/'+userName+'/"  target="_blank">' + userName + ' (Ich)' + '</a>');
    item.css('color', color);
    $('<li></li>').css('color', color).append(item).appendTo($('#userList'));

    for (name in users) {
        if (name && users.hasOwnProperty(name)) {
            userCount++;

            if (name != userName) {
                item = $('<a href="../../user/'+name+'/"  target="_blank">' + name + '</a>');
                item.css('color', users[name].color);
                $('<li></li>').css('color', users[name].color).append(item).appendTo($('#userList'));
            }

        }
    }

    //update the number of users in the text of the dialog
    if (userCount == 1) {
        $('#userDialog p').text(userCount + spreadsheetTexts.misc.userListTitleSingular);
    }
    else {
        $('#userDialog p').text(userCount + spreadsheetTexts.misc.userListTitle);
    }
}

/**
 * Gets called when another client commits an operation on the 
 * 'sheet' doc
 * 
 * @param {Object} op The operation commited by teh remote client 
 */
function onDocChanged(op, oldSnapshot) {                
    var path = op.p,
        sheet = path[1],
        row = path[3],
        col = path[5],
        style = path[7],
        isBefore = false,
        value, 
        color;

    if (path[0] == 'users') {
        if (path.length == 3) {
            if (path[2] == 'selection') {
                //update selection of other users
                color = sharejs_doc.at('users', path[1], 'color').get();

                if (op.od) {
                    $.sheet.instance[0].removeCellSelection(op.od.row, op.od.col, op.od.sheet);
                }
                if (op.oi) {
                    $.sheet.instance[0].setCellSelection(op.oi.row, op.oi.col, op.oi.sheet, color);
                }
            }
        }
        else if (path.length == 2) {
            if (op.od !== void 0) {
                //user removed
                updateUserList(); 
                if (path[1] == userName) {
                    //current user got diconnected from the server
                    $("#disconnectedDialog").dialog('open');
                }
                else {
                    //remove selection of user
                    $.sheet.instance[0].removeCellSelection(op.od.selection.row, op.od.selection.col, op.od.selection.sheet);
                }
            }
            else if (op.oi !== void 0) {
                //user addeed
                updateUserList();
            }
        }
    }
    else if (path.length == 2) {
        if (op.li !== void 0) {
            //sheet inserted
            //not used
        }
        else if (op.ld !== void 0) {
            //sheet deleted
            //not used
        }
    }
    else if (path[2] == 'columns' ) {
        col = path[3];
        if (path[4] == 'size' && op.oi !== void 0) {
            //column resized
            $.sheet.instance[0].setColumnSize(col, op.oi);
        }
        else if (path.length == 4 && op.li !== void 0) {
            //column inserted
            if (col === 0) {
                col++;
                isBefore = true;
            }
            $.sheet.instance[0].controlFactory.addCells(col-1, isBefore, null, 1, 'col');
        }
        else if (path.length == 4 && op.ld !== void 0) {
            //column deleted
            $.sheet.instance[0].deleteColumnAt(col);
        }
    }
    else if (path[2] == 'rows' ) {
        if (path[4] == 'cells') {
            if (path[6] == 'value' && op.oi !== void 0 && op.od !== void 0) {
                //cell value changed
                value = op.oi;
                if (value.charAt(0) == '=') {
                    //Translate funtion names into localized versions
                    for (var fn in spreadsheetTexts.functions) {
                        if (spreadsheetTexts.functions.hasOwnProperty(fn)) {
                            value = value.replace(new RegExp(fn+'\\\('  ,'g'), spreadsheetTexts.functions[fn].name+'(');
                        }
                    }
                }
                $.sheet.instance[0].setCellValue(row, col, sheet, value);
            }
            else if (path[6] == 'style') {
                if (style == 'font-size') {
                    //font size changed
                    $.sheet.instance[0].setFontSize(sheet, row, col, op.oi);
                }
                if (style == 'background-color') {
                    $.sheet.instance[0].cellSetStyle(sheet, row, col, style, op.oi);
                }
                if (style == 'color') {
                    $.sheet.instance[0].cellSetStyle(sheet, row, col,style, op.oi);
                }
                if (style == 'flags') {
                    style = path[8];
                    if (op.oi !== void 0) {
                        //style added to cell
                        $.sheet.instance[0].addCellStyle(row, col, sheet, style);
                    }
                    if (op.od !== void 0) {
                        //style removed from cell
                        $.sheet.instance[0].removeCellStyle(row, col, sheet, style);
                    }
                }
            }
            else if (path[6] == 'lockedBy' && op.oi !== void 0) {
                $.sheet.instance[0].lockCell(row, col, sheet);
            }
            else if (path[6] == 'lockedBy' && op.od !== void 0) {
                $.sheet.instance[0].releaseCell(row, col, sheet);
            }
        }
        else if (path[4] == 'size') {
            //row resized
            $.sheet.instance[0].setRowSize(row, op.oi);
        }
        else if (path.length == 4 && op.li !== void 0) {
            //row inserted
            if (row === 0) {
                row++;
                isBefore = true;
            }
            $.sheet.instance[0].controlFactory.addCells(row-1, isBefore, null, 1, 'row');
        }
        else if (path.length == 4 && op.ld !== void 0) {
            //row deleted
            $.sheet.instance[0].deleteRowAt(row);
        }
    }
}