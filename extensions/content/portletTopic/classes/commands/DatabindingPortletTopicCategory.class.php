<?php

namespace PortletTopic\Commands;

class DatabindingPortletTopicCategory extends \AbstractCommand implements \IAjaxCommand {
	
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
		if (isset($this->params["categoryIndex"]) && isset($this->params["value"])) {
			$data = array();
			$oldValue = $this->getCategoryTitle($this->object, $this->params["categoryIndex"]);
			try {
				$this->setCategoryTitle($this->object, $this->params["categoryIndex"], $this->params["value"]);
			} catch (steam_exception $e) {
				$data["oldValue"] = $oldValue;
			 	$data["error"] = $e->get_message();
				$data["undo"] = false;
				$ajaxResponseObject->setStatus("ok");
				$ajaxResponseObject->setData($data);
				return $ajaxResponseObject;
			}
			$ajaxResponseObject->setStatus("ok");
			
			$newValue = $this->getCategoryTitle($this->object, $this->params["categoryIndex"]);
			
			if ($newValue === $this->params["value"]) {
				$data["oldValue"] = $oldValue;
				$data["newValue"] = $newValue;
				$data["error"] = "none";
				$data["undo"] = true;
			 } else {
			 	$data["oldValue"] = $oldValue;
			 	$data["error"] = "Data could not be saved.";
				$data["undo"] = false;
			 }
			 $ajaxResponseObject->setData($data);
		} else {
			$ajaxResponseObject->setStatus("error");
		}
		return $ajaxResponseObject;
	}
	
	
	private function setCategoryTitle($object, $categoryIndex, $title){
		$objectId = $object->get_id();
		$topicObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$content = $topicObject->get_attribute("bid:portlet:content");
		
		$topic = $content[$categoryIndex];
		$topic["title"] = $title;
		$content[$categoryIndex] = $topic;
		$topicObject->set_attribute("bid:portlet:content", $content);
		return true;
	}
	
	private function getCategoryTitle($object, $categoryIndex){
		$objectId = $object->get_id();
		$topicObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$content = $topicObject->get_attribute("bid:portlet:content");
		return $content[$categoryIndex]["title"];
	}
	
	
	
}
?>