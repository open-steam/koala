<?php
namespace PortletMsg\Commands;
class CreateNewForm extends \AbstractCommand implements  \IAjaxCommand {

	private $params;
	private $id;
	private $content;
	private $dialog;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject){
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");

		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->setSubmitCommand("Create");
		$ajaxForm->setSubmitNamespace("PortletMsg");

		$ajaxForm->setHtml(<<<END
<style type="text/css">
.attribute {
  clear: left;
  padding: 5px 2px 5px 2px;
}

.attributeName {
  float: left;
  padding-right: 20px;
  width: 130px;
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
  width: 100px;
}

.text{
	width:142px;
}

.attributeValueColumn {
  float: left;
  position: relative;
  text-align: center;
}
</style>
<input type="hidden" name="id" value="{$this->id}">

<div class="attribute">
	<div class="attributeName">Überschrift:</div>
	<div><input type="text" class="text" value="Meldungen" name="title"></div>
</div>

<div class="attribute">
	<div class="attributeName">Sichtbare Meldungen:</div>
	<div><input type="number" value="10" name="numberOfMessages" min="1"></div>
  <input type="hidden" name="number" value="10">
  <script>$("input[name=\"numberOfMessages\"]").bind("keyup mouseup", function() { $("input[name=\"number\"]").val($("input[name=\"numberOfMessages\"]").val())});</script>
</div>

<div class="attribute">
	<div><input type="hidden" name="parent" value="{$this->id}"></div>
</div>

END
);
		$ajaxResponseObject->addWidget($ajaxForm);
		return $ajaxResponseObject;
	}
}
?>
