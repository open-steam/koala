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

        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
        $this->id = $currentUser->get_id();

        $objects = $currentUser->get_inventory();
        if($objects === 0){
            $objects = array();
        }

        $this->getExtension()->addJS();
        $this->getExtension()->addCSS();

        $title = "Zwischenablage";

        $breadcrumb = new \Widgets\Breadcrumb();
        $breadcrumb->setData(array("", array("name" => "<img src=\"" . PATH_URL . "explorer/asset/icons/clipboard_16.png\"></img> " . $title)));

        //$actionBar = new \Widgets\ActionBar();
        //$actionBar->setActions(array(array("name" => "Zwischenablage leeren", "ajax" => array("onclick" => array("command" => "EmptyClipboard", "params" => array(), "requestType" => "popup", "namespace" => "explorer")))));
        //$actionBar->setActions(array(array("name"=>"Neu", "ajax"=>array("onclick"=>array("command"=>"newelement"))), array("name"=>"Eigenschaften", "link"=>PATH_URL."explorer/properties/"), array("name"=>"Rechte", "link"=>PATH_URL."explorer/rights/")));

        $loader = new \Widgets\Loader();
        $loader->setWrapperId("clipboardWrapper");
        $loader->setMessage("Lade Zwischenablage...");
        $loader->setCommand("LoadClipboard");
        $loader->setNamespace("Clipboard");
        $loader->setParams(array("id" => $this->id));
        $loader->setElementId("clipboardWrapper");
        $loader->setType("updater");

        $environment = new \Widgets\RawHtml();
        $environment->setHtml("<input type=\"hidden\" id=\"environment\" name=\"environment\" value=\"{$this->id}\">");

        $frameResponseObject->setTitle("Zwischenablage");
        $frameResponseObject->addWidget($breadcrumb);
        $frameResponseObject->addWidget($environment);
        $frameResponseObject->addWidget($loader);
        return $frameResponseObject;
    }

}

?>
