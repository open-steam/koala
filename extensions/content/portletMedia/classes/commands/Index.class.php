<?php

namespace PortletMedia\Commands;

class Index extends \AbstractCommand implements \IFrameCommand, \IIdCommand {

    private $params;
    private $content;
    private $rawHtmlWidget;

    public function validateData(\IRequestObject $requestObject) {

        //robustness for missing ids and objects
        try {
            $objectId = $requestObject->getId();
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        } catch (\Exception $e) {
            \ExtensionMaster::getInstance()->send404Error();
        }

        if (!$object instanceof \steam_object) {
            \ExtensionMaster::getInstance()->send404Error();
        }

        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $objectId = $requestObject->getId();
        $portletInstance = \PortletMedia::getInstance();
        $portletPath = $portletInstance->getExtensionPath();
        $portlet = $portletObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);

        $this->getExtension()->addCSS();


        //reference handling
        $params = $requestObject->getParams();
        if (isset($params["referenced"]) && $params["referenced"] == true) {
            if (!$portlet->check_access_read()) {
                $this->rawHtmlWidget = new \Widgets\RawHtml();
                $this->rawHtmlWidget->setHtml("");
                return null;
            }

            $portletIsReference = true;
            $referenceId = $params["referenceId"];
        } else {
            $portletIsReference = false;
        }

        include_once(PATH_BASE . "core/lib/bid/slashes.php");

        //get content of portlet
        $content = $portlet->get_attribute("bid:portlet:content");
        if (is_array($content) && count($content) > 0) {
            array_walk($content, "_stripslashes");
        } else {
            $content = array();
        }

        if (sizeof($content) > 0) {
            $portletFileName = $portletPath . "/ui/html/index.html";
            $tmpl = new \HTML_TEMPLATE_IT();
            $tmpl->loadTemplateFile($portletFileName);

            //popupmenu
            if (!$portletIsReference && $portlet->check_access_write(\lms_steam::get_current_user())) {
                $popupmenu = new \Widgets\PopupMenu();
                $popupmenu->setData($portlet);
                $popupmenu->setNamespace("PortletMedia");
                $popupmenu->setElementId("portal-overlay");
                $tmpl->setVariable("POPUPMENU", $popupmenu->getHtml());
            }

            if ($portletIsReference && $portlet->check_access_write(\lms_steam::get_current_user())) {
                $popupmenu = new \Widgets\PopupMenu();
                $popupmenu->setData($portlet);
                $popupmenu->setNamespace("Portal");
                $popupmenu->setElementId("portal-overlay");
                $popupmenu->setParams(array(array("key" => "sourceObjectId", "value" => $portlet->get_id()),
                    array("key" => "linkObjectId", "value" => $referenceId)
                ));
                $popupmenu->setCommand("PortletGetPopupMenuReference");
                $tmpl->setVariable("POPUPMENU", $popupmenu->getHtml());
            }

            $tmpl->setVariable("PORTLET_ID", $portlet->get_id());
            $tmpl->setVariable("HEADLINE", $content["headline"]);

            //if the title is empty the headline will not be displayed (only in edit mode)
            if (trim($content["headline"]) == "") {
                $tmpl->setVariable("HEADLINE_CLASS", "headline editbutton");
            } else {
                $tmpl->setVariable("HEADLINE_CLASS", "headline");
            }

            //reference icon
            if ($portletIsReference) {
                $referIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/refer.svg";
                $titleTag = "title='" . \Portal::getInstance()->getReferenceTooltip() . "'";
                $envId = $portlet->get_environment()->get_environment()->get_id();
                $envUrl = PATH_URL . "portal/index/" . $envId;
                $tmpl->setVariable("REFERENCE_ICON", "<a $titleTag href='{$envUrl}' target='_blank'><svg><use xlink:href='{$referIcon}#refer'></svg></a>");
            }

            //description
            if ($content["description"] === 0) {
                $tmpl->setVariable("DESCRIPTION", "");
            } else {
                $tmpl->setVariable("DESCRIPTION", $content["description"]);
            }

            $media_type = $content["media_type"];
            $url = $content["url"];

            //if internal object & page encrypted via https & url only contains http ---> replace with https
            if (strpos(strtolower($url), "download/document") && strpos(PATH_URL, "https") && strpos(strtolower($url), "http:")) {
                $url = str_replace("http", "https", strtolower($url));
            }

            $pathArray = explode("/", $url);
            $currentObjectID = "";
            for ($count = 0; $count < count($pathArray); $count++) {
                if (intval($pathArray[$count]) !== 0) {
                    $currentObjectID = $pathArray[$count];
                    break;
                }
            }

            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectID);
            if ($object instanceof \steam_document) {
                $mime = $object->get_attribute(DOC_MIME_TYPE);
            }

            //determine youtube video
            $isYoutubeVideo = false;
            if (strpos($url, "youtube")) {
                $isYoutubeVideo = true;
            }

