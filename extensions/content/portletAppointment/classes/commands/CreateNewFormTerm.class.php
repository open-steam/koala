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

        $currentDay = date("d") . "";
        $currentMonth = date("m") . "";
        $currentYear = date("Y") . "";

        $currentDate = $currentDay . "." . $currentMonth . "." . $currentYear;

        $clearer = '<div style="clear:both;"></div>';
        $rawHtml = new \Widgets\RawHtml();
        
        $html = '                               
<input type="hidden" name="id" value="' . $this->id . '">

';
        $title = new \Widgets\TextInput();
        $title->setLabel("Titel");
        $title->setName("title");
        $html .= $title->getHtml();
        
        $html .= $clearer;
        
        
        $desc = new \Widgets\TextInput();
        $desc->setLabel("Beschreibung");
        $desc->setName("desc");
        $html .= $desc->getHtml();
        $html .= $clearer;
        
        $datepickerStart = new \Widgets\DatePicker();
        $datepickerStart->setLabel("Startdatum");
        $datepickerStart->setDatePicker(true);
        $datepickerStart->setTimePicker(false);
        $datepickerStart->setName("startDate");
        
        $html .= '<div class="attribute">' . $datepickerStart->getHtml() . "</div>";
        $html .='<script>$(".hasDatepicker").val("' . $currentDate . '");</script>';
        $ajaxForm->setHtml($html);
        $dialog = new \Widgets\Dialog();
        
        $dialog->setCancelButtonLabel(NULL);
        $dialog->setSaveAndCloseButtonLabel(null);
        $dialog->setTitle("Termin anlegen");

        $dialog->addWidget($ajaxForm);


        $ajaxResponseObject->addWidget($dialog);
        return $ajaxResponseObject;
    }

}

?>