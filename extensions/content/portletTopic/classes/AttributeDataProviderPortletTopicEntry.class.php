<?php
namespace PortletTopic\Commands;

class AttributeDataProviderPortletTopicEntry {
	
	private $categoryIndex;
	private $entryIndex;
	private $field; // title, content etc
	
	public function __construct($categoryIndex=0, $entryIndex=0, $field=0) {
		$this->categoryIndex = $categoryIndex;
		$this->entryIndex = $entryIndex;
		$this->field = $field;
	}
	
	

	public function getData($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		}
		if ($object instanceof \steam_object) {
			$portletContent = $object->get_attribute("bid:portlet:content");
			$categoryContent = $portletContent[$this->categoryIndex];
			
			$entries = $categoryContent["topics"];
			$entry = $entries[$this->entryIndex];
			
			switch ($this->field){
				case "description":	
					return $entry["description"];
				case "link_target":
					return $entry["link_target"];
					
					
				case "title":
					return $entry["title"];
				case "link_url":
					return $entry["link_url"];
				default:
					return $entry["description"];
			}
		}
	}
	
	

	public function getUpdateCode($object, $elementId, $successMethode = "") {
		if (is_int($object)) {
			$objectId = $object;
		} else {
			$objectId = $object->get_id();
		}
		$function = ($successMethode != "") ? ", function(response){{$successMethode}({$elementId}, response);}" : ",''";
		return <<< END
sendRequest('DatabindingPortletTopicEntry', {'id': {$objectId}, 'categoryIndex': '{$this->categoryIndex}', 'entryIndex': '{$this->entryIndex}', 'field': '{$this->field}', 'value': value}, '', 'data'{$function}, '', 'PortletTopic');
END;
	}
	
	
	public function isChangeable($steamObject){
		return true;
	}
}
?>