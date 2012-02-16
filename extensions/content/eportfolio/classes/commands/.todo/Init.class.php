<?php
namespace Portfolio\Commands;
class Init extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {

	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
	
		if (!\PortfolioExtensionModel::init()) {
			$html .= "Portfolio Räume bereits initialisiert<br>";
		} else {
			$html .= "E-Portfolio erfolgreich initialisiert";
		}
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
		}
}
?>


