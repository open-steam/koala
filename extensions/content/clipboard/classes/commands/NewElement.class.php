<?php
namespace Clipboard\Commands;
class NewElement extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		
		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->setSubmitCommand("CreateFolder");
		$ajaxForm->setSubmitNamespace("Bookmarks");
		$ajaxForm->setHtml(<<<END
<style type="text/css">
.attribute {
  clear: left;
  padding: 5px 2px 5px 2px;
}

.attributeName {
  float: left;
  padding-right: 20px;
  text-align: right;
  width: 80px;
}

.attributeValue {
  float: left;
  width: 300px;
}

.attributeValue .text, .attributeValue textarea {
  width: 300px;
}

.attributeValueColumn {
  float: left;
  position: relative;
  text-align: center;
}

</style>
<input type="hidden" name="id" value="{$this->id}">
<div class="attribute">
	<div class="attributeName">Ordnername:</div>
	<div class="attributeValue"><input type="text" class="text" value="" name="name"></div>
</div>
<br>
END
);
		
		
		
		$ajaxResponseObject->setStatus("ok");
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Erstelle neuen Ordner in »" ."Lesezeichen". "«");
		$dialog->addWidget($ajaxForm);
		$dialog->setCancelButtonLabel(NULL);
                $dialog->setSaveAndCloseButtonLabel(null);
		$ajaxResponseObject->addWidget($dialog);
		
		//$idRequestObject = new \IdRequestObject();
		//$idRequestObject->setId($this->id);
		
		return $ajaxResponseObject;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
	}
}
?>