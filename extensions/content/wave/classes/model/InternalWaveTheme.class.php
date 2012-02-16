<?php
namespace Wave\Model;
class InternalWaveTheme extends WaveTheme {
	
	public function __construct($objectId, $myEngine) {
		$this->setEngine($myEngine);
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		if (!$this->object || !($this->object instanceof \steam_container)) {
			throw new Exception("Wave side not found.");
		}
		$this->loadPlistXml();
		$this->loadHtmlTemplate();
	}
	
	private function loadPlistXml() {
		$plistXmlObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $this->object->get_path() . "/Theme.plist");
		$plistXmlContent = $plistXmlObject->get_content();
		$this->setPlistXml(simplexml_load_string($plistXmlContent));
	}
	
	private function loadHtmlTemplate() {
		$htmlTemplateName = $this->plistXML->RWTemplateHTML;
		$htmlTemplateObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $this->object->get_path() . "/" . $htmlTemplateName);
		$this->setHtmlTemplate($htmlTemplateObject->get_content());
	}
	
}
?>