<?php
namespace Widgets;

class AttributeDataProvider implements IDataProvider {
	
	private $attribute;
	private $initValue;
	
	public function __construct($attribute, $initValue = null) {
		$this->attribute = $attribute;
		$this->initValue = $initValue;
	}
	
	public function getData($object) {
		if (isset($this->initValue)) {
			return $this->initValue;
		}
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $object);
		}
		return urldecode(\Databinding::getAttributeValue($object, $this->attribute));
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
                $jsVariableName = ($ownVariableName)? $ownVariableName:'value';
                
                //build the functioncall
		return "sendRequest('databinding', {'id': {$objectId}, 'attribute': '{$this->attribute}', 'value': {$jsVariableName}}, '', 'data'{$function});";

	}
	
	public function isChangeable($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $object);
		}
		return (!$object->is_locked($this->attribute) && $object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user()));
	}
}
?>