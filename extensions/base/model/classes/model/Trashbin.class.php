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
			$color = "#ff0300;";
		} else if ($trashbinCount > 10) {
			$color = "#ff8300;";
		} else {
			$color = "#ffffff;";
		}
		return "<div style=\"float:left; color:" . $color . "\" title=\"Papierkorb\"><svg><use xlink:href=\" " . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/trash.svg#trash\"/></svg></div>$trashbinCount";
	}

}
?>
