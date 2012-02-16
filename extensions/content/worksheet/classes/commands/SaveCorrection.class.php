<?php
namespace Worksheet\Commands;

class SaveCorrection extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $data;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->data = $this->params["data"];
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {


		$data = json_decode($this->data);
		
		if (is_array($data)) {
			
			$worksheet = new \Worksheet\Worksheet($this->id);

			$blocks = $worksheet->getBlocks();
			
			if ($blocks AND count($blocks) > 0) {
			
				$i = 0;
			
				foreach ($blocks as $block) {

					$d = Array();
					
					foreach ($data[$i] as $key => $value) {
						
						$d[$key] = $value;
						
					}
					
					$block->setCorrection($d);
					
				$i++;
			
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