<?php
namespace Widgets;

class AnnotationDataProvider implements IDataProvider {
	
	private $initValue;
	
	public function __construct() {
	}
	
	public function getData($object) {
		return "";
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
sendRequest('databinding', {'id': {$objectId}, 'annotate': value}, '', 'data'{$function});
END;
	}
	
	public function isChangeable($object) {
		return $object->check_access_annotate($GLOBALS["STEAM"]->get_current_steam_user());
	}
	
}
?>