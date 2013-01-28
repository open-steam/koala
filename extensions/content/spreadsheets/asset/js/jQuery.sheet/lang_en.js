var spreadsheetTexts = (function() {
    var exports = {};

    exports.msg = { /*msg = messages used throught sheet, for easy access to change them for other languages*/
                    addRowMulti:            "How many rows would you like to add?",
                    addColumnMulti:         "How many columns would you like to add?",
                    newSheet:               "What size would you like to make your spreadsheet? Example: '5x10' creates a sheet that is 5 columns by 10 rows.",
                    openSheet:              "Are you sure you want to open a different sheet?  All unsaved changes will be lost.",
                    cellFind:               "No results found.",
                    cellLocked:             " is locked by user ", 
                    cellLockedEdit:         "Editing this cell is not possible, it is locked by another user", 
                    deleteRowImpossible:    "Deleting this row is not possible, a cell in this row is locked by another user", 
                    deleteColImpossible:    "Deleting this column is not possible, a cell in this column is locked by another user", 
                    toggleHideRow:          "No row selected.",
                    toggleHideColumn:       "Now column selected.",
                    merge:                  "Merging is not allowed on the first row.",
                    evalError:              "Error, functions as formulas not supported.",
                    menuInsertColumnAfter:  "Insert column after",
                    menuInsertColumnBefore: "Insert column before",
                    menuAddColumnEnd:       "Add column to end",
                    menuDeleteColumn:       "Delete this column",
                    menuInsertRowAfter:     "Insert row after",
                    menuInsertRowBefore:    "Insert row before",
                    menuAddRowEnd:          "Add row to end",
                    menuDeleteRow:          "Delete this row",
                    menuAddSheet:           "Add spreadsheet",
                    menuDeleteSheet:        "Delete spreadsheet"
    };

    exports.toolbar = {
        insertRowAfter: "Insert Row After Selected",
        insertRowBefore: "Insert Row Before Selected",
        deleteRow: "Delete Row",
        insertColumnAfter: "Insert Column After Selected",
        insertColumnBefore: "Insert Column Before Selected",
        deleteColumn: "Delete Column",
        find: "Find",
        bold: "Bold",
        italic: "Italic",
        underline: "Underline",
        strikethrough: "Strikethrough",
        alignLeft: "Align Left",
        alignCenter: "Align Center",
        alignRight: "Align Right",
        increaseFontSize: "Increase Font Size",
        decreaseFontSize: "Decrease Font Size",
        hyperLink: "Web Link",
        deleteSpreadsheet: "Delete Spreadsheet",
        foregroundColor: "Foreground Color",
        backgroundColor: "Background Color",
        export: "Export:"
    };

    exports.misc = {
        reload: "Reload",
        disconnected: "Disconnected",
        disconnectMessage: "The connection to the server was lost. Please reload the page if you wish to continue editing this document.",
        userListTitle: " persons working on this Document:",
        userListTitleSingular: " person working on this Document:"
    };

    exports.functions = {
        VERSION: {
            name: 'VERSION',
            params: []
        },
        IMG: {
            name: 'IMG',
            params: [
                {
                    name: 'Image-URL',
                    type: 'Text',
                    optional: false
                }
            ]
        },
        AVERAGE: {
            name: 'AVERAGE',
            params: [
                {
                    name: 'Values',
                    type: 'Cell Range',
                    optional: false
                }
            ]
        },
        COUNT: {
            name: 'COUNT',
            params: [
                {
                    name: 'Values',
                    type: 'Cell Range',
                    optional: false
                }
            ]
        },
        SUM: {
            name: 'SUM',
            params: [
                {
                    name: 'Values',
                    type: 'Cell Range',
                    optional: false
                }
            ]
        },
        MAX: {
            name: 'MAX',
            params: [
                {
                    name: 'Values',
                    type: 'Cell Range',
                    optional: false
                }
            ]
        },
        MIN: {
            name: 'MIN',
            params: [
                {
                    name: 'Values',
                    type: 'Cell Range',
                    optional: false
                }
            ]
        },
        /*MEAN: {
            name: 'MIN',
            params: [
                {
                    name: 'Values',
                    type: 'Cell Range',
                    optional: false
                }
            ]
        },*/
        ABS: {
            name: 'ABS',
            params: [
                {
                    name: 'Number',
                    type: 'Number',
                    optional: false
                }
            ]
        },
        CEILING: {
            name: 'CEILING',
            params: [
                {
                    name: 'Number',
                    type: 'Number',
                    optional: false
                }
            ]
        },
        FLOOR: {
            name: 'FLOOR',
            params: [
                {
                    name: 'Number',
                    type: 'Number',
                    optional: false
                }
            ]
        },
        INT: {
            name: 'INT',
            params: [
                {
                    name: 'Number',
                    type: 'Number',
                    optional: false
                }
            ]
        },
        ROUND: {
            name: 'ROUND',
            params: [
                {
                    name: 'Number',
                    type: 'Number',
                    optional: false
                }
            ]
        },
        RAND: {
            name: 'RAND',
            params: []
        },
        TRUE: {
            name: 'TRUE',
            params: []
        },
        FALSE: {
            name: 'FALSE',
            params: []
        },
        NOW: {
            name: 'NOW',
            params: []
        },
        TODAY: {
            name: 'TODAY',
            params: []
        },
        DAYSFROM: {
            name: 'DAYSFROM',
            params: [
                {
                    name: 'Year',
                    type: 'Number',
                    optional: false
                },
                {
                    name: 'Month',
                    type: 'Number',
                    optional: false
                },
                {
                    name: 'Day',
                    type: 'Number',
                    optional: false
                }
            ]
        },
        DAYS: {
            name: 'DAYS',
            params: [
                {
                    name: 'Date1',
                    type: 'Text',
                    optional: false
                },
                {
                    name: 'Date2',
                    type: 'Text',
                    optional: false
                }
            ]
        },
        DATEVALUE: {
            name: 'DATEVALUE',
            params: [
                {
                    name: 'Date',
                    type: 'Text',
                    optional: false
                }
            ]
        },
        IF: {
            name: 'IF',
            params: [
                {
                    name: 'Condition',
                    type: 'Boolean',
                    optional: false
                },
                {
                    name: 'Result if true',
                    type: 'any',
                    optional: false
                },
                {
                    name: 'Result if false',
                    type: 'any',
                    optional: true
                }
            ]
        },
        FIXED: {
            name: 'FIXED',
            params: [
                {
                    name: 'Number',
                    type: 'Number',
                    optional: false
                },
                {
                    name: 'Decimals',
                    type: 'Number',
                    optional: true
                }
            ]
        },
        TRIM: {
            name: 'TRIM',
            params: [
                {
                    name: 'Text',
                    type: 'Text',
                    optional: false
                }
            ]
        },
        HYPERLINK: {
            name: 'HYPERLINK',
            params: [
                {
                    name: 'URL',
                    type: 'Text',
                    optional: false
                },
                {
                    name: 'Name',
                    type: 'Text',
                    optional: true
                }
            ]
        },
        DOLLAR: {
            name: 'DOLLAR',
            params: [
                {
                    name: 'Value',
                    type: 'Number',
                    optional: false
                },
                {
                    name: 'Decimals',
                    type: 'Number',
                    optional: true
                },
                {
                    name: 'Currency',
                    type: 'Text',
                    optional: true
                }
            ]
        },
        VALUE: {
            name: 'VALUE',
            params: [
                {
                    name: 'Value',
                    type: 'Text',
                    optional: false
                }
            ]
        },
        N: {
            name: 'N',
            params: [
                {
                    name: 'Value',
                    type: 'any',
                    optional: false
                }
            ]
        },
        PI: {
            name: 'PI',
            params: []
        },
        POWER: {
            name: 'POWER',
            params: [
                {
                    name: 'Base',
                    type: 'Number',
                    optional: false
                },
                {
                    name: 'Exponent',
                    type: 'Number',
                    optional: false
                }
            ]
        },
        SQRT: {
            name: 'SQRT',
            params: [
                {
                    name: 'Number',
                    type: 'Number',
                    optional: false
                }
            ]
        },
        DROPDOWN: {
            name: 'DROPDOWN',
            params: [
                {
                    name: 'Values',
                    type: 'Cell Range',
                    optional: false
                },
                {
                    name: 'Not Empty',
                    type: 'Boolean',
                    optional: true
                }
            ]
        },
        RADIO: {
            name: 'RADIO',
            params: [
                {
                    name: 'Values',
                    type: 'Cell Range',
                    optional: false
                }
            ]
        },
        CHECKBOX: {
            name: 'CHECKBOX',
            params: [
                {
                    name: 'Caption',
                    type: 'Text',
                    optional: false
                }
            ]
        },
        BARCHART: {
            name: 'BARCHART',
            params: [
                {
                    name: 'Values',
                    type: 'Cell Range',
                    optional: false
                }
            ]
        },
        HBARCHART: {
            name: 'HBARCHART',
            params: [
                {
                    name: 'Values',
                    type: 'Cell Range',
                    optional: false
                }
            ]
        },
        LINECHART: {
            name: 'LINECHART',
            params: [
                {
                    name: 'ValuesX',
                    type: 'Cell Range',
                    optional: false
                },
                {
                    name: 'ValuesY',
                    type: 'Cell Range',
                    optional: false
                }
            ]
        },
        PIECHART: {
            name: 'PIECHART',
            params: [
                {
                    name: 'Values',
                    type: 'Cell Range',
                    optional: false
                },
                {
                    name: 'Legend',
                    type: 'Text',
                    optional: true
                }
            ]
        },
        DOTCHART: {
            name: 'DOTCHART',
            params: [
                {
                    name: 'ValuesX',
                    type: 'Cell Range',
                    optional: false
                },
                {
                    name: 'ValuesY',
                    type: 'Cell Range',
                    optional: false
                }
            ]
        }
        
    };

    //Synonyms for functions
    exports.functions.AVG = exports.functions.AVERAGE;
    exports.functions.RND = exports.functions.RAND;

    return exports;
}());