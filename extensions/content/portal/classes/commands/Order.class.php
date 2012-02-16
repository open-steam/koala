<?php
namespace Portal\Commands;
class Order extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	private $order;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $this->objectId = $params["portletId"];
		$order = $this->order = $params["order"]; //up, down, first, last
		
		$steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$columnObject = $steamObject->get_environment();
		$columnInventory = $columnObject->get_inventory();
		
		
		$columnInventoryIds = array();
		foreach($columnInventory as $steamObject){
			$columnInventoryIds[]=$steamObject->get_id();
		}
		
		$actPos = array_search($objectId, $columnInventoryIds);
		$lastPos = count($columnInventoryIds)-1;
		$firstPos = 0;
		
		//order positions
		switch($this->order){
			case "up":
				//put portlet one up
				if($actPos==$firstPos) break;
				$tmp = $columnInventoryIds[$actPos-1];
				$columnInventoryIds[$actPos-1] = $columnInventoryIds[$actPos];
				$columnInventoryIds[$actPos] = $tmp;
				break;
			case "down":
				//put portlet one down
				if($actPos==$lastPos) break;
				$tmp = $columnInventoryIds[$actPos+1];
				$columnInventoryIds[$actPos+1] = $columnInventoryIds[$actPos];
				$columnInventoryIds[$actPos] = $tmp;
				break;
			case "first":
				//put portlet on the first position
				if($actPos==$firstPos) break;
				$columnInventoryIdsNew = array();
				$columnInventoryIdsNew[]=$columnInventoryIds[$actPos];
				foreach ($columnInventoryIds as $id) {
					if(intval($id)==intval($objectId)) continue;
					$columnInventoryIdsNew[]=$id;
				}
				$columnInventoryIds = $columnInventoryIdsNew;
				break;
			case "last":
				//put portlet on the last position
				if($actPos==$lastPos) break;
				$columnInventoryIdsNew = array();
				foreach ($columnInventoryIds as $id) {
					if(intval($id)==intval($objectId)) continue;
					$columnInventoryIdsNew[]=$id;
				}
				$columnInventoryIdsNew[]=$columnInventoryIds[$actPos];
				$columnInventoryIds = $columnInventoryIdsNew;
				break;
			default:;
		}
		
		$columnObject->order_inventory_objects($columnInventoryIds);
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