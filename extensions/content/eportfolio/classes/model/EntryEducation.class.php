<?php
namespace Portfolio\Model;
class EntryEducation extends Entry{
	
	public static $entryTypeDescription = "Berufliche Aus- und Weiterbildungsgänge";
	public static $entryTypeInfo = "Hier können relevante Informationen zu den beruflichen Aus- und Weiterbildungsgängen (wie die Art des Ausbildungsberufes sowie ggf. des Weiterbildungsberufes) hinterlegt, erläutert und belegt werden.";
	public static $entryTypeEditDescription ="";
	public static $entryTypeEditInfo ="";
	public static $entryType = "EDUCATION";
        public static $entryTypeHasCompetences = true;
	
	public function __construct(\steam_room $room) {
		parent::__construct($room);
	
		$this->entryAttributes["educationcompany"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_EDUCATION_COMPANY",
				"label"=>"Ausbildungsbetrieb",
				"description"=>"",
				"widget"=>"\Widgets\TextInput",
				"defaultValue"=>""
		);
		$this->entryAttributes["educationinstitution"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_EDUCATION_INSTITUTION",
				"label"=>"Ausbildungsstätte",
				"description"=>"",
				"widget"=>"\Widgets\TextInput",
				"defaultValue"=>""
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
				"defaultValue"=>""
		);
		$this->entryAttributes["educationtype"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_EDUCATION_TYPE",
				"label"=>"Abschluss",
				"description"=>"",
				"widget" => "\Widgets\ComboBox",
				"widgetMethods" => array("setOptions" => array(
						array("name"=>"", "value"=>0),
						array("name"=>"Chemikant", "value"=>"1"),
						array("name"=>"Chemielaborant", "value"=>"2"),
						array("name"=>"Chemietechniker", "value"=>"3"),
						array("name"=>"Industriemeister Chemie", "value"=>"4"),
						array("name"=>"Sonstige", "value"=>"5"))),
				"defaultValue"=>""
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
				"defaultValue"=>""
		);
	}
}
?>