<?php
namespace Forum\Commands;

class NewReply extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $id;
	private $params;

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
		$objectId=$this->id;
		$steam= $GLOBALS["STEAM"];
		$steamId=$steam->get_id();            
		/** log-in user */
		$steamUser =  \lms_steam::get_current_user();
		/** id of the log-in user */
		$steamUserId = $steamUser instanceof \steam_user ? $steamUser->get_id() : 0;
		/** the current category object */
		$object = \steam_factory::get_object($steamId, $objectId);
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Füge Antwort hinzu »" . getCleanName($object) . "«");
		$dialog->setCloseButtonLabel(null);

		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->setSubmitCommand("ReplyTopic");
		$ajaxForm->setSubmitNamespace("Forum");


		$ajaxForm->setHtml(<<<END
	<input type="hidden" name="id" value="{$this->id}">
	<input type="hidden" name="forum" value="{$this->params["forum"]}">
	<div class="widgets_lable">Überschrift:</div>
	<div class="widgets_textinput"><input type="text" id="title" value="" name="title"></div><br clear="all">
	<div class="widgets_lable">Inhalt:</div>
	<div class="widgets_textarea"><textarea rows="10" style="width:100%" class="mce-small"  value="" name="content" id="content"></textarea><br clear="all">
	<script type="text/javascript">
		    		var mce_defaults = {
	    			mode : "specific_textareas",
	    			
	    			// General options
					theme : "advanced",
					content_css : "{PATH_URL}widgets/css/tinymce.css",
					skin: "o2k7",
					remove_linebreaks: false,
				    convert_urls : false,
				    verify_html: "false",
					language: "de",
					
					// Theme options
					theme_advanced_buttons3 : "",
					theme_advanced_buttons4 : "",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "none",
					theme_advanced_resizing : false,
					
	    	};
	    	
	    	
	    		load("mce", function() {
					tinyMCE.init($.extend({
						editor_selector: "mce-small", 
						plugins : "emotions,paste,noneditable",
						// Theme options
						theme_advanced_buttons1 : "bold,italic,underline,|,bullist,numlist,|,link,unlink,|,forecolor,removeformat,|,undo,redo,pasteword",
						theme_advanced_buttons2 : ""
					}, mce_defaults));
	    		});
	

	</script>
END
		);

		$dialog->addWidget($ajaxForm);

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
	}
}
?>