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
	
	public function getUpdateCode($object, $elementId, $successMethode = null) {
		if (is_int($object)) {
			$objectId = $object;
		} else {
			$objectId = $object->get_id();
		}
		$function = "";
		if (isset($successMethode)) {
			$function = ", function(response){{$successMethode}({$elementId}, response);}";
		}
		return <<< END
sendRequest('databinding', {'id': {$objectId}, 'value': value}, '', 'data'{$function});
END;
	}
	
	public function isChangeable($object) {
		return $object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user());
	}
	
}
?>