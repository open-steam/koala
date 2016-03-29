<?php

namespace PortletAppointment\Commands;

class CreateNewFormTerm extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $content;
    private $dialog;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["portletId"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");

        $ajaxForm = new \Widgets\AjaxForm();
        $ajaxForm->setSubmitCommand("CreateTerm");
        $ajaxForm->setSubmitNamespace("PortletAppointment");

        $html = '<input type="hidden" name="id" value="' . $this->id . '">';

        $dialog = new \Widgets\Dialog();

        $titelInput = new \Widgets\TextInput();
        $titelInput->setLabel("Titel");
        $titelInput->setName("topic");
        $html .= '<div class="attribute">' . $titelInput->getHtml() . "</div>";

        $descriptionInput = new \Widgets\TextInput();
        $descriptionInput->setLabel("Beschreibung");
        $descriptionInput->setName("description");
        $html .= '<div class="attribute">' . $descriptionInput->getHtml() . "</div>";

        $loactionInput = new \Widgets\TextInput();
        $loactionInput->setLabel("Ort");
        $loactionInput->setName("location");
        $html .= '<div class="attribute">' . $loactionInput->getHtml() . "</div>";

        $datepickerStart = new \Widgets\DatePicker();
        $datepickerStart->setLabel("Start (Datum):");
        $datepickerStart->setName("start_date");
        $datepickerStart->setDatePicker(true);
        $datepickerStart->setTimePicker(false);
        $html .= '<div class="attribute">' . $datepickerStart->getHtml() . "</div>";

        $timepickerStart = new \Widgets\DatePicker();
        $timepickerStart->setLabel("Start (Uhrzeit):");
        $timepickerStart->setName("start_time");
        $timepickerStart->setDatePicker(false);
        $timepickerStart->setTimePicker(true);
        $html .= '<div class="attribute">' . $timepickerStart->getHtml() . "</div>";

        $datepickerEnd = new \Widgets\DatePicker();
        $datepickerEnd->setLabel("Ende (Datum):");
        $datepickerEnd->setName("end_date");
        $datepickerEnd->setDatePicker(true);
        $datepickerEnd->setTimePicker(false);
        $html .= '<div class="attribute">' . $datepickerEnd->getHtml() . "</div>";

        $timepickerEnd = new \Widgets\DatePicker();
        $timepickerEnd->setLabel("Ende (Uhrzeit):");
        $timepickerEnd->setName("end_time");
        $timepickerEnd->setDatePicker(false);
        $timepickerEnd->setTimePicker(true);
        $html .= '<div class="attribute">' . $timepickerEnd->getHtml() . "</div>";

        $linkurlInput = new \Widgets\TextInput();
        $linkurlInput->setLabel("URL (Titel als Link)");
        $linkurlInput->setName("linkurl");
        $html .= '<div class="attribute">' . $linkurlInput->getHtml() . "</div>";

        $linkurlInputOpenExtern = new \Widgets\Checkbox();
        $linkurlInputOpenExtern->setLabel("In neuem Tab öffnen");
        $linkurlInputOpenExtern->setName("new_tab");
        $linkurlInputOpenExtern->setCheckedValue("checked");
        $linkurlInputOpenExtern->setUncheckedValue("");
        $html .= '<div class="attribute">' . $linkurlInputOpenExtern->getHtml() . "</div>";
        $html .= '<input type="hidden" name="linkurl_open_extern" value="false">';
        $html .= '<script>$("input[name=\"new_tab\"]").bind("click", function() { if( $("input[name=\"linkurl_open_extern\"]").val()== "true"){ $("input[name=\"linkurl_open_extern\"]").val("false"); }else{ $("input[name=\"linkurl_open_extern\"]").val("true"); } });</script>';

        $ajaxForm->setHtml($html);

        $dialog->setCancelButtonLabel(NULL);
        $dialog->setSaveAndCloseButtonLabel(null);
        $dialog->setTitle("Termin anlegen");

        $dialog->addWidget($ajaxForm);

        $ajaxResponseObject->addWidget($dialog);
        return $ajaxResponseObject;
    }

}

?>
