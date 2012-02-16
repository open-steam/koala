<?php
namespace Wave\Model;
class WaveTags extends WaveObject{
	
	public static function processContent($html, $page) {
		$result = str_replace("%web_root%", $page->getSide()->getEngine()->getSideUrl(), $html);
		$extensions = \ExtensionMaster::getInstance()->getExtensionByType("ITagExtension");
		foreach ($extensions as $extension) {
			$result = $extension->processContent($result, $page);
		}
		return $result;
	}
	
}
?>