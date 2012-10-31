<?php
namespace Spreadsheets\Commands;

/**
 * This Command deletes the document with the given ID from sTeam and
 * from the node.js server
 */
class Delete extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $params, $id;
	//private $NodeServer = "192.168.63.1:8000";
	private $NodeServer = "10.205.155.132:8000";
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
		else {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		}
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject->setTitle("Tabellen");
		$frameResponseObject->setHeadline("Tabellen");
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($this->deleteSpreadsheet());
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$this->deleteSpreadsheet();
		$ajaxResponseObject->setStatus("ok");

		$path = PATH_URL;
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		closeDialog();
		location.href = '{$path}explorer/Index';
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}

	private function deleteSpreadsheet() {
		//delete from sTeam
		$document = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

		//delete from node.js server
		/*$response = file_get_contents("http://$this->NodeServer/doc/exists/$this->id");
		if ($response == "Document exists") {
			$response = file_get_contents("http://$this->NodeServer/doc/delete/$this->id");
		}
		return $response;*/
	}
}
?>
