<?php

namespace PortletSubscription\Commands;

class CreateNewForm extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

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
        $ajaxForm->setSubmitNamespace("PortletSubscription");
        $ajaxForm->setHtml(<<<END
<style type="text/css">
.attribute {
  clear: left;
  padding: 5px 2px 5px 2px;
}

.attributeName {
  float: left;
  padding-right: 20px;
  width: 150px;
}

.attributeNameRequired {
  float: left;
  padding-right: 20px;
  text-align: right;
  font-weight: bold;
  width: 180px;
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
	<div><input type="text" class="text" value="" name="title"></div>
</div>
<div class="attribute">
	<div class="attributeName">Objekt ID:</div>
	<div><input type="text" class="text" value="" name="objectid"></div>
</div>
<!-- <div class="attribute">
	<div class="attributeName">Änderungen im Zeitraum:</div>
	<div>
            <select name="type">
                <option value="604800">1 Woche</option>
                <option value="1209600">2 Wochen</option>
                <option value="1814400">3 Wochen</option>
                <option value="2419200">4 Wochen</option>
                <option value="0">Unbegrenzt</option>
            </select>
        </div>
</div>
Disabled time-limited notifications because if you hide all notifications, the filter is emptied and the timestamp is set to the current date.
But this timestamp is ignored if the timeframe is limited and thus already filtered notifications would be displayed
-->
<input type="hidden" name="type" value="0">


<div class="attribute">
	<div class="attributeName">Sortierung:</div>
	<div>
            <select name="sort">
                <option value="0">Älteste Änderungen zuerst anzeigen</option>
                <option value="1">Neueste Änderungen zuerst anzeigen</option>
            </select>
        </div>
</div>
END
        );
        $ajaxResponseObject->addWidget($ajaxForm);
        $ajaxForm->setPostJsCode('setTimeout(function(){$("input:text:visible:first").focus();}, 1300);');
        return $ajaxResponseObject;
    }

}

?>
