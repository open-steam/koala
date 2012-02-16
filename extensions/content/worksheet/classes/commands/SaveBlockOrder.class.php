<?php
namespace Worksheet\Commands;

class SaveBlockOrder extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $order;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->order = $this->params["order"];
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

		$order = json_decode($this->order);

		if (is_array($order)) {
			
			$worksheet = new \Worksheet\Worksheet($this->id);

			$blocks = $worksheet->getBlocks();
			
			if ($blocks AND count($blocks) > 0) {
			
				foreach ($blocks as $block) {

					/* get position of current block in posted order */
					$key = array_search($block->getId(), $order);
					
					if ($key !== false) {
					
						/* use the found key for order attribute */
						$block->setOrder($key);
			
					} else {
						
						/* current blocks id was not found in posted order */
						$block->setOrder(-1);
						
					}
					
				}
			
			}

		}
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml("");
		$ajaxResponseObject->addWidget($rawHtml);
		
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;
	
	}
	
}
?>