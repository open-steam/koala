<?php

namespace PortletAppointment\Commands;

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
        $portlet_name = $portlet->get_attribute(OBJ_DESC);
        $params = $requestObject->getParams();

        $this->getExtension()->addCSS();

        //reference handling
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

        $portletInstance = \PortletAppointment::getInstance();
        $portletPath = $portletInstance->getExtensionPath();

        $portletFileName = $portletPath . "/ui/html/index.html";
        $tmpl = new \HTML_TEMPLATE_IT();
        $tmpl->loadTemplateFile($portletFileName);

        $tmpl->setVariable("PORTLET_ID", $portlet->get_id());
        $tmpl->setVariable("APPOINTMENT_NAME", $portlet_name);

        //if the title is empty the headline will not be displayed (only in edit mode)
        if (trim($portlet_name == "")) {
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

        //main popupmenu
        if (!$portletIsReference && $portlet->check_access_write(\lms_steam::get_current_user())) {
            $tmpl->setCurrentBlock("BLOCK_EDIT_BUTTON_MAIN");
            $tmpl->setVariable("PORTLET_ID_EDIT", $portlet->get_id());
            $popupmenu = new \Widgets\PopupMenu();
            $popupmenu->setData($portlet);
            $popupmenu->setNamespace("PortletAppointment");
            $popupmenu->setElementId("portal-overlay");
            $popupmenu->setCommand("GetPopupMenu");
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
        $tmpl->parse("BLOCK_EDIT_BUTTON_MAIN");

        $UBB = new \UBBCode();
        include_once(PATH_BASE . "core/lib/bid/derive_url.php");

        if (sizeof($content) > 0) {
            //sort appointments
            $unsortedContent = $content;
            usort($content, "sortPortletAppointments");

            $sortOrder = $portletObject->get_attribute("bid:portlet:app:app_order");

            $sortOrderBool = false;
            if (($sortOrder === "latest_first")) {
                $sortOrderBool = true;
            }

            if ($sortOrderBool) {
                $content = array_reverse($content);
            }

            //write access is required to save the sorting
            //no problem, because only with write access elements can be added, removed or rearranged
            if ($portlet->check_access_write(\lms_steam::get_current_user())) {
                if ($unsortedContent != $content) {
                    $portletObject->set_attribute("bid:portlet:content", $content);
                }
            }

            $indexCount = 0;
            $showPastTerms = false;

            foreach ($content as $appointment) {
                $tmpl->setCurrentBlock("BLOCK_TERM");

                if ($this->checkIfPast($appointment)) { //appointment lies in the past
                    $tmpl->setVariable("HIDDEN", "hidden");
                    $showPastTerms = true;
                }

                //term popupmenu
                if (!$portletIsReference && $portlet->check_access_write(\lms_steam::get_current_user())) {
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
                $endterm = false;

                if ($appointment["start_date"]["day"] !== "") {
                    $tmpl->setCurrentBlock("BLOCK_TERM_STARTDATE");
                    $tmpl->setVariable("STARTDATE", $appointment["start_date"]["day"] . "." . $appointment["start_date"]["month"] . "." . $appointment["start_date"]["year"]);
                    $tmpl->parse("BLOCK_TERM_STARTDATE");
                }

                if (trim($appointment["location"]) !== "" && trim($appointment["location"]) !== "0") {
                    $tmpl->setCurrentBlock("BLOCK_TERM_LOCATION");
                    $tmpl->setVariable("LOCATION", $UBB->encode($appointment["location"]));
                    $tmpl->parse("BLOCK_TERM_LOCATION");
                }

                if ($appointment["end_date"]["day"] !== "") {
                    $tmpl->setCurrentBlock("BLOCK_TERM_ENDDATE");
                    $tmpl->setVariable("ENDDATE", $appointment["end_date"]["day"] . "." . $appointment["end_date"]["month"] . "." . $appointment["end_date"]["year"]);
                    $tmpl->parse("BLOCK_TERM_ENDDATE");
                    $tmpl->setCurrentBlock("BLOCK_TERM_ENDTERM");
                    $tmpl->setVariable("ENDTERM", "Ende:");
                    $tmpl->parse("BLOCK_TERM_ENDTERM");
                    $endterm = true;
                }

                if ($appointment["start_time"]["hour"] !== "") {
                    $tmpl->setCurrentBlock("BLOCK_TERM_STARTTIME");
                    $tmpl->setVariable("STARTTIME", $appointment["start_time"]["hour"] . "." . $appointment["start_time"]["minutes"] . " Uhr");
                    $tmpl->parse("BLOCK_TERM_STARTTIME");
                }

                if (isset($appointment["end_time"])) {
                    if (isset($appointment["end_time"]["hour"]) && $appointment["end_time"]["hour"] !== "") {
                        $tmpl->setCurrentBlock("BLOCK_TERM_ENDTIME");
                        $tmpl->setVariable("ENDTIME", $appointment["end_time"]["hour"] . "." . $appointment["end_time"]["minutes"] . " Uhr");
                        $tmpl->parse("BLOCK_TERM_ENDTIME");
                        if (!$endterm) {
                            $tmpl->setCurrentBlock("BLOCK_TERM_ENDTERM");
                            $tmpl->setVariable("ENDTERM", "Ende:");
                            $tmpl->parse("BLOCK_TERM_ENDTERM");
                        }
                    }
                }

                if (trim($appointment["description"]) !== "" && trim($appointment["description"]) !== "0") {
                    $tmpl->setCurrentBlock("BLOCK_TERM_DESCRIPTION");
                    $tmpl->setVariable("DESCRIPTION", $UBB->encode($appointment["description"]));
                    $tmpl->parse("BLOCK_TERM_DESCRIPTION");
                }

                if (trim($appointment["linkurl"]) !== "" && trim($appointment["linkurl"]) !== "0") {
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

            if ($showPastTerms) {
                $showAllTermsLink = '<div id="showAllTerms" style="padding-top: 10px; padding-bottom: 10px; text-align: center;"><a style="cursor:pointer;" onclick="$(\'#' . $objectId . ' > .hidden\').removeClass(\'hidden\');$(this).parent().remove();">Vergangene Termine anzeigen</a></div>';
                if ($sortOrderBool) {
                    $tmpl->setCurrentBlock("BLOCK_OLD_TERMS_BOTTOM");
                    $tmpl->setVariable("OLD_TERMS", $showAllTermsLink);
                    $tmpl->parse("BLOCK_OLD_TERMS_BOTTOM");
                } else {
                    $tmpl->setCurrentBlock("BLOCK_OLD_TERMS_UP");
                    $tmpl->setVariable("OLD_TERMS", $showAllTermsLink);
                    $tmpl->parse("BLOCK_OLD_TERMS_UP");
                }
            }
        } else {
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

    public function checkIfPast($appointment) {
        $startDate = strlen($appointment["start_date"]["day"]) == 2 && strlen($appointment["start_date"]["month"]) == 2 && strlen($appointment["start_date"]["year"]) == 4;
        $startTime = strlen($appointment["start_time"]["hour"]) == 2 && strlen($appointment["start_time"]["minutes"]) == 2;
        $endDate = false;
        if (isset($appointment["end_date"])) {
            $endDate = strlen($appointment["end_date"]["day"]) == 2 && strlen($appointment["end_date"]["month"]) == 2 && strlen($appointment["end_date"]["year"]) == 4;
        }
        $endTime = false;
        if (isset($appointment["end_time"])) {
            $endTime = strlen($appointment["end_time"]["hour"]) == 2 && strlen($appointment["end_time"]["minutes"]) == 2;
        }
        if ($endDate) {
            $endDateFormat = $appointment["end_date"]["year"] . "-" . $appointment["end_date"]["month"] . "-" . $appointment["end_date"]["day"];
            if ($endTime) {
                $endTimeFormat = $appointment["end_time"]["hour"] . ":" . $appointment["end_time"]["minutes"] . ":00";
                $date = date_create($endDateFormat . " " . $endTimeFormat);
            } else {
                $date = date_create($endDateFormat . " " . "24:00:00");
            }
        } else if ($startDate) {
            $startDateFormat = $appointment["start_date"]["year"] . "-" . $appointment["start_date"]["month"] . "-" . $appointment["start_date"]["day"];
            if ($endTime) {
                $endTimeFormat = $appointment["end_time"]["hour"] . ":" . $appointment["end_time"]["minutes"] . ":00";
                $date = date_create($startDateFormat . " " . $endTimeFormat);
            } else if ($startTime) {
                $startTimeFormat = $appointment["start_time"]["hour"] . ":" . $appointment["start_time"]["minutes"] . ":00";
                $date = date_create($startDateFormat . " " . $startTimeFormat);
            } else {
                $date = date_create($startDateFormat . " " . "24:00:00");
            }
        } else {
            return false; //no dates, show appointment
        }

        $now = date_create(date('Y-m-d H:i:s'));
        if ($date < $now) {
            return true; //date in the past
        }
        return false; //date in the future
    }

}

?>
