<?php
namespace PortletPoll\Commands;

class DatabindingPortletPollVotes extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $object;
	
	private $field;
	private $voteIndex;
	private $value;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		
		$this->id = $this->params["id"];
		
		$this->voteIndex = $this->params["voteIndex"];
		$this->field = $this->params["field"];
		$this->value = $this->params["value"];
		
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		if (isset($this->params["field"]) && isset($this->params["value"])) {
			$data = array();
			$oldValue = $this->getEntryField($this->object, $this->voteIndex, $this->field);
			try {
				$this->setEntryField($this->object, $this->voteIndex, $this->field, $this->value);
			} catch (steam_exception $e) {
				$data["oldValue"] = $oldValue;
			 	$data["error"] = $e->get_message();
				$data["undo"] = false;
				$ajaxResponseObject->setStatus("ok");
				$ajaxResponseObject->setData($data);
				return $ajaxResponseObject;
			}
			$ajaxResponseObject->setStatus("ok");
			
			$newValue = $this->getEntryField($this->object, $this->voteIndex, $this->field);
			
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
			$ajaxResponseObject->setStatus("error: parameter missing");
		}
		return $ajaxResponseObject;
	}
	
	
	private function setEntryField($object, $voteIndex, $field, $value){
		$objectId = $object->get_id();
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$portletContent = $object->get_attribute("bid:portlet:content");
		
		$optionsDescription = $portletContent["options"];
		$optionsVoteCount = $portletContent["options_votecount"];
		
		switch($this->field){
			case "votes":
				$optionsVoteCount[$voteIndex]=$value;
				break;
			case "description":
				$optionsDescription[$voteIndex]=$value;
				break;
		}
		
		//write attribute
		$portletContent["options"] = $optionsDescription;
		$portletContent["options_votecount"] = $optionsVoteCount;
		$object->set_attribute("bid:portlet:content", $portletContent);
		return true;
	}
	
	private function getEntryField($object, $voteIndex, $field){
		$objectId = $object->get_id();
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$portletContent = $object->get_attribute("bid:portlet:content");
		
		$optionsDescription = $portletContent["options"];
		$optionsVoteCount = $portletContent["options_votecount"];
		
		switch($field){
			case "votes": return $optionsVoteCount[$voteIndex];
			case "description": return $optionsDescription[$voteIndex];
		}
	}
}
?>