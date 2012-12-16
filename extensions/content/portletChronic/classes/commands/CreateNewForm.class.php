<?php
namespace PortletChronic\Commands;
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
		$ajaxForm->setSubmitNamespace("PortletChronic");
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
  width: 180px;
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

.attributeValueColumn {
  float: left;
  position: relative;
  text-align: center;
}
</style>
<input type="hidden" name="id" value="{$this->id}">

<div class="attribute">
	<div class="attributeName">Anzahl der angezeigten Objekte:</div>
	<div><input type="text" class="text" value="" name="elements"></div>
</div>
END
);
		$ajaxResponseObject->addWidget($ajaxForm);
                $ajaxForm->setPostJsCode('setTimeout(function(){$("input:text:visible:first").focus();}, 1300);');
		return $ajaxResponseObject;
	}
}
?>