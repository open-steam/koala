<?php
namespace Portfolio\Model;
class EntryEducation extends Entry{
	
	public static $entryTypeDescription = "Berufliche Aus- und Weiterbildungsgänge";
	public static $entryTypeInfo = "Hier können relevante Informationen zu den beruflichen Aus- und Weiterbildungsgängen (wie die Art des Ausbildungsberufes sowie ggf. des Weiterbildungsberufes) hinterlegt, erläutert und belegt werden.";
	public static $entryTypeEditDescription ="Beruflichen Aus- oder Weiterbildungsgang eintragen";
	public static $entryTypeEditInfo ="Dieser Dialog dient der Erfassung eines beruflichen Aus- oder Weiterbildungsganges. Wenn mehrere Abschlüsse vorliegen, muss dieser Dialog erneut geöffnet werden.";
	public static $entryType = "EDUCATION";
        public static $entryTypeHasCompetences = true;
	
	public function __construct(\steam_room $room) {
		parent::__construct($room);
	
		$this->entryAttributes["educationcompany"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_EDUCATION_COMPANY",
				"label"=>"Ausbildungsbetrieb",
				"description"=>"",
				"widget"=>"\Widgets\TextInput",
				"defaultValue"=>"",
                                "order" => 5
		);
		$this->entryAttributes["educationinstitution"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_EDUCATION_INSTITUTION",
				"label"=>"Ausbildungsstätte",
				"description"=>"",
				"widget"=>"\Widgets\TextInput",
				"defaultValue"=>"",
                                "order" => 4
		);
		$this->entryAttributes["educationstate"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_EDUCATION_STATE",
				"label"=>"Status",
				"description"=>"",
				"widget" => "\Widgets\ComboBox",
				"widgetMethods" => array("setOptions" => array(
						array("name"=>"", "value"=>0),
						array("name"=>"abgeschlossen", "value"=>"1"),
						array("name"=>"laufend", "value"=>"2"),
						array("name"=>"nicht abgeschlossen", "value"=>"3"))),
				"defaultValue"=>"",
                                "order" => 3
		);
		$this->entryAttributes["educationtype"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_EDUCATION_TYPE",
				"label"=>"Abschluss",
				"description"=>"",
				"widget" => "\Widgets\ComboBox",
				"widgetMethods" => array("setOptions" => array(
						array("name"=>"", "value"=>0),
						array("name"=>"Chemikant", "value"=>"CK"),
						array("name"=>"Chemielaborant", "value"=>"CL"),
						array("name"=>"Chemietechniker", "value"=>"CT"),
						array("name"=>"Industriemeister Chemie", "value"=>"IC"),
						array("name"=>"Sonstige", "value"=>"5"))),
				"defaultValue"=>"",
                                "order" => 2
		);
		$this->entryAttributes["educationgrade"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_EDUCATION_GRADE",
				"label"=>"Durchschnittsnote",
				"description"=>"",
				"widget" => "\Widgets\ComboBox",
				"widgetMethods" => array("setOptions" => array(
						array("name"=>"", "value"=>0),
						array("name"=>"Sehr gut (1)", "value"=>"1"),
						array("name"=>"Gut (2)", "value"=>"2"),
						array("name"=>"Befriedigend (3)", "value"=>"3"),
						array("name"=>"Ausreichend (4)", "value"=>"4"))),
				"defaultValue"=>"",
                                "order" => 1
		);
	}
}
?>