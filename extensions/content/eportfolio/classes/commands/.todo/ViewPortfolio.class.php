<?php
namespace Portfolio\Commands;
class ViewPortfolio extends \AbstractCommand implements \IFrameCommand {

	private $portfolioId;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->portfolioId = isset($this->params[0]) ? $this->params[0] : null;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$portfolio = \PortfolioModel::getById($this->portfolioId);
		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(
			array(
				array(
					"link" => "javascript:history.back()","name" => "zurück"),
					array("name"=>\Portfolio::getInstance()->getText("new"), "ajax"=>array("onclick"=>array("command"=>"newArtefact", "params"=>array(), "requestType"=>"popup"))))
				);	
					
			
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("Belege des Portfolios »" . $portfolio->getName() . "«"))));
		
		
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(array("name"=>\Portfolio::getInstance()->getText("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"), array("name"=>\Portfolio::getInstance()->getText("Portfolio"), "link"=>$this->getExtension()->getExtensionUrl() . "MyPortfolio/"), array("name"=>\Portfolio::getInstance()->getText("Shared Portfolios"), "link"=>$this->getExtension()->getExtensionUrl() . "SharedProfiles/")));
		$tabBar->setActiveTab(1);
		
		$clearer = new \Widgets\Clearer();
		
	
		$loader = new \Widgets\Loader();
		$loader->setWrapperId("artefactsWrapper");
		$loader->setMessage("loading artefacts ...");
		$loader->setCommand("loadArtefacts");
		$loader->setParams(array(0 => $this->portfolioId));
		$loader->setElementId("artefactsWrapper");
		$loader->setType("updater");
	
		
	
		$frameResponseObject->setTitle("Belege verwalten");
		$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget($tabBar);
		$frameResponseObject->addWidget($clearer);
		$frameResponseObject->addWidget($loader);
		
		
		//$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>