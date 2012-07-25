<?php
namespace Explorer\Model;
class Trashbin extends \AbstractObjectModel {
	private $trashbin;
	
	public static function isObject(\steam_object $steamObject) {
	
	}
	
	public function __construct($trashbin) {
		$this->trashbin = $trashbin;
	}
	
	public function getIconbarHtml() {
		$trashbinCount = count($this->trashbin->get_inventory());
		if ($trashbinCount > 25) {
			$trashbinIconName = "trashbin_red_16.png";
		} else if ($trashbinCount > 10) {
			$trashbinIconName = "trashbin_orange_16.png";
		} else {
			$trashbinIconName = "trashbin_white_16.png";
		}
		return "<img title=\"Papierkorb\" src=\"" . \Explorer::getInstance()->getExtensionUrl() . "asset/icons/{$trashbinIconName}\">$trashbinCount";
	}
	
}
?>