<?php
namespace Worksheet\Commands;
class Build extends \AbstractCommand implements \IFrameCommand {
	
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
		
		
		
		$tplBlocks = Array();
		
		/* get all blocks for this worksheet */
		$blocks = $worksheet->getBlocks();
		
		foreach ($blocks as $block) {

			$tplBlocks[] = Array(
				"id" => $block->getId(),
				"name" => $block->getName(),
				"content" => $block->getBuildViewHtml(),
				"type" => $block->getType(),
				"order" => $block->getOrder()
			);
			
		}
		

		if (count($blocks) == 0) {
			$tplBlocks = false;
		}		

		$tpl->assign("blocks", $tplBlocks);
		
		$tpl->display("Build.template.html");
				
				
		
		
		
		
		
		
		
		
		
		
		
		
		/* action bar */
		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(array(
			array("name"=>"Eine Aufgabe hinzufügen", "link"=>PATH_URL."worksheet/AddBlock/".$this->id),
			array("name"=>"Arbeitsblatt veröffentlichen", "link"=>PATH_URL."worksheet/Deploy/".$this->id)
		));
		$frameResponseObject->addWidget($actionBar);
		
		/* template output */
		$tpl->parse($frameResponseObject);

		/* page title */
		$frameResponseObject->setTitle($worksheet->getName());
		
		$frameResponseObject->setHeadline(array(
			array("name" => $worksheet->getName(), "link" => PATH_URL."worksheet/Build/".$this->id)
		));
		
		
		return $frameResponseObject;
		
	}
}
?>