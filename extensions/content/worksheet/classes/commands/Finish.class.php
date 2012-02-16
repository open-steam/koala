<?php
namespace Worksheet\Commands;
class Finish extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$tpl = new \Worksheet\Template($this->id);


		$worksheet = new \Worksheet\Worksheet($this->id);
		
		$worksheet->validateRole("edit");
		
		
		
		if (!isset($_POST['confirm'])) {
		
			$tpl->display("Finish.template.html");
				
		} else {
			
			$worksheet->finish();
		
			$_SESSION[ "confirmation" ] = "Das Arbeitsblatt wurde abgegeben.";
			
			header("Location: ".PATH_URL."worksheet/Edit/".$worksheet->getId());
		
		}
		
		
		
		
		
		
		
		
		

		/* template output */
		$tpl->parse($frameResponseObject);

		/* page title */
		$frameResponseObject->setTitle($worksheet->getName());
		
		$frameResponseObject->setHeadline(array(
			array("name" => $worksheet->getName(), "link" => PATH_URL."worksheet/Edit/".$this->id),
			array("name" => "Arbeitsblatt abgeben")
		));
		
		
		return $frameResponseObject;
		
	}
}
?>