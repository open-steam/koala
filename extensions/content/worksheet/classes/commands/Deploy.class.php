<?php
namespace Worksheet\Commands;
class Deploy extends \AbstractCommand implements \IFrameCommand {
	
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
		
		$worksheet->validateRole("build");
		
		
		
		if (!isset($_POST['confirm'])) {
		
			$tpl->display("Deploy.template.html");
				
		} else {
			
			$newWorksheet = $worksheet->deploy();
		
			$_SESSION[ "confirmation" ] = "Das Arbeitsblatt wurde veröffentlicht.";
			
			header("Location: ".PATH_URL."worksheet/View/".$newWorksheet->getId());
		
		}
		
		
		
		
		
		
		
		
		
		
		/* action bar */
		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(array(
			array("name"=>"Eine Aufgabe hinzufügen", "link"=>PATH_URL."worksheet/AddBlock/".$this->id),
			array("name"=>"Arbeitsblatt verteilen", "link"=>PATH_URL."worksheet/Deploy/".$this->id)
		));
		$frameResponseObject->addWidget($actionBar);
		
		/* template output */
		$tpl->parse($frameResponseObject);

		/* page title */
		$frameResponseObject->setTitle($worksheet->getName());
		
		$frameResponseObject->setHeadline(array(
			array("name" => $worksheet->getName(), "link" => PATH_URL."worksheet/Edit/".$this->id)
		));
		
		
		return $frameResponseObject;
		
	}
}
?>