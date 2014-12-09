<?php
namespace Widgets;

class NameURLEncodeDataProvider implements IDataProvider {
	
	public function getData($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $object);
		}
		return rawurldecode($object->get_attribute("OBJ_NAME"));
	}
	
	public function getUpdateCode($object, $elementId, $successMethod = "") {
		if (is_int($object)) {
			$objectId = $object;
		} else {
			$objectId = $object->get_id();
		}
		$function = ($successMethod != "") ? ", function(response){{$successMethod}({$elementId}, response);}" : ",''";
                $variableName = ($elementId)? $elementId:'value';
		return "sendRequest('DatabindingURLEncodeName', {'id': {$objectId}, 'value': {$variableName}}, '', 'data'{$function});";
	}
	
	public function isChangeable($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $object);
		}
		return (!$object->is_locked("OBJ_NAME") && $object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user()));
	}
}
?>
