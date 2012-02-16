<?php
namespace Portfolio\Commands;
class Dummy extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {

	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$newPortfolios = array();
		//create portfolios
		$newPortfolios = array();
		for ($j = 0; $j < 10; $j++) {
			$newPortfolios[] = \PortfolioModel::create("Portfolio " . rand(1000, 9999999));	
		}
		
		//create artefacts
		$newArtefacts = array();
		for ($i = 0; $i < 50; $i++) {
			$newArtefacts[] = \ArtefactModel::create("Artefact " . rand(1000, 99999999), "text content");
		}
		
		//add two random artefacts to every portfolio
		for ($k = 0; $k < 10; $k++) {
			$newPortfolios[$k]->addArtefact($newArtefacts[rand(0, 25)]);
			$newPortfolios[$k]->addArtefact($newArtefacts[rand(25, 49)]);
		}
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml("Dummy Portfolios und Belege erstellt");
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>