<?php

namespace Trashbin\Commands;

class Index extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

    private $params;
    private $id;
    

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        //chronic
        \ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentOther("trashbin");

        $currentUser = \lms_steam::get_current_user_no_guest();
        if (isset($this->id)) {
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            if ($object instanceof \steam_exit) {
                $object = $object->get_exit();
                $this->id = $object->get_id();
            }
        } else {
            $object = $currentUser->get_trashbin();
            $this->id = $object->get_id();
        }

        if ($object && $object instanceof \steam_container) {
            $objects = $object->get_inventory();
        } else {
            $objects = array();
        }

        $this->getExtension()->addJS();
        $this->getExtension()->addCSS();

        $title = "Papierkorb";
        $breadcrumb = new \Widgets\Breadcrumb();
        $breadcrumb->setData(array(array("name" => "<svg><use xlink:href='" . PATH_URL . "explorer/asset/icons/trashbin.svg#trashbin'/></svg>" . $title)));

        $loader = new \Widgets\Loader();
        $loader->setWrapperId("trashbinWrapper");
        $loader->setMessage("Lade gelöschte Objekte...");
        $loader->setParams(array("id" => $this->id));
        $loader->setNamespace("Trashbin");
        $loader->setElementId("trashbinWrapper");
        $loader->setType("updater");

        //check the explorer view attribute which is specified in the profile
        $viewAttribute = $currentUser->get_attribute("EXPLORER_VIEW");
        $selectAll = new \Widgets\RawHtml();
        if ($viewAttribute && $viewAttribute == "gallery") {
            $loader->setCommand("loadGalleryContent");
            
            $selectAll->setHtml("<div id='selectAll'><input onchange='elements = jQuery(\".galleryEntry > input\"); for (i=0; i<elements.length; i++) { if (this.checked != elements[i].checked) { elements[i].click() }}' type='checkbox'><p>Alle auswählen</p></div>");
            
        } else {
            $loader->setCommand("loadContent");
        }
        $frameResponseObject->addWidget($breadcrumb);
        $frameResponseObject->addWidget($selectAll);
        $frameResponseObject->setTitle($title);
        $frameResponseObject->addWidget($loader);

        return $frameResponseObject;
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}

?>
