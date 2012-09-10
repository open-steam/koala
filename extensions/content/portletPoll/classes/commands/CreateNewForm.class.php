<?php

namespace PortletPoll\Commands;

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

        //$ajaxForm->setHtml(<<<END
        $html = ' 
<style type="text/css">
.attribute {
  clear: left;
  padding: 5px 2px 5px 2px;
}

.attributeName {
  float: left;
  padding-right: 20px;
  text-align: right;
  width: 80px;
}

.attributeNameRequired {
  float: left;
  padding-right: 20px;
  text-align: right;
  font-weight: bold;
  width: 80px;
}

.attributeValue {
  float: left;
  width: 300px;
}

.attributeValue .text, .attributeValue textarea {
  wwidth: 100px;
}

.attributeValueColumn {
  float: left;
  position: relative;
  text-align: center;
}
</style>
<input type="hidden" name="id" value="'.$this->id.'">

<div class="attribute">
	<div class="attributeNameRequired">Titel*:</div>
	<div><input type="text" class="text" value="" name="title"></div>
</div>
<div class="attribute">
	<div class="attributeName">Beschreibung:</div>
	<div><input type="text" class="text" value="" name="desc"></div>
</div>

<div class="attribute">
	<div><input type="hidden" name="parent" value="'.$this->id.'"></div>
</div>

';
        $datepickerStart = new \Widgets\DatePicker();
        $datepickerStart->setLabel("Startdatum");
        $datepickerStart->setDatePicker(true);
        $datepickerStart->setTimePicker(false);
        $datepickerStart->setName("startDate");

        $datepickerEnd = new \Widgets\DatePicker();
        $datepickerEnd->setLabel("Enddatum");
        $datepickerEnd->setDatePicker(true);
        $datepickerEnd->setTimePicker(false);
        $datepickerEnd->setName("endDate");

        $html .= '<div class="attribute">' . $datepickerStart->getHtml() . "</div>";
        $html .='<script>$("input[name=\"startDate\"]").val("' . $currentDate . '");</script>';

        $html .= '<div class="attribute">' . $datepickerEnd->getHtml() . "</div>";
        $html .='<script>$("input[name=\"endDate\"]").val("' . $futureDate . '");</script>';

        $descLabelWidth = 60;
        $descInputWidth = 250;

        $item0Description = new \Widgets\TextInput();
        $item0Description->setLabelWidth($descLabelWidth);
        $item0Description->setInputWidth($descInputWidth);
        $item0Description->setInputBackgroundColor("rgb(255,120,111)");
        $item0Description->setLabel("Antworten");
        $item0Description->setName("input0");
        $html .= '<div class="attribute">' . $item0Description->getHtml() . "</div>";
        $html .='<script>$("input[name=\"input0\"]").val("Eintrag A")</script>';

        $item1Description = new \Widgets\TextInput();
        $item1Description->setLabelWidth($descLabelWidth);
        $item1Description->setInputWidth($descInputWidth);
        $item1Description->setInputBackgroundColor("rgb(250,186,97)");
        $item1Description->setName("input1");
        $html .= '<div class="attribute">' . $item1Description->getHtml() . "</div>";
        $html .='<script>$("input[name=\"input1\"]").val("Eintrag B")</script>';

        $item2Description = new \Widgets\TextInput();
        $item2Description->setLabelWidth($descLabelWidth);
        $item2Description->setInputWidth($descInputWidth);
        $item2Description->setInputBackgroundColor("rgb(244,229,123)");
        $item2Description->setName("input2");
        $html .= '<div class="attribute">' . $item2Description->getHtml() . "</div>";

        $item3Description = new \Widgets\TextInput();
        $item3Description->setLabelWidth($descLabelWidth);
        $item3Description->setInputWidth($descInputWidth);
        $item3Description->setInputBackgroundColor("rgb(194,222,102)");
        $item3Description->setName("input3");
        $html .= '<div class="attribute">' . $item3Description->getHtml() . "</div>";

        $item4Description = new \Widgets\TextInput();
        $item4Description->setLabelWidth($descLabelWidth);
        $item4Description->setInputWidth($descInputWidth);
        $item4Description->setInputBackgroundColor("rgb(113,182,255)");
        $item4Description->setName("input4");
        $html .= '<div class="attribute">' . $item4Description->getHtml() . "</div>";

        $item5Description = new \Widgets\TextInput();
        $item5Description->setLabelWidth($descLabelWidth);
        $item5Description->setInputWidth($descInputWidth);
        $item5Description->setInputBackgroundColor("rgb(207,163,224)");
        $item5Description->setName("input5");
        $html .= '<div class="attribute">' . $item5Description->getHtml() . "</div>";
        
        

        $ajaxForm->setHtml($html);
     


        $ajaxResponseObject->addWidget($ajaxForm);
        return $ajaxResponseObject;
    }

}

?>