            if ($portletIsReference) {
                $columnWidth = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $referenceId)->get_environment()->get_attribute("bid:portal:column:width");
            } else {
                $columnWidth = intval($portlet->get_environment()->get_attribute("bid:portal:column:width"));
            }

            if (strpos($columnWidth, "px") === TRUE) {
                $columnWidth = substr($columnWidth, 0, count($columnWidth) - 3);
            }

            if ($media_type == "image") {
                $tmpl->setCurrentBlock("image");
                $tmpl->setVariable("URL", $url);
                $tmpl->parse("image");
            } else if ($media_type == "movie" && !$isYoutubeVideo) {
                $tmpl->setCurrentBlock("movie");



                if ($mime && strpos($mime, "mp4") !== false) { //mp4 format, use html 5 video tag
                    $tmpl->setVariable("MEDIA_PLAYER", '<div class="CSSLoader"></div><video controls width="' . intval($columnWidth - 10) . '" oncanplay="$(this).prev().remove();$(this).show();" style="display:none;"><source src="' . $url . '" type="video/mp4">Ihr Browser unterstützt das Video-Element nicht.</video>');
                } else {
                    $mediaplayerHtml = new \Widgets\Videoplayer();
                    $mediaplayerHtml->setHeight(intval(($columnWidth - 10) / 4 * 3));
                    $mediaplayerHtml->setWidth($columnWidth - 10);
                    $mediaplayerHtml->setTarget($url);
                    $tmpl->setVariable("MEDIA_PLAYER", $mediaplayerHtml->getHtml());
                }
                $tmpl->parse("movie");
            } else if ($media_type == "movie" && $isYoutubeVideo) {
                $tmpl->setCurrentBlock("movieYoutube");

                $youTubeUrlCode = "";


                $tmpl->setVariable("MEDIA_PLAYER_WIDTH", $columnWidth - 10);
                $tmpl->setVariable("MEDIA_PLAYER_HEIGHT", intval(($columnWidth - 10) / 4 * 3));

                //case watch
                if (strpos($url, "watch")) {
                    $begin = strpos($url, "watch?v=") + 8;
                    $lenght = strpos(substr($url, $begin), "&");
                    if ($lenght) {
                        $youTubeUrlCode = substr($url, $begin, $lenght);
                    } else {
                        $youTubeUrlCode = substr($url, $begin);
                    }
                }

                //case embed
                else if (strpos($url, "embed")) {
                    $begin = strpos($url, "/embed/") + 7;
                    $lenght = strpos(substr($url, $begin), '"');
                    $youTubeUrlCode = substr($url, $begin, $lenght);
                }

                $tmpl->setVariable("YOUTUBE_URL_CODE", $youTubeUrlCode . "/?wmode=opaque");
                $tmpl->parse("movieYoutube");
            } else if ($media_type == "audio") {
                $tmpl->setCurrentBlock("audio");

                $columnWidth -= 10;

                if ($mime && strpos($mime, "mpeg") !== false) { //mp3 format, use html 5 audio tag
                    $mediaPlayerUrl = getDownloadUrlForObjectId($currentObjectID);
                    $tmpl->setVariable("AUDIO_PLAYER", '<div class="CSSLoader"></div><audio controls style="width:' . $columnWidth . 'px; display:none;" oncanplay="$(this).prev().remove();$(this).show();"><source src="' . $mediaPlayerUrl . '" type="audio/mpeg">Ihr Browser unterstützt das Audio-Element nicht.</audio>');
                } else {
                    $media_player = $portletInstance->getAssetUrl() . 'emff_lila_info.swf';
                    $tmpl->setVariable("AUDIO_PLAYER", '<object style="width:' . $columnWidth . 'px; height:' . round($columnWidth * 11 / 40) . 'px" type="application/x-shockwave-flash" data="' . $media_player . '"><param name="movie" value="{MEDIA_PLAYER}" /><param name="FlashVars" value="src=' . $url . '" /><param name="bgcolor" value="#cccccc"></object>');
                }
                $tmpl->parse("audio");
            }
            if ($portlet->check_access_write(\lms_steam::get_current_user())) {
                $tmpl->setCurrentBlock("BLOCK_EDIT_BUTTON");
                $tmpl->setVariable("PORTLET_ID_EDIT", $portlet->get_id());
                $tmpl->parse("BLOCK_EDIT_BUTTON");
            }

            //output
            $htmlBody = $tmpl->get();
        } else {
            //output for no content
            $htmlBody = "";
        }
        $this->content = $htmlBody;

        //widgets
        $outputWidget = new \Widgets\RawHtml();
        $outputWidget->setHtml($htmlBody);

        //popummenu
        $popupmenu = new \Widgets\PopupMenu();
        $popupmenu->setData($portlet);
        $popupmenu->setNamespace("PortletMedia");
        $popupmenu->setElementId("portal-overlay");
        $outputWidget->addWidget($popupmenu);

        $this->rawHtmlWidget = $outputWidget;
    }

    public function idResponse(\IdResponseObject $idResponseObject) {
        $idResponseObject->addWidget($this->rawHtmlWidget);
        return $idResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $frameResponseObject->setTitle("Portal");
        $frameResponseObject->addWidget($this->rawHtmlWidget);
        return $frameResponseObject;
    }

}

?>
