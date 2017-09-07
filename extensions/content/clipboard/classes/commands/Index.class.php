<?php

namespace Clipboard\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {

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
        \ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentOther("clipboard");

        $currentUser = \lms_steam::get_current_user();
        $this->id = $currentUser->get_id();

        $this->getExtension()->addJS();
        $this->getExtension()->addCSS();

        $title = "Zwischenablage";
        $breadcrumb = new \Widgets\Breadcrumb();
        $breadcrumb->setData(array(array("name" => "<svg><use xlink:href='" . PATH_URL . "explorer/asset/icons/clipboard.svg#clipboard'/></svg>" . $title)));

        $frameResponseObject->setTitle("Zwischenablage");
        $environment = new \Widgets\RawHtml();
        $environment->setHtml("<input type=\"hidden\" id=\"environment\" name=\"environment\" value=\"{$this->id}\">");
        $frameResponseObject->addWidget($breadcrumb);
        $frameResponseObject->addWidget($environment);

        $loader = new \Widgets\Loader();
        $loader->setWrapperId("clipboardWrapper");
        $loader->setMessage("Lade Zwischenablage...");
        $loader->setNamespace("Clipboard");
        $loader->setParams(array("id" => $this->id));
        $loader->setElementId("clipboardWrapper");
        $loader->setType("updater");

        //check the explorer view attribute which is specified in the profile
        $viewAttribute = $currentUser->get_attribute("EXPLORER_VIEW");
        if ($viewAttribute && $viewAttribute == "gallery") {
            $loader->setCommand("LoadGalleryContent");
            $selectAll = new \Widgets\RawHtml();
            $selectAll->setHtml("<div id='selectAll'><input onchange='elements = jQuery(\".galleryEntry > input\"); for (i=0; i<elements.length; i++) { if (this.checked != elements[i].checked) { elements[i].click() }}' type='checkbox'><p>Alle auswählen</p></div>");
            $frameResponseObject->addWidget($selectAll);
        } else {
            $loader->setCommand("LoadClipboard");
        }

        $frameResponseObject->addWidget($loader);
        return $frameResponseObject;
    }

}

?>
