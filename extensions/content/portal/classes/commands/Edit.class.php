<?php
namespace Portal\Commands;
class Edit extends \AbstractCommand implements \IFrameCommand, \IIdCommand {

	private $params;
	private $id;
	private $content;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject){

		$objectId=$requestObject->getId();
		$portal= $portletObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $objectId );
		$steam = $GLOBALS["STEAM"];

		$portal_name = $portal->get_attribute(OBJ_DESC);

		$portalInstance = \Portal::getInstance();
		$portalPath = $portalInstance->getExtensionPath();
	
		$htmlBody = "Dummy output for properties command of portal";

		$this->content=$htmlBody;
	}

	public function idResponse(\IdResponseObject $idResponseObject) {
		$idResponseObject->setContent($this->content);
		return $idResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject->setTitle("Portal");
		$frameResponseObject->setContent($this->content);
		return $frameResponseObject;
	}

}
?>
