<?php
namespace Worksheet\Commands;
class TeacherView extends \AbstractCommand implements \IFrameCommand {
	
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
		
		$worksheet->validateRole("edit");
		

		$tplBlocks = Array();
		
		/* get all blocks for this worksheet */
		$blocks = $worksheet->getBlocks();
		
		foreach ($blocks as $block) {

			$tplBlocks[] = Array(
				"id" => $block->getId(),
				"name" => $block->getName(),
				"type" => $block->getType(),
				"order" => $block->getOrder(),
				"content" => $block->getEditHtml($worksheet->getStatus())
			);

		}
		

		if (count($blocks) == 0) {
			$tplBlocks = false;
		}		

		$tpl->assign("blocks", $tplBlocks);

		
		$tpl->display("TeacherView.template.html");
				

		$sourceWorksheet = new \Worksheet\Worksheet($this->source);
		

		
		/* template output */
		$tpl->parse($frameResponseObject);
		


		/* page title */
		$frameResponseObject->setTitle($worksheet->getName());
		
		$frameResponseObject->setHeadline(array(
			array("name" => $sourceWorksheet->getName(), "link" => PATH_URL."worksheet/View/".$this->source),
			array("name" => "Arbeitsblätter korrigieren", "link" => PATH_URL."worksheet/CopyList/".$this->source),
			array("name" => $worksheet->getName())
		));
		
		
		return $frameResponseObject;
		
	}
}
?>