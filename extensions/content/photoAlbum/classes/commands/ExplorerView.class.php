<?php

namespace PhotoAlbum\Commands;

class ExplorerView extends \AbstractCommand implements \IFrameCommand {

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

        if (isset($this->id)) {
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            if ($object instanceof \steam_exit) {
                $object = $object->get_exit();
                $this->id = $object->get_id();
            }
        } else {

            $currentUser = \lms_steam::get_current_user();
            $object = $currentUser->get_workroom();
            $this->id = $object->get_id();
        }

        if (!$object instanceof \steam_object) {
           \ExtensionMaster::getInstance()->send404Error();
        }

        $objectModel = \AbstractObjectModel::getObjectModel($object);

        if ($object && $object instanceof \steam_container) {
            $count = $object->count_inventory();
            if ($count > 500) {
                throw new \Exception("Es befinden sich $count Objekte in diesem Ordner. Das Laden ist nicht möglich.");
            }
            try{
                $objects = $object->get_inventory();
            }
            catch(\NotFoundException $e) {\ExtensionMaster::getInstance()->send404Error();}
            catch(\AccessDeniedException $e) {throw new \Exception("", E_USER_ACCESS_DENIED);}

        } else {
            $objects = array();
        }
        $title = getCleanName($object);

        $parent = $object->get_environment();
        if ($parent instanceof \steam_container) {
            //$parentLink = array("name"=>"nach oben", "link"=>PATH_URL . "explorer/Index/" . $parent->get_id() . "/");
            $parentLink = "";
        } else {
            $parentLink = "";
        }

        $breadcrumb = new \Widgets\Breadcrumb();
        $breadcrumb->setData(array($parentLink, array("name" => "<svg><use xlink:href='" . PATH_URL . "explorer/asset/icons/mimetype/svg/gallery.svg#gallery'/></svg> " . $title . " ")));


        $this->getExtension()->addJS();
        $this->getExtension()->addCSS();

        if ($object->check_access(SANCTION_SANCTION)) {
            $actionBar = new \Widgets\ActionBar();
            //$actionBar->setActions(array(
              //array("name" => "Galerie-Ansicht", "link" => PATH_URL . "photoAlbum/index/" . $this->id . "/"),
              //array("name" => "Neues Bild", "ajax" => array("onclick" => array("command" => "Addpicture", "params" => array("id" => $this->id), "requestType" => "popup"))),
              //array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "Properties", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "explorer"))),
              //array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "explorer")))));
        } else {
            $actionBar = new \Widgets\ActionBar();
            //$actionBar->setActions(array(
              //array("name" => "Galerie-Ansicht", "link" => PATH_URL . "photoAlbum/index/" . $this->id . "/"),
              //array("name" => "Neues Bild", "ajax" => array("onclick" => array("command" => "Addpicture", "params" => array("id" => $this->id), "requestType" => "popup"))),
              //array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "Properties", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "explorer")))));
        }

        $environment = new \Widgets\RawHtml();
        $environment->setHtml("<input type=\"hidden\" id=\"environment\" name=\"environment\" value=\"{$this->id}\">");

        $loader = new \Widgets\Loader();
        $loader->setWrapperId("explorerWrapper");
        $loader->setMessage("Lade Fotos...");
        $loader->setCommand("loadContent");
        $loader->setParams(array("id" => $this->id));
        $loader->setElementId("explorerWrapper");
        $loader->setType("updater");
        $loader->setNamespace("Photoalbum");

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml("<div id=\"explorerContent\">" . $breadcrumb->getHtml() . $environment->getHtml() . $loader->getHtml() . "</div>");

        $rawHtml->addWidget($breadcrumb);
        $rawHtml->addWidget($environment);
        $rawHtml->addWidget($loader);

        $frameResponseObject->setTitle($title);
        $frameResponseObject->addWidget($actionBar);
        $frameResponseObject->addWidget($rawHtml);
        return $frameResponseObject;
    }

}

?>
