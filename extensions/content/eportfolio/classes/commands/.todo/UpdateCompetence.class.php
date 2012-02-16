<?php
namespace Portfolio\Commands;
class UpdateCompetence extends \AbstractCommand implements \IAjaxCommand {

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
		$this->competenceId = $this->params["competence"];
		$this->checked = $this->params["checked"];
		$this->artefactId = $this->params["artefactId"];
		$this->artefact = \Artefacts::getById($this->artefactId);
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$competence = \CompetenceRaster::getCompetenceById($this->competenceId);
		if ($this->checked == "true"){
			$this->artefact->addCompetenceString($this->competenceId);
		} else {
			$this->artefact->removeCompetenceString($this->competenceId);
		}
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;
	}
}
?>