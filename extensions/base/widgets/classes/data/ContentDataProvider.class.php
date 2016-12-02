<?php
namespace Widgets;

class ContentDataProvider implements IDataProvider {
	
	private $initValue;
	
	public function __construct($initValue) {
		$this->initValue = $initValue;
	}
	
	public function getData($object) {
		if (isset($this->initValue)) {
			return $this->initValue;
		}
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		}
		if ($object instanceof \steam_document) {
			return $object->get_content();
		}
	}
	
	public function getUpdateCode($object, $ownVariableName, $successMethod = null) {
            
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
                
                //build the functioncall
                return "sendRequest('databinding', {'id': {$objectId}, 'value': {$variableName}}, '', 'data'{$function});"; 
        }
	
	public function isChangeable($object) {
		return $object->check_access_write(\lms_steam::get_current_user());
	}
	
}
?>