<?php
namespace Portfolio\Commands;
class ResetPortfolio extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {

	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$portfolios = \PortfolioModel::getMyPortfolios();
		$artefacts = \ArtefactModel::getAllArtefacts();
		
		foreach ($portfolios as $portfolio) {
			$portfolio->delete();
		}
		foreach ($artefacts as $artefact) {
			$artefact->delete();
		}
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml("E-Portfolio erfolgreich zurückgesetzt");
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>