<?php

namespace Questionnaire\Commands;
class EditMessageImage extends \AbstractCommand implements \IAjaxCommand {

	//TODO: never used

	private $params;
	private $dialog;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();

		$objectId = $params["id"];
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);

		$oldImageId = $object->get_attribute("bid:rfb:picture_id");

		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Meldungsbild bearbeiten");

		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);
		//$dialog->setWidth(450);

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
		$ajaxUploader->setNamespace("Questionnaire");
		$ajaxUploader->setCommand("UploadImage");
		$ajaxUploader->setDestId($object->get_id());
		$ajaxUploader->setMultiUpload(false);
		$ajaxUploader->setOnComplete("function(id, fileName, responseJSON){document.getElementById('uploaderImage').src = '" . PATH_URL . "download/document/' + responseJSON.oid; jQuery('#uploaderImage').addClass('saved')}");

		$dialog->addWidget($ajaxUploader);
		$raw = new \Widgets\RawHtml();
		$raw->setHtml(<<<END
		<a href="#" class="button pill negative" onclick="sendRequest('DeleteImage', {'id':{$object->get_id()}}, '', 'data', null, function() {document.getElementById('uploaderImage').src = '{$emptyImageUrl}'; jQuery('#uploaderImage').addClass('saved')}, 'Questionnaire');">Bild löschen</a>
END
);
		$dialog->addWidget($raw);
		$dialog->addWidget(new \Widgets\Clearer());

	/*	$radioButton = new \Widgets\RadioButton();
		$radioButton->setLabel("Bildposition");
		$radioButton->setOptions(array(array("name"=>"links", "value"=>"left"), array("name"=>"nicht umfließend", "value"=>"none"), array("name"=>"rechts", "value"=>"right")));
		$radioButton->setData($object);
		$radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:rfb:picture_alignment"));
		$dialog->addWidget($radioButton); */
		$dialog->addWidget(new \Widgets\Clearer());

		$sizeInput = new \Widgets\TextInput();
		$sizeInput->setLabel("Bildbreite");
		$sizeInput->setData($object);
		$sizeInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:rfb:picture_width"));
		$dialog->addWidget($sizeInput);
		$dialog->setSaveAndCloseButtonForceReload(false);
                $dialog->setCloseJs( "setTimeout(function(){jQuery('#save-que-button').click(); }, 750);" );

		$this->dialog = $dialog;
	}



	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");

		$ajaxResponseObject->addWidget($this->dialog);
		return $ajaxResponseObject;
	}
}
?>
