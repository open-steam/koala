<?php

namespace Gallery\Commands;

class Delete extends \AbstractCommand implements \IFrameCommand {
	
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
		$frameResponseObject->setTitle("Galerie");
		$frameResponseObject = $this->getHtmlForDelete($frameResponseObject);
		return $frameResponseObject;
	}
	public function getHtmlForDelete(\FrameResponseObject $frameResponseObject){
		$objectId = $this->id;
		//get steam user
		$steam = $GLOBALS["STEAM"];
		$steamId=$steam->get_id();
		$image = ($objectId != 0)?\steam_factory::get_object($steamId, $objectId):0;
		$steamUser =  $steam->get_current_steam_user();
		//Check rights
		$readable = $image->check_access_read( $steamUser );
		$writable = $image->check_access_write( $steamUser );
		//get trashbin and move object
		$trash = $steamUser->get_attribute(USER_TRASHBIN, 0);
		if (is_object($trash)) {
			$image->move($trash, 1);
			$steam->buffer_flush();
		}
		$content = "<script type=\"text/javascript\">history.back();</script>";
		$widget = new \Widgets\RawHtml();
		$widget->setHtml($content);
		$frameResponseObject->addWidget($widget);
		return $frameResponseObject;
		
	}
}

?>
