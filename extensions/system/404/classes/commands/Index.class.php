<?php
namespace NotFound\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {
	
	public function isGuestAllowed(\IRequestObject $iRequestObject) {
		return true;
	}
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		header("HTTP/1.0 404 Not Found");
		$content = \NotFound::getInstance()->loadTemplate("404-inline.template.html");
		$content->setVariable("TITLE", "Endstation Hbf");
		$content->setVariable("MESSAGE", "Sie haben das Ende erreicht. Hier war noch niemand zuvor. Nicht mal wir Entwickler.<br/><br/><a href=\"javascript:history.back();\">Der einzige Weg führt zurück.</a>");
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$rawHtml->setCss(\NotFound::getInstance()->readCss());
		$frameResponseObject->setTitle("Seite nicht gefunden (Fehler 404).");
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>