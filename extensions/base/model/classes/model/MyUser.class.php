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
		return "<img title=\"Zwischenablage\" src=\"" . \Explorer::getInstance()->getExtensionUrl() . "asset/icons/clipboard_white_16.png\">$clipboardCount";
	}
	
}
?>