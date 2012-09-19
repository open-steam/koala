<?php

namespace PortletTermplan\Commands;

class CreateNewForm extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $content;
    private $dialog;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

        $ajaxResponseObject->setStatus("ok");

        $ajaxForm = new \Widgets\AjaxForm();
        $ajaxForm->setSubmitCommand("Create");
        $ajaxForm->setSubmitNamespace("PortletTermplan");

        $currentDay = date("d") . "";
        $currentMonth = date("m") . "";
        $currentYear = date("Y") . "";
        if (intval($currentMonth) <= 11) {
            $futureMonth = intval($currentMonth) + 1;
            $futureYear = intval($currentYear);
        } else {
            $futureMonth = 1;
            $futureYear = intval($currentYear) + 1;
        }

        $currentDate = $currentDay . "." . $currentMonth . "." . $currentYear;
        $futureDate = $currentDay . "." . $futureMonth . "." . $futureYear;

        $titelInput = new \Widgets\TextInput();
        $titelInput->setLabel("Überschrift");
        $titelInput->setName("title");
        $html = $titelInput->getHtml();

        $descriptionInput = new \Widgets\TextInput();
        $descriptionInput->setLabel("Beschreibung");
        $descriptionInput->setName("desc");
        $html .= $descriptionInput->getHtml();

        $datepickerStart = new \Widgets\DatePicker();
        $datepickerStart->setLabel("Start der Abstimmung");
        $datepickerStart->setName("startDate");
        $html .= $datepickerStart->getHtml();
        $html .='<script>$("input[name=\"startDate\"]").val("' . $currentDate . '");</script>';

        $datepickerEnd = new \Widgets\DatePicker();
        $datepickerEnd->setLabel("Ende der Abstimmung");
        $datepickerEnd->setName("endDate");
        $html .= $datepickerEnd->getHtml();
        $html .='<script>$("input[name=\"endDate\"]").val("' . $futureDate . '");</script>';




        $term0 = new \Widgets\TextInput();
        $term0->setLabel("Eintrag 1");
        $term0->setName("term0");
        $html .= $term0->getHtml();
        $html .='<script>$("input[name=\"term0\"]").val("Termin A");</script>';

        $term1 = new \Widgets\TextInput();
        $term1->setLabel("Eintrag 2");
        $term1->setName("term1");
        $html .= $term1->getHtml();
        $html .='<script>$("input[name=\"term1\"]").val("Termin B");</script>';


        $term2 = new \Widgets\TextInput();
        $term2->setLabel("Eintrag 3");
        $term2->setName("term2");
        $html .= $term2->getHtml();

        $term3 = new \Widgets\TextInput();
        $term3->setLabel("Eintrag 4");
        $term3->setName("term3");
        $html .= $term3->getHtml();

        $term4 = new \Widgets\TextInput();
        $term4->setLabel("Eintrag 5");
        $term4->setName("term4");
        $html .= $term4->getHtml();

        $term5 = new \Widgets\TextInput();
        $term5->setLabel("Eintrag 6");
        $term5->setName("term5");
        $html .= $term5->getHtml();
        
        $clearer = new \Widgets\Clearer();
        $html .= $clearer->getHtml();

        $html .= '<input type="hidden" name="id" value="' . $this->id . '">';

        $ajaxForm->setHtml($html);
        $ajaxResponseObject->addWidget($ajaxForm);
        return $ajaxResponseObject;
    }

}

?>