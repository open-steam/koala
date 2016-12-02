<?php
namespace Widgets;

class NameURLEncodeDataProvider implements IDataProvider {
	
	public function getData($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $object);
		}
		return rawurldecode($object->get_attribute("OBJ_NAME"));
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
                
                //build the functioncall
                return "sendRequest('DatabindingURLEncodeName', {'id': {$objectId}, 'value': {$variableName}}, '', 'data'{$function});";
	}
	
	public function isChangeable($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $object);
		}
		return (!$object->is_locked("OBJ_NAME") && $object->check_access_write(\lms_steam::get_current_user()));
	}
}
?>
