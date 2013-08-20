<?php

namespace PhotoAlbum\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;
    private $rowHtmlBegin = '<div class="row">';
    private $rowHtmlEnd = '</div>';
    private $cssStyle = '<style>
</style>';

    private function getPictureHtml($name, $path, $fullscreenPath, $title) {
        //  $title .= '<a onclick=\"vollbild();return false;\">Vollbildmodus</a>';
        return '<div class="pic">
<a class="slideshow" title="' . $title . '" href="' . $fullscreenPath . '"><img class="lazy" width="90%" height="90%" src="" data-original="' . $path . '"></a>
</div>';
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
            $errorHtml->setHtml("Sie verfügen nicht über die erforderliche Zugriffsberechtigung! Die Galerie kann nicht angezeigt werden. Sie benötigen mindestens Leserechte!");
            $frameResponseObject->addWidget($errorHtml);
            return $frameResponseObject;
        }
        $titleCss = '#gallery-title{margin-top:-31px;margin-left:50px;}';
        if ($gallery->check_access(SANCTION_SANCTION)) {
            $actionBar = new \Widgets\ActionBar();
            $actionBar->setActions(array(array("name" => "Explorer-Ansicht", "link" => PATH_URL . "photoAlbum/explorerView/" . $this->id . "/"), array("name" => "Neues Bild", "ajax" => array("onclick" => array("command" => "Addpicture", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "photoAlbum"))), array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "Properties", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "explorer"))), array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "explorer")))));
            $frameResponseObject->addWidget($actionBar);
        } else if ($gallery->check_access_write()) {
            $actionBar = new \Widgets\ActionBar();
            $actionBar->setActions(array(array("name" => "Explorer-Ansicht", "link" => PATH_URL . "photoAlbum/explorerView/" . $this->id . "/"), array("name" => "Neues Bild", "ajax" => array("onclick" => array("command" => "Addpicture", "params" => array("id" => $this->id), "requestType" => "popup"))), array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "Properties", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "explorer")))));
            $frameResponseObject->addWidget($actionBar);
        } else if($gallery->check_access_read()){
             $titleCss = '#gallery-title{margin-left:50px;}';
        }
        $titleHtml = new \Widgets\RawHtml();
        $title = getCleanName($gallery);
        $titleHtml->setCss($titleCss.
            '#gallery{margin-top:25px;}
            .gal-element{background:#E0EEEE;height:204px;width:204px;margin:5px;float:left;}
            .row{clear:both;margin-left:45px;margin-right:45;}
            .pic {width:200px;height:200px;}
            img.lazy{padding:10px;}
          }');
        $titleHtml->setHtml('<h2 id="gallery-title">' . $title . '</h2>');
        $frameResponseObject->addWidget($titleHtml);

        $inventory = $gallery->get_inventory();

        $html = $this->cssStyle;
        $html .= '<div id="gallery">';
        foreach ($inventory as $i => $pic) {
            if ($i % 4 == 0) {
                $html .= $this->rowHtmlBegin;
            }
            $html .= '<div class="gal-element">';
            $name = $pic->get_name();
            //$fullscreen = PATH_URL . $pic->get_path();
            $fullscreen = PATH_URL . "download/document/" . $pic->get_id();
            $pictureURL = PATH_URL . "download/image/" . $pic->get_id() . "/200/200";
            $html .= $this->getPictureHtml($name, $pictureURL, $fullscreen, $name);
            $html .= '</div>';
            if ($i % 4 == 3) {
                $html .= $this->rowHtmlEnd;
            }
        }
        $html .= $this->rowHtmlBegin;
        $html .= $this->rowHtmlEnd;

        $html.= "<script>function vollbild() {
 var element = document.getElementById('cboxWrapper');
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
}
 $(document).ready(function() {jQuery('img.lazy').lazyload({failure_limit : 10});});
            $('a.slideshow').colorbox({rel: 'slideshow', slideshow:true, scalePhotos: true,photo:true, width: '100%', height:'100%',slideshowAuto:false, transition:'elastic', escKey:false, 
         
onCleanup: function(){ var element = document.getElementById('cboxWrapper');
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
//title: function(){var fullscreen='<a id=\"fullscreenCbox\" style=\"position:absolute;\" onclick=\"vollbild();\">Vollbild</a>';return $(this).attr('title')+fullscreen;}});</script>";
//TODO: CSS-Settings für VollbildIcon: ca. position:absolute;right:88px;top:-20px;
?>