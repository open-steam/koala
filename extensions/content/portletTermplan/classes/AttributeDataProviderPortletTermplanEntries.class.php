<?php
namespace PortletTermplan\Commands;

class AttributeDataProviderPortletTermplanEntries{
	
	private $voteIndex;
	private $field;
	
	public function __construct($entryIndex=0) {
		$this->entryIndex = $entryIndex;
	}
	
	
	public function getData($object) {
		if (is_int($object)){
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		}
		if ($object instanceof \steam_object) {
			$portletContent = $object->get_attribute("bid:portlet:content");
			$optionsDescription = $portletContent["options"];
			return $optionsDescription[$this->entryIndex];
		}
		return "";
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
	
                return "sendRequest('DatabindingPortletTermplanEntries', {'id': {$objectId}, 'entryIndex': '{$this->entryIndex}', 'value': {$variableName}}, '', 'data'{$function}, '', 'PortletTermplan');";
	}
	
	
	public function isChangeable($steamObject){
		return true;
	}
}
?>