<?php

namespace Explorer\Commands;

class CopyFilterSettings extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $filter;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        isset($this->params["filter"]) ? $this->filter = $this->params["filter"] : "";
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $url = PATH_URL . "explorer/index/"; // . $this->id . "/" . $this->filter . "/";
        if($this->id !== ""){
            $url .= $this->id . "/";
        }
        if($this->filter !== ""){
            $url .= "filter=" . $this->filter . "/";
        }
        
        $dialog = new \Widgets\Dialog();
        //$dialog->setCustomButtons(null); not neccessary
        $dialog->setTitle("Filtereinstellungen kopieren...");
        $dialog->setCloseButtonLabel("Schließen");
        
        $rawHtml = new \Widgets\RawHtml();
        
        //TODO: ADD CSS SETTINGS
        $css = '.filter-link-input{min-width:450px;}';
        $rawHtml->setCss($css);
        
        
        $desc = '<div class="filter-description">Um einen Link zu erzeugen, der den aktuellen Ordner mit gewählten Filtereinstellungen einem anderen Benutzer zugänglich macht, kann der untenstehende Link verwendet werden.</div>';
        $input = '<div class="filter-link"><input class="filter-link-input" type="text" value="'.$url.'" readonly></div>';
        $rawHtml->setHtml($desc . $input);
        
        $dialog->addWidget($rawHtml);
        $ajaxResponseObject->addWidget($dialog);
        
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

   

}

?>