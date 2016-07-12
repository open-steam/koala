<?php

namespace PortletSubscription\Commands;

class Index extends \AbstractCommand implements \IIdCommand, \IFrameCommand {

    private $contentHtml;
    private $portlet;
    private $portletInstance;
    private $params;
    private $template;
    private $subscriptionObjectId;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {

        $this->portlet = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $requestObject->getId());

        $this->params = $requestObject->getParams();
        //get the width of the column
        $width = $this->portlet->get_environment()->get_attribute("bid:portal:column:width");
        if (strpos($width, "px") == TRUE) {
            $width = substr($width, 0, count($width) - 3);
        }

        //icon
        $referIcon = \Portal::getInstance()->getAssetUrl() . "icons/refer_white.png";

        //reference handling
        if (isset($this->params["referenced"]) && $this->params["referenced"] == true) {
            $this->portletIsReference = true;
            $referenceId = $this->params["referenceId"];
            if (!$this->portlet->check_access_read()) {
                $this->rawHtmlWidget = new \Widgets\RawHtml();
                $this->rawHtmlWidget->setHtml("");
                return null;
            }
        } else {
            $this->portletIsReference = false;
        }

        $this->portletInstance = \PortletSubscription::getInstance();
        $this->template = new \HTML_TEMPLATE_IT();
        $this->template->loadTemplateFile($this->portletInstance->getExtensionPath() . "/ui/html/index.template.html");

        $this->subscriptionObjectId = trim($this->portlet->get_attribute("PORTLET_SUBSCRIPTION_OBJECTID"));

