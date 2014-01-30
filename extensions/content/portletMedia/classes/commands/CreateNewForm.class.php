<?php
namespace PortletMedia\Commands;
class CreateNewForm extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	
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
		$ajaxForm->setSubmitNamespace("PortletMedia");
                
                $html = '';
                
                $headlineInput = new \Widgets\TextInput();
		$headlineInput->setLabel("Überschrift");
                $headlineInput->setName("title");
                $html .= $headlineInput->getHtml();
                
                $urlInput = new \Widgets\TextInput();
		$urlInput->setLabel("Adresse");
                $urlInput->setName("url");
		$html .= $urlInput->getHtml();
                
                $descriptionInput = new \Widgets\TextInput();
		$descriptionInput->setLabel("Beschreibung");
                $descriptionInput->setName("desc");
		$html .= $descriptionInput->getHtml();
                
                $radioButton = new \Widgets\RadioButton();
		$radioButton->setLabel("Typ");
		$radioButton->setOptions(array(array("name"=>"Film", "value"=>"movie"), array("name"=>"Bild", "value"=>"image"), array("name"=>"Ton", "value"=>"audio")));
                $radioButton->setCurrentValue("Film");
		$html.= $radioButton->getHtml();
                $clearer = new \Widgets\Clearer();
                $html .= $clearer->getHtml();
                
                $html .= '<input type="hidden" name="id" value="'.$this->id.'">';
                       
                 $css = "<style>.widgets_textinput, .widgets_textinput input, .widgets_textinput div {
	float:left;
        
        
}.widgets_label {
	clear:both;
        float: left;
	margin-right: 2px;
	white-space: nowrap;
}.widgets_datepicker, .widgets_datepicker div, .widgets_datepicker input {
	float: left;
}
.widgets_checkbox {
	float: left;
}
.widgets_radiobutton {
	float: left;
        margin-left:0px;
}

</style>";
        $ajaxForm->setHtml($css . $html);
		$ajaxResponseObject->addWidget($ajaxForm);
		return $ajaxResponseObject;
	}
}
?>