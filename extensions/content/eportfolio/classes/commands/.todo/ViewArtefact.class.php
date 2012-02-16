<?php
namespace Portfolio\Commands;
class ViewArtefact extends \AbstractCommand implements \IFrameCommand {

	private $artefactId;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->artefactId = $this->params[0]: "";
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$artefact = \Artefacts::getById($this->artefactId);

		$actionBar = new \Widgets\ActionBar();
		
		$actionBar->setActions(array(
		array(
			"link" => "javascript:history.back()","name" => "zurück"),
		array("name"=>\Portfolio::getInstance()->getText("Beleg hinzufügen"), "ajax"=>array(
						"onclick"=>
						array(
							"command"=>"UploadArtefactMessage", 
							"params"=>array("id" => $this->artefactId), 
							"requestType"=>"popup"
						))))
		);
			
			
		$breadcrumb = new \Widgets\Breadcrumb();
		//	$breadcrumb->setData(array(array("name"=>gettext("Manage Artefacts"))));
		//	$breadcrumb->setData(array($artefact->getRoom()));
		$breadcrumb->setData(array(array("name" => $artefact->getName())));


		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(
		array(
		array("name"=>\Portfolio::getInstance()->getText("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"),
		array("name"=>\Portfolio::getInstance()->getText("Competences"), "link"=>$this->getExtension()->getExtensionUrl() . "ViewChart/"),
		array("name"=>\Portfolio::getInstance()->getText("Artefacts"), "link"=>$this->getExtension()->getExtensionUrl() . "ManageArtefacts/"),
		array("name"=>$artefact->getName(), "link"=>"#")));
		$tabBar->setActiveTab(3);

		$clearer = new \Widgets\Clearer();

		/*
		 $loader = new \Widgets\Loader();
		 $loader->setWrapperId("artefactsWrapper");
		 $loader->setMessage("loading artefacts ...");
		 $loader->setCommand("loadArtefacts");
		 $loader->setParams(array());
		 $loader->setElementId("artefactsWrapper");
		 $loader->setType("updater");
		 */

		$boxTip = new \Widgets\Box();
		$boxTip->setTitle(\Portfolio::getInstance()->getText("Content of your Artefact"));
		$boxTip->setContent(
		$artefact->getName() .
			"<br>" . +$artefact->getDescription() 
		//			. "<br>Inhalt: " . $artefact->getContent()
		);
//		$content = "<pre>";
		$content = "";
//		$attributes = $artefact->get_attributes();
//		foreach ($attributes as $attribute) {
//			$content .= $attribute . "<br>";
//		}
		$content .= "Mime Type : " . $artefact->getMimeType() . "<br>";
//		$content .= "</pre>";
		$boxTip->setContent($content);
		
		$boxInfo = new \Widgets\Box();
		$boxInfo->setTitle(\Portfolio::getInstance()->getText("Last Changes"));
		$boxInfo->setContent("TODO: Keine Änderungen vorhanden");

		$boxDownload = new \Widgets\Box();
		$boxDownload->setTitle(\Portfolio::getInstance()->getText("Download Artefact"));
		$boxDownload->setContent("<a href>TODO: Download</a>");

		$boxDiscuss = new \Widgets\PortfolioViewBox();
		$boxDiscuss->setTitle(\Portfolio::getInstance()->getText("Discuss View"));
		$boxDiscuss->setTitleLink($this->getextension()->getExtensionUrl()."ViewChart/");
		$boxDiscuss->setContent("Discuss view shows what other have commeted about your artefacts.if needed this content  can be edited");
		$boxDiscuss->setButtons(array(array("name"=>"Edit", "link"=>"#"), array("name"=>"Manage Acess", "link"=>$this->getextension()->getExtensionUrl()."ManageArtefacts/")));



		$boxCompetences = new \Widgets\PortfolioViewBox();
		$boxCompetences->setTitle(\Portfolio::getInstance()->getText("Competences"));
		$htmlCompetences = "";
		$competences = $artefact->getCompetences();
		foreach ($competences as $competence){
			$htmlCompetences .= "<br>" . $competence->short . ": " . $competence->name;
		}
		$boxCompetences->setContent($htmlCompetences);
		$url = \ExtensionMaster::getInstance()->getUrlForObjectId($artefact->get_id(), "competences");
		$boxCompetences->setButtons(array(array("name"=>"Set Competences", "link"=>$url)));

		$frameResponseObject->setTitle("Manage Artefacts");
		$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget($tabBar);
		$frameResponseObject->addWidget($clearer);
		$frameResponseObject->addWidget($boxTip);
		$frameResponseObject->addWidget($clearer);
		$frameResponseObject->addWidget($boxInfo);
		$frameResponseObject->addWidget($clearer);
		$frameResponseObject->addWidget($boxDownload);
		$frameResponseObject->addWidget($clearer);
		$frameResponseObject->addWidget($boxCompetences);
		$frameResponseObject->addWidget($clearer);


		//$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>