<?php
namespace Spreadsheets\Commands;

/**
 * This Command perpares the document with the given ID for realtime-editing
 * and shows the spreadsheet editor.
 */
class Index extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $document;
	private $write_access;
	private $NodeServer = SPREADSHEETS_RT_SERVER;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		
		if (isset($this->params[0])) {
			$user = $GLOBALS["STEAM"]->get_current_steam_user();
			$this->id = $this->params[0];
			$this->document = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			$this->write_access = $this->document->check_access_write($user);

			//see if the document already exists on the node.js server
			$response = file_get_contents("http://$this->NodeServer/doc/exists/$this->id");
			if ($response != "Document exists") {
				//set the document if it doesn't exist
				$this->setNodeDocument($this->document->get_content());
			}

			if ($this->write_access) {
				$this->document->set_attribute("RT_EDIT", 1);
			}
		}		
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$doc_title = "Tabelle " . $this->document->get_name();
		if (!$this->write_access) {
			$doc_title .= " (schreibgeschützt)";
		}
		$rawWidget = $this->displaySpreadsheet();
		$frameResponseObject->setTitle($doc_title);
		$frameResponseObject->setHeadline($doc_title);
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}
	
	private function displaySpreadsheet() {
		$spreadsheetExtension = \Spreadsheets::getInstance();

		// addJS did not work for some reason with theese files, so i put them 
		// into the asset directory as a workaround
		/*$spreadsheetExtension->addJS("jquery.sheet.js");
		$spreadsheetExtension->addJS("parser.js");
		$spreadsheetExtension->addJS("raphael-min.js");
		$spreadsheetExtension->addJS("g.raphael-min.js");*/

		$spreadsheetExtension->addJS();
		$spreadsheetExtension->addCSS();
		/*$spreadsheetExtension->addCSS("jquery-ui.css");
		$spreadsheetExtension->addCSS("jquery.sheet.css");
		$spreadsheetExtension->addCSS("jquery.colorPicker.css");*/
		
		$content = $spreadsheetExtension->loadTemplate("spreadsheets_index.template.html");
		$user_name = $GLOBALS["STEAM"]->get_login_user_name();
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$extension_url = $spreadsheetExtension->getExtensionUrl();
	
		//js files for jQuery.sheet
		$content->setCurrentBlock("BLOCK_SPREADSHEET_SCRIPTS");
		$content->setVariable("SCRIPT_SRC", $extension_url . "asset/js/jQuery.sheet/jquery.sheet.js");
		$content->parse("BLOCK_SPREADSHEET_SCRIPTS");
		
		$content->setCurrentBlock("BLOCK_SPREADSHEET_SCRIPTS");
		$content->setVariable("SCRIPT_SRC", $extension_url . "asset/js/jQuery.sheet/parser.js");
		$content->parse("BLOCK_SPREADSHEET_SCRIPTS");

		$content->setCurrentBlock("BLOCK_SPREADSHEET_SCRIPTS");
		$content->setVariable("SCRIPT_SRC", $extension_url . "asset/js/jQuery.sheet/raphael-min.js");
		$content->parse("BLOCK_SPREADSHEET_SCRIPTS");
		
		$content->setCurrentBlock("BLOCK_SPREADSHEET_SCRIPTS");
		$content->setVariable("SCRIPT_SRC", $extension_url . "asset/js/jQuery.sheet/g.raphael-min.js");
		$content->parse("BLOCK_SPREADSHEET_SCRIPTS");

		$content->setCurrentBlock("BLOCK_SPREADSHEET_SCRIPTS");
		$content->setVariable("SCRIPT_SRC", $extension_url . "asset/js/jQuery.sheet/jquery.colorPicker.min.js");
		$content->parse("BLOCK_SPREADSHEET_SCRIPTS");

		//german localization
		$content->setCurrentBlock("BLOCK_SPREADSHEET_SCRIPTS");
		$content->setVariable("SCRIPT_SRC", $extension_url . "asset/js/jQuery.sheet/lang_de.js");
		$content->parse("BLOCK_SPREADSHEET_SCRIPTS");

		//js files for ShareJS
		$content->setCurrentBlock("BLOCK_SPREADSHEET_SCRIPTS");
		$content->setVariable("SCRIPT_SRC", "http://$this->NodeServer/channel/bcsocket.js");
		$content->parse("BLOCK_SPREADSHEET_SCRIPTS");

		$content->setCurrentBlock("BLOCK_SPREADSHEET_SCRIPTS");
		$content->setVariable("SCRIPT_SRC", "http://$this->NodeServer/share/share.uncompressed.js");
		$content->parse("BLOCK_SPREADSHEET_SCRIPTS");
		
		$content->setCurrentBlock("BLOCK_SPREADSHEET_SCRIPTS");
		$content->setVariable("SCRIPT_SRC", "http://$this->NodeServer/share/json.uncompressed.js");
		$content->parse("BLOCK_SPREADSHEET_SCRIPTS");

		//CSS Files for jQuery.sheet
		$content->setCurrentBlock("BLOCK_SPREADSHEET_CSS");
		$content->setVariable("CSS_HREF", $extension_url . "asset/js/jQuery.sheet/jquery.sheet.css");
		$content->parse("BLOCK_SPREADSHEET_CSS");

		$content->setCurrentBlock("BLOCK_SPREADSHEET_CSS");
		$content->setVariable("CSS_HREF", $extension_url . "asset/js/jQuery.sheet/jquery-ui.css");
		$content->parse("BLOCK_SPREADSHEET_CSS");

		$content->setCurrentBlock("BLOCK_SPREADSHEET_CSS");
		$content->setVariable("CSS_HREF", $extension_url . "asset/js/jQuery.sheet/jquery.colorPicker.css");
		$content->parse("BLOCK_SPREADSHEET_CSS");
		
		//write document ID, document name and username in the script
		$content->setCurrentBlock("BLOCK_SHEET_NAME_SCRIPT");
		$content->setVariable("SHEET_ID", $this->document->get_id());
		$content->setVariable("SHEET_TITLE", $this->document->get_name());
		$content->setVariable("USER_NAME", $user_name);
		$content->setVariable("RT_SERVER", $this->NodeServer);
		if ($this->write_access) {
			$content->setVariable("SHEET_EDITABLE", "true");
		}
		else {
			$content->setVariable("SHEET_EDITABLE", "false");
		}
                
                $content->setVariable("SESSION_COOKIE_NAME", SESSION_NAME);
		
                
		$content->parse("BLOCK_SHEET_NAME_SCRIPT");

		//insert URLs for toolbar buttons
		$content->setCurrentBlock("BLOCK_SHEET_TOOLBAR");
		$content->setVariable("EXTENSION_URL", $extension_url);
		$content->setVariable("SHEET_ID", $this->document->get_id());
		$content->parse("BLOCK_SHEET_TOOLBAR");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		return $rawWidget;
	}

	// Sets the content for the document on the node.js server
	private function setNodeDocument($content) {
		//POST request to set the content
		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/json',
		        'content' => $content
		    )
		);
		$context = stream_context_create($opts);
		$response = file_get_contents("http://$this->NodeServer/doc/set/$this->id", false, $context);
	}
}
?>
