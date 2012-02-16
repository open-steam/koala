<?php
namespace PortletTopic\Commands;
class OrderCategory extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
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
		//$entryIndex = $this->entryIndex = $params["entryIndex"];
		$order = $this->order = $params["order"]; //up, down, first, last
		
		$portletObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $portletObjectId);
		$categorys = $portletObject->get_attribute("bid:portlet:content");
		
		$actPos = $categoryIndex;
		$lastPos = count($categorys)-1;
		$firstPos = 0;
		
		//order positions
		switch($this->order){
			case "up":
				//put category one up
				if($actPos==$firstPos) break;
				$tmp = $categorys[$actPos-1];
				$categorys[$actPos-1] = $categorys[$actPos];
				$categorys[$actPos] = $tmp;
				break;
			case "down":
				//put category one down
				if($actPos==$lastPos) break;
				$tmp = $categorys[$actPos+1];
				$categorys[$actPos+1] = $categorys[$actPos];
				$categorys[$actPos] = $tmp;
				break;
			case "first":
				//put category on the first position
				if($actPos==$firstPos) break;
				$categorysNew = array();
				$categorysNew[]=$categorys[$actPos]; //add selected category
				$catCount = 0;
				foreach ($categorys as $category){ //add other categorys
					if(intval($actPos)==$catCount){
						$catCount++;
						continue;
					}else{
						$categorysNew[]=$category;
						$catCount++;
					}
					
				}
				$categorys = $categorysNew;
				break;
			case "last":
				//put category on the last position
				if($actPos==$lastPos) break;
				$categorysNew = array();
				$catCount = 0;
				foreach ($categorys as $category){
					if(intval($actPos)==$catCount) {
						$catCount++;
						continue;
					}else{
						$categorysNew[]=$category;
						$catCount++;
					}
				}
				$categorysNew[]=$categorys[$actPos]; //add selected category
				$categorys = $categorysNew;
				break;
			default:;
		}
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