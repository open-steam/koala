<?php
namespace PortletSlideShow\Commands;

class CreateNewForm extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject){
		$this->params = $requestObject->getParams();
		$this->environmentId = $this->params["id"];
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");

		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->setSubmitCommand("Create");
		$ajaxForm->setSubmitNamespace("PortletSlideShow");
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
			<input type="hidden" name="id" value="{$this->environmentId}">

			<div class="attribute">
				<div class="attributeName">Name:</div>
				<div><input type="text" class="text" value="" name="title"></div>
			</div>
			<div class="attribute">
				<div class="attributeName">Galerie-ID:</div>
				<div><input type="text" class="text" value="" name="galleryid"></div>
			</div>
                        <div class="attribute">
				<div class="attributeName">Beschreibung anzeigen:</div>
				<div><input type="checkbox" class="text" value="" name="showDescription"></div>
			  <div><input type="hidden" class="text" value="false" name="showDescriptionHidden"></div>
			</div>
			<script type="text/javascript">
			$("input[name=\"showDescription\"]").bind("click", function() { if( $("input[name=\"showDescriptionHidden\"]").val() == "true"){ $("input[name=\"showDescriptionHidden\"]").val("false"); }else{ $("input[name=\"showDescriptionHidden\"]").val("true"); }});
			</script>
END
		);
		$ajaxResponseObject->addWidget($ajaxForm);
		$ajaxForm->setPostJsCode('setTimeout(function(){$("input:text:visible:first").focus();}, 1300);');
		return $ajaxResponseObject;
	}
}
?>