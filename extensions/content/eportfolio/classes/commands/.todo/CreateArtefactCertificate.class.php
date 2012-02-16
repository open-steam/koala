<?php
namespace Portfolio\Commands;
class CreateArtefactCertificate extends CreateArtefact{

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$user = \lms_steam::get_current_user();
		$description = strip_tags($this->params["desc"]);
		$name = strip_tags($this->params["name"]);
		
		$newArtefact = \ArtefactCertificate::create($name, $description);
		return parent::ajaxResponseNew($ajaxResponseObject, $newArtefact);
	}

}
?>