        try {

            $subscriptionObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->subscriptionObjectId);
        } catch (\exception $ex) {

        }
        $this->template->setVariable("PORTLET_ID", $this->portlet->get_id());
        $this->portletName = $this->portlet->get_name();
        $this->template->setCurrentBlock("BLOCK_FOLDER_HEADLINE");
        $this->template->setVariable("HEADLINE", $this->portletName);

        if (trim($this->portletName) == "") {
            $this->template->setVariable("HEADLINE_CLASS", "headline editbutton");
        } else {
            $this->template->setVariable("HEADLINE_CLASS", "headline");
        }


        //reference icon
        if ($this->portletIsReference) {
            $envUrl = PATH_URL . "portal/index/" . $this->portlet->get_environment()->get_environment()->get_id();
            $this->template->setVariable("REFERENCE_ICON", "<a title='" . \Portal::getInstance()->getReferenceTooltip() . "' href='{$envUrl}' target='_blank'><img src='{$referIcon}'></a>");
        }

        $popupmenu = new \Widgets\PopupMenu();
        $popupmenu->setData($this->portlet);
        $popupmenu->setElementId("portal-overlay");
        if (!$this->portletIsReference) {
            $popupmenu->setNamespace("PortletSubscription");
            $popupmenu->setParams(array(array("key" => "portletObjectId", "value" => $this->portlet->get_id())));
            $popupmenu->setCommand("GetPopupMenuHeadline");
        } else {
            $popupmenu->setNamespace("Portal");
            $popupmenu->setParams(array(array("key" => "sourceObjectId", "value" => $this->portlet->get_id()), array("key" => "linkObjectId", "value" => $referenceId)));
            $popupmenu->setCommand("PortletGetPopupMenuReference");
        }
        $this->template->setVariable("POPUPMENU_HEADLINE", $popupmenu->getHtml());

        if ($subscriptionObject instanceof \steam_object && $subscriptionObject->check_access_read()) {

            if ($subscriptionObject->get_attribute("OBJ_TYPE") == "postbox" && $subscriptionObject->get_attribute("bid:postbox:container") instanceof \steam_object && !$subscriptionObject->get_attribute("bid:postbox:container")->check_access_read()) {
                self::displayForbidden();
            } else {


                $updates = $this->portletInstance->calculateUpdates($subscriptionObject, $this->portlet);

                if (count($updates) > 1) {

                    $this->template->setCurrentBlock("BLOCK_HIDE_BUTTON");
                    $this->template->setVariable("HIDE_ALL_BUTTON", \PortletSubscription\Subscriptions\AbstractSubscription::getElementJS($this->portlet->get_id(), -1, time(), ""));
                    $this->template->parse("BLOCK_HIDE_BUTTON");
                }

                $this->template->parse("BLOCK_FOLDER_HEADLINE");



                //the object could be created, we can read the object and it is not moved to the trashbin (deleted for the user)
                if (!strpos($subscriptionObject->get_attribute("OBJ_PATH"), "trashbin")) {
                    //do not take care of the name as it is the user's tast to choose an appropriate name
                    // if($this->portlet->get_name() !== "Änderungen in ".$subscriptionObject->get_name()){
                    //   $this->portlet->set_attribute("OBJ_NAME", "Änderungen in ".$subscriptionObject->get_name());
                    //}
                    //$this->portletName = getCleanName($this->portlet);
                    if (count($updates) === 0) {
                        $this->template->setCurrentBlock("BLOCK_SUBSCRIPTION_ELEMENT");
                        $this->template->setVariable("SUBSCRIPTION_ELEMENT_HTML", "<h3>Keine Neuigkeiten</h3>");
                        $this->template->parse("BLOCK_SUBSCRIPTION_ELEMENT");
                    } else {
                        foreach ($updates as $update) {
                            $this->template->setCurrentBlock("BLOCK_SUBSCRIPTION_ELEMENT");
                            $this->template->setVariable("SUBSCRIPTION_ELEMENT_HTML", $update[2]);
                            $this->template->parse("BLOCK_SUBSCRIPTION_ELEMENT");
                        }
                    }
                } else {
                    $this->template->setCurrentBlock("BLOCK_SUBSCRIPTION_ELEMENT");
                    $this->template->setVariable("SUBSCRIPTION_ELEMENT_HTML", "<h3>Das abonnierte Objekt mit der id " . $this->subscriptionObjectId . " existiert nicht (mehr). Es liegt vermutlich im Papierkorb.</h3>");
                    $this->template->parse("BLOCK_SUBSCRIPTION_ELEMENT");
                }
            }
        } else if (!is_numeric($this->subscriptionObjectId)) {
            $this->template->setCurrentBlock("BLOCK_SUBSCRIPTION_ELEMENT");
            if ($this->subscriptionObjectId !== "") {
                $this->template->setVariable("SUBSCRIPTION_ELEMENT_HTML", "<h3>Die Id enthält nicht ausschließlich Ziffern.</h3>");
            } else {
                $this->template->setVariable("SUBSCRIPTION_ELEMENT_HTML", "<h3>Sie müssen die Id des zu überwachenden Objektes festlegen.</h3>");
            }

            $this->template->parse("BLOCK_SUBSCRIPTION_ELEMENT");
        } else if ($subscriptionObject instanceof \steam_object) {
            self::displayForbidden();
        } else {
            $this->template->setCurrentBlock("BLOCK_SUBSCRIPTION_ELEMENT");
            $this->template->setVariable("SUBSCRIPTION_ELEMENT_HTML", "<h3>Das abonnierte Objekt mit der id " . $this->subscriptionObjectId . " existiert nicht (mehr).</h3>");
            $this->template->parse("BLOCK_SUBSCRIPTION_ELEMENT");
        }

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($this->template->get());
        $rawHtml->setCss(".subscription-close-button {
            height: 16px;
            width: 16px;
            position: relative;
            top: 0px;
            left: 0px;
        }

        .subscription-close-button:hover {
            cursor: pointer;
        }

        .subscription-close-button.blueeye{
            background-image: url(\"" . PATH_URL . "widgets/asset/eye.png\");
            clear:both;
        }

        .subscription-close-button.whiteeye{
            background-image: url(\"" . PATH_URL . "widgets/asset/eye_white.png\");
            margin-right:1px;
        }
"
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

    public function displayForbidden() {
        $this->template->parse("BLOCK_FOLDER_HEADLINE");
        $this->template->setCurrentBlock("BLOCK_SUBSCRIPTION_ELEMENT");
        $this->template->setVariable("SUBSCRIPTION_ELEMENT_HTML", "<h3>Sie haben nicht die nötigen Rechte, um das Objekt mit der id " . $this->subscriptionObjectId . " zu überwachen.</h3>");
        $this->template->parse("BLOCK_SUBSCRIPTION_ELEMENT");
    }

}

?>
