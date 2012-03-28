<?php

namespace Explorer\Commands;

class Paste extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $env;
    private $id;
    private $elements;
    private $clipboard;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->clipboard = $GLOBALS["STEAM"]->get_current_steam_user();
        if (!isset($this->params["env"])) {
            $path = $this->params["path"];
            if ($path == "bookmarks/") {
                $this->env = $this->clipboard->get_attribute("USER_BOOKMARKROOM")->get_id();
            } else if (strpos($path, "bookmark") !== false) {
               $pathArray = explode("/", $path);
               if(is_numeric($pathArray[2])){
                   $this->env = $pathArray[2];
               }
            }
        } else {
            $this->env = $this->params["env"];
        }
        if (isset($this->params["id"])) {
            $this->id = $this->params["id"];
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            $environment = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->env);
            $object->move($environment);
        } else {
            $this->elements = $this->clipboard->get_inventory();
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        if (!isset($this->id)) {
            $ajaxResponseObject->setStatus("ok");
            $jswrapper = new \Widgets\JSWrapper();
            $ids = "";
            $elements = "";
            foreach ($this->elements as $key => $element) {
                if (count($this->elements) > $key + 1) {
                    $ids .= "{\"id\":\"" . $element->get_id() . "\", \"env\":\"" . $this->env . "\"}, ";
                    $elements .= "\"\", ";
                } else {
                    $ids .= "{\"id\":\"" . $element->get_id() . "\", \"env\":\"" . $this->env . "\"}";
                    $elements .= "\"\"";
                }
            }
            $js = "sendMultiRequest('Paste', jQuery.parseJSON('[$ids]'), jQuery.parseJSON('[$elements]'), 'updater', null, null, 'explorer', 'Füge Objekt ein ...', 0, " . count($this->elements) . ");";
            $jswrapper->setJs($js);
            $ajaxResponseObject->addWidget($jswrapper);
            return $ajaxResponseObject;
        } else {
            $ajaxResponseObject->setStatus("ok");
            $clipboardModel = new \Explorer\Model\Clipboard($this->clipboard);
            $jswrapper = new \Widgets\JSWrapper();
            $js = "document.getElementById('clipboardIconbarWrapper').innerHTML = '" . $clipboardModel->getIconbarHtml() . "';";
            if (count($this->clipboard->get_inventory()) == 0) {
                $js .= "window.location.reload();";
            }
            $jswrapper->setJs($js);
            $ajaxResponseObject->addWidget($jswrapper);
            return $ajaxResponseObject;
        }
    }

}

?>