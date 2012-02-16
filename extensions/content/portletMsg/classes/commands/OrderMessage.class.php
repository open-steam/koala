<?php
namespace PortletMsg\Commands;
class OrderMessage extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	
	private $order;
	private $portletObjectId;
	private $messageObjectId;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$portletObjectId = $this->portletObjectId = $params["portletObjectId"];
		$messageObjectId = $this->messageObjectId = $params["messageObjectId"];
		$order = $this->order = $params["order"]; //up, down, first, last
		$portletObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $portletObjectId);
		
		$portletOrder = $portletObject->get_attribute("bid:portlet:content");
		
		$actPos = array_search($messageObjectId, $portletOrder);
		$lastPos = count($portletOrder)-1;
		$firstPos = 0;
		
		
		//order positions
		switch($this->order){
			case "up":
				//put portlet one up
				if($actPos==$firstPos) break;
				$tmp = $portletOrder[$actPos-1];
				$portletOrder[$actPos-1] = $portletOrder[$actPos];
				$portletOrder[$actPos] = $tmp;
				break;
			case "down":
				//put portlet one down
				if($actPos==$lastPos) break;
				$tmp = $portletOrder[$actPos+1];
				$portletOrder[$actPos+1] = $portletOrder[$actPos];
				$portletOrder[$actPos] = $tmp;
				break;
			case "first":
				//put portlet on the first position
				if($actPos==$firstPos) break;
				$portletOrderNew = array();
				$portletOrderNew[]=$portletOrder[$actPos];
				foreach ($portletOrder as $id) {
					if(intval($id)==intval($messageObjectId)) continue;
					$portletOrderNew[]=$id;
				}
				$portletOrder = $portletOrderNew;
				break;
			case "last":
				//put portlet on the last position
				if($actPos==$lastPos) break;
				$portletOrderNew = array();
				foreach ($portletOrder as $id) {
					if(intval($id)==intval($messageObjectId)) continue;
					$portletOrderNew[]=$id;
				}
				$portletOrderNew[]=$portletOrder[$actPos];
				$portletOrder = $portletOrderNew;
				break;
			default:;
		}
		
		$portletOrder = $portletObject->set_attribute("bid:portlet:content",$portletOrder);
		
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