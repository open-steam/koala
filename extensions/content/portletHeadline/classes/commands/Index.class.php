<?php

namespace PortletHeadline\Commands;

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


        //hack
        include_once(PATH_BASE . "core/lib/bid/slashes.php");

        //get content of portlet
        $content = $portlet->get_attribute("bid:portlet:content");
        if (is_array($content) && count($content) > 0) {
            array_walk($content, "_stripslashes");
        } else {
            $content = array();
        }

        //get singleton and portlet path
        $portletInstance = \PortletHeadline::getInstance();
        $portletPath = $portletInstance->getExtensionPath();

        if (sizeof($content) > 0) {
            $portletFileName = $portletPath . "/ui/html/index.html";
            $tmpl = new \HTML_TEMPLATE_IT();
            $tmpl->loadTemplateFile($portletFileName);

            //popupmenu
            if (!$portletIsReference && $portlet->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())) {
                $popupmenu = new \Widgets\PopupMenu();
                $popupmenu->setData($portlet);
                $popupmenu->setNamespace("PortletHeadline");
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



            $UBB = new \UBBCode();
            include_once(PATH_BASE . "core/lib/bid/derive_url.php");

            $tmpl->setVariable("DUMMY", "");
            $tmpl->setVariable("EDIT_BUTTON", "");
            $tmpl->setVariable("PORTLET_ID", $portlet->get_id());
            $tmpl->setVariable("ALIGNMENT", trim($content["alignment"]));
            $title = $UBB->encode($content["headline"]);
            $tmpl->setVariable("HEADLINE", $title);

            //if the title is empty the headline will not be displayed (only in edit mode)
            if ($title == "" || $title == " ") {
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

            $tmpl->setVariable("SIZE", trim($content["size"]));

            if ($portlet->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())) {
                $tmpl->setCurrentBlock("BLOCK_EDIT_BUTTON");
                $tmpl->setVariable("PORTLET_ID_EDIT", $portlet->get_id());
                $tmpl->parse("BLOCK_EDIT_BUTTON");
            }

            $htmlBody = $tmpl->get();
        } else {
            $htmlBody = "";
        }

        $this->content = $htmlBody;

        //widgets
        $outputWidget = new \Widgets\RawHtml();

        //popummenu
        $outputWidget->addWidget(new \Widgets\PopupMenu());

        $outputWidget->setHtml($htmlBody);
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
