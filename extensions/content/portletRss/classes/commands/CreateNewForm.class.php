<?php

namespace PortletRss\Commands;

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
        $ajaxForm->setSubmitNamespace("PortletRss");


        $html = '<input type="hidden" name="id" value="' . $this->id . '">
                <input type="hidden" name="html" value="">';

        $titelInput = new \Widgets\TextInput();
        $titelInput->setLabel("Überschrift");
        $titelInput->setName("title");
        
        $addressInput = new \Widgets\TextInput();
        $addressInput->setLabel("RSS-Adresse");
        $addressInput->setName("rss");

        $checkbox = new \Widgets\Checkbox();
        $checkbox->setLabel("HTML zulassen");
        $checkbox->setCheckedValue("checked");
        $checkbox->setUncheckedValue("");
        $checkbox->setName("html2");
        
        $html .= $titelInput->getHtml() . $addressInput->getHtml() . $checkbox->getHtml();
        $html .= '<script>$("input[name=\"html2\"]").bind("click", function() {
  if( $("input[name=\"html\"]").val()== "true"){
    $("input[name=\"html\"]").val("false");
    
}else{
    $("input[name=\"html\"]").val("true");
    
}
    });</script>';


        $ajaxForm->setHtml($html);




        $ajaxResponseObject->addWidget($ajaxForm);
        return $ajaxResponseObject;
    }

}

?>