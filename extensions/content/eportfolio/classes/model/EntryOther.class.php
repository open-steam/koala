<?php
namespace Portfolio\Model;
class EntryOther extends Entry{
	
	public static $entryTypeDescription = "Sonstiges";
	public static $entryTypeInfo = "Hier können relevante Informationen zu sonstigen Erfahrungen (wie z.B. Trainerschein für Fußball, Handball etc., Nachweise über soziales Engagement usw.) hinterlegt, erläutert und belegt werden.";
	public static $entryTypeEditDescription ="";
	public static $entryTypeEditInfo ="";
	public static $entryType = "OTHER";
	
	public function __construct(\steam_room $room) {
		parent::__construct($room);
	
		$this->entryAttributes["othername"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_OTHER_NAME",
				"label"=>"Name",
				"description"=>"",
				"widget"=>"\Widgets\TextInput",
				"defaultValue"=>""
		);
	}
}
?>