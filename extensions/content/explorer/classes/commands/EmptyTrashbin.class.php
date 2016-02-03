<?php

namespace Explorer\Commands;

class EmptyTrashbin extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $elements;
    private $trashbin;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->trashbin = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("USER_TRASHBIN");
        if (isset($this->params["id"])) {
            $this->id = $this->params["id"];
            if ($this->id !== 0) {
                $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
                if (getObjectType($object) === "pyramiddiscussion") {
                    \ExtensionMaster::getInstance()->getExtensionById("Trashbin")->deletePyramiddiscussion($object);
                } else {
                    $object->delete();
                }
            }
        } else {
            $this->elements = $this->trashbin->get_inventory();
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        if($this->id === 0){
            $rawHtml = new \Widgets\RawHtml();
            $rawHtml->setHtml("");
            $ajaxResponseObject->addWidget($rawHtml);
            return $ajaxResponseObject;
        }
        if (!isset($this->id)) {

            $jswrapper = new \Widgets\JSWrapper();
            $ids = "";
            $elements = "";
            foreach ($this->elements as $key => $element) {
                if (count($this->elements) > $key + 1) {
                    $ids .= "{\"id\":\"" . $element->get_id() . "\"}, ";
                    $elements .= "\"\", ";
                } else {
                    $ids .= "{\"id\":\"" . $element->get_id() . "\"}";
                    $elements .= "\"\"";
                }
            }
            $js = "sendMultiRequest('EmptyTrashbin', jQuery.parseJSON('[$ids]'), jQuery.parseJSON('[$elements]'), 'updater', null, null, 'explorer', 'Leere Papierkorb ...', 0, " . count($this->elements) . ");";
            $jswrapper->setJs($js);
            $ajaxResponseObject->addWidget($jswrapper);
        } else if ($this->params["path"] == "trashbin/") {
            $trashbinModel = new \Explorer\Model\Trashbin($this->trashbin);
            $hideCurrentItem = new \Widgets\JSWrapper();
            $js="$('#" . $this->id . "').hide();";
            $js .="$('img[title=\"Papierkorb leeren\"]').parent().parent().parent().hide();";
            $js .= "$('#trashbinIconbarWrapper').html('" . $trashbinModel->getIconbarHtml() . "');";
            $js .= "if ($('div.listviewer-items div:visible').length == 0) $('div.listviewer-items').append('<div class=\"listviewer-noitem\">Der Papierkorb ist leer.</div>');";

            $hideCurrentItem->setJs($js);
            $ajaxResponseObject->addWidget($hideCurrentItem);
        } else {
            $trashbinModel = new \Explorer\Model\Trashbin($this->trashbin);
            $jswrapper = new \Widgets\JSWrapper();
            $js = "$('#trashbinIconbarWrapper').html('" . $trashbinModel->getIconbarHtml() . "'); $('.justTrashed').hide();";
            $jswrapper->setJs($js);
            $ajaxResponseObject->addWidget($jswrapper);
        }

        return $ajaxResponseObject;
    }

}

?>
