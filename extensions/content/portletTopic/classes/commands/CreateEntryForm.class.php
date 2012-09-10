<?php
namespace PortletTopic\Commands;
class CreateEntryForm extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$this->params = $requestObject->getParams();
		$this->id = $this->params["portletId"];
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		
		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->setSubmitCommand("CreateEntry");
		$ajaxForm->setSubmitNamespace("PortletTopic");
                
                $titel = new \Widgets\TextInput();
		$titel->setLabel("Titel");
                $titel->setName("title");
                
                $desc = new \Widgets\TextInput();
		$desc->setLabel("Beschreibung");
                $desc->setName("desc");
                
                $html = $titel->getHtml();                
                $html.= '<input type="hidden" name="id" value="'.$this->id.'">';
                $html .= $desc->getHtml();
                
                $link = new \Widgets\TextInput();
		$link->setLabel("Link-Adresse");
                $link->setName("link");
                
                $html .= $link->getHtml();
                
                $window = new \Widgets\Checkbox();
                $window->setLabel("Neues Fenster");
                $window->setCheckedValue("CHECKED");
                $window->setUncheckedValue("");
                $window->setName("window2");
                
                $html .= $window->getHtml();
                $html .= '<input type="hidden" name="categoryIndex" value="'.$this->params["categoryIndex"].'">';
                $html .= '<input type="hidden" name="window" value="false">';
                $html .= '<script>$("input[name=\"window2\"]").bind("click", function() {
  if( $("input[name=\"window\"]").val()== "true"){
    $("input[name=\"window\"]").val("false");
    
}else{
    $("input[name=\"window\"]").val("true");
    
}
    });</script>';
                
                $ajaxForm->setHtml($html);
                
                $dialog = new \Widgets\Dialog();
                $dialog->setCloseButtonLabel(NULL);
                $dialog->addWidget($ajaxForm);
                
                $ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
        }
}