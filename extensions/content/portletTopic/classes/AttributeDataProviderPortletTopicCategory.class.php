<?php
namespace PortletTopic\Commands;

class AttributeDataProviderPortletTopicCategory {
	
	private $categoryIndex;
	
	public function __construct($categoryIndex=0) {
		$this->categoryIndex = $categoryIndex;
	}
	
	
	/*
	 * return the content of the attributes index
	 */
	public function getData($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		}
		if ($object instanceof \steam_object) {
			$portletContent = $object->get_attribute("bid:portlet:content");
			$categoryContent = $portletContent[$this->categoryIndex];
			$categoryTitle = $categoryContent["title"];
			return $categoryTitle;
		}
	}
	
	
	/*
	 * return the js code for the databinding call
	 */
	public function getUpdateCode($object, $elementId, $successMethode = "") {
		if (is_int($object)) {
			$objectId = $object;
		} else {
			$objectId = $object->get_id();
		}
		$function = ($successMethode != "") ? ", function(response){{$successMethode}({$elementId}, response);}" : ",''";
		return <<< END
sendRequest('DatabindingPortletTopicCategory', {'id': {$objectId}, 'categoryIndex': '{$this->categoryIndex}', 'value': value}, '', 'data'{$function}, '', 'PortletTopic');
END;
	}
	
	
	public function isChangeable($steamObject){
		return true;
	}
}
?>