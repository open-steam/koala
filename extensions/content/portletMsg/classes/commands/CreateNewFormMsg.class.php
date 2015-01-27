<?php

namespace PortletMsg\Commands;

class CreateNewFormMsg extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["portletObjectId"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

        $ajaxResponseObject->setStatus("ok");
        $dialog = new \Widgets\Dialog();
        $dialog->setTitle("Einfügen einer Meldung");
        $dialog->setCancelButtonLabel(NULL);
        $dialog->setSaveAndCloseButtonLabel(NULL);
        $dialog->setWidth("600");
        $ajaxForm = new \Widgets\AjaxForm();
        $insertOption = new \Widgets\DropDownList();
        $insertOption->setName("insertOptionName");
        $insertOption->setId("insertOptionId");
        $optionValues = array();
        $optionValues[0] = "oben";
        $optionValues[1] = "unten";
        $insertOption->setOptionValues($optionValues);
        $insertOption->setStartValue("oben");
        $insertOption->setClass("attribute");
        
        $ajaxForm->setSubmitCommand("CreateMessage");
        $ajaxForm->setSubmitNamespace("PortletMsg");

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
  width: 80px;
}

.attributeValue {
  float: left;
  width: 300px;
}

.attributeValue .text, .attributeValue textarea {
  width: 100px;
}
.widgets_textarea {
    width:250px;
    float:left;
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
    <div class="attributeName">Meldung einfügen:</div>
    <div><select name="insertOption"
        id="insertOptionId" size="1">        
        <option value="0">oben</option>
        <option value="1">unten</option>
</select> </div>
   </div>
<div class="attribute">
	<div class="attributeName">Text:</div>
	<div class="widgets_textarea"><textarea rows="10" style="height:206px;width:480px;" class="mce-small"  value="" name="text" id="text"></textarea><br clear="all">
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
</div>

<div class="attribute">
	<div><input type="hidden" name="parent" value="{$this->id}"></div>
</div>



END
        );
        $dialog->addWidget($ajaxForm);       
        $ajaxResponseObject->addWidget($dialog);
        $pollingDummy = new \Widgets\PollingDummy();
        $ajaxResponseObject->addWidget($pollingDummy);
        return $ajaxResponseObject;
    }

}

?>
