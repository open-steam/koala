<?php
namespace PortletTopic\Commands;
class EditTopicEntry extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {

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
		$categoryIndex = $params["categoryIndex"];
		$entryIndex = $params["entryIndex"];

		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$dialog = new \Widgets\Dialog();
		//$dialog->setTitle("Eigenschaften von Eintrag $entryIndex Kategorie $categoryIndex in " . $object->get_name());
		$dialog->setTitle("Link bearbeiten");

		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);

		$clearer = new \Widgets\Clearer();
		$titel = new \Widgets\TextInput();
		$description = new \Widgets\TextInput();
		$link = new \Widgets\TextInput();
		$newBrowser = new \Widgets\Checkbox();

		//set labels
		$titel->setLabel("Link-Text");
		$description->setLabel("Beschreibung");
		$link->setLabel("Link-Adresse");
		$newBrowser->setLabel("In neuem Tab öffnen");

		$titel->setData($object);
		$titel->setContentProvider(new AttributeDataProviderPortletTopicEntry($categoryIndex, $entryIndex, "title"));

		$description->setData($object);
		$description->setContentProvider(new AttributeDataProviderPortletTopicEntry($categoryIndex, $entryIndex, "description"));

		$link->setData($object);
		$link->setContentProvider(new AttributeDataProviderPortletTopicEntry($categoryIndex, $entryIndex, "link_url"));

		$titel->setPlaceholder("Beispiel-Link");
		$link->setPlaceholder("http://www.beispiel.de");
		$description->setPlaceholder("Dies ist ein Beispiel-Link");

		//checkbox
		$newBrowser->setData($object);
		$newBrowser->setCheckedValue("checked");
		$newBrowser->setUncheckedValue("");
		$newBrowser->setContentProvider(new AttributeDataProviderPortletTopicEntry($categoryIndex, $entryIndex, "link_target"));

		$dialog->addWidget($titel);
		$dialog->addWidget($clearer);
		$dialog->addWidget($link);
		$dialog->addWidget($clearer);
		$dialog->addWidget($description);
		$dialog->addWidget($clearer);
		$dialog->addWidget($newBrowser);
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
