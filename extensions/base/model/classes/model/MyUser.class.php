<?php
namespace Explorer\Model;
class Clipboard extends \AbstractObjectModel {
	private $clipboard;

	public static function isObject(\steam_object $steamObject) {

	}

	public function __construct($clipboard) {
		$this->clipboard = $clipboard;
	}

	public function getIconbarHtml() {
		$clipboardCount = count($this->clipboard->get_inventory());
		return "<div style=\"float:left;\" title=\"Zwischenablage\"><svg><use xlink:href=\" " . \Explorer::getInstance()->getAssetUrl() . "icons/clipboard.svg#clipboard\"/></svg></div><span class=\"icon_bar_description_display_none\">$clipboardCount</span> <span class=\"icon_bar_description\">Zwischenablage ($clipboardCount)</span>";
	}

}
