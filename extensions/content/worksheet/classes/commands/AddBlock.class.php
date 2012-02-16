<?php
namespace Worksheet\Commands;
class AddBlock extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $id;
	private $blockType;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
		isset($this->params[1]) ? $this->blockType = $this->params[1]: false;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {

		$tpl = new \Worksheet\Template($this->id);

		
		
		$worksheet = new \Worksheet\Worksheet($this->id);

		$worksheet->validateRole("build");

		
		if (!$this->blockType) {
			
			/* show blocktype selection */
			
			$blockTypes = \Worksheet\Block::getBlockTypes();
			
			$tpl->assign("blockTypes", $blockTypes);
			
			$tpl->display("AddBlock.template.html");
			
		} else {
			
			/* create new block of specified type */
			
			$block = $worksheet->createBlock($this->blockType);
			
			header("Location: ".PATH_URL."worksheet/EditBlock/".$this->id."/".$block->getId());
			
		}
			
		
		
		
		
		/* template output */
		$tpl->parse($frameResponseObject);

		/* page title */
		$frameResponseObject->setTitle("Aufgabe bearbeiten");
		$frameResponseObject->setHeadline(array(
			array("name" => $worksheet->getName(), "link" => PATH_URL."worksheet/Build/".$this->id),
			array("name" => "Aufgabe hinzufügen")
		));
		
		
		return $frameResponseObject;
		
	}
}
?>