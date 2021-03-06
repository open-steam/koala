<?php

namespace PortletUserPicture\Commands;

class Index extends \AbstractCommand implements \IIdCommand, \IFrameCommand {

    private $contentHtml;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $objectId = $requestObject->getId();
        $portlet = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        $params = $requestObject->getParams();


        //reference handling
        if (isset($params["referenced"]) && $params["referenced"] == true) {
            if (!$portlet->check_access_read()) {
                $this->rawHtmlWidget = new \Widgets\RawHtml();
                $this->rawHtmlWidget->setHtml("");
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
        $portletPath = \PortletUserPicture::getInstance()->getExtensionPath();

        $tmpl = new \HTML_TEMPLATE_IT();
        $tmpl->loadTemplateFile($portletPath . "/ui/html/index.template.html");
        $tmpl->setVariable("PORTLET_ID", $portlet->get_id());

        //headline
        $headline = $portlet->get_attribute("OBJ_DESC");
        $tmpl->setVariable("HEADLINE", $headline);

        //if the title is empty the headline will not be displayed (only in edit mode)
        if (trim($headline) == "") {
            $tmpl->setVariable("HEADLINE_CLASS", "headline editbutton");
        } else {
            $tmpl->setVariable("HEADLINE_CLASS", "headline");
        }

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
            $popupmenu->setNamespace("PortletUserPicture");
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

        $user = $portlet->get_creator();
        $currentUser = \lms_steam::get_current_user();
        $tmpl->setVariable("DESCRIPTION", "Zum Profil");
        $tmpl->setVariable("URL", PATH_URL . "user/index/" . $user->get_name() . "/");
        $pic_id = $user->get_attribute("OBJ_ICON")->get_id();
        $pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/" . ($width - 20) . "/" . round(($width - 20) * (185 / 140));
        $tmpl->setVariable("PICTURE_URL", $pic_link);
        $tmpl->setVariable("DOCUMENTS_LABEL", "Meine Dokumente");
        $tmpl->setVariable("DOCUMENTS_URL", PATH_URL . "explorer/");
        if ($user->get_id() !== $currentUser->get_id()) {
            $tmpl->setVariable("DISPLAY_DOCUMENTS", "none");
        }
        $tmpl->parse();

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($tmpl->get());
        $this->contentHtml = $rawHtml;
    }

    public function idResponse(\IdResponseObject $idResponseObject) {
        $idResponseObject->addWidget($this->contentHtml);
        return $idResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $frameResponseObject->addWidget($this->contentHtml);
        return $frameResponseObject;
    }

}

?>
