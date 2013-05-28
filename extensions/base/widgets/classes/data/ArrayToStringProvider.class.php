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
	
	public function getUpdateCode($object, $elementId, $successMethod = "") {
		if (is_int($object)) {
			$objectId = $object;
		} else {
			$objectId = $object->get_id();
		}
		$function = ($successMethod != "") ? ", function(response){{$successMethod}({$elementId}, response);}" : ",''";
		return <<< END
window.ajaxSaving==true;
               
sendRequest('SendArrayToStringRequest', {'id': {$objectId}, 'attribute': '{$this->attribute}', 'value': value}, '', 'data'{$function});
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