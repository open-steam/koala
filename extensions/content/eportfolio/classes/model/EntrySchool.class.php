<?php

namespace Portfolio\Model;

class EntrySchool extends Entry {

    public static $entryTypeDescription = "Schulischer Werdegang";
    public static $entryTypeInfo = "Hier können relevante Informationen zum schulischen Werdegang (wie die Art des Abschlusses) hinterlegt, erläutert und belegt werden.";
    public static $entryTypeEditDescription = "Schulabschluss eintragen";
    public static $entryTypeEditInfo = "Dieser Dialog dient der Erfassung eines schulischen Abschlusses. Wenn mehrere Abschlüsse vorliegen, muss dieser Dialog erneut geöffnet werden.";
    public static $entryType = "SCHOOL";
    public static $entryTypeHasCompetences = false;

    public function __construct(\steam_room $room) {
        parent::__construct($room);

        $this->entryAttributes["schooltype"] = array(
            "attributeName" => PORTFOLIO_PREFIX . "ENTRY_SCHOOL_TYPE",
            "label" => "Schulabschluss",
            "description" => "",
            "widget" => "\Widgets\ComboBox",
            "widgetMethods" => array("setOptions" => array(
                            array("name" => "", "value" => 0),
                            array("name" => "Volks-/ Hauptschulabschluss", "value" => "haupt"),
                            array("name" => "Mittlere Reife/Realschulabschluss", "value" => "real"),
                            array("name" => "Fachhochschulreife", "value" => "fh"),
                            array("name" => "Abitur", "value" => "abi"),
                            array("name" => "Sonstige", "value" => "sonst"))
            ),
            "defaultValue" => ""
        );
        $this->entryAttributes["schoolgrade"] = array(
            "attributeName" => PORTFOLIO_PREFIX . "ENTRY_SCHOOL_GRADE",
            "label" => "Durchschnittsnote",
            "description" => "",
            "widget" => "\Widgets\ComboBox",
            "widgetMethods" => array("setOptions" => array(
                            array("name" => "", "value" => 0),
                            array("name" => "Sehr gut (1)", "value" => "1"),
                            array("name" => "Gut (2)", "value" => "2"),
                            array("name" => "Befriedigend (3)", "value" => "3"),
                            array("name" => "Ausreichend (4)", "value" => "4"))
            ),
            "defaultValue" => ""
        );
    }

}

?>