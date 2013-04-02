<?php

namespace Portal\Commands;

class Index extends \AbstractCommand implements \IFrameCommand, \IIdCommand {

    private $params;
    private $id;
    private $rawHtmlWidget;
    private $portalObject;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->id = $this->params[0] : "";
        } else if ($requestObject instanceof \IdRequestObject) {
            $this->id = $requestObject->getId();
        }

        //get singleton and portlet path
        $portalInstance = \Portal::getInstance();
        $portalPath = $portalInstance->getExtensionPath();

        //template
        $templateFileName = $portalPath . "/ui/html/index.html";
        $tmpl = new \HTML_TEMPLATE_IT();
        $tmpl->loadTemplateFile($templateFileName);

        $this->getExtension()->addCSS();
        $this->getExtension()->addJS();

        $objectId = $this->id;

        //get the portal object
        $this->portalObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);

        $type = getObjectType($this->portalObject);
        if (!($type === "portal")) {
            \ExtensionMaster::getInstance()->send404Error();
            die;
        }

        \Portal::getInstance()->setPortalObject($this->portalObject);

        //get the content of the portal object
        $portalColumns = $this->portalObject->get_inventory();

        $htmlBody = "";
        $extensionMaster = \ExtensionMaster::getInstance();

        $count = 0;

        $this->rawHtmlWidget = new \Widgets\RawHtml();
        $portalWidth = 0;
        foreach ($portalColumns as $columnObject) {
            $columnObjectId = $columnObject->get_id();
            $portalWidth += $columnObject->get_attribute("bid:portal:column:width");
            $widgets = $extensionMaster->getWidgetsByObjectId($columnObjectId, "view");
            $this->rawHtmlWidget->addWidgets($widgets);
            $data = \Widgets\Widget::getData($widgets);
            $htmlBody.= $data["html"];
            $count++;
        }
        /*    if ($portalWidth > 900) {
          $warning = "Damit das Portal korrekt dargestellt werden kann, müssen die Breite der Spalten verringert werden.
          Eine Verminderung der Spaltengröße kann in den <a onclick=\"sendRequest('Sort', {'id':" . $objectId . "}, '', 'popup', null, null, 'portal');return false;;menu_clicked(this);\">Optionen</a> vorgenommen werden.";
          $tmpl->setVariable("WARNING", $warning);
          } */

        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
        if (isset($this->portalObject) && $this->portalObject->check_access_write($currentUser)) {
            $htmlBody .= "<script>if (readCookie(\"portalEditMode\") === \"{$objectId}\") {portalLockButton({$objectId})}</script>";
        }

        $tmpl->setVariable("PORTAL_WIDTH", $portalWidth + 40);
        $tmpl->setVariable("BODY", $htmlBody);
        $tmpl->setVariable("PORTAL_OBJECT_ID", $this->portalObject->get_id());


        $htmlBodyTemplated = $tmpl->get();

        $this->rawHtmlWidget->setHtml($htmlBodyTemplated);
    }

    public function idResponse(IdResponseObject $idResponseObject) {
        
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {

        $cssGenerator = new ColorGeneratorPortal();
        $cssGenerator->setId($this->id);
        $cssRaw = $cssGenerator->generateCss();
        
        $cssWidget = new \Widgets\RawHtml();
        $cssWidget->setCss($cssRaw);
        $cssWidget->setHtml("");

        $frameResponseObject->addWidget($cssWidget);

        //Start Testcase
        $testLink = new \Widgets\RawHtml();
        $link = "<a onclick=\"sendRequest('ColorOptions', {'id':'" . $this->id . "'}, '', 'popup', null, null, 'portal');return false;\">Farben</a>";
        $testLink->setHtml($link);
      //  $frameResponseObject->addWidget($testLink); //TODO: Einkommentieren zum Testen der Farbkonfig
        //End Testcase

        $assetUrl = \Portal::getInstance()->getAssetUrl();
        $minPicUrl = $assetUrl . "icons/min.png";
        $maxPicUrl = $assetUrl . "icons/max.png";

        $frameResponseObject->setTitle(getCleanName($this->portalObject));
        $frameResponseObject->addWidget($this->rawHtmlWidget);

        $obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $isStatusbarDeact = $obj->get_attribute("bid:portal_status_deactivate");
        if ($isStatusbarDeact == "1") {
            $jsWrapper = new \Widgets\JSWrapper();
            $jsWrapper->setPostJsCode(<<<END
                    $('#menu_wrapper').hide();
                    $('#content_wrapper').prepend('<div id="max-layer" class="min-max-layer"><a id="max-href"><img id="max-pic" class="max-min-pic" src="{$maxPicUrl}"></a></div>');
                    
                    $('.max-min-pic').css('width', '20px');
                    $('.min-max-layer').css('width', '20px');
                    $('.min-max-layer').css('margin-left', 'auto');
                    $('.min-max-layer').css('margin-right', 'auto');
                    $('.min-max-layer').css('padding-left', '943px');
                    $('.min-max-layer').css('margin-top', '-20px');
                    $('.max-min-pic').css('margin-left', 'auto');
                    $('.max-min-pic').css('margin-right', 'auto');
                    $('.max-min-pic').css('margin-top', 'auto');
                    $('.max-min-pic').css('margin-bottom', 'auto');
                    
                    $("#max-pic").click(function() {
                        $("#max-layer").hide();
                        $('#menu_wrapper').show();
                        return false;
                    });
                    
                    
                    
                    
END
            );

            $frameResponseObject->addWidget($jsWrapper);
        }



        return $frameResponseObject;
    }

}

?>