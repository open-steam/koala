<?php
namespace Portfolio\Commands;
class NewArtefact extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {
	
	private $params;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
		}
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$idRequestObject = new \IdRequestObject();
		
	/*	$extensions = \ExtensionMaster::getInstance()->getExtensionByType("IObjectExtension");
		$commands = array();

		foreach ($extensions as $extension) {
			$command = $extension->getCreateNewCommand($idRequestObject);
			if ($command) {
				$commands[] = $command;
			}
		}*/
		
		
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Create a new artefact.");
		
		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);
		
		$html = "<div style=\"margin-left: 20px; margin-right: 20px\">";
		
		$noteImageUrl = $this->getExtension()->getAssetUrl() . "images/note.gif";
		$fileImageUrl = $this->getExtension()->getAssetUrl() . "images/file.gif";
		$html .= "<a href=\"\" onclick=\"sendRequest('NewUploadForm', {}, 'wizard_wrapper', 'wizard');return false;\" title=\"Create an artefact from a file.\"><img src=\"{$fileImageUrl}\"> Artefact from file (e.g. document, image, ...)</a><br>";
		$html .= "<a href=\"\" onclick=\"sendRequest('NewTextForm', {}, 'wizard_wrapper', 'wizard');return false;\" title=\"Create a artefact with text.\"><img src=\"{$noteImageUrl}\"> Text </a><br>";
		
		
		$html .= "</div><div id=\"wizard_wrapper\"></div>";
		
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		
		$dialog->addWidget($rawHtml);
		
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
		$object = $currentUser->get_workroom();
		
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Eigenschaften von " . $object->get_name());
		
		$dialog->setContent("Nulla dui purus, eleifend vel, consequat non, <br>
							dictum porta, nulla. Duis ante mi, laoreet ut,  <br>
							commodo eleifend, cursus nec, lorem. Aenean eu est.  <br>
							Etiam imperdiet turpis. Praesent nec augue. Curabitur  <br>
							ligula quam, rutrum id, tempor sed, consequat ac, dui. <br>
							Vestibulum accumsan eros nec magna. Vestibulum vitae dui. <br>
							Vestibulum nec ligula et lorem consequat ullamcorper.  <br>
							Class aptent taciti sociosqu ad litora torquent per  <br>
							conubia nostra, per inceptos hymenaeos. Phasellus  <br>
							eget nisl ut elit porta ullamcorper. Maecenas  <br>
							tincidunt velit quis orci. Sed in dui. Nullam ut  <br>
							mauris eu mi mollis luctus. Class aptent taciti  <br>
							sociosqu ad litora torquent per conubia nostra, per  <br>
							inceptos hymenaeos. Sed cursus cursus velit. Sed a  <br>
							massa. Duis dignissim euismod quam. Nullam euismod  <br>
							metus ut orci. Vestibulum erat libero, scelerisque et,  <br>
							porttitor et, varius a, leo.");
		$dialog->setButtons(array(array("name"=>"speichern", "href"=>"save")));
		return $dialog->getHtml();
	}
}
?>