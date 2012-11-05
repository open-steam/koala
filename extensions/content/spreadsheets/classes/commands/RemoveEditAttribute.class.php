<?php
namespace Spreadsheets\Commands;

/**
 * This Command can be used with a HTTP request to remove the RT_EDIT attribute of the document  
 * with the given ID.
 * Must be used with authentication data in the URL.
 */
class RemoveEditAttribute extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;
	private $data;
	private $document;
	
	public function httpAuth(\IRequestObject $requestObject) {
		return true;
	}

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		if (isset($this->params[0])) {
			$this->id = $this->params[0];
			$this->document = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		}
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		//save the document and return an answer
		if ($this->document->get_attribute("OBJ_TYPE") == "document_spreadsheet") {
			$this->document->set_attribute("RT_EDIT", 0);
			echo "edit attribute removed from document $this->id";
		}
		else {
			echo "document $this->id is not a spreadsheet!";
		}
		
		die;
	}

}
?>