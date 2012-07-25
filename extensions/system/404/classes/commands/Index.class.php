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
		$content->setVariable("TITLE", "Objekt nicht gefunden");
		$content->setVariable("MESSAGE", "Das angeforderte Dokument/Objekt existiert in der Datenbank nicht.<br/><br/><a href=\"javascript:history.back();\">Zurück zum letzten Dokument.</a>");
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$rawHtml->setCss(\NotFound::getInstance()->readCss());
		$frameResponseObject->setTitle("Seite nicht gefunden (Fehler 404).");
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>