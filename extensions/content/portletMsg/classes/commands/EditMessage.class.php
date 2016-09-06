<?php
namespace PortletMsg\Commands;
class EditMessage extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {

	private $params;
	private $id;
	private $content;
	private $dialog;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $params["messageObjectId"];

		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Meldung bearbeiten");

		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);
		$dialog->setWidth(480);
		$clearer = new \Widgets\Clearer();

		$title = new \Widgets\TextInput();
		$title->setLabel("Überschrift");
		$title->setData($object);
		$title->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_NAME"));
		$dialog->addWidget($title);
		$dialog->addWidget($clearer);
		$dialog->addWidget($clearer);

		$subTitle = new \Widgets\TextInput();
		$subTitle->setLabel("Untertitel");
		$subTitle->setData($object);
		$subTitle->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
		$dialog->addWidget($subTitle);
		$dialog->addWidget($clearer);
		$dialog->addWidget($clearer);

		$content = new \Widgets\Textarea();
		$content->setLabel("Inhalt");
		$content->setTextareaClass("mce-small");
		$content->setWidth(330);
		$content->setData($object);
		$content->setContentProvider(\Widgets\DataProvider::contentProvider());
		$dialog->addWidget($content);
		$dialog->addWidget($clearer);

		$linkText = new \Widgets\TextInput();
		$linkText->setLabel("Link-Text");
		$linkText->setData($object);
		$linkText->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:msg:link_url_label"));
		$dialog->addWidget($linkText);
		$dialog->addWidget($clearer);
		$dialog->addWidget($clearer);

		$linkAdress = new \Widgets\TextInput();
		$linkAdress->setLabel("Link-Adresse");
		$linkAdress->setData($object);
		$linkAdress->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:msg:link_url"));
		$dialog->addWidget($linkAdress);
		$dialog->addWidget($clearer);
		$dialog->addWidget($clearer);

		$newTab = new \Widgets\Checkbox();
		$newTab->setLabel("In neuem Tab öffnen:");
		$newTab->setData($object);
		$newTab->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:msg:link_open"));
		$newTab->setCheckedValue("checked");
		$newTab->setUncheckedValue("");
		$dialog->addWidget($newTab);

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
