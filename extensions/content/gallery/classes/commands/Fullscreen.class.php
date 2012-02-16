<?php
namespace Gallery\Commands;
class Fullscreen extends \AbstractCommand implements \IFrameCommand {
	
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
		$objectId = $this->id;
		$frameResponseObject->setTitle("Galerie");
		$frameResponseObject = $this->getHtmlForFullScreen($frameResponseObject);
		return $frameResponseObject;
	}
	public function getHtmlForFullScreen(\FrameResponseObject $frameResponseObject){
		$objectId = $this->id;
		$content = '<img src='.PATH_URL.'download/image/'.$objectId.'/946/710"
          border="0"
          title="Vollbild"
         >';
		$widget = new \Widgets\RawHtml();
		$widget->setHtml($content);
		$frameResponseObject->addWidget($widget);
		return $frameResponseObject;
	}
}

?>