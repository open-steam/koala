<?php

namespace PhotoAlbum\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;
    private $content;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        if ($this->id === "") {
            $errorHtml = new \Widgets\RawHtml();
            $errorHtml->setHtml("Die angeforderte Seite kann nicht dargestellt werden.");
            $frameResponseObject->addWidget($errorHtml);
            return $frameResponseObject;
        }

        $gallery = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $objType = getObjectType($gallery);
        if ($objType !== "gallery") {
            $errorHtml = new \Widgets\RawHtml();
            $errorHtml->setHtml("Die angeforderte Seite kann nicht dargestellt werden.");
            $frameResponseObject->addWidget($errorHtml);
            return $frameResponseObject;
        }
        if (!($gallery->check_access_read())) {
            $errorHtml = new \Widgets\RawHtml();
            $errorHtml->setHtml("Das Fotoalbum kann nicht angezeigt werden, da Sie nicht über die erforderlichen Leserechte verfügen.");
            $frameResponseObject->addWidget($errorHtml);
            return $frameResponseObject;
        }

        \lms_portal::get_instance()->add_javascript_src("lazyload", PATH_URL . "styles/standard/javascript/lazy/jquery.lazyload.min.js");
        \lms_portal::get_instance()->add_javascript_src("colorbox", PATH_URL . "styles/standard/javascript/colorbox/jquery.colorbox.js");

        $this->getExtension()->addJS();
        $this->getExtension()->addCSS();

        $titleHtml = new \Widgets\RawHtml();
        $title = getCleanName($gallery);
        $titleHtml->setHtml("<svg style='width:16px; height:16px; float:left; color:#3a6e9f; right:5px; position:relative;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/mimetype/svg/gallery.svg#gallery'/></svg><h1 id='gallery-title'>" . $title . "</h1>");
        $frameResponseObject->addWidget($titleHtml);

        $inventory = $gallery->get_inventory();

        $this->content = \Photoalbum::getInstance()->loadTemplate("index.template.html");

        $invisiblePicCounter = 0;
        $rowOpen = false;
        foreach ($inventory as $i => $pic) {
            if (!$pic->check_access_read()) {
                $invisiblePicCounter++;
            } else {
                if (($i - $invisiblePicCounter) % 4 == 0) {
                    $this->content->setCurrentBlock("BLOCK_ROW");
                    $rowOpen = true;
                }
                $id = $pic->get_id();
                $name = $pic->get_name();
                $fullscreen = PATH_URL . "download/document/" . $id;
                $class = "";

                if($pic->get_attribute(DOC_MIME_TYPE) === "image/svg+xml"){
                  $pictureURL = PATH_URL . "download/document/" . $id;
                } elseif($pic->get_attribute(DOC_MIME_TYPE) === "image/bmp"){
                  $pictureURL = PATH_URL . "download/document/" . $id;
                  $class = "bmp";
                }
                else{
                  $pictureURL = PATH_URL . "download/image/" . $id . "/200/200";
                }

                $this->content->setCurrentBlock("BLOCK_PICTURE");
                $this->content->setVariable("TITLE", $name);
                $this->content->setVariable("FULLSCREENPATH", $fullscreen);
                $this->content->setVariable("CLASS", $class);
                $this->content->setVariable("PATH", $pictureURL);
                $this->content->parse("BLOCK_PICTURE");

                if (($i - $invisiblePicCounter) % 4 == 3) {
                    $this->content->parse("BLOCK_ROW");
                    $rowOpen = false;
                }

            }
        }

        if($rowOpen) $this->content->parse("BLOCK_ROW");

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($this->content->get() . "<script>initialize()</script>");
        $frameResponseObject->addWidget($rawHtml);
        return $frameResponseObject;
    }

}

?>
