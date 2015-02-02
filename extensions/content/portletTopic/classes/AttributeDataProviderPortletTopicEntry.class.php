<?php
namespace PortletTopic\Commands;

class AttributeDataProviderPortletTopicEntry {
	
	private $categoryIndex;
	private $entryIndex;
	private $field; // title, content etc
	
	public function __construct($categoryIndex=0, $entryIndex=0, $field=0) {
		$this->categoryIndex = $categoryIndex;
		$this->entryIndex = $entryIndex;
		$this->field = $field;
	}
	
	

	public function getData($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		}
		if ($object instanceof \steam_object) {
			$portletContent = $object->get_attribute("bid:portlet:content");
			$categoryContent = $portletContent[$this->categoryIndex];
			
			$entries = $categoryContent["topics"];
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
		}
	}
	
	

	public function getUpdateCode($object, $ownVariableName, $successMethod = "") {
		if (is_int($object)) {
			$objectId = $object;
		} else {
			$objectId = $object->get_id();
		}
                
                
                //if the user has an own successMethod, then call both methods, else just call the data-saveFunctionCallback method to wait until everything is saved
                $function = ($successMethod != "") ? ", function(response){dataSaveFunctionCallback(response); {$successMethod}({$ownVariableName}, response);}" : ", function(response){dataSaveFunctionCallback(response);}";
		
                //offer the possibility to use own variablenames
                //standard is 'value'
                $variableName = ($ownVariableName)? $ownVariableName:'value';
	
		return "sendRequest('DatabindingPortletTopicEntry', {'id': {$objectId}, 'categoryIndex': '{$this->categoryIndex}', 'entryIndex': '{$this->entryIndex}', 'field': '{$this->field}', 'value': {$variableName}}, '', 'data'{$function}, '', 'PortletTopic');";
	}
	
	
	public function isChangeable($steamObject){
		return true;
	}
}
?>