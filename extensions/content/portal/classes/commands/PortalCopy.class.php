<?php
namespace Portal\Commands;
class PortalCopy extends \AbstractCommand implements \IAjaxCommand, \IFrameCommand {

    private $params;
    private $id;
    private $user;
    private $name;
    private $duplicateNameObject;
    private $rename;

    public function validateData(\IRequestObject $requestObject){
        return true;
    }

    public function processData(\IRequestObject $requestObject){
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        $portalObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $this->id);
        $this->user = $GLOBALS["STEAM"]->get_current_steam_user();
        $this->rename = $this->params["rename"];
        $this->name = $portalObject->get_name();
        $this->duplicateNameObject = $this->user->get_object_by_name($this->name);
        if($this->duplicateNameObject == 0 || $this->rename){
          //copy portal
          $portalCopy = $portalObject->copy();

          //remove broken messages
          if(!($portalCopy instanceof \steam_link)){
            foreach ($portalCopy->get_inventory() as $columnObject) {
                foreach ($columnObject->get_inventory() as $portletObject) {
                    if ($portletObject->get_attribute("bid:portlet")==="msg" && !($portletObject instanceof \steam_link)) {

                        //get ids in attrbute
                        $oldIds = $portletObject->get_attribute("bid:portlet:content");
                        $newIds = array();

                        //delete wrong references messages
                        foreach ($portletObject->get_inventory() as $oldMessageObject) {
                            $oldMessageObject->delete();
                        }

                        foreach ($oldIds as $messageId) {
                            //copy to here
                            //make new id list
                            $msgObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $messageId );
                            $msgCopy = $msgObject->copy();
                            $msgCopy->move($portletObject);
                            $newIds[]=$msgCopy->get_id();
                            //handle included pics
                            $pictrueId = $msgObject->get_attribute("bid:portlet:msg:picture_id");
                            if ($pictrueId!="") {
                                $pictureObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $pictrueId );
                                $pictuteCopy = $pictureObject->copy();
                                $pictuteCopy->move($portletObject); //test
                                $msgCopy->set_attribute("bid:portlet:msg:picture_id",$pictuteCopy->get_id());
                                $msgCopy->move($portletObject);
                            }

                        }
                        //save in attrubute
                        $portletObject->set_attribute("bid:portlet:content",$newIds);
                    }
                }
            }
          }

          $portalCopy->move($this->user);

        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject){
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
        $keepBothButton["js"] = "sendRequest('PortalCopy', {'id':{$this->id}, 'rename':true}, '', 'data', null, null, 'portal');closeDialog();";
        $buttons[0] = $keepBothButton;

        $replaceButton = array();
        $replaceButton["label"] = "Ersetzen";
        $replaceButton["js"] = "sendRequest('Delete', {'id':{$this->duplicateNameObject->get_id()}}, '', 'data', function(){sendRequest('PortalCopy', {'id':{$this->id}}, '', 'data', null, null, 'portal');}, null, 'explorer');closeDialog();";
        $buttons[1] = $replaceButton;

        $dialog->setButtons($buttons);
        $dialog->setWidth(500);
        $ajaxResponseObject->addWidget($dialog);
      }
      else{
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml("");
        $ajaxResponseObject->addWidget($rawHtml);

        $ajaxResponseObject->setStatus("ok");
        $jswrapper = new \Widgets\JSWrapper();
        $clipboardModel = new \Explorer\Model\Clipboard($this->user);
        $js = "document.getElementById('clipboardIconbarWrapper').innerHTML = '" . $clipboardModel->getIconbarHtml() . "';";
        $jswrapper->setJs($js);
        $ajaxResponseObject->addWidget($jswrapper);
        return $ajaxResponseObject;

      }
      return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject){
        //no response
    }

    //works in case of no references (not used)
    private function getMessage($portalObject, $columnIndex, $portletIndex, $messageIndex){
        $columnCount=0;
        foreach ($portalObject->get_inventory() as $columnObject) {
            //iterate over column
            $portletCount=0;
            if ($columnCount==$columnIndex) foreach ($columnObject->get_inventory() as $portletObject) {
                //iterate over portlet
                if ($portletObject->get_attribute("bid:portlet")==="msg") {
                    $messageCount=0;
                    $messageIndexArray = $portletObject->get_attribute("bid:portlet:content");
                    if ($portletCount==$portletIndex) foreach ($messageIndexArray as $messageId) {
                        //iterate over messages
                        if ($messageCount==$messageIndex) {
                            $message = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $messageId);

                            return $message;
                        }
                        $messageCount++;
                    }
                    $portletObject->delete();
                }
                $portletCount++;
            }
            $columnCount++;
        }
    }
}
