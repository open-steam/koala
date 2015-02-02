<?php
namespace PortletTopic\Commands;
class CreateCatForm extends \AbstractCommand implements \IAjaxCommand {
	
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
		$ajaxForm->setSubmitCommand("CreateCategory");
		$ajaxForm->setSubmitNamespace("PortletTopic");
                
                $titel = new \Widgets\TextInput();
		$titel->setLabel("Titel");
                $titel->setName("title");
                
                $html = $titel->getHtml();                
                $html.= '<input type="hidden" name="id" value="'.$this->id.'">';
                $html.='<script>$(document).ready(function(){$("input[name=title]").css("width", "196px")});</script>';
                
                $ajaxForm->setHtml($html);
                
                $dialog = new \Widgets\Dialog();
                $dialog->setCancelButtonLabel(NULL);
                $dialog->setSaveAndCloseButtonLabel(null);
                $dialog->addWidget($ajaxForm);
                $dialog->setTitle("Kategorie hinzufügen");
                
                $ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
        }
}
