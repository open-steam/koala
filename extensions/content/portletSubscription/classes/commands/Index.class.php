<?php
namespace PortletSubscription\Commands;

class Index extends \AbstractCommand implements \IIdCommand, \IFrameCommand {

    private $contentHtml;
    private $portlet;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $objectId = $requestObject->getId();
        $portlet = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        $this->portlet = $portlet;
        $params = $requestObject->getParams();
        $column = $portlet->get_environment();
        $width = $column->get_attribute("bid:portal:column:width");
        if (strpos($width, "px") == TRUE) {
            $width = substr($width, 0, count($width)-3);
        }
        
        //icon
        $referIcon = \Portal::getInstance()->getAssetUrl() . "icons/refer_white.png";

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

        
        
        try {
            $subscriptionObjectID = $portlet->get_attribute("PORTLET_SUBSCRIPTION_OBJECTID");
            $subscriptionObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $subscriptionObjectID);
        } catch (\steam_exception $ex) {
            $subscriptionObject = "";
        }
        
        //the object could be created, we can read the object and it is not moved to the trashbin (deleted for the user)
        if ($subscriptionObject instanceof \steam_object && $subscriptionObject->check_access_read() && !strpos($subscriptionObject->get_attribute("OBJ_PATH"), "trashbin")) {
            if($portlet->get_name() !== "Änderungen in ".$subscriptionObject->get_name()){
                $portlet->set_attribute("OBJ_NAME", "Änderungen in ".$subscriptionObject->get_name());
            } 
            
            //$portletName = getCleanName($portlet);
        $portletName = $portlet->get_name();
        
        $portletInstance = \PortletSubscription::getInstance();
        $portletPath = $portletInstance->getExtensionPath();

        $tmpl = new \HTML_TEMPLATE_IT();
        $tmpl->loadTemplateFile($portletPath . "/ui/html/index.template.html");
        $tmpl->setVariable("PORTLET_ID", $portlet->get_id());
        
        //headline
        $tmpl->setCurrentBlock("BLOCK_FOLDER_HEADLINE");
        $tmpl->setVariable("HEADLINE", $portletName);

        //refernce icon
        if ($portletIsReference) {
            $titleTag = "title='".\Portal::getInstance()->getReferenceTooltip()."'";
            $envId = $portlet->get_environment()->get_environment()->get_id();
            $envUrl = PATH_URL . "portal/index/" . $envId;
            $tmpl->setVariable("REFERENCE_ICON", "<a $titleTag href='{$envUrl}' target='_blank'><img src='{$referIcon}'></a>");
        }

        if (!$portletIsReference) {
            $popupmenu = new \Widgets\PopupMenu();
            $popupmenu->setData($portlet);
            $popupmenu->setNamespace("PortletSubscription");
            $popupmenu->setElementId("portal-overlay");
            $popupmenu->setParams(array(array("key" => "portletObjectId", "value" => $portlet->get_id())));
            $popupmenu->setCommand("GetPopupMenuHeadline");
            $tmpl->setVariable("POPUPMENU_HEADLINE", $popupmenu->getHtml());
        } else {
            $popupmenu = new \Widgets\PopupMenu();
            $popupmenu->setData($portlet);
            $popupmenu->setNamespace("Portal");
            $popupmenu->setElementId("portal-overlay");
            $popupmenu->setParams(array(array("key" => "sourceObjectId", "value" => $portlet->get_id()),
                array("key" => "linkObjectId", "value" => $referenceId)
            ));
            $popupmenu->setCommand("PortletGetPopupMenuReference");
            $tmpl->setVariable("POPUPMENU_HEADLINE", $popupmenu->getHtml());
        }

        if (trim($portletName) == "") {
            $tmpl->setVariable("HEADLINE_CLASS", "headline editbutton");
        } else {
            $tmpl->setVariable("HEADLINE_CLASS", "headline");
        }
        $tmpl->parse("BLOCK_FOLDER_HEADLINE");
            
            $updates = $portletInstance->calculateUpdates($subscriptionObject, $portlet);
            if (count($updates) === 0) {
                $tmpl->setCurrentBlock("BLOCK_SUBSCRIPTION_ELEMENT");
                $tmpl->setVariable("SUBSCRIPTION_ELEMENT_HTML", "<h3>Keine Neuigkeiten</h3>");
                $tmpl->parse("BLOCK_SUBSCRIPTION_ELEMENT");
            } else {
                //var_dump($updates);
                foreach ($updates as $update) {
                    //echo "hier".$update[2][1];
                    $tmpl->setCurrentBlock("BLOCK_SUBSCRIPTION_ELEMENT");
                    $tmpl->setVariable("SUBSCRIPTION_ELEMENT_HTML", $update[2]);
                    $tmpl->parse("BLOCK_SUBSCRIPTION_ELEMENT");
                }
            }
        } else {
            $tmpl = new \HTML_TEMPLATE_IT();
            $tmpl->loadTemplateFile($portletPath . "/ui/html/index.template.html");
        
            $tmpl->setCurrentBlock("BLOCK_SUBSCRIPTION_ELEMENT");
            $tmpl->setVariable("SUBSCRIPTION_ELEMENT_HTML", "Das abonnierte Objekt wurde gelöscht.");
            $tmpl->parse("BLOCK_SUBSCRIPTION_ELEMENT");
        }
        
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($tmpl->get());
        $rawHtml->setCss(".close-button {
            height: 16px;
            width: 16px;
            background-image: url(\"".PATH_URL."widgets/asset/close.gif\");
            position: relative;
            top: 0px;
            left: 0px;
        }

        .close-button:hover {
            cursor: pointer;
        }"
        );
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