<?php

namespace PortletTopic\Commands;

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

        $portletName = $portlet->get_attribute(OBJ_DESC);

        $this->getExtension()->addCSS();
        $this->getExtension()->addJS();

        include_once(PATH_BASE . "/core/lib/bid/slashes.php");

        //get content of portlet
        $content = $portlet->get_attribute("bid:portlet:content");
        if (is_array($content) && count($content) > 0) {
            array_walk($content, "_stripslashes");
        } else {
            $content = array();
        }

        $UBB = new \UBBCode();
        include_once(PATH_BASE . "core/lib/bid/derive_url.php");

        $portletInstance = \PortletTopic::getInstance();
        $portletPath = $portletInstance->getExtensionPath();

        $portletFileName = $portletPath . "/ui/html/index.html";
        $tmpl = new \HTML_TEMPLATE_IT();
        $tmpl->loadTemplateFile($portletFileName);

        $tmpl->setVariable("PORTLET_ID", $portlet->get_id());
        $tmpl->setVariable("PORTLET_NAME", $portletName);

        //if the title is empty the headline will not be displayed (only in edit mode)
        if (trim($portletName == "")) {
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

        //popupmenu main
        if (!$portletIsReference && $portlet->check_access_write(\lms_steam::get_current_user())) {
            $tmpl->setCurrentBlock("BLOCK_EDIT_BUTTON_MAIN");
            $tmpl->setVariable("PORTLET_ID_EDIT", $portlet->get_id());

            $popupmenu = new \Widgets\PopupMenu();
            $popupmenu->setData($portlet);
            $popupmenu->setNamespace("PortletTopic");
            $popupmenu->setElementId("portal-overlay");
            $popupmenu->setCommand("GetPopupMenu");
            $popupmenu->setParams(array(array("key" => "menu", "value" => "nerd")));
            $tmpl->setVariable("POPUPMENU", $popupmenu->getHtml());
            $tmpl->parse("BLOCK_EDIT_BUTTON_MAIN");
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
            $tmpl->parse("BLOCK_EDIT_BUTTON_MAIN");
        }

        if (sizeof($content) > 0) {
            $categoryCount = 0;
            foreach ($content as $category) {

                if (isset($category["topics"])) {
                    $entryCount = 0;
                    foreach ($category["topics"] as $topic) {
                        $tmpl->setCurrentBlock("topic_entry");

                        //popupmenu topic
                        if ($portlet->check_access_write(\lms_steam::get_current_user())) {
                            $tmpl->setCurrentBlock("BLOCK_EDIT_BUTTON_TOPIC");
                            $tmpl->setVariable("PORTLET_ID_EDIT", $portlet->get_id());

                            $popupmenu = new \Widgets\PopupMenu();
                            $popupmenu->setData($portlet);
                            $popupmenu->setNamespace("PortletTopic");
                            $popupmenu->setElementId("portal-overlay");
                            $popupmenu->setCommand("GetPopupMenuEntry");
                            $popupmenu->setParams(array(array("key" => "category", "value" => $categoryCount), array("key" => "entry", "value" => $entryCount)));
                            $tmpl->setVariable("POPUPMENU", $popupmenu->getHtml());
                            $tmpl->parse("BLOCK_EDIT_BUTTON_TOPIC");
                        }
                        if (!isset($topic["title"])) {
                            $topic["title"] = "";
                        }
                        if (!isset($topic["link_url"])) {
                            $topic["link_url"] = "";
                        }
                        if (!isset($topic["link_target"])) {
                            $topic["link_target"] = "";
                        }

                        if (trim($topic["link_url"]) != "") {
                            $tmpl->setCurrentBlock("TOPIC_LINK");
                            $tmpl->setVariable("TOPIC_TITLE", $UBB->encode($topic["title"]));
                            //$tmpl->setVariable("TOPIC_LINK_URL", revealPath($topic["link_url"], $portlet->get_path()));
                            //$tmpl->setVariable("TOPIC_LINK_TARGET", ($topic["link_target"] == "checked" ? "_blank" : "_top"));
                            $tmpl->parse("TOPIC_LINK");

                            $tmpl->setCurrentBlock("topic_entry");
                            $tmpl->setVariable("TOPIC_LINK_URL", revealPath($topic["link_url"], $portlet->get_path()));
                            $tmpl->setVariable("TOPIC_LINK_TARGET", ($topic["link_target"] == "checked" ? "_blank" : "_top"));
                        } else {
                            $tmpl->setCurrentBlock("TOPIC_NOLINK");
                            $tmpl->setVariable("TOPIC_TITLE", $UBB->encode($topic["title"]));
                            $tmpl->parse("TOPIC_NOLINK");
                        }

                        $tmpl->setVariable("TOPIC_DESCRIPTION", $UBB->encode(@$topic["description"]));  //TODO: fix notice
                        //if there is a url parse headline as link
                        if (trim($topic["link_url"]) == "") {
                            //$tmpl->parse("TOPIC_DISPLAY_TITLE", "topic_display_title");
                        } else {
                            //$tmpl->parse("TOPIC_DISPLAY_TITLE", "topic_display_title_link");
                        }

                        //if there is a description parse out
                        $tmpl->setCurrentBlock("topic_display_description");
                        if (trim(@$topic["description"]) == "") { //TODO: fix notice
                            // $tmpl->setVariable("TOPIC_DISPLAY_DESCRIPTION", "");
                        } else {
                            //$tmpl->parse("TOPIC_DISPLAY_DESCRIPTION", "topic_display_description");
                        }
                        $tmpl->parse("topic_display_description");

                        //parse out every topic
                        $tmpl->parse("topic_entry");
                        $entryCount++;
                    }
                }

                //parse out category
                //$tmpl->parse("category");
                $categoryCount++;
            }
        } else {
            //NO MESSAGE
            $tmpl->setCurrentBlock("BLOCK_NO_MESSAGE");
            $tmpl->setVariable("NO_MESSAGE_INFO", "Keine Links vorhanden.");
            $tmpl->parse("BLOCK_NO_MESSAGE");
        }

        $htmlBody = $tmpl->get();
        $this->content = $htmlBody;

        //widgets
        $outputWidget = new \Widgets\RawHtml();
        $outputWidget->setHtml($htmlBody);

        //popummenu
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
