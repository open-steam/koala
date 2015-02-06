<?php
namespace Widgets;

class AnnotationDataProvider implements IDataProvider {
	
	private $initValue;
	
	public function __construct() {
	}
	
	public function getData($object) {
		return "";
	}
	
        //not tested with the cancelbutton in the dialog because this class was unsued at that time 
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
                $jsVariableName = ($ownVariableName)? $ownVariableName:'value';
                
                //build the functioncall
		return "sendRequest('databinding', {'id': {$objectId}, 'annotate': {$jsVariableName}}, '', 'data'{$function});";
	}
	
	public function isChangeable($object) {
		return $object->check_access_annotate($GLOBALS["STEAM"]->get_current_steam_user());
	}
	
}
?>