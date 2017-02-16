<?php
namespace Bookmarks\Commands;
class AddBookmark extends \AbstractCommand implements \IAjaxCommand{

    private $params;
    private $id;
    private $name;
    private $link;
    private $bookmarks;
    private $duplicateNameObject;
    private $rename;

    public function validateData(\IRequestObject $requestObject){
        return true;
    }

    public function processData(\IRequestObject $requestObject){
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        $this->rename = $this->params["rename"];
        $this->bookmarks = \lms_steam::get_current_user_no_guest()->get_attribute(USER_BOOKMARKROOM);
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        if ($object instanceof \steam_link) {
            $this->link = \steam_factory::create_link($GLOBALS["STEAM"]->get_id(), $object->get_link_object());
        } elseif ($object instanceof \steam_docextern) {
            $this->link = $object->copy();
        } elseif ($object instanceof \steam_exit) {
            $this->link = \steam_factory::create_link($GLOBALS["STEAM"]->get_id(), $object->get_exit());
        } else {
            $this->link = \steam_factory::create_link($GLOBALS["STEAM"]->get_id(), $object);
        }
        $this->link->set_attribute(OBJ_DESC,  $object->get_attribute(OBJ_DESC));
        $this->link->set_attribute(DOC_MIME_TYPE,  $object->get_attribute(DOC_MIME_TYPE));
        $this->name = $this->link->get_name();
        $this->duplicateNameObject = $this->bookmarks->get_object_by_name($this->name);
        if($this->duplicateNameObject == 0 || $this->rename){
          $this->link->move($this->bookmarks);
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject){
        $ajaxResponseObject->setStatus("ok");

        if($this->duplicateNameObject != 0  && !$this->rename){   //there exists a bookmark with this name, ask the user what to do
          $dialog = new \Widgets\Dialog();
          $dialog->setTitle("Information");
          $dialog->setSaveAndCloseButtonLabel(null);
          $dialog->setCancelButtonLabel("Abbrechen");

          $rawHtml = new \Widgets\RawHtml();
          $rawHtml->setHtml('<div>Es existiert bereits ein Lesezeichen mit dem Namen "' . $this->name . '". Bitte wählen Sie eine der angegebenen Handlungsalternativen.</div><br style="clear:both"><div><div style="font-weight: bold; float: left;">Beide behalten:</div><div style="margin-left: 100px;"> Der Name des neuen Lesezeichens wird zur eindeutigen Zuordnung durch eine Ziffer ergänzt.</div></div><div><div style="font-weight: bold; float: left;">Ersetzen:</div><div style="margin-left: 100px;">Das bestehende Lesezeichen wird durch das neue Lesezeichen ersetzt.</div></div><div><div style="font-weight: bold; float: left;">Abbrechen:</div><div style="margin-left: 100px;">Das Erstellung des Lesezeichens wird abgebrochen.</div></div>');
          $dialog->addWidget($rawHtml);

          $jswrapper = new \Widgets\JSWrapper();
      		$jswrapper->setJs('createOverlay("white", null, "show")');
      		$ajaxResponseObject->addWidget($jswrapper);

    			$keepBothButton = array();
    			$keepBothButton["label"] = "Beide behalten";
    			$keepBothButton["js"] = "sendRequest('AddBookmark', {'id':{$this->id}, 'rename':true}, '', 'inform', null, null, 'bookmarks');$('#dialog_wrapper').remove();";
    			$buttons[0] = $keepBothButton;

          $replaceButton = array();
    			$replaceButton["label"] = "Ersetzen";
    			$replaceButton["js"] = "sendRequest('Delete', {'id':{$this->duplicateNameObject->get_id()}}, '', 'nonModalUpdater', function(){sendRequest('AddBookmark', {'id':{$this->id}}, '', 'inform', null, null, 'bookmarks');}, null, 'explorer');$('#dialog_wrapper').remove();";
    			$buttons[1] = $replaceButton;

    			$dialog->setButtons($buttons);
          $dialog->setWidth(500);
          $ajaxResponseObject->addWidget($dialog);
        }
        else{
          $rawHtml = new \Widgets\RawHtml();
          $rawHtml->setHtml("");
          $ajaxResponseObject->addWidget($rawHtml);

          $jswrapper = new \Widgets\JSWrapper();
      		$jswrapper->setPostJsCode('closeDialog()');
      		$ajaxResponseObject->addWidget($jswrapper);
        }

        return $ajaxResponseObject;
    }
}
