<?php
namespace Portfolio\Commands;
class LoadPortfolio extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $portfolioModel;
	private $artefacts;
	private $portfolio;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->portfolio = \PortfolioModel::getById($this->params[0]);
		$this->artefacts = $this->portfolio->getArtefacts();
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$rawHtml = new \Widgets\RawHtml();

		$boxManage = new \Widgets\PortfolioViewBox();
		$boxManage->setTitle(\Portfolio::getInstance()->getText("Belege verwalten"));
		$boxManage->setTitleLink($this->getExtension()->getExtensionUrl() . "ManageArtefacts/");
		$boxManage->setContent("this view helps in uploading and managing of desired artefacts");
		//$boxManage->setButtons(array(array("name"=>"Edit", "link"=>$this->getextension()->getExtensionUrl()."ManageArtefacts/"), array("name"=>"Manage Acess", "link"=>'')));

		/*
		 $boxDiscuss = new \Widgets\PortfolioViewBox();
		 $boxDiscuss->setTitle(gettext("Discuss View"));
		 $boxDiscuss->setTitleLink($this->getextension()->getExtensionUrl()."ViewChart/");
		 $boxDiscuss->setContent("Discuss view shows what other have commeted about ur artefacts.if needed this content  can be edited");
		 $boxDiscuss->setButtons(array(array("name"=>"Edit", "link"=>"#"), array("name"=>"Manage Acess", "link"=>$this->getextension()->getExtensionUrl()."ManageArtefacts/")));
		 */
		$boxcompetence = new \Widgets\PortfolioViewBox();
		$boxcompetence->setTitle(\Portfolio::getInstance()->getText("Kompetenzansicht"));
		$boxcompetence->setTitleLink($this->getextension()->getExtensionUrl()."ViewCompetence/");
		$boxcompetence->setContent(" Competence view shows the grading of artifacts on the basis of comments given by friends and other group members");
		$boxcompetence->setButtons(array(array("name"=>"Edit", "link"=>"#"), array("name"=>"Manage Acess", "link"=>"#")));

		//TODO
		//		$box = new \Widgets\Box();
		//		$box->addWidget($boxManage);
		//		$box->addWidget($boxDiscuss);
		//		$box->addWidget($boxcompetence);
		$html ="";
		$html .= <<<END
    <div class="box" style="float: left; width: 316px;">
    <h3>{$this->portfolio->getName()}</h3>
    <br>
END
		;
		//		$html .= $box->getHtml();
		$html .= $boxManage->getHtml();
		//		$html .= $boxDiscuss->getHtml();
		$html .= $boxcompetence->getHtml();
		$html .= <<<END
    </div>
END
		;


		$rawHtml->setHtml($html);
		//$rawHtml->addWidget($box);
		$rawHtml->addWidget($boxManage);
		$rawHtml->addWidget($boxcompetence);

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($rawHtml);
		return $ajaxResponseObject;
	}
}
?>