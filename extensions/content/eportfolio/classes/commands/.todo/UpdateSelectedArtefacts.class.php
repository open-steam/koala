<?php
namespace Portfolio\Commands;
class UpdateSelectedArtefacts extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $artefactId;
	private $artefact;
	private $competenceId;
	private $checked;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->portfolioId = $this->params["portfolio"];
		$this->checked = $this->params["checked"];
		$this->artefactId = $this->params["artefactId"];
		$this->artefact = new \ArtefactModel(\steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->artefactId));
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$portfolio = \PortfolioModel::getById($this->portfolioId);
		if ($this->checked == "true"){
			$portfolio->addArtefact($this->artefact);
		} else {
			$portfolio->removeArtefact($this->artefact);
		}
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;
	}
}
?>