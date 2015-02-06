<?php
namespace PortletTopic\Commands;

class AttributeDataProviderPortletTopicCategory {
	
	private $categoryIndex;
	
	public function __construct($categoryIndex=0) {
		$this->categoryIndex = $categoryIndex;
	}
	
	
	/*
	 * return the content of the attributes index
	 */
	public function getData($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		}
		if ($object instanceof \steam_object) {
			$portletContent = $object->get_attribute("bid:portlet:content");
			$categoryContent = $portletContent[$this->categoryIndex];
			$categoryTitle = $categoryContent["title"];
			return $categoryTitle;
		}
	}
	
	
	/*
	 * return the js code for the databinding call
	 */
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
		
                return "sendRequest('DatabindingPortletTopicCategory', {'id': {$objectId}, 'categoryIndex': '{$this->categoryIndex}', 'value': {$variableName}}, '', 'data'{$function}, '', 'PortletTopic');";
	}
	
	
	public function isChangeable($steamObject){
		return true;
	}
}
?>