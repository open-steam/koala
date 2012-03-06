<?php
namespace Portfolio\Model;
class EntryEmployment extends Entry{

	public static $entryTypeDescription = "Berufliche Erfahrungen";
	public static $entryTypeInfo = "Hier können relevante Informationen zu den beruflichen Erfahrungen (Art der Erfahrung) hinterlegt, erläutert und belegt werden.";
	public static $entryTypeEditDescription ="Berufliche Erfahrung eintragen";
	public static $entryTypeEditInfo ="Dieser Dialog dient der Erfassung eines schulischen Abschlusses. Wenn mehrere Abschlüsse vorliegen, muss dieser Dialog erneut geöffnet werden.";
	public static $entryType = "EMPLOYMENT";
        public static $entryTypeHasCompetences = true;
	
	public function __construct(\steam_room $room) {
		parent::__construct($room);
	
		$this->entryAttributes["employmentcompany"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_EMPLOYMENT_COMPANY",
				"label"=>"Betrieb",
				"description"=>"",
				"widget"=>"\Widgets\TextInput",
				"defaultValue"=>"",
                                "order" => 4
		);
		$this->entryAttributes["employmentduration"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_EMPLOYMENT_DURATION",
				"label"=>"Dauer",
				"description"=>"",
				"widget"=>"\Widgets\TextInput",
				"defaultValue"=>"",
                                "order" => 3
		);
		$this->entryAttributes["employmenttype"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_EMPLOYMENT_TYPE",
				"label"=>"Art",
				"description"=>"",
				"widget" => "\Widgets\ComboBox",
				"widgetMethods" => array("setOptions" => array(
						array("name"=>"", "value"=>0),
						array("name"=>"Beschäftigung", "value"=>"1"),
						array("name"=>"Praktikum", "value"=>"2"),
						array("name"=>"Sonstiges", "value"=>"3"))),
				"defaultValue"=>"",
                                "order" => 2
		);
		$this->entryAttributes["employmentposition"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_EMPLOYMENT_POSITION",
				"label"=>"Position",
				"description"=>"",
				"widget" => "\Widgets\ComboBox",
				"widgetMethods" => array("setOptions" => array(
						array("name"=>"", "value"=>0),
						array("name"=>"Chemikant", "value"=>"1"),
						array("name"=>"Chemielaborant", "value"=>"2"),
						array("name"=>"Chemietechniker", "value"=>"3"),
						array("name"=>"Industriemeister Chemie", "value"=>"4"),
						array("name"=>"Sonstige", "value"=>"5"))),
				"defaultValue"=>"",
                                "order" => 1
		);
	}
}

?>