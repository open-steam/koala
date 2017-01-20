<?php
namespace PhotoAlbum\Commands;
class NewGallery extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->setSubmitCommand("Create");
		$ajaxForm->setSubmitNamespace("PhotoAlbum");
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
<br>
END
	);

		$ajaxForm->setPostJsCode('setTimeout(function(){$("input:text:visible:first").focus();}, 1300);');
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($ajaxForm);

		return $ajaxResponseObject;

	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$currentUser = \lms_steam::get_current_user();
		$object = $currentUser->get_workroom();

		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Eigenschaften");

		$dialog->setContent("Nulla dui purus, eleifend vel, consequat non, <br>
			dictum porta, nulla. Duis ante mi, laoreet ut,  <br>
			commodo eleifend, cursus nec, lorem. Aenean eu est.  <br>
			Etiam imperdiet turpis. Praesent nec augue. Curabitur  <br>
			ligula quam, rutrum id, tempor sed, consequat ac, dui. <br>
			Vestibulum accumsan eros nec magna. Vestibulum vitae dui. <br>
			Vestibulum nec ligula et lorem consequat ullamcorper.  <br>
			Class aptent taciti sociosqu ad litora torquent per  <br>
			conubia nostra, per inceptos hymenaeos. Phasellus  <br>
			eget nisl ut elit porta ullamcorper. Maecenas  <br>
			tincidunt velit quis orci. Sed in dui. Nullam ut  <br>
			mauris eu mi mollis luctus. Class aptent taciti  <br>
			sociosqu ad litora torquent per conubia nostra, per  <br>
			inceptos hymenaeos. Sed cursus cursus velit. Sed a  <br>
			massa. Duis dignissim euismod quam. Nullam euismod  <br>
			metus ut orci. Vestibulum erat libero, scelerisque et,  <br>
			porttitor et, varius a, leo."
		);
		$dialog->setButtons(array(array("name"=>"speichern", "href"=>"save")));
		return $dialog->getHtml();
	}
}
?>
