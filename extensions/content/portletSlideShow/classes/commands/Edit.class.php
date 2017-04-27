<?php
namespace PortletSlideShow\Commands;

class Edit extends \AbstractCommand implements \IAjaxCommand {

	private $dialog;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $params["portletId"];

		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Bearbeiten von " . $object->get_attribute("OBJ_NAME"));

		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Name");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_NAME"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget(new \Widgets\Clearer());

		$folderInput = new \Widgets\TextInput();
		$folderInput->setLabel("Galerie-ID");
		$folderInput->setData($object);
		$folderInput->setContentProvider(\Widgets\DataProvider::attributeProvider("PORTLET_SLIDESHOW_GALERY_ID"));
		$dialog->addWidget($folderInput);
		$dialog->addWidget(new \Widgets\Clearer());

		$dateInput = new \Widgets\Checkbox();
		$dateInput->setLabel("Beschreibung anzeigen:");
		$dateInput->setData($object);
		$dateInput->setCheckedValue("true");
		$dateInput->setUncheckedValue("false");
		$dateInput->setContentProvider(\Widgets\DataProvider::attributeProvider("PORTLET_SLIDESHOW_SHOW_DESCRIPTION"));
		$dialog->addWidget($dateInput);

		$jsWrapper = new \Widgets\RawHtml();
		$jsWrapper->setPostJsCode("$('.widgets_label').css('width', '160px');");
		$dialog->addWidget($jsWrapper);

		$this->dialog = $dialog;
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($this->dialog);
		return $ajaxResponseObject;
	}
}
?>
