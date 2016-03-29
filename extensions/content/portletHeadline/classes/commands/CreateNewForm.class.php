<?php

namespace PortletHeadline\Commands;

class CreateNewForm extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $content;
    private $dialog;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");

        $ajaxForm = new \Widgets\AjaxForm();
        $ajaxForm->setSubmitCommand("Create");
        $ajaxForm->setSubmitNamespace("PortletHeadline");

        $ajaxForm->setHtml(<<<END
<style type="text/css">
.attribute {
  clear: left;
  padding: 5px 2px 5px 2px;
}

.attributeName {
  float: left;
  padding-right: 20px;
  width: 80px;
}

.attributeNameRequired {
  float: left;
  padding-right: 20px;
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
  width:196px;
}

.attributeValueColumn {
  float: left;
  position: relative;
  text-align: center;
}
</style>
<input type="hidden" name="id" value="{$this->id}">

<div class="attribute">
	<div class="attributeName">Titel:</div>
	<div><input type="text" class="text" value="Überschrift" name="title"></div>
</div>

<div class="attribute">
	<div class="attributeName">Ausrichtung:</div>
<select size="1" name="alignment">
        <option value="left">Linksbündig</option>
        <option value="right">Rechtsbündig</option>
        <option value="center" selected>Zentriert</option>
</select>
</div>

<div class="attribute">
	<div class="attributeName">Größe:</div>
	<div><input type="number" class="text" value="15" name="sizeInput" min="1"></div>
  <input type="hidden" name="size" value="15">
  <script>$("input[name=\"sizeInput\"]").bind("keyup mouseup", function() { $("input[name=\"size\"]").val($("input[name=\"sizeInput\"]").val())});</script>
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
