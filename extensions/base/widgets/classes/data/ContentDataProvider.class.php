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
	
	public function getUpdateCode($object, $elementId, $successMethod = null) {
            
           	if (is_int($object)) {
			$objectId = $object;
		} else {
			$objectId = $object->get_id();
		}
		$function = "";
		$function = ($successMethod != "") ? ", function(response){{$successMethod}({$elementId}, response);}" : ",''";
                $variableName = ($elementId)? $elementId:'value';
		return "sendRequest('databinding', {'id': {$objectId}, 'value': {$variableName}}, '', 'data'{$function});"; 
        }
	
	public function isChangeable($object) {
		return $object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user());
	}
	
}
?>