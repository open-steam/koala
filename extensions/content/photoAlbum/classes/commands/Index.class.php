<?php

namespace PhotoAlbum\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;
    private $rowHtmlBegin = '<div class="row">';
    private $rowHtmlEnd = '</div>';
    private $cssStyle = '<style></style>';

    private function getPictureHtml($name, $path, $fullscreenPath, $title, $class) {
        return '<div class="pic"><a class="slideshow" title="' . $title . '" href="' . $fullscreenPath . '"><img class="lazy ' . $class . '" src="' . $path . '"></a></div>';
    }

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

//TODO: CLEAN UP!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
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

        $titleCss = '#gallery-title{margin-left:50px;}';
        if ($gallery->check_access(SANCTION_SANCTION)) {
            $actionBar = new \Widgets\ActionBar();
            //$actionBar->setActions(array(
              //array("name" => "Explorer-Ansicht", "link" => PATH_URL . "photoAlbum/explorerView/" . $this->id . "/"),
              //array("name" => "Neues Bild", "ajax" => array("onclick" => array("command" => "Addpicture", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "photoAlbum"))),
              //array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "Properties", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "explorer"))),
              //array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "explorer")))));
            $frameResponseObject->addWidget($actionBar);
        } else if ($gallery->check_access_write()) {
            $actionBar = new \Widgets\ActionBar();
            //$actionBar->setActions(array(
              //array("name" => "Explorer-Ansicht", "link" => PATH_URL . "photoAlbum/explorerView/" . $this->id . "/"),
              //array("name" => "Neues Bild", "ajax" => array("onclick" => array("command" => "Addpicture", "params" => array("id" => $this->id), "requestType" => "popup"))),
              //array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "Properties", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "explorer")))));
            $frameResponseObject->addWidget($actionBar);
        }

        //$this->getExtension()->addJS();
        $this->getExtension()->addCSS();

        $titleHtml = new \Widgets\RawHtml();
        $title = getCleanName($gallery);
        $titleHtml->setCss($titleCss .
                '#gallery{margin-top:25px;}
            .gal-element{background:#EEEEEE;height:204px;width:204px;margin:5px;float:left;}
            .row{clear:both;margin-left:45px;margin-right:45;}
            .pic {width:200px;height:200px;max-height:200px;max-width:200px;display: table-cell;
    vertical-align: middle;
	text-align: center;}
            img.lazy{padding:2px;max-width:200px;}
          ');
        $titleHtml->setHtml('<h1 id="gallery-title">' . $title . '</h1>');
        $frameResponseObject->addWidget($titleHtml);

        $inventory = $gallery->get_inventory();

        $html = $this->cssStyle;
        $html .= '<div id="gallery">';
        $invisiblePicCounter = 0;
        foreach ($inventory as $i => $pic) {
            if (!$pic->check_access_read()) {
                $invisiblePicCounter++;
            } else {
                if (($i - $invisiblePicCounter) % 4 == 0) {
                    $html .= $this->rowHtmlBegin;
                }
                $html .= '<div class="gal-element">';
                $name = $pic->get_name();
                $desc = $pic->get_attribute("OBJ_DESC");
                $keywords = $pic->get_attribute("OBJ_KEYWORDS");
                $keywordString = "";
                if($keywords !== 0 && is_array($keywords)){
                    foreach ($keywords as $key) {
                        $keywordString.= $key. " ";
                    }
                   $keywordString =  trim($keywordString);
                }
                if ($desc !== 0 && $desc !== "") {
                    $name.= " | " . $desc;
                }
                if($keywordString !== ""){
                    $name .= " | " . $keywordString;
                }
                $fullscreen = PATH_URL . "download/document/" . $pic->get_id();

                if($pic->get_attribute(DOC_MIME_TYPE) === "image/svg+xml"){
                  $pictureURL = PATH_URL . "download/document/" . $pic->get_id();
                  $html .= $this->getPictureHtml($name, $pictureURL, $fullscreen, $name, "");
                } elseif($pic->get_attribute(DOC_MIME_TYPE) === "image/bmp"){
                  $pictureURL = PATH_URL . "download/document/" . $pic->get_id();
                  $html .= $this->getPictureHtml($name, $pictureURL, $fullscreen, $name, "bmp");
                }
                else{
                  $pictureURL = PATH_URL . "download/image/" . $pic->get_id() . "/200/200";
                  $html .= $this->getPictureHtml($name, $pictureURL, $fullscreen, $name, "");
                }

                $html .= '</div>';
                if (($i - $invisiblePicCounter) % 4 == 3) {
                    $html .= $this->rowHtmlEnd;
                }
            }
        }
        $html .= $this->rowHtmlBegin;
        $html .= $this->rowHtmlEnd;

        $html.= "<script>

function fullscreen() {
 var element = document.getElementById('colorbox');

if (element.requestFullScreen) {

    if (!document.fullScreen) {
        element.requestFullscreen();

    } else {
        document.exitFullScreen();
    }

 } else if (element.mozRequestFullScreen) {

    if (!document.mozFullScreen) {
        element.mozRequestFullScreen();

    } else {
       document.mozCancelFullScreen();
    }

} else if (element.webkitRequestFullScreen) {

    if (!document.webkitIsFullScreen) {
        element.webkitRequestFullScreen();


    } else {
        document.webkitCancelFullScreen();
    }

}
setTimeout(function(){jQuery.colorbox.reload()},1000);

}
 $(document).ready(function() {jQuery('img.lazy').lazyload({failure_limit : 10});});
            $('a.slideshow').colorbox({rel: 'slideshow', slideshow:true, scalePhotos: true,photo:true, width: '100%', height:'100%',slideshowAuto:false, transition:'elastic', escKey:false, reposition:true,
 onOpen: function(){jQuery('#cboxContent').append('<img id=\"cboxFullscreen\" onclick=\"fullscreen()\" src=\"".\PhotoAlbum::getInstance()->getAssetUrl()."icons/image_fullscreen_grey.png"."\">');
                    jQuery('#gallery').hide();

                    $('#cboxFullscreen').mouseover(function(){this.src='".\PhotoAlbum::getInstance()->getAssetUrl()."icons/image_fullscreen_black.png"."';});
                    $('#cboxFullscreen').mouseout(function(){this.src='".\PhotoAlbum::getInstance()->getAssetUrl()."icons/image_fullscreen_grey.png"."';});}

,onCleanup: function(){
jQuery('#gallery').show();
var element = document.getElementById('colorbox');
 if (element.requestFullScreen) {

    if (!document.fullScreen) {

    } else {
        document.exitFullScreen();
    }

 } else if (element.mozRequestFullScreen) {

    if (!document.mozFullScreen) {

    } else {
       document.mozCancelFullScreen();
    }

} else if (element.webkitRequestFullScreen) {

    if (!document.webkitIsFullScreen) {

    } else {
        document.webkitCancelFullScreen();
    }
} }
            });</script>";
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($html);


        $frameResponseObject->addWidget($rawHtml);
        return $frameResponseObject;
    }

}

?>
