<?php

namespace PortletTopic\Commands;

class DatabindingPortletTopicEntry extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $object;
	private $categoryIndex;
	private $entryIndex;
	private $field;
	private $value;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		
		$this->id = $this->params["id"];
		$this->categoryIndex = $this->params["categoryIndex"];
		$this->entryIndex = $this->params["entryIndex"];
		$this->field = $this->params["field"];
		$this->value = $this->params["value"];
		
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		if (isset($this->params["categoryIndex"]) && isset($this->params["value"])) {
			$data = array();
			$oldValue = $this->getEntryField($this->object,$this->categoryIndex,$this->entryIndex, $this->field);
			try {
				$this->setEntryField($this->object,$this->categoryIndex,$this->entryIndex, $this->field, $this->value);
			} catch (steam_exception $e) {
				$data["oldValue"] = $oldValue;
			 	$data["error"] = $e->get_message();
				$data["undo"] = false;
				$ajaxResponseObject->setStatus("ok");
				$ajaxResponseObject->setData($data);
				return $ajaxResponseObject;
			}
			$ajaxResponseObject->setStatus("ok");
			
			$newValue = $this->getEntryField($this->object,$this->categoryIndex,$this->entryIndex, $this->field);
			
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
	
	
	private function setEntryField($object, $categoryIndex, $entryIndex, $field, $value){
		$objectId = $object->get_id();
		$topicObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$content = $topicObject->get_attribute("bid:portlet:content");
		
		//read
		$category = $content[$categoryIndex];
		$entries = $category["topics"];
		$entry = $entries[$entryIndex];
		$entry = $entries[$this->entryIndex];
		
		
		//write
		if($field=="link_target"){ //case for checkbox
			if($value){
				$entry[$field] = "checked";
			}else{
				$entry[$field] = "";
			}
			
		}else{ //text case
			$entry[$field] = $value;
		}
		
		
		$entries[$entryIndex] = $entry;
		$category["topics"] =  $entries;
		$content[$categoryIndex] = $category;
		$topicObject->set_attribute("bid:portlet:content", $content);
		
		return true;
	}
	
	private function getEntryField($object, $categoryIndex, $entryIndex, $field){
		$objectId = $object->get_id();
		$topicObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$content = $topicObject->get_attribute("bid:portlet:content");
		
		$category = $content[$categoryIndex];
		$entries = $category["topics"];
		$entry = $entries[0];
		
		$entry = $entries[$this->entryIndex];
		switch ($this->field){
			case "description":	
				return $entry["description"];
			case "link_target":
				return $entry["link_target"];
			case "title":
				return $entry["title"];
			case "link_url":
				return $entry["link_url"];
			default:
				return $entry["description"];
			}
		return $content[$categoryIndex]["title"];
	}
}
?>