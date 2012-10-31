<?php
namespace Spreadsheets\Commands;
class CreateFromGroup extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $params, $groupId;
	private $NodeServer = "192.168.63.1:8000";
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->groupId = $this->params["id"]: "";
		}
		else {
			$group_course = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $this->params[0] . "." . $this->params[1]);
			$this->groupId = $group_course->get_id();
		}
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject->setTitle("Tabellen");
		$frameResponseObject->setHeadline("Tabellen");
		$path = PATH_URL;
		$docId = $this->newSpreadsheet("TEST");
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml('<a href="{$path}Spreadsheets/Index/{$docId}">Dokument</a>');
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$this->newSpreadsheet($this->params["title"]);
		$ajaxResponseObject->setStatus("ok");

		$path = PATH_URL;
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		closeDialog();
		location.href = '{$path}Spreadsheets/Index/{$this->id}';
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}

	private function newSpreadsheet($name) {
		$members = \lms_steam::group_get_members($this->groupId);
		$spreadsheetExtension = \Spreadsheets::getInstance();		
		$container = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $groupId);
		$document = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $name, "", "application/json", $container);
		$document->set_attribute("OBJ_TYPE", "document_spreadsheet");
		$document->set_content($this->createJsonDocument($members));
		return $document->get_id();
	}

	private function createJsonDocument($members) {
        $sheets = array();
        $cols = array();
		$col_info = array();
		$rows = array();
		$users = array(''=>'');

		$row_count = count($members);
		$col_count = 4;

		for ($col = 0; $col < $col_count; $col++) {
			$col_info[] = array('size' => 120);
		}
        
		for ($row = 0; $row < $row_count; $row++) {
			$cols = array();
			$cols[] = array('value' => $members[$row], 'style' =>  array(''=>''));
			for ($col = 1; $col < $col_count; $col++) {
				$cols[] = array('value' => "", 'style' =>  array(''=>''));
			}
			$rows[] = array('cells' => $cols, 'size' => 20);
		}
        
        
		$sheets[] = array('rows' => $rows, 'columns' => $col_info);

		return json_encode(array('sheets' => $sheets, 'users' => $users));
	}
}
?>