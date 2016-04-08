<?php
namespace PortletFolderList\Commands;

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
		$ajaxForm->setSubmitNamespace("PortletFolderList");
		$ajaxForm->setHtml(<<<END

			<style type="text/css">
			.attribute {
  			clear: left;
  			padding: 5px 2px 5px 2px;
			}

			.attributeName {
  			float: left;
  			padding-right: 20px;
  			width: 180px;
			}

			.attributeNameRequired {
  			float: left;
  			padding-right: 20px;
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
				<div class="attributeName">Name:</div>
				<div><input type="text" class="text" value="" name="title"></div>
			</div>
			<div class="attribute">
				<div class="attributeName">Ordner-ID:</div>
				<div><input type="text" class="text" value="" name="folderid"></div>
			</div>
			<div class="attribute">
				<div class="attributeName">Sichtbare Objekte:</div>
				<div><input type="number" value="10" name="numberOfObjects" min="1"></div>
			  <input type="hidden" name="number" value="10">
			  <script>$("input[name=\"numberOfObjects\"]").bind("keyup mouseup", function() { $("input[name=\"number\"]").val($("input[name=\"numberOfObjects\"]").val())});</script>
			</div>
			<div class="attribute">
				<div class="attributeName">Änderungsdatum anzeigen:</div>
				<div><input type="checkbox" class="text" value="" name="showChangeDate"></div>
			  <div><input type="hidden" class="text" value="false" name="showChangeDateHidden"></div>
			</div>
			<script type="text/javascript">
			$("input[name=\"showChangeDate\"]").bind("click", function() { if( $("input[name=\"showChangeDateHidden\"]").val() == "true"){ $("input[name=\"showChangeDateHidden\"]").val("false"); }else{ $("input[name=\"showChangeDateHidden\"]").val("true"); }});
			</script>
END
		);
		$ajaxResponseObject->addWidget($ajaxForm);
		$ajaxForm->setPostJsCode('setTimeout(function(){$("input:text:visible:first").focus();}, 1300);');
		return $ajaxResponseObject;
	}
}
?>
