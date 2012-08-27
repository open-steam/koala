<?php
namespace NotAccess\Commands;
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
                $back = -1;
                $request_url = str_ireplace(PATH_URL, "", $_SERVER["HTTP_REFERER"]);
                if (substr($request_url, 0, strlen("signin")) === "signin") {
                    $back = -2;
                }
                
		header("HTTP/1.0 403 Not Found");
		$content = \NotAccess::getInstance()->loadTemplate("403-inline.template.html");
		$content->setVariable("TITLE", "Kein Zugriff");
		$content->setVariable("MESSAGE", "Sie haben keinen Zugriff auf dieses Dokument.<br/><br/><a href=\"javascript:history.go('$back');\">Zurück zum letzten Dokument.</a>");
                
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$rawHtml->setCss(\NotAccess::getInstance()->readCss());
		$frameResponseObject->setTitle("Sie haben keinen Zugriff (Fehler 403).");
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>