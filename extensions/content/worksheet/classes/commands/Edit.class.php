<?php
namespace Worksheet\Commands;
class Edit extends \AbstractCommand implements \IFrameCommand {
	
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

		
		if ($worksheet->getStatus() > 1) {
			$tpl->display("EditLocked.template.html");
		} else {
			$tpl->display("Edit.template.html");
		}	
				


		if ($worksheet->getStatus() == 1) {
			/* action bar */
			$actionBar = new \Widgets\ActionBar();
			$actionBar->setActions(array(
				array("name"=>"Arbeitsblatt abgeben", "link"=>"javascript: worksheet_finish()"),
				array("name"=>"Speichern", "link"=>"javascript: worksheet_save()")
			));
			$frameResponseObject->addWidget($actionBar);
		}
		
		/* template output */
		$tpl->parse($frameResponseObject);
		
		if ($worksheet->getStatus() == 1) {
			/* action bar */
			$actionBar = new \Widgets\ActionBar();
			$actionBar->setActions(array(
				array("name"=>"Arbeitsblatt abgeben", "link"=>"javascript: worksheet_finish()"),
				array("name"=>"Speichern", "link"=>"javascript: worksheet_save()")
			));
			$frameResponseObject->addWidget($actionBar);
		}

		/* page title */
		$frameResponseObject->setTitle($worksheet->getName());
		
		$frameResponseObject->setHeadline(array(
			array("name" => $worksheet->getName(), "link" => PATH_URL."worksheet/Edit/".$this->id)
		));
		
		
		return $frameResponseObject;
		
	}
}
?>