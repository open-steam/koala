<?php
namespace PortletPoll\Commands;

class AttributeDataProviderPortletPollVotes{
	
	private $voteIndex;
	private $field;
	
	public function __construct($voteIndex=0, $field="votes") {
		$this->voteIndex = $voteIndex;
		$this->field = $field;
	}
	
	
	public function getData($object) {
		if (is_int($object)){
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		}
		if ($object instanceof \steam_object) {
			$portletContent = $object->get_attribute("bid:portlet:content");
			
			$optionsDescription = $portletContent["options"];
			$optionsVoteCount = $portletContent["options_votecount"];
			
			switch($this->field){
				case "votes": return $optionsVoteCount[$this->voteIndex];
				case "description": return $optionsDescription[$this->voteIndex];
			}
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
sendRequest('DatabindingPortletPollVotes', {'id': {$objectId}, 'voteIndex': '{$this->voteIndex}', 'field': '{$this->field}', 'value': value}, '', 'data'{$function}, '', 'PortletPoll');
END;
	}
	
	
	public function isChangeable($steamObject){
		return true;
	}
}
?>