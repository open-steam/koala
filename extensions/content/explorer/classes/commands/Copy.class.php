<?php

namespace Explorer\Commands;

class Copy extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $user;
    private $success = true;
    private $name;
    private $duplicateNameObject;
    private $rename;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        $this->user = $GLOBALS["STEAM"]->get_current_steam_user();
        $this->rename = $this->params["rename"];
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        if (getObjectType($object) === "portal") {
            $portalInstance = \PortletTopic::getInstance();
            $portalObjectId = $object->get_id();
            \ExtensionMaster::getInstance()->callCommand("PortalCopy", "Portal", array("id" => $portalObjectId));
        } elseif (getObjectType($object) === "pyramiddiscussion") {
            \ExtensionMaster::getInstance()->getExtensionById("Pyramiddiscussion")->copyPyramiddiscussion($object);
        } else if (getObjectType($object) === "forum") {
            \ExtensionMaster::getInstance()->callCommand("ForumCopy", "Forum", array("objectId" => $object->get_id()));
        } else {
          $this->name = $object->get_name();
          $this->duplicateNameObject = $this->user->get_object_by_name($this->name);
          if($this->duplicateNameObject == 0 || $this->rename){
            if ($object instanceof \steam_link) {
                $copy = \steam_factory::create_link($GLOBALS["STEAM"]->get_id(), $object->get_link_object());
                $copy->set_name($this->name);
                $copy->move($this->user);
            } elseif ($object instanceof \steam_container) {
                list($countObjects, $countSize) = $this->countInventoryRecursive($object);
                if ($countObjects <= 500 && $countSize <= 524288000) {
                    $copy = $object->copy();
                    $copy->move($this->user);
                } else {
                    $this->success = false;
                }
            } else {
                $copy = $object->copy();
                $copy->move($this->user);
            }
          }
        }
    }

    private function countInventoryRecursive($object, $countObjects = 0, $countSize = 0) {
        $inventory = $object->get_inventory();
        $countObjects = $countObjects + count($inventory);
        foreach ($inventory as $element) {
            if ($countObjects > 500 || $countSize > 524288000) {
                break;
            }
            if ($element instanceof \steam_document) {
                $countSize = $countSize + $element->get_content_size();
            } elseif ($element instanceof \steam_container) {
                list($countObjects, $countSize) = $this->countInventoryRecursive($element, $countObjects, $countSize);
            }
        }

        return array($countObjects, $countSize);
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
      $ajaxResponseObject->setStatus("ok");

      if($this->duplicateNameObject != 0  && !$this->rename){   //there exists an object with this name in the clipboard, ask the user what to do
        $dialog = new \Widgets\Dialog();
        $dialog->setTitle("Information");
        $dialog->setSaveAndCloseButtonLabel(null);
        $dialog->setCancelButtonLabel("Abbrechen");

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml('<div>In der Zwischenablage existiert bereits ein Objekt mit dem Namen "' . $this->name . '". Bitte wählen Sie eine der angegebenen Handlungsalternativen.</div><br style="clear:both"><div><div style="font-weight: bold; float: left;">Beide behalten:</div><div style="margin-left: 100px;"> Der Name des neuen Objekts wird zur eindeutigen Zuordnung durch eine Ziffer ergänzt.</div></div><div><div style="font-weight: bold; float: left;">Ersetzen:</div><div style="margin-left: 100px;">Das bestehende Objekt wird durch das neue Objekt ersetzt.</div></div><div><div style="font-weight: bold; float: left;">Abbrechen:</div><div style="margin-left: 100px;">Das Erstellung der Kopie wird abgebrochen.</div></div>');
        $dialog->addWidget($rawHtml);

        $jswrapper = new \Widgets\JSWrapper();
        $jswrapper->setJs('createOverlay("white", null, "show")');
        $ajaxResponseObject->addWidget($jswrapper);

        $keepBothButton = array();
        $keepBothButton["label"] = "Beide behalten";
        $keepBothButton["js"] = "sendRequest('Copy', {'id':{$this->id}, 'rename':true}, '', 'data', null, null, 'explorer');closeDialog();";
        $buttons[0] = $keepBothButton;

        $replaceButton = array();
        $replaceButton["label"] = "Ersetzen";
        $replaceButton["js"] = "sendRequest('Delete', {'id':{$this->duplicateNameObject->get_id()}}, '', 'data', function(){sendRequest('Copy', {'id':{$this->id}}, '', 'data', null, null, 'explorer');}, null, 'explorer');closeDialog();";
        $buttons[1] = $replaceButton;

        $dialog->setButtons($buttons);
        $dialog->setWidth(500);
        $ajaxResponseObject->addWidget($dialog);
      }
      else{
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml("");
        $ajaxResponseObject->addWidget($rawHtml);
/*
        $jswrapper = new \Widgets\JSWrapper();
        $jswrapper->setPostJsCode('closeDialog()');
        $ajaxResponseObject->addWidget($jswrapper);
*/
        if ($this->success === true) {
            $ajaxResponseObject->setStatus("ok");
            $jswrapper = new \Widgets\JSWrapper();
            $clipboardModel = new \Explorer\Model\Clipboard($this->user);
            $js = "document.getElementById('clipboardIconbarWrapper').innerHTML = '" . $clipboardModel->getIconbarHtml() . "';";
            $jswrapper->setJs($js);
            $ajaxResponseObject->addWidget($jswrapper);
            return $ajaxResponseObject;
        } else {
            $ajaxResponseObject->setStatus("ok");
            $jswrapper = new \Widgets\JSWrapper();
            $js = " if ($('#error').length == 0) {
                        $('#content').prepend('<p id=\"error\" style=\"\">Es ist nicht möglich Ordner zu kopieren, die mehr als 200 Objekte enthalten oder deren Dateigröße 500MB übersteigt.<br>Folgende Ordner wurden nicht kopiert: " . $this->name . "</p>');
                    } else {
                        var html = $('#error').html();
                        html = html.substring(156, html.length);
                        if (html.indexOf(' " . $this->name . "') == -1) {
                            $('#error').append(', ' + '" . $this->name . "');
                        }
                    }";
            $jswrapper->setJs($js);
            $ajaxResponseObject->addWidget($jswrapper);
          }
        }
        return $ajaxResponseObject;
    }
}
