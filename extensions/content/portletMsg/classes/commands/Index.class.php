<?php

namespace PortletMsg\Commands;

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
            die; 
        }

        if (!$object instanceof \steam_object) {
            \ExtensionMaster::getInstance()->send404Error();
            die;
        }

        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        
        
        $objectId = $requestObject->getId();
        $portlet = $portletObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        $params = $requestObject->getParams();

        
        try{
        
        //icon
        $referIcon = \Portal::getInstance()->getAssetUrl() . "icons/refer_white.png";

        //reference handling
        if (isset($params["referenced"]) && $params["referenced"] == true) {
            $portletIsReference = true;
            $referenceId = $params["referenceId"];
        } else {
            $portletIsReference = false;
        }


        $this->getExtension()->addCSS();
        $this->getExtension()->addJS();

        $portletName = $portlet->get_attribute(OBJ_DESC);

        //hack
        include_once(PATH_BASE . "core/lib/bid/slashes.php");

        //get content of portlet
        $content = $portlet->get_attribute("bid:portlet:content");
        if (is_array($content) && count($content) > 0) {
            array_walk($content, "_stripslashes");
        } else {
            $content = array();
        }

        $portletInstance = \PortletMsg::getInstance();
        $portletPath = $portletInstance->getExtensionPath();

        $UBB = new \UBBCode();
        include_once(PATH_BASE . "core/lib/bid/derive_url.php");

        $portletFileName = $portletPath . "/ui/html/index.html";
        $tmpl = new \HTML_TEMPLATE_IT();
        $tmpl->loadTemplateFile($portletFileName);
        $tmpl->setVariable("PORTLET_ID", $portlet->get_id());

        //headline
        $tmpl->setCurrentBlock("BLOCK_MESSAGE_HEADLINE");

        $tmpl->setVariable("HEADLINE", $portletName);

        //refernce icon
        if ($portletIsReference) {
            $envId = $portlet->get_environment()->get_environment()->get_id();
            $envUrl = PATH_URL . "portal/index/" . $envId;
            $tmpl->setVariable("REFERENCE_ICON", "<a href='{$envUrl}' target='_blank'><img src='{$referIcon}'></a>");
        }

        if (!$portletIsReference) {
            $popupmenu = new \Widgets\PopupMenu();
            $popupmenu->setData($portlet);
            $popupmenu->setNamespace("PortletMsg");
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


        //show empty headline in edit mode
        if (trim($portletName) == "") {
            $tmpl->setVariable("HEADLINE_CLASS", "headline editbutton");
        } else {
            $tmpl->setVariable("HEADLINE_CLASS", "headline");
        }


        $tmpl->parse("BLOCK_MESSAGE_HEADLINE");

        if (sizeof($content) > 0) {
            /*
             * Convert old messages which save its content as UBB code to new messages
             * using a html representation 
             */
            $convertUBB = false;
            $version = $portlet->get_attribute("bid:portlet:version");
            /* 			
              if(!$version){
              $convertUBB = true;
              require_once("name.php");
              $portlet->set_attribute("bid:portlet:version", $msg_version);
              }
             */

            $separator = false;
            foreach ($content as $messageId) {
                $tmpl->setCurrentBlock("BLOCK_MESSAGE");

                $message = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $messageId);
                $message->get_attributes(array("OBJ_DESC", "bid:portlet:msg:picture_id", "bid:portlet:msg:picture_alignment", "bid:portlet:msg:link_url", "bid:portlet:msg:link_url_label", "bid:portlet:msg:link_open"));

                //popupmenu
                $popupmenu = new \Widgets\PopupMenu();
                $popupmenu->setData($message);
                $popupmenu->setNamespace("PortletMsg");
                $popupmenu->setElementId("portal-overlay");
                $popupmenu->setParams(array(array("key" => "messageObjectId", "value" => $messageId),
                    array("key" => "portletObjectId", "value" => $portlet->get_id())
                ));
                $popupmenu->setCommand("GetPopupMenuMessage");
                if (!$portletIsReference)
                    $tmpl->setVariable("POPUPMENU_MESSAGE", $popupmenu->getHtml());

                /*
                 * Convert old messages which save its content as UBB code to new messages
                 * using a html representation
                 */
                if ($convertUBB) {
                    $message->set_content($UBB->encode($message->get_content()));
                }


                $tmpl->setVariable("MESSAGE_PICTURE", "");
                $tmpl->setVariable("MESSAGE_LINK", "");
                $tmpl->setVariable("MESSAGE_HEADLINE", $UBB->encode(rawurldecode($message->get_attribute("OBJ_NAME"))));
                if ($UBB->encode($message->get_attribute("OBJ_DESC")) != "") {
                    $tmpl->setVariable("MESSAGE_SUBHEADLINE", $UBB->encode($message->get_attribute("OBJ_DESC")));
                }
                
                //edit message content
                $messageContent = $message->get_content();
                $messageContent = cleanHTML($messageContent);
                
                $tmpl->setVariable("MESSAGE_CONTENT", $messageContent);

                
                //get column width
                $columnObject = $portletObject->get_environment();
                $column_width = $columnObject->get_attribute("bid:portal:column:width");

                //PICTURE
                // parse in picture if it exists
                if ($message->get_attribute("bid:portlet:msg:picture_id") != "") {
                    $tmpl->setCurrentBlock("BLOCK_MESSAGE_PICTURE");
                    $picture_width = (($message->get_attribute("bid:portlet:msg:picture_width") != "") ? trim($message->get_attribute("bid:portlet:msg:picture_width")) : "");
                    if (extract_percentual_length($picture_width) == "") {
                        $bare_picture_width = extract_length($picture_width);
                        if ($bare_picture_width == "") {
                            $picture_width = "";
                        } else if ($bare_picture_width > $column_width - 25) {
                            $picture_width = $column_width - 25;
                        }
                    }

                    $tmpl->setVariable("MESSAGE_PICTURE_ID", $message->get_attribute("bid:portlet:msg:picture_id")); //not used anymore
                    $tmpl->setVariable("MESSAGE_PICTURE_URL", getDownloadUrlForObjectId($message->get_attribute("bid:portlet:msg:picture_id")));
                    $tmpl->setVariable("MESSAGE_PICTURE_ALIGNMENT", $message->get_attribute("bid:portlet:msg:picture_alignment"));
                    $tmpl->setVariable("MESSAGE_PICTURE_WIDTH", $picture_width);
                    $tmpl->parse("BLOCK_MESSAGE_PICTURE");
                }

                //LINK
                //parse in link if it exists
                if (trim($message->get_attribute("bid:portlet:msg:link_url")) != "") {
                    $tmpl->setCurrentBlock("BLOCK_MESSAGE_LINK");
                    if (trim($message->get_attribute("bid:portlet:msg:link_open")) != "checked") {
                        $tmpl->setVariable("MESSAGE_LINK_URL_LABEL", (($message->get_attribute("bid:portlet:msg:link_url_label") !== "") ? $UBB->encode($message->get_attribute("bid:portlet:msg:link_url_label")) : $message->get_attribute("bid:portlet:msg:link_url")));
                        $tmpl->setVariable("MESSAGE_LINK_URL", revealPath($message->get_attribute("bid:portlet:msg:link_url"), $message->get_path()));
                        $tmpl->setVariable("MESSAGE_LINK_TARGET", "_top");
                    } else {
                        $tmpl->setVariable("MESSAGE_LINK_URL_LABEL", (($message->get_attribute("bid:portlet:msg:link_url_label") !== "") ? $UBB->encode($message->get_attribute("bid:portlet:msg:link_url_label")) : $message->get_attribute("bid:portlet:msg:link_url")));
                        $tmpl->setVariable("MESSAGE_LINK_URL", revealPath($message->get_attribute("bid:portlet:msg:link_url"), $message->get_path()));
                        $tmpl->setVariable("MESSAGE_LINK_TARGET", "_blank");
                    }
                    $tmpl->parse("BLOCK_MESSAGE_LINK");
                }

                //SEPARATOR
                if ($separator) {
                    $tmpl->setCurrentBlock("BLOCK_SEPARATOR");
                    $tmpl->parse("BLOCK_SEPARATOR");
                }

                $separator = true;
                $tmpl->parse("BLOCK_MESSAGE");
            }
        } else {
            //NO MESSAGE
            $tmpl->setCurrentBlock("BLOCK_NO_MESSAGE");
            $tmpl->setVariable("NO_MESSAGE_INFO", "Keine Nachrichten vorhanden.");
            $tmpl->parse("BLOCK_NO_MESSAGE");
        }
        $tmpl->setVariable("PATH_URL", PATH_URL);
        $tmpl->parse("BLOCK_PORTLET_MESSAGE");
        $htmlBody = $tmpl->get();
        $this->content = $htmlBody;
        
        }catch (\steam_exception $e){
            $htmlBody = '<div style="background-color:red;color:white;text-align:center;">';
            $htmlBody.= "Die Meldungen im Portal wurden durch das Kopieren mit der alten Oberfläche zerstört. ";
            $htmlBody.= "Kopieren Sie Portale nur mit der neuen Oberfläche.<br>";
            //$htmlBody.= "Eine Wiederherstellung ist nur durch den Support möglich.<br>";
            $htmlBody.= "<br>";
            
            $htmlBody.= "Bei einer Reparatur können die in den Meldungen enthaltenen Bilder nicht den ursprünglichen Meldungen zugeordnet werden. ";
            $htmlBody.= "Die Bilder werden daher in die Zwischenablage verschoben. ";
            $htmlBody.= "Ferner geht die ursprüngliche Reihenfolge der Meldungen verloren. ";
            $htmlBody.= "Eine Reparatur ist nur mit Schreibrechten möglich.<br>";
            
            $htmlBody.= "<br>";
            
            $htmlBody.= '<a style="color:white" href="/portletmsg/repair/'.$objectId.'/">Reparaturversuch durchführen</a><br>';
            $htmlBody.= "</div>";
        }
        

        //widgets
        $outputWidget = new \Widgets\RawHtml();
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

    //get message ids in container order
    //not used
    private function getMessageIds($messageContainer) {
        $realMessageIds = array();
        $inventory = $messageContainer->get_inventory();

        foreach ($inventory as $steamObject) {
            $docType = $steamObject->get_attribute("DOC_MIME_TYPE");
            if ($docType == "text/plain") {
                $realMessageIds[] = $steamObject->get_id();
            } else {
                //continue;
            }
        }

        //repair old portals
        $messageContainer->set_attribute("content", $inventory);
        return $realMessageIds;
    }

    //not used
    private function getImagePath($id, $portlet = "") {
        if ($portlet != "") {
            $inventory = $portlet->get_inventory();
            foreach ($inventory as $object) {
                //TODO: return url by name
            }
        }
        return getDownloadUrlForObjectId($id);
    }

}

?>