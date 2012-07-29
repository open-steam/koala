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
	
	public function getUpdateCode($object, $elementId, $successMethod = "") {
		if (is_int($object)) {
			$objectId = $object;
		} else {
			$objectId = $object->get_id();
		}
		$function = ($successMethod != "") ? ", function(response){{$successMethod}({$elementId}, response);}" : ",''";
		return <<< END
sendRequest('databinding', {'id': {$objectId}, 'attribute': '{$this->attribute}', 'value': value}, '', 'data'{$function});
END;
	}
	
	public function isChangeable($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $object);
		}
		return (!$object->is_locked($this->attribute) && $object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user()));
	}
}
?>