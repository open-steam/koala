<?php

namespace Forum\Commands;

class NewTopic extends \AbstractCommand implements \IAjaxCommand {

    private $id;
    private $params;

    public function validateData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        if (isset($this->params["id"])) {
            $this->id = $this->params["id"];
            return true;
        } else {
            return false;
        }
    }

    public function processData(\IRequestObject $requestObject) {
        
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $dialog = new \Widgets\Dialog();
        $dialog->setTitle("Erstelle neues Thema in »" . getCleanName($object) . "«");
        $ajaxForm = new \Widgets\AjaxForm();
        $ajaxForm->setSubmitCommand("CreateTopic");
        $ajaxForm->setSubmitNamespace("Forum");

        $ajaxForm->setHtml(<<<END
	<input type="hidden" name="id" value="{$this->id}">
	<div class="widgets_lable">Überschrift:</div>
	<div class="widgets_textinput"><input type="text" value="" name="title"></div><br clear="all">
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
						theme_advanced_buttons1 : "bold,italic,underline,|,bullist,numlist,|,image,link,unlink,|,forecolor,removeformat,|,undo,redo,pasteword",
						theme_advanced_buttons2 : ""
					}, mce_defaults));
	    		});
	

	</script>
END
        );
        $dialog->addWidget($ajaxForm);
        $dialog->setCloseButtonLabel(null);
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($dialog);
        $pollingDummy = new \Widgets\PollingDummy();

        $ajaxResponseObject->addWidget($pollingDummy);
        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        
    }

}

?>