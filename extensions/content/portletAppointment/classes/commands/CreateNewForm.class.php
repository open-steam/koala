<?php
namespace PortletAppointment\Commands;
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
		$ajaxForm->setSubmitNamespace("PortletAppointment");

		$ajaxForm->setHtml(<<<END
<style type="text/css">
.attribute {
  clear: left;
  padding: 5px 2px 5px 2px;
	margin-left: -20px;
}

.attributeName {
  float: left;
  width: 180px;
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
  width: 150px;
}

.text{
	width:267px;
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
	<div><input type="text" class="text" value="Termine" name="title"></div>
</div>

<div class="attribute">
    <div class="attributeName">Sortierung (nach Startdatum):</div>
    <div>
    <select size="1">
        <option selected="" name="earliest_first" value="earliest_first">chronologisch aufsteigend: älteste Termine zuerst</option>
        <option name="latest_first" value="latest_first">chronologisch absteigend: jüngste Termine zuerst</option>
    </select>
    </div>
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
