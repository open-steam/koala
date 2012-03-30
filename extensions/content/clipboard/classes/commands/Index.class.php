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
        $breadcrumb->setData(array("", array("name" => "<img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/" . deriveIcon($currentUser) . "\"></img> " . $title . " " . \Explorer\Model\Sanction::getMarkerHtml($currentUser, false))));

        //$bookmarkIcon = \Bookmarks::getInstance()->getAssetUrl() . "icons/bookmark.png";
        //$breadcrumb = new \Widgets\Breadcrumb();
        //$breadcrumb->setData(array(array("name"=>"<img src=\"{$bookmarkIcon}\"> Lesezeichenordner")));

        $actionBar = new \Widgets\ActionBar();
        $actionBar->setActions(array(array("name" => "Zwischenablage löschen", "ajax" => array("onclick" => array("command" => "EmptyClipboard", "params" => array(), "requestType" => "popup", "namespace" => "explorer")))));
        //$actionBar->setActions(array(array("name"=>"Neu", "ajax"=>array("onclick"=>array("command"=>"newelement"))), array("name"=>"Eigenschaften", "link"=>PATH_URL."explorer/properties/"), array("name"=>"Rechte", "link"=>PATH_URL."explorer/rights/")));

        $loader = new \Widgets\Loader();
        $loader->setWrapperId("clipboardWrapper");
        $loader->setMessage("Lade Daten ...");
        $loader->setCommand("loadClipboard");
        $loader->setParams(array("id" => $this->id));
        $loader->setElementId("clipboardWrapper");
        $loader->setType("updater");


        $frameResponseObject->setTitle("Zwischenablage");
        $frameResponseObject->addWidget($actionBar);
        $frameResponseObject->addWidget($loader);
        return $frameResponseObject;
    }

}

?>