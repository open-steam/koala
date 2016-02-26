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
		$titel->setLabel("Link-Text");
		$titel->setName("title");

		$link = new \Widgets\TextInput();
		$link->setLabel("Link-Adresse");
		$link->setName("link");

		$desc = new \Widgets\TextInput();
		$desc->setLabel("Beschreibung");
		$desc->setName("desc");

		$window = new \Widgets\Checkbox();
		$window->setLabel("In neuem Tab öffnen");
		$window->setCheckedValue("CHECKED");
		$window->setUncheckedValue("");
		$window->setName("window2");

		//build html
		$html = $titel->getHtml();
		$html.= $seperatorHtml;
		$html.= '<input type="hidden" name="id" value="'.$this->id.'">';
		$html .= $link->getHtml();
		$html.= $seperatorHtml;
		$html .= $desc->getHtml();
		$html.= $seperatorHtml;
		$html .= $window->getHtml();
		$html .= '<input type="hidden" name="categoryIndex" value="'.$this->params["categoryIndex"].'">';
		$html .= '<input type="hidden" name="window" value="false">';
		$html .= '<script>$("input[name=\"window2\"]").bind("click", function() { if( $("input[name=\"window\"]").val()== "true"){ $("input[name=\"window\"]").val("false"); }else{ $("input[name=\"window\"]").val("true"); } });</script>';

    $ajaxForm->setHtml($html);

    $dialog = new \Widgets\Dialog();
    $dialog->setCancelButtonLabel(NULL);
    $dialog->setSaveAndCloseButtonLabel(null);
    $dialog->addWidget($ajaxForm);
    $dialog->setTitle("Link hinzufügen");

    $ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
  }
}
