<?php

namespace PortletAppointment\Commands;

class CreateNewFormTerm extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {

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

    public function idResponse(\IdResponseObject $idResponseObject) {
        
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");

        $ajaxForm = new \Widgets\AjaxForm();
        $ajaxForm->setSubmitCommand("CreateTerm");
        $ajaxForm->setSubmitNamespace("PortletAppointment");

        $currentDay = date("d") . "";
        $currentMonth = date("m") . "";
        $currentYear = date("Y") . "";
        
        $currentDate = $currentDay.".".$currentMonth.".".$currentYear;

        $rawHtml = new \Widgets\RawHtml();

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
.dialog .widgets_label {  
    width: 80px;
}
.widgets_label {
  float: left;
  padding-right: 20px;
  text-align: right;
  width: 80px;
  margin: 0px;
}
.widgets_datepicker{
   float: left;
  width: 149px;
  margin 0px:
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

.attributeValueColumn {
  float: left;
  position: relative;
  text-align: center;
}
</style>
                
<input type="hidden" name="id" value="' . $this->id . '">


<div class="attribute">
	<div class="attributeName">Titel:</div>
	<div class="attributeValue"><input type="text" class="text" value="" name="title"></div>
</div>
<div class="attribute">
	<div class="attributeName">Beschreibung:</div>
	<div class="attributeValue"><input type="text" class="text" value="" name="desc"></div>
</div>
';

        $datepickerStart = new \Widgets\DatePicker();
        $datepickerStart->setLabel("Startdatum");
        $datepickerStart->setDatePicker(true);
        $datepickerStart->setTimePicker(false);
        $datepickerStart->setName("startDate");

        $html .= '<div class="attribute">'.$datepickerStart->getHtml()."</div>";
        $html .='<script>$(".hasDatepicker").val("'.$currentDate.'");</script>';
        $ajaxForm->setHtml($html);
        $dialog = new \Widgets\Dialog();
        $dialog->setCloseButtonLabel(NULL);
        $dialog->setWidth(600);

        $dialog->addWidget($ajaxForm);


        $ajaxResponseObject->addWidget($dialog);
        return $ajaxResponseObject;
    }

}

?>