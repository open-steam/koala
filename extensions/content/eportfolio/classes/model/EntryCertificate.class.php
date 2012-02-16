<?php
namespace Portfolio\Model;
class EntryCertificate extends Entry{
	
	public static $entryTypeDescription = "Zertifizierte Zusatzqualifikationen";
	public static $entryTypeInfo = "Hier können relevante Zusatzqualifikation eingetragen werden (z.B. Ausbilderschein, Gabelstablerschein, einzelne Weiterbildungszertifikate).";
	public static $entryTypeEditDescription ="";
	public static $entryTypeEditInfo ="";
	public static $entryType = "CERTIFICATE";
	
	public function __construct(\steam_room $room) {
		parent::__construct($room);
	
		$this->entryAttributes["certificatetype"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_CERTIFICATE_TYPE",
				"label"=>"Schulabschluss",
				"description"=>"",
				"values"=>array(
						array("name"=>"", "value"=>0),
						array("name"=>"DAWINCI Lernmodul", "value"=>"dawinci"),
						array("name"=>"Sonstige", "value"=>"sonst")),
				"defaultValue"=>""
		);
		$this->entryAttributes["certificatename"] = array(
				"attributeName"=>PORTFOLIO_PREFIX . "ENTRY_CERTIFICATE_NAME",
				"label"=>"Name",
				"description"=>"",
				"values"=>"",
				"defaultValue"=>""
		);
	}
}
?>