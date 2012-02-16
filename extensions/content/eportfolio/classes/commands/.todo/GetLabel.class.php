<?php
namespace Portfolio\Commands;
class GetLabel extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $object;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$rawHtml = new \Widgets\RawHtml();
		
		$url = \ExtensionMaster::getInstance()->getUrlForObjectId($this->object->get_id(), "view");
		$desc = $this->object->get_attribute("OBJ_DESC");
		$name = getCleanName($this->object, 50);
		if (isset($url) && $url != "") {
			$html = "<a href=\"".$url."\" title=\"$desc\"> " . $name ."</a>";
		} else {
			$html = $name;
		}
		
		
		$rawHtml->setHtml($html);
		
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($rawHtml);
		return $ajaxResponseObject;
	}
}
?>