<?php
namespace PortletTopic\Commands;
class OrderEntry extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	
	private $order;
	private $categoryIndex;
	private $entryIndex;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		
		$portletObjectId = $this->portletObjectId = $params["portletObjectId"];
		$categoryIndex = $this->categoryIndex = $params["categoryIndex"];
		$entryIndex = $this->entryIndex = $params["entryIndex"];
		$order = $this->order = $params["order"]; //up, down, first, last
		
		$portletObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $portletObjectId);
		$categorys = $portletObject->get_attribute("bid:portlet:content");
		$entries = $categorys[$categoryIndex]["topics"];
		
		$actPos = $entryIndex;
		$lastPos = count($entries)-1;
		$firstPos = 0;
		
		//order positions
		switch($this->order){
			case "up":
				//put entry one up
				if($actPos==$firstPos) break;
				$tmp = $entries[$actPos-1];
				$entries[$actPos-1] = $entries[$actPos];
				$entries[$actPos] = $tmp;
				break;
			case "down":
				//put entry one down
				if($actPos==$lastPos) break;
				$tmp = $entries[$actPos+1];
				$entries[$actPos+1] = $entries[$actPos];
				$entries[$actPos] = $tmp;
				break;
			case "first":
				//put entry on the first position
				if($actPos==$firstPos) break;
				$entriesNew = array();
				$entriesNew[]=$entries[$actPos]; //add selected category
				$catCount = 0;
				foreach ($entries as $category){ //add other categorys
					if(intval($actPos)==$catCount){
						$catCount++;
						continue;
					}else{
						$entriesNew[]=$category;
						$catCount++;
					}
					
				}
				$entries = $entriesNew;
				break;
			case "last":
				//put entry on the last position
				if($actPos==$lastPos) break;
				$entriesNew = array();
				$catCount = 0;
				foreach ($entries as $category){
					if(intval($actPos)==$catCount) {
						$catCount++;
						continue;
					}else{
						$entriesNew[]=$category;
						$catCount++;
					}
				}
				$entriesNew[]=$entries[$actPos]; //add selected category
				$entries = $entriesNew;
				break;
			default:;
		}
		
		$categorys[$categoryIndex]["topics"] = $entries;
		$portletObject->set_attribute("bid:portlet:content",$categorys);
	}
	
	public function idResponse(\IdResponseObject $idResponseObject) {
		//no response
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		// no response
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		window.location.reload();
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
?>