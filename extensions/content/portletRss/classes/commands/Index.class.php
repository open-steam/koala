<?php

namespace PortletRss\Commands;

class Index extends \AbstractCommand implements \IFrameCommand, \IIdCommand {

    private $params;
    private $id;
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
        $portlet = $portletObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);

        //icon
        $referIcon = \Portal::getInstance()->getAssetUrl() . "icons/refer_white.png";

        //reference handling
        $params = $requestObject->getParams();

        //reference handling
        if (isset($params["referenced"]) && $params["referenced"] == true) {
            $portletIsReference = true;
            $referenceId = $params["referenceId"];
            if (!$portlet->check_access_read()) {
                $this->rawHtmlWidget = new \Widgets\RawHtml();
                $this->rawHtmlWidget->setHtml("");
                return null;
            }
        } else {
            $portletIsReference = false;
        }

        $portletName = $portlet->get_attribute(OBJ_DESC);

        $this->getExtension()->addCSS();
        $this->getExtension()->addJS();

        //old bib
        include_once(PATH_BASE . "core/lib/bid/slashes.php");


        //get content of portlet
        $content = $portlet->get_attribute("bid:portlet:content");
        if (is_array($content) && count($content) > 0) {
            array_walk($content, "_stripslashes");
        } else {
            $content = array();
        }

        $portletInstance = \PortletRss::getInstance();
        $portletPath = $portletInstance->getExtensionPath();

        $num_items = (isset($content["num_items"])) ? $content["num_items"] : 0;
        if (isset($content["address"])) {
            $feed = new \SimplePie();
            $feed->enable_cache(true);
            $feed->enable_order_by_date(false);
            $feed->set_cache_location(PATH_CACHE);
            $feed->set_feed_url(derive_url($content["address"]));
            $feed->init();
            $feed->handle_content_type();
            if ($num_items == 0) {
                $items = $feed->get_items();
            } else {
                $items = array_slice($feed->get_items(), 0, $num_items);
            }
        }

        $desc_length = (isset($content["desc_length"])) ? $content["desc_length"] : 0;
        if (isset($content["allow_html"])) {
            $allow_html = ($content["allow_html"] == "checked" ? true : false);
        } else {
            $allow_html = false;
        }


        $UBB = new \UBBCode();
        include_once(PATH_BASE . "core/lib/bid/derive_url.php");


        $portletFileName = $portletPath . "/ui/html/index.html";
        $tmpl = new \HTML_TEMPLATE_IT();
        $tmpl->loadTemplateFile($portletFileName);

        $tmpl->setVariable("EDIT_BUTTON", "");
        $tmpl->setVariable("PORTLET_ID", $portlet->get_id());
        $tmpl->setVariable("RSS_NAME", $portletName);

        //if the title is empty the headline will not be displayed (only in edit mode)
        if ($portletName == "" || $portletName == " ") {
            $tmpl->setVariable("HEADLINE_CLASS", "headline editbutton");
        } else {
            $tmpl->setVariable("HEADLINE_CLASS", "headline");
        }

        //refernce icon
        if ($portletIsReference) {
            $titleTag = "title='" . \Portal::getInstance()->getReferenceTooltip() . "'";
            $envId = $portlet->get_environment()->get_environment()->get_id();
            $envUrl = PATH_URL . "portal/index/" . $envId;
            $tmpl->setVariable("REFERENCE_ICON", "<a $titleTag href='{$envUrl}' target='_blank'><img src='{$referIcon}'></a>");
        }

        //popupmenu
        if (!$portletIsReference && $portlet->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())) {
            $popupmenu = new \Widgets\PopupMenu();
            $popupmenu->setData($portlet);
            $popupmenu->setNamespace("PortletRss");
            $popupmenu->setElementId("portal-overlay");
            $tmpl->setVariable("POPUPMENU", $popupmenu->getHtml());
        }

        if ($portletIsReference && $portlet->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())) {
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


        if (sizeof($content) > 0) {
            if ($feed->error()) {
                $tmpl->setVariable("NOITEMSTEXT", "RSS-Ladefehler");
            } else {
                if (count($items) == 0) {
                    $tmpl->setVariable("NOITEMSTEXT", "RSS-Feed ist leer.");
                } else {
                    foreach ($items as $item) {
                        $tmpl->setCurrentBlock("BLOCK_RSS_ITEM");
                        if ($allow_html) {
                            $itemtitle = $item->get_title();
                            $itemdesc = $item->get_description();
                        } else {
                            $itemtitle = strip_tags($item->get_title());
                            $itemdesc = strip_tags($item->get_description());
                        }

                        if ($desc_length == 0) {
                            $itemdesc = "";
                        } else if (($desc_length > 0 && strlen($itemdesc) > $desc_length) && !$allow_html) {
                            $itemdesc = substr($itemdesc, 0, $desc_length) . "...";
                        }

                        $tmpl->setVariable("ITEMTITLE", $itemtitle);
                        $tmpl->setVariable("ITEMDESC", $itemdesc);

                        $tmpl->setVariable("ITEMURL", derive_url($item->get_permalink()));
                        $tmpl->setVariable("LINK", "");

                        $tmpl->parse("BLOCK_RSS_ITEM");
                    }
                }
            }
        } else {
            $tmpl->setVariable("NOITEMSTEXT", "RSS-Feed nicht konfiguriert.");
        }

        $htmlBody = $tmpl->get();
        $this->content = $htmlBody;

        //widgets
        $outputWidget = new \Widgets\RawHtml();
        $outputWidget->setHtml($htmlBody);
        $outputWidget->addWidget(new \Widgets\PopupMenu());
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
