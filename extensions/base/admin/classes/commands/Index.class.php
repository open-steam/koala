<?php
namespace Admin\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$html = "";
		if (isset($this->params[0]) && $this->params[0] != "") {
			$extension = \ExtensionMaster::getInstance()->getExtensionById($this->params[0]);
			
			$html =  $extension->getInfoHtml();
		} else {
			$content = \Admin::getInstance()->loadTemplate("ExtensionIndex.template.html");
			$extensions = \ExtensionMaster::getInstance()->getAllExtensions();
			foreach($extensions as $extension) {
				$content->setCurrentBlock("BLOCK_EXTERNSION");
				$content->setVariable("EXTERNSION_ID", $extension->getId());
				$content->setVariable("EXTERNSION_NAME", $extension->getName());
				$content->setVariable("EXTERNSION_ICON", "");
				$content->setVariable("EXTERNSION_VERSION", $extension->getVersion());
				$content->parse("BLOCK_EXTERNSION");
			}
			$html = $content->get();
		}
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->setTitle("Extension Information");
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
		
	}
}
?>