<?php
namespace Spreadsheets\Commands;

/**
 * This Command creates a new spreadsheet with the members of the given course.
 * The first parameter is the semester and the second parameter the ID of the course.
 */
class CreateFromCourse extends \AbstractCommand implements \IFrameCommand {

	private $params, $group_course;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->group_course = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $this->params[0] . "." . $this->params[1]);
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject->setTitle("Tabellen");
		$frameResponseObject->setHeadline("Tabellen");
		$path = PATH_URL;
		$docId = $this->newSpreadsheet();
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml('<a href="' . $path . 'Spreadsheets/Index/' . $docId . '">Dokument</a> Erstellt');
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}

	private function newSpreadsheet() {
		$group = new \koala_group_course($this->group_course);
		$members = $group->get_learners();
		$users = array();
		
		foreach ($members as $m) {
			if ($m instanceof \steam_user) {
				$users[] = $m->get_full_name();
			}
		}
		$spreadsheetExtension = \Spreadsheets::getInstance();		
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$name = "List " . $group->get_course_name();
		$container = $user->get_workroom();

		$document = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $name, "", "application/json", $container);
		$document->set_attribute("OBJ_TYPE", "document_spreadsheet");
		$document->set_content($this->createJsonDocument($users));
		return $document->get_id();
	}

	private function createJsonDocument($members) {
		//creates a new spreadsheet document as a JSON-Object
        $sheets = array();
        $cols = array();
		$col_info = array();
		$rows = array();
		$users = array(''=>'');

		//have at least 3 rows in the table
		while ( count($members) < 3) {
			$members[] = '';
		}

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
