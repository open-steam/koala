<?php

namespace PortletAppointment\Commands;

class Index extends \AbstractCommand implements \IFrameCommand, \IIdCommand {

    private $params;
    private $id;
    private $content;
    private $rawHtmlWidget;


    public function validateData(\IRequestObject $requestObject) {

        //robustness for missing ids and objects
        try{
            $objectId=$requestObject->getId();
            $object = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $objectId );
        } catch (\Exception $e){
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
        $portlet_name = $portlet->get_attribute(OBJ_DESC);
        $params = $requestObject->getParams();

        $this->getExtension()->addCSS();
        //$this->getExtension()->addJS();

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

        //if the title is empty the headline will not be displayed (only in edit mode)
        if ($portlet_name == "" || $portlet_name == " ") {
            $tmpl->setVariable("HEADLINE_CLASS", "headline editbutton");
        } else {
            $tmpl->setVariable("HEADLINE_CLASS", "headline");
        }

        //reference icon
        if ($portletIsReference) {
            $titleTag = "title='".\Portal::getInstance()->getReferenceTooltip()."'";
            $envId = $portlet->get_environment()->get_environment()->get_id();
            $envUrl = PATH_URL . "portal/index/" . $envId;
            $tmpl->setVariable("REFERENCE_ICON", "<a $titleTag href='{$envUrl}' target='_blank'><img src='{$referIcon}'></a>");
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
            $unsortedContent = $content;
            usort($content, "sortPortletAppointments");

            $sortOrder = $portletObject->get_attribute("bid:portlet:app:app_order");

            $sortOrderBool = false; //oldest first --> show more link above content
            if (($sortOrder === "latest_first")){
                $sortOrderBool=true; //latest first --> show more link below content
            }

            if($sortOrderBool){
               $content = array_reverse($content);
            }

            //write access is required to save the sorting
            //no problem, because only with write access elements can be added, removed or rearranged
            if ($portlet->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())){
                if ($unsortedContent != $content){
                    $portletObject->set_attribute("bid:portlet:content", $content);
                }
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

                    $popupmenu->setParams(array(array("key" => "termIndex", "value" => $contextMenuIndex)));
                    $tmpl->setVariable("POPUPMENU_ENTRY", $popupmenu->getHtml());
                    $tmpl->parse("BLOCK_EDIT_BUTTON_TERM");
                }

                $indexCount++;
                $dash = false;

                if ($appointment["start_date"]["day"] != "") {
                    $tmpl->setCurrentBlock("BLOCK_TERM_STARTDATE");
                    $tmpl->setVariable("STARTDATE", $appointment["start_date"]["day"] . "." . $appointment["start_date"]["month"] . "." . $appointment["start_date"]["year"]);
                    $tmpl->setVariable("ENDDATE_ROW", "");
                    $tmpl->parse("BLOCK_TERM_STARTDATE");
                }

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
                    $tmpl->setCurrentBlock("BLOCK_TERM_DASH");
                    $tmpl->setVariable("DASH", "&nbsp;-&nbsp;");
                    $tmpl->parse("BLOCK_TERM_DASH");
                    $dash = true;
                }

                if ($appointment["start_time"]["hour"] != "") {
                    $tmpl->setCurrentBlock("BLOCK_TERM_STARTTIME");
                    $tmpl->setVariable("STARTTIME", $appointment["start_time"]["hour"] . "." . $appointment["start_time"]["minutes"] . " Uhr");
                    $tmpl->setVariable("TIME_ROW", "");
                    $tmpl->parse("BLOCK_TERM_STARTTIME");
                }

                if ($appointment["end_time"]["hour"] != "") {
                    $tmpl->setCurrentBlock("BLOCK_TERM_ENDTIME");
                    $tmpl->setVariable("ENDTIME", $appointment["end_time"]["hour"] . "." . $appointment["end_time"]["minutes"] . " Uhr");
                    $tmpl->setVariable("TIME_ROW", "");
                    $tmpl->parse("BLOCK_TERM_ENDTIME");
                    if(!$dash){
                      $tmpl->setCurrentBlock("BLOCK_TERM_DASH");
                      $tmpl->setVariable("DASH", "&nbsp;-&nbsp;");
                      $tmpl->parse("BLOCK_TERM_DASH");
                    }
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
        }else {
          //NO MESSAGE
          $tmpl->setCurrentBlock("BLOCK_NO_MESSAGE");
          $tmpl->setVariable("NO_MESSAGE_INFO", "Keine Termine vorhanden.");
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
        $frameResponseObject->addWidget($this->rawHtmlWidget);
        return $frameResponseObject;
    }

    public function checkIfPast($startDate, $Enddate) {

    }

}

?>
