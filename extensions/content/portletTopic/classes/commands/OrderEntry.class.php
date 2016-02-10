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
		$categories = $portletObject->get_attribute("bid:portlet:content");
		$entries = $categories[$categoryIndex]["topics"];

		$lastPos = count($entries)-1;
		$lastPosCat = count($categories)-1;

		//order positions
		switch($this->order){
			case "up":
				if($entryIndex == 0){ //first entry in category: put it in the category above (before last item)
					$entriesAbove = $categories[$categoryIndex-1]["topics"];
					$lastPos = count($entriesAbove)-1;
					array_splice($entriesAbove, $lastPos, 0, array($entries[$entryIndex]));
					$categories[$categoryIndex-1]["topics"] = $entriesAbove;
					array_shift($entries);
				}
				else{ //put entry one up
					$tmp = $entries[$entryIndex-1];
					$entries[$entryIndex-1] = $entries[$entryIndex];
					$entries[$entryIndex] = $tmp;
				}
				break;
			case "down":
				if($entryIndex == $lastPos){ //last entry in category: put it in the category below (as second item)
					$entriesBelow = $categories[$categoryIndex+1]["topics"];
					array_splice($entriesBelow, 1, 0, array($entries[$entryIndex]));
					$categories[$categoryIndex+1]["topics"] = $entriesBelow;
					array_pop($entries);
				}
				else{ //put entry one down
					$tmp = $entries[$entryIndex+1];
					$entries[$entryIndex+1] = $entries[$entryIndex];
					$entries[$entryIndex] = $tmp;
				}
				break;
			case "first":
				if($categoryIndex == 0){ //put entry on the first position
					array_splice($entries, 0, 0, array($entries[$entryIndex]));
					unset($entries[$entryIndex+1]);
					$entries = array_values($entries);
				}
				else{ //put entry in the first category on the first position
					$entriesFirstCat = $categories[0]["topics"];
					array_splice($entriesFirstCat, 0, 0, array($entries[$entryIndex]));
					unset($entries[$entryIndex]);
					$entries = array_values($entries);
					$categories[0]["topics"] = $entriesFirstCat;
				}
				break;
			case "last":
				if($categoryIndex == $lastPosCat){ //put entry on the last position
					$lastPos = count($entries);
					array_splice($entries, $lastPos, 0, array($entries[$entryIndex]));
					unset($entries[$entryIndex]);
					$entries = array_values($entries);
				}
				else{ //put entry in the last category on the last position
					$entriesLastCat = $categories[$lastPosCat]["topics"];
					$lastPos = count($entriesLastCat);
					array_splice($entriesLastCat, $lastPos, 0, array($entries[$entryIndex]));
					unset($entries[$entryIndex]);
					$entries = array_values($entries);
					$categories[$lastPosCat]["topics"] = $entriesLastCat;
				}
				break;
			default:;
		}
		if(count($entries) == 0){
			$portletObject->set_attribute("bid:portlet:content",$categories);
			\ExtensionMaster::getInstance()->callCommand("DeleteCategory", "PortletTopic", array('portletId'=>$portletObjectId,'categoryIndex'=>$categoryIndex));
		}
		else{
			$categories[$categoryIndex]["topics"] = $entries;
			$portletObject->set_attribute("bid:portlet:content",$categories);
		}
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
