<?php
namespace Wiki\Commands;
class NewWikiForm extends \AbstractCommand implements \IAjaxCommand {
	
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
		$ajaxResponseObject->setStatus("ok");
		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->setSubmitCommand("Create");
		$ajaxForm->setSubmitNamespace("Wiki");
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

.attributeNameRequired {
  float: left;
  padding-right: 20px;
  text-align: right;
  font-weight: bold;
  width: 80px;
}

.attributeValue {
  float: left;
  width: 300px;
}

.attributeValue .text, .attributeValue textarea {
  wwidth: 100px;
}

.attributeValueColumn {
  float: left;
  position: relative;
  text-align: center;
}
</style>
<div class="attribute">
	<div class="attributeName">Name:</div>
	<div class="attributeValue"><input type="text" class="text" value="" name="title"></div>
</div>
<input type="hidden" name="id" value="{$this->id}">
END
);

		$ajaxResponseObject->addWidget($ajaxForm);
		return $ajaxResponseObject;
	}
}
?>