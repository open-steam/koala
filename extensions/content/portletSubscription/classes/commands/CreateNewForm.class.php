<?php
namespace PortletSubscription\Commands;

class CreateNewForm extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	
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
  text-align: right;
  width: 100px;
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
<div class="attribute">
	<div class="attributeName">Typ:</div>
	<div>
            <select name="type">
                <option value="0">Unbegrenzt</option>
                <option value="604800">Zeitraum: 1 Woche</option>
                <option value="1209600">Zeitraum: 2 Wochen</option>
                <option value="1814400">Zeitraum: 3 Wochen</option>
                <option value="2419200">Zeitraum: 4 Wochen</option>
            </select>
        </div>
</div>
<div class="attribute">
	<div class="attributeName">Sortierung:</div>
	<div>
            <select name="sort">
                <option value="0">Frühe Neuigkeiten zuerst anzeigen</option>
                <option value="1">Späte Neuigkeiten zuerst anzeigen</option>
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