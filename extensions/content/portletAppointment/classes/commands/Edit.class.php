<?php
namespace PortletAppointment\Commands;
class Edit extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {

	private $params;
	private $id;
	private $content;
	private $dialog;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $params["portletId"];
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Bearbeiten von " . $object->get_attribute("OBJ_DESC"));

		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Überschrift");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget(new \Widgets\Clearer());

		$align = new \Widgets\ComboBox();
		$align->setLabel("Sortierung (Nach Startdatum)");
		$align->setOptions(array(
							array("name"=>"chronologisch aufsteigend: älteste Termine zuerst", "value"=>"earliest_first"),
							array("name"=>"chronologisch absteigend: jüngste Termine zuerst", "value"=>"latest_first")
								));
		$align->setData($object);
		$align->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:app:app_order"));
		$dialog->addWidget($align);

		$jsWrapper = new \Widgets\RawHtml();
		$jsWrapper->setPostJsCode("$('.widgets_label').css('width', '180px');$('.widgets_combobox > select').css('width', '300px');$('.widgets_textinput > input').css('width', '296px');");
		$dialog->addWidget($jsWrapper);

		$this->dialog = $dialog;
	}

	public function idResponse(\IdResponseObject $idResponseObject) {
		$idResponseObject->setContent($this->content);
		return $idResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject->setTitle("Portal");
		$frameResponseObject->setContent($this->content);
		return $frameResponseObject;
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($this->dialog);
		return $ajaxResponseObject;
	}
}
?>
