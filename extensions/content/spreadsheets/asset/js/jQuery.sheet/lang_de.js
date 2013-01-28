var spreadsheetTexts = (function() {
    var exports = {};

    exports.msg = { /*msg = messages used throught sheet, for easy access to change them for other languages*/
                    addRowMulti:            "Wie viele Zeilen möchten Sie einfügen?",
                    addColumnMulti:         "Wie viele Spalten möchten Sie einfügen?",
                    newSheet:               "Welche Größe soll die Tabelle haben? Beispiel: '5x10' ergibt eine Tabelle mit 5 Spalten und 10 Zeilen",
                    openSheet:              "Wirklich eine andere Tabelle öffnen?",
                    cellFind:               "Keine Ergebnisse gefunden.",
                    cellLockedEdit:         "Diese Zelle kann gerade nicht geändert werden, sie wird von einem anderen Nutzer blockiert", 
                    deleteRowImpossible:    "Diese Zeille kann gerade nicht gelöscht werden, sie wird von einem anderen Nutzer blockiert", 
                    deleteColImpossible:    "Diese Spalte kann gerade nicht gelöscht werden, sie wird von einem anderen Nutzer blockiert", 
                    toggleHideRow:          "Keine Zeile ausgewählt.",
                    toggleHideColumn:       "Keine Zeile ausgewählt.",
                    merge:                  "Zusammenführen der ersten Zeile ist nicht möglich",
                    evalError:              "Fehler, Funktionen als Formeln werden nicht unterstützt.",
                    menuInsertColumnAfter:  "Spalte nachher einfügen",
                    menuInsertColumnBefore: "Spalte vorher einfügen",
                    menuAddColumnEnd:       "Spalte am Ende einfügen",
                    menuDeleteColumn:       "Spalte löschen",
                    menuInsertRowAfter:     "Zeile nachher einfügen",
                    menuInsertRowBefore:    "Zeile vorher einfügen",
                    menuAddRowEnd:          "Zeile am Ende einfügen",
                    menuDeleteRow:          "Zeile löschen",
                    menuAddSheet:           "Tabelle hinzufügen",
                    menuDeleteSheet:        "Tabelle löschen"
    };

    exports.toolbar = {
        insertRowAfter: "Zeile nachher einfügen",
        insertRowBefore: "Zeile vorher einfügen",
        deleteRow: "Zeile löschen",
        insertColumnAfter: "Spalte nachher einfügen",
        insertColumnBefore: "Spalte vorher einfügen",
        deleteColumn: "Spalte löschen",
        find: "Suchen",
        bold: "Fett",
        italic: "Kursiv",
        underline: "Unsterstrichen",
        strikethrough: "Durchgestrichen",
        alignLeft: "Linksbündig",
        alignCenter: "Zentriert",
        alignRight: "Rechtsbündig",
        increaseFontSize: "Schrift vergrößern",
        decreaseFontSize: "Schrift verkleinern",
        hyperLink: "Web Link",
        deleteSpreadsheet: "Tabelle löschen",
        foregroundColor: "Schriftfarbe",
        backgroundColor: "Hintergrundfarbe",
        export: "Exportieren:"
    };

    exports.misc = {
        reload: "Neu laden",
        disconnected: "Verbindung unterbrochen",
        disconnectMessage: "Die Verbindung zum Server ist unterbrochen. Bitte laden Sie die Seite neu um weiter am Dokument zu arbeiten.",
        userListTitle: " Personen arbeiten am Dokument:",
        userListTitleSingular: " Person arbeitet am Dokument:"
    };

    exports.functions = {
        VERSION: {
            name: 'VERSION',
            params: []
        },
        IMG: {
            name: 'BILD',
            params: [
                {
                    name: 'Bild-URL',
                    type: 'Text',
                    optional: false
                }
            ]
        },
        AVERAGE: {
            name: 'MITTELWERT',
            params: [
                {
                    name: 'Werte',
                    type: 'Zellenbereich',
                    optional: false
                }
            ]
        },
        COUNT: {
            name: 'ANZAHL',
            params: [
                {
                    name: 'Werte',
                    type: 'Zellenbereich',
                    optional: false
                }
            ]
        },
        SUM: {
            name: 'SUMME',
            params: [
                {
                    name: 'Werte',
                    type: 'Zellenbereich',
                    optional: false
                }
            ]
        },
        MAX: {
            name: 'MAX',
            params: [
                {
                    name: 'Werte',
                    type: 'Zellenbereich',
                    optional: false
                }
            ]
        },
        MIN: {
            name: 'MIN',
            params: [
                {
                    name: 'Werte',
                    type: 'Zellenbereich',
                    optional: false
                }
            ]
        },
        /*MEAN: {
            name: 'MIN',
            params: [
                {
                    name: 'Werte',
                    type: 'Zellenbereich',
                    optional: false
                }
            ]
        },*/
        ABS: {
            name: 'ABS',
            params: [
                {
                    name: 'Zahl',
                    type: 'Zahl',
                    optional: false
                }
            ]
        },
        CEILING: {
            name: 'AUFRUNDEN',
            params: [
                {
                    name: 'Zahl',
                    type: 'Zahl',
                    optional: false
                }
            ]
        },
        FLOOR: {
            name: 'ABRUNDEN',
            params: [
                {
                    name: 'Zahl',
                    type: 'Zahl',
                    optional: false
                }
            ]
        },
        INT: {
            name: 'GANZZAHL',
            params: [
                {
                    name: 'Zahl',
                    type: 'Zahl',
                    optional: false
                }
            ]
        },
        ROUND: {
            name: 'RUNDEN',
            params: [
                {
                    name: 'Zahl',
                    type: 'Zahl',
                    optional: false
                }
            ]
        },
        RAND: {
            name: 'ZUFALLSZAHL',
            params: []
        },
        TRUE: {
            name: 'WAHR',
            params: []
        },
        FALSE: {
            name: 'FALSCH',
            params: []
        },
        NOW: {
            name: 'JETZT',
            params: []
        },
        TODAY: {
            name: 'HEUTE',
            params: []
        },
        DAYSFROM: {
            name: 'TAGEVON',
            params: [
                {
                    name: 'Jahr',
                    type: 'Zahl',
                    optional: false
                },
                {
                    name: 'Monat',
                    type: 'Zahl',
                    optional: false
                },
                {
                    name: 'Tag',
                    type: 'Zahl',
                    optional: false
                }
            ]
        },
        DAYS: {
            name: 'TAGE',
            params: [
                {
                    name: 'Datum1',
                    type: 'Text',
                    optional: false
                },
                {
                    name: 'Datum2',
                    type: 'Text',
                    optional: false
                }
            ]
        },
        DATEVALUE: {
            name: 'DATUMSWERT',
            params: [
                {
                    name: 'Datum',
                    type: 'Text',
                    optional: false
                }
            ]
        },
        IF: {
            name: 'WENN',
            params: [
                {
                    name: 'Bedingung',
                    type: 'Wahrheitswert',
                    optional: false
                },
                {
                    name: 'Ergebnis wenn wahr',
                    type: 'beliebig',
                    optional: false
                },
                {
                    name: 'Ergebnis wenn falsch',
                    type: 'beliebig',
                    optional: true
                }
            ]
        },
        FIXED: {
            name: 'FEST',
            params: [
                {
                    name: 'Zahl',
                    type: 'Zahl',
                    optional: false
                },
                {
                    name: 'Nachkommastellen',
                    type: 'Zahl',
                    optional: true
                }
            ]
        },
        TRIM: {
            name: 'GLÄTTEN',
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
            name: 'WÄHRUNG',
            params: [
                {
                    name: 'Wert',
                    type: 'Zahl',
                    optional: false
                },
                {
                    name: 'Nachkommastellen',
                    type: 'Zahl',
                    optional: true
                },
                {
                    name: 'Währung',
                    type: 'Text',
                    optional: true
                }
            ]
        },
        VALUE: {
            name: 'WERT',
            params: [
                {
                    name: 'Wert',
                    type: 'Text',
                    optional: false
                }
            ]
        },
        N: {
            name: 'N',
            params: [
                {
                    name: 'Wert',
                    type: 'beliebig',
                    optional: false
                }
            ]
        },
        PI: {
            name: 'PI',
            params: []
        },
        POWER: {
            name: 'POTENZ',
            params: [
                {
                    name: 'Basis',
                    type: 'Zahl',
                    optional: false
                },
                {
                    name: 'Exponent',
                    type: 'Zahl',
                    optional: false
                }
            ]
        },
        SQRT: {
            name: 'WURZEL',
            params: [
                {
                    name: 'Zahl',
                    type: 'Zahl',
                    optional: false
                }
            ]
        },
        DROPDOWN: {
            name: 'DROPDOWN',
            params: [
                {
                    name: 'Werte',
                    type: 'Zellenbereich',
                    optional: false
                },
                {
                    name: 'Nicht leer',
                    type: 'Wahrheitswert',
                    optional: true
                }
            ]
        },
        RADIO: {
            name: 'RADIO',
            params: [
                {
                    name: 'Werte',
                    type: 'Zellenbereich',
                    optional: false
                }
            ]
        },
        CHECKBOX: {
            name: 'CHECKBOX',
            params: [
                {
                    name: 'Beschriftung',
                    type: 'Text',
                    optional: false
                }
            ]
        },
        BARCHART: {
            name: 'BALKENDIAGRAMM',
            params: [
                {
                    name: 'Werte',
                    type: 'Zellenbereich',
                    optional: false
                }
            ]
        },
        HBARCHART: {
            name: 'HBALKENDIAGRAMM',
            params: [
                {
                    name: 'Werte',
                    type: 'Zellenbereich',
                    optional: false
                }
            ]
        },
        LINECHART: {
            name: 'LINIENDIAGRAMM',
            params: [
                {
                    name: 'WerteX',
                    type: 'Zellenbereich',
                    optional: false
                },
                {
                    name: 'WerteY',
                    type: 'Zellenbereich',
                    optional: false
                }
            ]
        },
        PIECHART: {
            name: 'TORTENDIAGRAMM',
            params: [
                {
                    name: 'Werte',
                    type: 'Zellenbereich',
                    optional: false
                },
                {
                    name: 'Legende',
                    type: 'Text',
                    optional: true
                }
            ]
        },
        DOTCHART: {
            name: 'PUNKTDIAGRAMM',
            params: [
                {
                    name: 'WerteX',
                    type: 'Zellenbereich',
                    optional: false
                },
                {
                    name: 'WerteY',
                    type: 'Zellenbereich',
                    optional: false
                }
            ]
        }        
    };

    return exports;
}());