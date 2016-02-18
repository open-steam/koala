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

		$seperatorHtml = "<br style=\"clear:both\"/>";

		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->setSubmitCommand("CreateEntry");
		$ajaxForm->setSubmitNamespace("PortletTopic");

		$titel = new \Widgets\TextInput();
		$titel->setLabel("Titel");
		$titel->setName("title");

		$desc = new \Widgets\TextInput();
		$desc->setLabel("Beschreibung");
		$desc->setName("desc");

		$link = new \Widgets\TextInput();
		$link->setLabel("Link-Adresse");
		$link->setName("link");

		$window = new \Widgets\Checkbox();
		$window->setLabel("Neues Fenster");
		$window->setCheckedValue("CHECKED");
		$window->setUncheckedValue("");
		$window->setName("window2");

		//build html
		$html = $titel->getHtml();
		$html.= $seperatorHtml;
		$html.= '<input type="hidden" name="id" value="'.$this->id.'">';
		$html .= $desc->getHtml();
		$html.= $seperatorHtml;
		$html .= $link->getHtml();
		$html.= $seperatorHtml;
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
		$dialog->setCancelButtonLabel(NULL);
		$dialog->setSaveAndCloseButtonLabel(null);
		$dialog->addWidget($ajaxForm);
		$dialog->setTitle("Eintrag hinzufügen");
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
	}
}
