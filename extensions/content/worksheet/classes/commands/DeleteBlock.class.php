<?php
namespace Worksheet\Commands;
class DeleteBlock extends \AbstractCommand implements \IFrameCommand {
	
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


		
		$worksheet = new \Worksheet\Worksheet($this->id);
		
		$worksheet->validateRole("build");
		
		
		if (!isset($_POST['delete'])) {

			$block = new \Worksheet\Block($this->blockId);

			$tpl->assign("name", $block->getName());
			
			$tpl->assign("BLOCK_ID", $this->blockId);

			$tpl->display("DeleteBlock.template.html");
			
		} else {
			
			/* delete block */
			$worksheet->deleteBlock($this->blockId);
			
			$_SESSION[ "confirmation" ] = "Die Aufgabe wurde gelöscht.";
			
			header("Location: ".PATH_URL."worksheet/Build/".$this->id);
			
		}
		
		
		
		
		
		/* template output */
		$tpl->parse($frameResponseObject);

		/* page title */
		$frameResponseObject->setTitle("Aufgabe löschen");
		$frameResponseObject->setHeadline(array(
			array("name" => $worksheet->getName(), "link" => PATH_URL."worksheet/Build/".$this->id),
			array("name" => "Aufgabe löschen")
		));
		
		
		return $frameResponseObject;
		
	}
}
?>