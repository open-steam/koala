<?php
namespace Portfolio\Commands;
class ManagePortfolios extends \AbstractCommand implements \IFrameCommand {

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {

	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(array(array("name"=>\Portfolio::getInstance()->getText("new"), "ajax"=>array("onclick"=>array("command"=>"newArtefact", "params"=>array(), "requestType"=>"popup")))));	
			
			
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("Manage Portfolios"))));
		
		
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(array("name"=>\Portfolio::getInstance()->getText("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"), array("name"=>\Portfolio::getInstance()->getText("Portfolio"), "link"=>$this->getExtension()->getExtensionUrl() . "MyPortfolio/"), array("name"=>\Portfolio::getInstance()->getText("Shared Portfolios"), "link"=>$this->getExtension()->getExtensionUrl() . "SharedProfiles/")));
		$tabBar->setActiveTab(1);
		
		$clearer = new \Widgets\Clearer();
		
	
		$loader = new \Widgets\Loader();
		$loader->setWrapperId("portfoliosWrapper");
		$loader->setMessage("loading portfolios ...");
		$loader->setCommand("loadPortfolios");
		$loader->setParams(array());
		$loader->setElementId("portfoliosWrapper");
		$loader->setType("updater");
	
		
	
		$frameResponseObject->setTitle("Manage Portfolios");
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