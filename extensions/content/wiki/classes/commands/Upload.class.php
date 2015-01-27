<?php
namespace Wiki\Commands;
class Upload extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		// display upload image dialog
		$container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Bilder hinzufügen");
		$dialog->setWidth("410");
		$dialog->setSaveAndCloseButtonForceReload(true);
			
		$upload = new \Widgets\AjaxUploader();
		$upload->setNamespace("explorer");
		$upload->setDestId($container->get_id());
		$dialog->addWidget($upload);
			
		$ajaxResponseObject->addWidget($dialog);
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;
	}
}
?>