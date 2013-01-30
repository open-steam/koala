<?php

namespace PortletAppointment\Commands;

class Index extends \AbstractCommand implements \IFrameCommand, \IIdCommand {

    private $params;
    private $id;
    private $content;
    private $rawHtmlWidget;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $objectId = $requestObject->getId();
        $portlet = $portletObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        $portlet_name = $portlet->get_attribute(OBJ_DESC);
        $params = $requestObject->getParams();

        //icon
        $referIcon = \Portal::getInstance()->getAssetUrl() . "icons/refer_white.png";

        //reference handling
        if (isset($params["referenced"]) && $params["referenced"] == true) {
            $portletIsReference = true;
            $referenceId = $params["referenceId"];
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

        $portletInstance = \PortletAppointment::getInstance();
        $portletPath = $portletInstance->getExtensionPath();

        $portletFileName = $portletPath . "/ui/html/index.html";
        $tmpl = new \HTML_TEMPLATE_IT();
        $tmpl->loadTemplateFile($portletFileName);

        $tmpl->setVariable("PORTLET_ID", $portlet->get_id());
        $tmpl->setVariable("APPOINTMENT_NAME", $portlet_name);
        $tmpl->setVariable("linkurl", "");

        //refernce icon
        if ($portletIsReference) {
            $envId = $portlet->get_environment()->get_environment()->get_id();
            $envUrl = PATH_URL . "portal/index/" . $envId;
            $tmpl->setVariable("REFERENCE_ICON", "<a href='{$envUrl}' target='_blank'><img src='{$referIcon}'></a>");
        }

        //main popupmenu
        if (!$portletIsReference && $portlet->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())) {
            $tmpl->setCurrentBlock("BLOCK_EDIT_BUTTON_MAIN");
            $tmpl->setVariable("PORTLET_ID_EDIT", $portlet->get_id());
            $popupmenu = new \Widgets\PopupMenu();
            $popupmenu->setData($portlet);
            $popupmenu->setNamespace("PortletAppointment");
            $popupmenu->setElementId("portal-overlay");
            $popupmenu->setCommand("GetPopupMenu");
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
        $tmpl->parse("BLOCK_EDIT_BUTTON_MAIN");

        $UBB = new \UBBCode();
        include_once(PATH_BASE . "core/lib/bid/derive_url.php");

        if (sizeof($content) > 0) {
            //sort appointments
            usort($content, "sortPortletAppointments");

            $sortOrder = $portletObject->get_attribute("bid:portlet:app:app_order");
           
            $sortOrderBool = true;
            if (!($sortOrder === "latest_first")){
                $sortOrderBool=false;
            }
            
            
            if($sortOrderBool){
               $content = array_reverse($content); 
            }
            
            $indexCount = 0; 
            
            foreach ($content as $appointment) {
                $tmpl->setCurrentBlock("BLOCK_TERM");

                //term popupmenu
                if (!$portletIsReference && $portlet->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())) {
                    $tmpl->setCurrentBlock("BLOCK_EDIT_BUTTON_MAIN");
                    $tmpl->setVariable("PORTLET_ID_EDIT", $portlet->get_id());
                    $popupmenu = new \Widgets\PopupMenu();
                    $popupmenu->setCommand("GetPopupMenuTerm");
                    $popupmenu->setData($portlet);
                    $popupmenu->setNamespace("PortletAppointment");
                    $popupmenu->setElementId("portal-overlay");
                    
                    //reverse index
                    $contextMenuIndex = $indexCount;
                    if (!$sortOrderBool){
                        $elementsSum = sizeof($content);
                        $contextMenuIndex = $elementsSum - $indexCount -1; 
                    }
                    
                    $popupmenu->setParams(array(array("key" => "termIndex", "value" => $contextMenuIndex)));
                    $tmpl->setVariable("POPUPMENU_ENTRY", $popupmenu->getHtml());
                    $tmpl->parse("BLOCK_EDIT_BUTTON_TERM");
                }
                
                $indexCount++;
                
                $tmpl->setVariable("STARTDATE", $appointment["start_date"]["day"] . "." . $appointment["start_date"]["month"] . "." . $appointment["start_date"]["year"]);

                if (trim($appointment["location"]) != "" && trim($appointment["location"]) != "0") {
                    $tmpl->setCurrentBlock("BLOCK_TERM_LOCATION");
                    $tmpl->setVariable("LOCATION", $UBB->encode($appointment["location"]));
                    $tmpl->setVariable("LOCATION_ROW", "");
                    $tmpl->parse("BLOCK_TERM_LOCATION");
                }

                if ($appointment["end_date"]["day"] != "") {
                    $tmpl->setCurrentBlock("BLOCK_TERM_ENDDATE");
                    $tmpl->setVariable("ENDDATE", $appointment["end_date"]["day"] . "." . $appointment["end_date"]["month"] . "." . $appointment["end_date"]["year"]);
                    $tmpl->setVariable("ENDDATE_ROW", "");
                    $tmpl->parse("BLOCK_TERM_ENDDATE");
                }

                if ($appointment["start_time"]["hour"] != "") {
                    $tmpl->setCurrentBlock("BLOCK_TERM_TIME");
                    $tmpl->setVariable("TIME", $appointment["start_time"]["hour"] . "." . $appointment["start_time"]["minutes"] . " Uhr");
                    $tmpl->setVariable("TIME_ROW", "");
                    $tmpl->parse("BLOCK_TERM_TIME");
                }

                if (trim($appointment["description"]) != "" && trim($appointment["description"]) != "0") {
                    $tmpl->setCurrentBlock("BLOCK_TERM_DESCRIPTION");
                    $tmpl->setVariable("DESCRIPTION", $UBB->encode($appointment["description"]));
                    $tmpl->parse("BLOCK_TERM_DESCRIPTION");
                }

                if (trim($appointment["linkurl"]) != "" && trim($appointment["linkurl"]) != "0") {
                    $tmpl->setCurrentBlock("BLOCK_TERM_LINK");

                    //open link in new window
                    if (isset($appointment["linkurl_open_extern"]) && $appointment["linkurl_open_extern"] == "checked") {
                        $tmpl->setVariable("LINKURL_OPEN_EXTERN", 'target="_blank"');
                    } else {
                        $tmpl->setVariable("LINKURL_OPEN_EXTERN", "");
                    }


                    $tmpl->setVariable("LINKURL", derive_url($appointment["linkurl"]));
                    $tmpl->setVariable("TOPIC", $UBB->encode($appointment["topic"]));
                    $tmpl->parse("BLOCK_TERM_LINK");
                } else {
                    $tmpl->setCurrentBlock("BLOCK_TERM_NOLINK");
                    $tmpl->setVariable("TOPIC", $UBB->encode($appointment["topic"]));
                    $tmpl->parse("BLOCK_TERM_NOLINK");
                }
                $tmpl->parse("BLOCK_TERM");
            }
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
        $frameResponseObject->addWidget($this->rawHtmlWidget);
        return $frameResponseObject;
    }

}

?>