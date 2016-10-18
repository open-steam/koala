<?php
namespace Widgets;

class ArrayToStringProvider implements IDataProvider {
	
	private $attribute;
	
	public function __construct($attribute) {
		$this->attribute = $attribute;		
	}
	
	public function getData($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $object);
		}
                $value = \Databinding::getAttributeValue($object, $this->attribute);
                $string = "";
                if(is_array($value)){
                    foreach($value as $v){
                        $string .= $v . " ";
                    }
                }
		return urldecode($string);
	}
	
	public function getAttribute() {
		return $this->attribute;
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
                return "sendRequest('SendArrayToStringRequest', {'id': {$objectId}, 'attribute': '{$this->attribute}', 'value': {$variableName}.trim()}, '', 'data'{$function}, null, 'Explorer');";
	}
	
	public function isChangeable($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $object);
		}
		return (!$object->is_locked($this->attribute) && $object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user()));
	}
}




?>