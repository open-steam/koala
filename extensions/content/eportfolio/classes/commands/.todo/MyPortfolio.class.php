<?php
namespace Portfolio\Commands;
class MyPortfolio extends \AbstractCommand implements \IFrameCommand {

	private $portfolioId;
	private $portfolioId1;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->portfolioId = isset($this->params[0]) ? $this->params[0] : null;
		$this->portfolioId1 = isset($this->params[1]) ? $this->params[1] : null;
	}


	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(array(
		array("name"=>\Portfolio::getInstance()->getText("new artefact"), "ajax"=>array("onclick"=>array("command"=>"newElement", "params"=>array("newElement"=>"newElement"), "requestType"=>"popup"))),
		array("name"=>\Portfolio::getInstance()->getText("new portfolio"), "ajax"=>array("onclick"=>array("command"=>"NewArtefactForm", "params"=>array(), "requestType"=>"popup")))
		));
			
			
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("Meine Portfolios"))));


		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(array("name"=>\Portfolio::getInstance()->getText("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"), array("name"=>gettext("Portfolio"), "link"=>$this->getExtension()->getExtensionUrl() . "myportfolio/"), array("name"=>\Portfolio::getInstance()->getText("Shared Portfolios"), "link"=>$this->getExtension()->getExtensionUrl() . "SharedProfiles/")));
		$tabBar->setActiveTab(1);

		$clearer = new \Widgets\Clearer();

		$globalArtefacsBox = new \Widgets\Box();
		$globalArtefacsBox->setTitle("Alle Belege");
		$globalArtefacsBox->setTitleLink($this->getextension()->getExtensionUrl()."ManageArtefacts/");
		$globalArtefacsBox->setContent(count(\Artefacts::getAllArtefacts()));
		/*
		 $artefactsLoader = new \Widgets\Loader();
		 $artefactsLoader->setWrapperId("artefactsWrapper");
		 $artefactsLoader->setMessage("loading artefacts ...");
		 $artefactsLoader->setCommand("loadArtefacts");
		 $artefactsLoader->setParams(array());
		 $artefactsLoader->setElementId("artefactsWrapper");
		 $artefactsLoader->setType("updater");
		 */



		$frameResponseObject->setTitle("Portfolio");
		$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget($tabBar);
		$frameResponseObject->addWidget($clearer);
		$frameResponseObject->addWidget($globalArtefacsBox);
		$frameResponseObject->addWidget($clearer);
		$portfolios = \PortfolioModel::getMyPortfolios();
		foreach ($portfolios as $key => $portfolio ) {
			$loader = new \Widgets\Loader();
			$loader->setWrapperId("portfolioWrapper".$key);
			$loader->setMessage("loading portfolio infos...");
			$loader->setCommand("loadPortfolio");
			$loader->setParams(array($portfolio->getId()));
			$loader->setElementId("portfolioWrapper".$key);
			$loader->setType("updater");
			$frameResponseObject->addWidget($loader);
		}



		//$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;

		/*
		 $actionBar = new \Widgets\ActionBar();
		 $actionBar->setActions(array(array("name"=>gettext("Profile"), "link"=>$this->getExtension()->getExtensionUrl() . "profile/"), array("name"=>gettext("Groups"), "link"=>$this->getExtension()->getExtensionUrl() . "groups/"), array("name"=>gettext("File Uploads"), "link"=>$this->getExtension()->getExtensionUrl()."Fileupload/"),array("name"=>gettext("Blogs"), "link"=>$this->getExtension()->getExtensionUrl() . "Blog/")));


		 $breadcrumb = new \Widgets\Breadcrumb();
		 $breadcrumb->setData(array(array("name"=>gettext("My Views"))));


		 $tabBar = new \Widgets\TabBar();
		 $tabBar->setTabs(array(array("name"=>gettext("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"), array("name"=>gettext("Portfolio"), "link"=>$this->getExtension()->getExtensionUrl() . "myportfolio/"), array("name"=>gettext("Shared Portfolios"), "link"=>$this->getExtension()->getExtensionUrl() . "SharedProfiles/")));
		 $tabBar->setActiveTab(1);

		 $clearer = new \Widgets\Clearer();

		 $boxManage = new \Widgets\PortfolioViewBox();
		 $boxManage->setTitle(gettext("Manage Artefacts"));
		 $boxManage->setTitleLink($this->getExtension()->getExtensionUrl() . "ManageArtefacts/");
		 $boxManage->setContent("this view helps in uploading and managing of desired artefacts");
		 //$boxManage->setButtons(array(array("name"=>"Edit", "link"=>$this->getextension()->getExtensionUrl()."ManageArtefacts/"), array("name"=>"Manage Acess", "link"=>'')));


		 $boxDiscuss = new \Widgets\PortfolioViewBox();
		 $boxDiscuss->setTitle(gettext("Discuss View"));
		 $boxDiscuss->setTitleLink($this->getextension()->getExtensionUrl()."ViewChart/");
		 $boxDiscuss->setContent("Discuss view shows what other have commeted about ur artefacts.if needed this content  can be edited");
		 $boxDiscuss->setButtons(array(array("name"=>"Edit", "link"=>"#"), array("name"=>"Manage Acess", "link"=>$this->getextension()->getExtensionUrl()."ManageArtefacts/")));

		 $boxcompetence = new \Widgets\PortfolioViewBox();
		 $boxcompetence->setTitle(gettext("Competence View"));
		 $boxcompetence->setTitleLink($this->getextension()->getExtensionUrl()."ViewCompetence/");
		 $boxcompetence->setContent(" Competence view shows the grading of artifacts on the basis of comments given by friends and other group members");
		 $boxcompetence->setButtons(array(array("name"=>"Edit", "link"=>"#"), array("name"=>"Manage Acess", "link"=>"#")));



		 $frameResponseObject->setTitle("Groups");
		 $rawHtml = new \Widgets\RawHtml();
		 $rawHtml->setHtml($html);
		 $frameResponseObject->addWidget($actionBar);
		 $frameResponseObject->addWidget($breadcrumb);
		 $frameResponseObject->addWidget($tabBar);
		 $frameResponseObject->addWidget($clearer);
		 $frameResponseObject->addWidget($boxManage);
		 $frameResponseObject->addWidget($boxDiscuss);
		 $frameResponseObject->addWidget($boxcompetence);

		 //$frameResponseObject->addWidget($rawHtml);
		 return $frameResponseObject;
		 */
	}
}
?>