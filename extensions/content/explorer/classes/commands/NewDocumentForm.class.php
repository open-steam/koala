<?php

namespace Explorer\Commands;

class NewDocumentForm extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

    private $params;
    private $id;

    public function getExtension() {
        return \DocumentObject::getInstance();
    }

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->id = $this->params[0] : "";
        } else if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");


        $ajaxUploader = new \Widgets\AjaxUploader();
        $ajaxUploader->setSizeLimit(return_bytes(ini_get('post_max_size')));
        $ajaxUploader->setNamespace("Explorer");
        $ajaxUploader->setDestId($this->id);
        $ajaxUploader->setMultiUpload(TRUE);
        $ajaxResponseObject->addWidget($ajaxUploader);

        $descriptionInput = new \Widgets\RawHtml();
        $descriptionInput->setHtml("Beschreibung: <br /><textarea style='width:calc(100% - 2px); ' id='descriptionForUploadedFiles'></textarea/>");
        $ajaxResponseObject->addWidget($descriptionInput);

        $saveAndCloseButton = new \Widgets\SaveButton();
        $saveAndCloseButton->setLabel("Speichern & Schließen");
        $saveAndCloseButton->setProgessbar(true);
        $ajaxResponseObject->addWidget($saveAndCloseButton);

        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        
    }

}

?>
