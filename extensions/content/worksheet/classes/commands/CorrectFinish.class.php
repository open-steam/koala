<?php
namespace Worksheet\Commands;
class CorrectFinish extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
		isset($this->params[1]) ? $this->source = $this->params[1]: "";
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$tpl = new \Worksheet\Template($this->id);


		$worksheet = new \Worksheet\Worksheet($this->id);
		$sourceWorksheet = new \Worksheet\Worksheet($this->source);
		
		$worksheet->validateRole("edit");
		
		
		if (!isset($_POST['confirm'])) {
		
			$tpl->assign("SOURCE_ID", $this->source);
		
			$tpl->display("CorrectFinish.template.html");
				
		} else {
			
			$worksheet->correctionFinish((isset($_POST['lock']) AND $_POST['lock'] == "1"));
		
			$_SESSION[ "confirmation" ] = "Das Arbeitsblatt wurde zurückgegeben.";
			
			header("Location: ".PATH_URL."worksheet/CopyList/".$this->source);
		
		}
		
		
		
		
		
		
		
		
		

		/* template output */
		$tpl->parse($frameResponseObject);

		/* page title */
		$frameResponseObject->setTitle($worksheet->getName());
		
		$frameResponseObject->setHeadline(array(
			array("name" => $sourceWorksheet->getName(), "link" => PATH_URL."worksheet/View/".$this->source),
			array("name" => "Arbeitsblätter korrigieren", "link" => PATH_URL."worksheet/CopyList/".$this->source),
			array("name" => $worksheet->getName(), "link" => PATH_URL."worksheet/Correct/".$this->id."/".$this->source),
			array("name" => "Arbeitsblatt zurückgeben")
		));
		
		
		return $frameResponseObject;
		
	}
}
?>