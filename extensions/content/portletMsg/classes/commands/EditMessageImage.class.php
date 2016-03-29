<?php

namespace PortletMsg\Commands;
class EditMessageImage extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {

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

		$oldImageId = $object->get_attribute("bid:portlet:msg:picture_id");

		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Meldungsbild bearbeiten");
		$dialog->setWidth(410);
		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);

		$emptyImageUrl = PATH_URL ."portletMsg/asset/empty.jpg";
		$ajaxUploader = new \Widgets\AjaxUploader();
		if ($oldImageId !== 0) {
			$imgWidget = new \Widgets\RawHtml();
			$imgWidget->setHtml("Um ein Bild hochzuladen ziehen sie eine Datei auf dieses Feld oder doppelklicken sie hier.<br><img id=\"uploaderImage\" src=\"". PATH_URL ."download/document/$oldImageId\"></img>");
			$ajaxUploader->setPreview($imgWidget);
		} else {
			$imgWidget = new \Widgets\RawHtml();
			$imgWidget->setHtml("Um ein Bild hochzuladen ziehen sie eine Datei auf dieses Feld oder doppelklicken sie hier.<br><img id=\"uploaderImage\" src=\"{$emptyImageUrl}\"></img>");
			$ajaxUploader->setPreview($imgWidget);
		}
		$ajaxUploader->setSizeLimit(return_bytes(ini_get('post_max_size')));
		$ajaxUploader->setNamespace("PortletMsg");
		$ajaxUploader->setCommand("UploadImage");
		$ajaxUploader->setDestId($object->get_id());
		$ajaxUploader->setMultiUpload(false);
		$ajaxUploader->setOnComplete("function(id, fileName, responseJSON){document.getElementById('uploaderImage').src = '" . PATH_URL . "download/document/' + responseJSON.oid; jQuery('#uploaderImage').addClass('saved')}");
		$dialog->addWidget($ajaxUploader);

		$radioButton = new \Widgets\RadioButton();
		$radioButton->setLabel("Bildposition");
		$radioButton->setOptions(array(array("name"=>"links", "value"=>"left"), array("name"=>"rechts", "value"=>"right"), array("name"=>"oben", "value"=>"top"), array("name"=>"unten", "value"=>"bottom")));
		$radioButton->setData($object);
		$radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:msg:picture_alignment"));
		$dialog->addWidget($radioButton);
		$dialog->addWidget(new \Widgets\Clearer());

		$sizeInput = new \Widgets\TextInput();
		$sizeInput->setLabel("Bildbreite");
		$sizeInput->setData($object);
		$sizeInput->setType("number");
		$sizeInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:msg:picture_width"));
		$dialog->addWidget($sizeInput);
		$dialog->addWidget(new \Widgets\Clearer());

		if ($oldImageId !== 0) {
			$button = array();
			$button["label"] = "Bild löschen";
			$button["js"] = "sendRequest('DeleteImage', {'id':{$object->get_id()}}, '', 'data', null, function() {document.getElementById('uploaderImage').src = '{$emptyImageUrl}'; jQuery('#uploaderImage').addClass('saved')}, 'PortletMsg');";
			$button["class"] = "negative";
			$buttons[0] = $button;
			$dialog->setButtons($buttons);
		}

		$this->dialog = $dialog;
		$dialog->setSaveAndCloseButtonForceReload(true);
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
