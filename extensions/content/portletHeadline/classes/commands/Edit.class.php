<?php
namespace PortletHeadline\Commands;
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

		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);

		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Überschrift");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([headline])"));
		$titelInput->setSuccessMethodForDataProvider("sendRequest('databinding', {'id': {$objectId}, 'attribute': 'OBJ_DESC', 'value': {$titelInput->getId()}}, '', 'data');");
		$dialog->addWidget($titelInput);
		$dialog->addWidget(new \Widgets\Clearer());

		$align =  new \Widgets\ComboBox();
		$align->setLabel("Ausrichtung");
		$align->setOptions(
			array(
				array("name"=>"Linksbündig", "value"=>"left"),
				array("name"=>"Rechtsbündig", "value"=>"right"),
				array("name"=>"Zentriert", "value"=>"center")
			)
		);
		$align->setData($object);
		$align->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([alignment])"));
		$dialog->addWidget($align);
		$dialog->addWidget(new \Widgets\Clearer());

		$size = new \Widgets\Textinput();
		$size->setLabel("Größe");
		$size->setType("number");
		$size->setData($object);
		$size->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([size])"));
		$dialog->addWidget($size);

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
