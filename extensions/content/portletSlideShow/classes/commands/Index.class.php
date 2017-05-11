<?php

namespace PortletSlideShow\Commands;

class Index extends \AbstractCommand implements \IIdCommand, \IFrameCommand {

    static $JSloaded = false; //only load the JS code once
    private $contentHtml;
    private $listViewer;
    private $objectId;
    private $galleryId;

    public function validateData(\IRequestObject $requestObject) {

        //robustness for missing ids and objects
        try {
            $this->objectId = $requestObject->getId();
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->objectId);
        } catch (\Exception $e) {
            \ExtensionMaster::getInstance()->send404Error();
        }

        if (!$object instanceof \steam_object) {
            \ExtensionMaster::getInstance()->send404Error();
        }

        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $portlet = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->objectId);
        $this->galleryId = $portlet->get_attribute("PORTLET_SLIDESHOW_GALERY_ID");
        $params = $requestObject->getParams();


        //reference handling
        if (isset($params["referenced"]) && $params["referenced"] == true) {
            if (!$portlet->check_access_read()) {
                $this->listViewer = new \Widgets\RawHtml();
                $this->listViewer->setHtml("");
                return null;
            }

            $portletIsReference = true;
            $referenceId = $params["referenceId"];
            $realPortlet = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $referenceId);
            $column = $realPortlet->get_environment();
        } else {
            $portletIsReference = false;
            $column = $portlet->get_environment();
        }

        $width = $column->get_attribute("bid:portal:column:width");
        if (strpos($width, "px") === TRUE) {
            $width = substr($width, 0, count($width) - 3);
        }

        $portletName = getCleanName($portlet);
        $portletInstance = \PortletSlideShow::getInstance();
        $portletPath = $portletInstance->getExtensionPath();

        $tmpl = new \HTML_TEMPLATE_IT();
        $tmpl->loadTemplateFile($portletPath . "/ui/html/index.template.html");
        $tmpl->setVariable("PORTLET_ID", $portlet->get_id());

        //headline
        $tmpl->setCurrentBlock("BLOCK_SLIDESHOW_HEADLINE");
        $tmpl->setVariable("HEADLINE", $portletName);

        //reference icon
        if ($portletIsReference) {
            $referIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/refer.svg";
            $envId = $portlet->get_environment()->get_environment()->get_id();
            $envUrl = PATH_URL . "portal/index/" . $envId;
            $tmpl->setVariable("REFERENCE_ICON", "<a href='{$envUrl}' target='_blank'><svg><use xlink:href='{$referIcon}#refer'></svg></a>");
        }
        $popupmenu = new \Widgets\PopupMenu();
        $popupmenu->setData($portlet);
        $popupmenu->setElementId("portal-overlay");
        if (!$portletIsReference) {
            $popupmenu->setNamespace("PortletSlideShow");
            $popupmenu->setParams(array(array("key" => "portletObjectId", "value" => $portlet->get_id())));
            $popupmenu->setCommand("GetPopupMenuHeadline");
        } else {
            $popupmenu->setNamespace("Portal");
            $popupmenu->setParams(array(array("key" => "sourceObjectId", "value" => $portlet->get_id()),
                array("key" => "linkObjectId", "value" => $referenceId)
            ));
            $popupmenu->setCommand("PortletGetPopupMenuReference");
        }
        $tmpl->setVariable("POPUPMENU_HEADLINE", $popupmenu->getHtml());

        if (trim($portletName) == "") {
            $tmpl->setVariable("HEADLINE_CLASS", "headline editbutton");
        } else {
            $tmpl->setVariable("HEADLINE_CLASS", "headline");
        }
        $tmpl->parse("BLOCK_SLIDESHOW_HEADLINE");


        try {
            $gallery = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $portlet->get_attribute("PORTLET_SLIDESHOW_GALERY_ID"));
        } catch (\steam_exception $ex) {
            $gallery = "";
        }

        $tmpl->setVariable("OBJECT_ID", $this->objectId);

        if (getObjectType($gallery) !== "gallery") {
            $tmpl->setVariable("ERROR", "<div style='margin:5px;margin-bottom:15px;'>Das Objekt mit der ID " . $this->galleryId . " kann nicht als Diashow angezeigt werden, da es kein Fotoalbum ist.</div>");
        } else if (!$gallery->check_access_read()) {
            $tmpl->setVariable("ERROR", "<div style='margin:5px;margin-bottom:15px;'>Das Fotoalbum mit der ID " . $this->galleryId . " kann nicht angezeigt werden, da Sie nicht über die erforderlichen Leserechte verfügen.</div>");
        } else if (getObjectType($gallery) === "gallery" && $gallery->check_access_read()) {
            $inventory = $gallery->get_inventory();
            if (count($inventory) < 1) {
                $tmpl->setVariable("ERROR", "<div style='margin:5px;margin-bottom:15px;'>Das Fotoalbum mit der ID " . $this->galleryId . " enthält keine Bilder.</div>");
            } else {
                $tmpl->setVariable(
                        "JS_FUNCTION", 
                        "jQuery(document).ready(
                        function ($) {
                            var slider = \$('.my-slider_" . $this->objectId . "').unslider(
                            {
                                arrows: {
                                    prev: '<svg class=\"unslider-arrow prev\"><use xlink:href=\'" . \Explorer::getInstance()->getAssetUrl() . "icons/button_left.svg#button_left\'/></svg>',
                                    next: '<svg class=\"unslider-arrow next\"><use xlink:href=\'" . \Explorer::getInstance()->getAssetUrl() . "icons/button_right.svg#button_right\'/></svg>'
                                }, 
                                keys:false, 
                                nav: false
                                } 
                            );
                            $(document).on('click', 
                                function(event, index, slide) {
                                    setTimeout(function (){ $(window).trigger(\"scroll\")}, 1000); //wait until the gallery is scrolled and the next (invisible) image is in the viewport
                                }
                            );
                            initialize('a.slideshow_" . $this->objectId . "');
                            }
                        );"
                );
                $tmpl->setVariable("WIDTH_HEIGHT", $width);
                $tmpl->setVariable("OBJECT_ID", $this->objectId);


                if (!self::$JSloaded) {
                    \lms_portal::get_instance()->add_javascript_src("unslider", PATH_URL . "styles/standard/javascript/unslider/src/js/unslider.js");
                    \lms_portal::get_instance()->add_javascript_src("lazyload", PATH_URL . "styles/standard/javascript/lazy/jquery.lazyload.min.js");
                    \lms_portal::get_instance()->add_javascript_src("colorbox", PATH_URL . "styles/standard/javascript/colorbox/jquery.colorbox.js");
                    \lms_portal::get_instance()->add_css_style_link(PATH_URL . "styles/standard/javascript/unslider/dist/css/unslider.css");
                    \lms_portal::get_instance()->add_css_style_link(PATH_URL . "styles/standard/javascript/unslider/dist/css/unslider-dots.css");
                    $this->getExtension()->addCSS();
                    $this->getExtension()->addJS();
                    self::$JSloaded = true;
                }
                foreach ($inventory as $i => $pic) {
                    if ($pic->check_access_read()) {
                        $id = $pic->get_id();
                        $description = $pic->get_attribute("OBJ_DESC");
                        $title = (trim($description) == "") ? $pic->get_name() : $pic->get_name() . " (" . $description . ")";

                        if (defined('PHOTOALBUM_ROTATE_IMAGES') && PHOTOALBUM_ROTATE_IMAGES) {
                            //the image size has to be set, otherwise extensions/system/download/classes/commands/AbstractDownloadCommand.class.php (68)
                            //would refer to the document download where the ThumbnailHelper is circumvented
                            $fullscreen = PATH_URL . "download/image/" . $id . "/-1/-1";
                        } else {
                            $fullscreen = PATH_URL . "download/document/" . $id . PHOTOALBUM_ROTATE_IMAGES;
                        }

                        $pictureURL = PATH_URL . "download/image/" . $id . "/" . ($width - 10) . "/" . ($width - 10);


                        $tmpl->setCurrentBlock("BLOCK_SLIDESHOW_BODY");
                        $tmpl->setVariable("OBJECT_ID2", $this->objectId);
                        $tmpl->setVariable("TITLE", $title);
                        if ($portlet->get_attribute("PORTLET_SLIDESHOW_SHOW_DESCRIPTION") === "true") {
                            $tmpl->setVariable("DESCRIPTION", "<div class='slideshow_description'>" . $description . "</div>");
                        }
                        //$tmpl->setVariable("CLASS", $class);
                        $tmpl->setVariable("PATH", $pictureURL);
                        $tmpl->setVariable("FULLSCREENPATH", $fullscreen);
                        $tmpl->parse("BLOCK_SLIDESHOW_BODY");
                    }
                }
            }
        }
        
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($tmpl->get());
        $this->contentHtml = $rawHtml;
    }

    public function idResponse(\IdResponseObject $idResponseObject) {
        $idResponseObject->addWidget($this->contentHtml);
        //$idResponseObject->addWidget($this->listViewer);
        //$idResponseObject->addWidget($this->endHtml);
        return $idResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $frameResponseObject->addWidget($this->contentHtml);
        //$frameResponseObject->addWidget($this->listViewer);
        //$frameResponseObject->addWidget($this->endHtml);
        return $frameResponseObject;
    }

}

?>
