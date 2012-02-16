<?php
namespace PortletTermplan\Commands;

class AttributeDataProviderPortletTermplanEntries{
	
	private $voteIndex;
	private $field;
	
	public function __construct($entryIndex=0) {
		$this->entryIndex = $entryIndex;
	}
	
	
	public function getData($object) {
		if (is_int($object)){
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		}
		if ($object instanceof \steam_object) {
			$portletContent = $object->get_attribute("bid:portlet:content");
			$optionsDescription = $portletContent["options"];
			return $optionsDescription[$this->entryIndex];
		}
		return "";
	}
	
	
	public function getUpdateCode($object, $elementId, $successMethode = "") {
		if (is_int($object)) {
			$objectId = $object;
		} else {
			$objectId = $object->get_id();
		}
		$function = ($successMethode != "") ? ", function(response){{$successMethode}({$elementId}, response);}" : ",''";
		return <<< END
sendRequest('DatabindingPortletTermplanEntries', {'id': {$objectId}, 'entryIndex': '{$this->entryIndex}', 'value': value}, '', 'data'{$function}, '', 'PortletTermplan');
END;
	}
	
	
	public function isChangeable($steamObject){
		return true;
	}
}
?>