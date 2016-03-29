<?php

namespace PortletPoll\Commands;

class CreateNewForm extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $content;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");

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

        $ajaxForm = new \Widgets\AjaxForm();
        $ajaxForm->setSubmitCommand("Create");
        $ajaxForm->setSubmitNamespace("PortletPoll");

        $html = '<input type="hidden" name="id" value="' . $this->id . '"><div class="attribute"><div><input type="hidden" name="parent" value="' . $this->id . '"></div></div>';
        $title = new \Widgets\TextInput();
        $title->setLabel("Überschrift");
        $title->setName("title");
        $title->setValue("Abstimmung");
        $html .= $title->getHtml();

        $desc = new \Widgets\TextInput();
        $desc->setLabel("Beschreibung");
        $desc->setName("desc");
        $html .= $desc->getHtml();

        $datepickerStart = new \Widgets\DatePicker();
        $datepickerStart->setLabel("Start:");
        $datepickerStart->setDatePicker(true);
        $datepickerStart->setTimePicker(false);
        $datepickerStart->setName("startDate");
        $datepickerStart->setValue($currentDate);
        $html .= $datepickerStart->getHtml();

        $datepickerEnd = new \Widgets\DatePicker();
        $datepickerEnd->setLabel("Ende:");
        $datepickerEnd->setDatePicker(true);
        $datepickerEnd->setTimePicker(false);
        $datepickerEnd->setName("endDate");
        $datepickerEnd->setValue($futureDate);
        $html .= $datepickerEnd->getHtml();

        $clearer = new \Widgets\Clearer();
        $html .= $clearer->getHtml();
        $html .='<h3>Optionen</h3>';

        $item0Description = new \Widgets\TextInput();
        $item0Description->setInputBackgroundColor("rgb(255,120,111)");
        $item0Description->setLabel("Option 1");
        $item0Description->setName("input0");
        $html .= $item0Description->getHtml();

        $item1Description = new \Widgets\TextInput();
        $item1Description->setInputBackgroundColor("rgb(250,186,97)");
        $item1Description->setLabel("Option 2");
        $item1Description->setName("input1");
        $html .= $item1Description->getHtml();

        $item2Description = new \Widgets\TextInput();
        $item2Description->setInputBackgroundColor("rgb(244,229,123)");
        $item2Description->setLabel("Option 3");
        $item2Description->setName("input2");
        $html .= $item2Description->getHtml();

        $item3Description = new \Widgets\TextInput();
        $item3Description->setInputBackgroundColor("rgb(194,222,102)");
        $item3Description->setLabel("Option 4");
        $item3Description->setName("input3");
        $html .= $item3Description->getHtml();

        $item4Description = new \Widgets\TextInput();
        $item4Description->setInputBackgroundColor("rgb(113,182,255)");
        $item4Description->setLabel("Option 5");
        $item4Description->setName("input4");
        $html .= $item4Description->getHtml();

        $item5Description = new \Widgets\TextInput();
        $item5Description->setInputBackgroundColor("rgb(207,163,224)");
        $item5Description->setLabel("Option 6");
        $item5Description->setName("input5");
        $html .= $item5Description->getHtml();

        $clearer = new \Widgets\Clearer();
        $html .= $clearer->getHtml();

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
