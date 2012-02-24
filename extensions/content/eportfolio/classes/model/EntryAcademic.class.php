<?php
namespace Portfolio\Model;
class EntryAcademic extends Entry{
	
	public static $entryTypeDescription = "Akademische Ausbildung(en)";
	public static $entryTypeInfo = "Hier können relevante Informationen zu einer akademischen Ausbildung (wie Art des Studiums) hinterlegt, erläutert und belegt werden.";
	public static $entryTypeEditDescription ="";
	public static $entryTypeEditInfo ="";
	public static $entryType = "ACADEMIC";
	
	public function __construct(\steam_room $room) {
		parent::__construct($room);
	
		$this->entryAttributes["academictype"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_ACADEMIC_TYPE",
				"label"=>"Akademischer Abschluss",
				"description"=>"",
                                "widget" => "\Widgets\ComboBox",
				"widgetMethods" => array("setOptions" => array(
						array("name"=>"", "value"=>0),
						array("name"=>"Bachelor", "value"=>"1"),
						array("name"=>"Master", "value"=>"2"),
						array("name"=>"Sonstiger", "value"=>"3"))),
				"defaultValue"=>""
		);
		$this->entryAttributes["academicinstitution"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_ACADEMIC_INSTITUTION",
				"label"=>"Ausbildungseinrichtung",
				"description"=>"",
				"widget"=>"\Widgets\TextInput",
				"defaultValue"=>""
		);
		$this->entryAttributes["academicgrade"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_ACADEMIC_GRADE",
				"label"=>"Druchschnittsnote",
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