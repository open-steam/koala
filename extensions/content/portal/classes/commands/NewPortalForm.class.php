<?php
namespace Portal\Commands;
class NewPortalForm extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
		$ajaxForm->setSubmitNamespace("Portal");

		$img_3_col = $this->getExtension()->getAssetUrl() . "images/portal_3_col.jpg";
		$img_2_col = $this->getExtension()->getAssetUrl() . "images/portal_2_col.jpg";
		$img_1_col = $this->getExtension()->getAssetUrl() . "images/portal_1_col.jpg";
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
	<div class="attributeName">Name:</div>
	<div class="attributeValue"><input type="text" class="text" value="" name="name" onkeyup="checkInput(this)"></div>
</div>
<div style="height:150px" class="attribute">
	<div class="attributeName">Spalten:</div>
	<div class="attributeValue">
		<div style="width:100px" class="attributeValueColumn">
				<input type="radio" checked="" value="3" name="columns">3<br>
				<img width="100" height="100" border="0" src="{$img_3_col}">
		</div>
		<div style="width:100px" class="attributeValueColumn">
				<input type="radio" value="2" name="columns">2<br>
				<img width="100" height="100" border="0" src="{$img_2_col}">
		</div>
		<div style="width:100px" class="attributeValueColumn">
				<input type="radio" value="1" name="columns">1<br>
				<img width="100" height="100" border="0" src="{$img_1_col}">
		</div>
	</div>
</div>
END
);

		$ajaxForm->setPostJsCode('setTimeout(function(){$("input:text:visible:first").focus();}, 1300);');

		$ajaxResponseObject->addWidget($ajaxForm);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {

	}
}
?>
