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
        $titelInput->setValue("Terminplaner");
        $html = $titelInput->getHtml();

        $descriptionInput = new \Widgets\TextInput();
        $descriptionInput->setLabel("Beschreibung");
        $descriptionInput->setName("desc");
        $html .= $descriptionInput->getHtml();

        $datepickerStart = new \Widgets\DatePicker();
        $datepickerStart->setLabel("Start:");
        $datepickerStart->setName("startDate");
        $html .= $datepickerStart->getHtml();
        $html .='<script>$("input[name=\"startDate\"]").val("' . $currentDate . '");</script>';

        $datepickerEnd = new \Widgets\DatePicker();
        $datepickerEnd->setLabel("Ende:");
        $datepickerEnd->setName("endDate");
        $html .= $datepickerEnd->getHtml();
        $html .='<script>$("input[name=\"endDate\"]").val("' . $futureDate . '");</script>';

        $clearer = new \Widgets\Clearer();
        $html .= $clearer->getHtml();
        $html .='<h3>Termine</h3>';

        $term0 = new \Widgets\DatePicker();
        $term0->setLabel("Termin 1:");
        $term0->setName("term0");
        $term0->setTimePicker(true);
        $html .= $term0->getHtml();

        $term1 = new \Widgets\DatePicker();
        $term1->setLabel("Termin 2:");
        $term1->setName("term1");
        $term1->setTimePicker(true);
        $html .= $term1->getHtml();

        $term2 = new \Widgets\DatePicker();
        $term2->setLabel("Termin 3:");
        $term2->setName("term2");
        $term2->setTimePicker(true);
        $html .= $term2->getHtml();

        $term3 = new \Widgets\DatePicker();
        $term3->setLabel("Termin 4:");
        $term3->setName("term3");
        $term3->setTimePicker(true);
        $html .= $term3->getHtml();

        $term4 = new \Widgets\DatePicker();
        $term4->setLabel("Termin 5:");
        $term4->setName("term4");
        $term4->setTimePicker(true);
        $html .= $term4->getHtml();

        $term5 = new \Widgets\DatePicker();
        $term5->setLabel("Termin 6:");
        $term5->setName("term5");
        $term5->setTimePicker(true);
        $html .= $term5->getHtml();

        $clearer = new \Widgets\Clearer();
        $html .= $clearer->getHtml();

        $html .= '<input type="hidden" name="id" value="' . $this->id . '">';

        $css = "<style>
          .widgets_textinput, .widgets_textinput input, .widgets_textinput div {
            float:left;
          }
          .widgets_label {
            clear:both;
            float: left;
            margin-right: 2px;
            white-space: nowrap;
          }
          .widgets_datepicker, .widgets_datepicker div, .widgets_datepicker input {
            float: left;
          }
        </style>";
        $ajaxForm->setHtml($css . $html);
        $ajaxResponseObject->addWidget($ajaxForm);
        return $ajaxResponseObject;
    }

}

?>
