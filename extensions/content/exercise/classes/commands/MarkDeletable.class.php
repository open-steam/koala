<?php
namespace Exercise\Commands;
class MarkDeletable extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
	}
	
	/**
	 * markDeletableCommand($id)
	 * 
	 * marks the object as ready to delete, but does not delete immediately in case it
	 * should be restored later.
	 * 
	 * @param int $id id of the file
	 * @return array result of the action
	 */
	public static function markDeletableCommand($id) {
		
		//in case there was no file in the uploader:
		if ( $id == "noop" ) return array('success' => true);
		
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		
		$object->set_attribute("DELETEFLAG", "TRUE");
		$object->set_attribute("ORIG_NAME", $object->get_name());
		$object->set_name("DELETEME", 0);
		
		return array('success' => true);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		
		$response = self::markDeletableCommand($_GET["steamid"]);
		
		echo htmlspecialchars(json_encode($response), ENT_NOQUOTES);
		die;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
	}
}
?>