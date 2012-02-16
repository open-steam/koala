<?php
namespace Portfolio\Commands;
class ManageArtefacts extends \AbstractCommand implements \IFrameCommand {

	private $portfolioId;
	private $job;
	private $facet;
	private $activity;
	private $index;
	private $params;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		//$this->params = $requestObject->getParams();
		$this->portfolioId = isset($_GET["portfolio"]) ? $_GET["portfolio"] : null;
		$this->job = isset($_GET["job"]) ? $_GET["job"] : null;
		$this->facet = isset($_GET["facet"]) ? $_GET["facet"] : null;
		$this->activity = isset($_GET["activity"]) ? $_GET["activity"] : null;
		$this->index = isset($_GET["index"]) ? $_GET["index"] : null;
		$this->params = array(
			"portfolioId" => $this->portfolioId,
			"job" => $this->job,
			"facet" => $this->facet,
			"activity" => $this->activity,
			"index" => $this->index
		);
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$actionBar = new \Widgets\ActionBar();
		$this->getExtension()->addJS();
						
		$actionBar->setActions(
			array(
				array(
					"name"=>\Portfolio::getInstance()->getText("newArtefacts"),
					"ajax"=>
					array(
						"onclick"=>
						array(
//							"command" => "UploadArtefactMessage",
							"command" => "newArtefactForm",
							"namespace" => "portfolio", 
							"params" => "", 
							"requestType" => "popup"
						)))));
//		array("name" => "Bild anfügen<img src=\"{$editIcon}\">", "command" => "EditMessageImage", "namespace" => "forum", "params" => "{'messageObjectId':'{$this->id}','forum':'{$forumId}'}", "type" => "popup");
			
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("Manage Artefacts"))));

		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(array("name"=>\Portfolio::getInstance()->getText("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"), array("name"=>\Portfolio::getInstance()->getText("Competences"), "link"=>$this->getExtension()->getExtensionUrl() . "ViewChart/"), array("name"=>\Portfolio::getInstance()->getText("Artefacts"), "link"=>$this->getExtension()->getExtensionUrl() . "ManageArtefacts/")));
		$tabBar->setActiveTab(2);




		$clearer = new \Widgets\Clearer();


		$loader = new \Widgets\Loader();
		$loader->setWrapperId("artefactsWrapper");
		$loader->setMessage("loading artefacts ...");
		$loader->setCommand("loadArtefacts");
		$loader->setParams($this->params);
		$loader->setElementId("artefactsWrapper");
		$loader->setType("updater");



		$frameResponseObject->setTitle("Manage Artefacts");
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