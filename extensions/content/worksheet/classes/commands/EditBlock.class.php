<?php
namespace Worksheet\Commands;
class EditBlock extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $id;
	private $blockId;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
		isset($this->params[1]) ? $this->blockId = $this->params[1]: "";
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$tpl = new \Worksheet\Template($this->id);
		
		$tpl->loadEditor();
		
		$tpl->assign("BLOCK_ID", $this->blockId);
		
		
		$worksheet = new \Worksheet\Worksheet($this->id);
		
		$worksheet->validateRole("build");
		
		
		
		/* get current block object */
		$block = new \Worksheet\Block($this->blockId);

		
		
		
		if (!isset($_POST['name'])) {

			$tpl->assign("content", $block->getBuildEditHtml());

			$name = \Worksheet\Helper::htmlentitiesDeep($block->getName());

			$tpl->assign("name", $name);

			$tpl->display("EditBlock.template.html");

			
		} else {
			
			$block->saveBuildEdit($_POST);
			
			$_SESSION[ "confirmation" ] = "Die Aufgabe wurde gespeichert.";
			
			header("Location: ".PATH_URL."worksheet/Build/".$this->id);
			
		}
		
		
		
		
		
		/* template output */
		$tpl->parse($frameResponseObject);

		/* page title */
		$frameResponseObject->setTitle("Aufgabe bearbeiten");
		$frameResponseObject->setHeadline(array(
			array("name" => $worksheet->getName(), "link" => PATH_URL."worksheet/Build/".$this->id),
			array("name" => "Aufgabe bearbeiten")
		));
		
		
		return $frameResponseObject;
		
	}
}
?>