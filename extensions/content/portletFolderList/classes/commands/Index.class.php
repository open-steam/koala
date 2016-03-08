<?php
namespace PortletFolderList\Commands;

class Index extends \AbstractCommand implements \IIdCommand, \IFrameCommand {

    private $contentHtml;
    private $endHtml;
    private $listViewer;

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
        $portlet = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        $params = $requestObject->getParams();
        $elements = $portlet->get_attribute("PORTLET_FOLDERLIST_ITEMCOUNT");
        if (intval($elements) <= 0) {
            $elements = 10;
        }
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

        $portletName = getCleanName($portlet);
        $portletInstance = \PortletFolderList::getInstance();
        $portletPath = $portletInstance->getExtensionPath();

        $tmpl = new \HTML_TEMPLATE_IT();
        $tmpl->loadTemplateFile($portletPath . "/ui/html/index.template.html");
        $tmpl->setVariable("PORTLET_ID", $portlet->get_id());

        //headline
        $tmpl->setCurrentBlock("BLOCK_FOLDER_HEADLINE");
        $tmpl->setVariable("HEADLINE", $portletName);

        //reference icon
        if ($portletIsReference) {
            $envId = $portlet->get_environment()->get_environment()->get_id();
            $envUrl = PATH_URL . "portal/index/" . $envId;
            $tmpl->setVariable("REFERENCE_ICON", "<a href='{$envUrl}' target='_blank'><img src='{$referIcon}'></a>");
        }

        if (!$portletIsReference) {
            $popupmenu = new \Widgets\PopupMenu();
            $popupmenu->setData($portlet);
            $popupmenu->setNamespace("PortletFolderList");
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

        $contentProvider = new ContentProvider();
        try {
            $folder = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $portlet->get_attribute("PORTLET_FOLDERLIST_FOLDERID"));
        } catch (\steam_exception $ex) {
            $folder = "";
        }
        if (getObjectType($folder) === "room" && $folder->check_access_read()) {
            $display = $folder->get_inventory();
        } else {
            $display = array();
            $contentProvider->setValid(false);
        }
        if (count($display) > $elements) {
            $display = array_slice($display, 0, $elements);
        }

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($tmpl->get());
        $this->contentHtml = $rawHtml;

        $listViewer = new \Widgets\ListViewer();
        $headlineProvider = new HeadlineProvider();
        $headlineProvider->setWidth($width);
        $headlineProvider->setDate($portlet->get_attribute("PORTLET_FOLDERLIST_CHANGEDATE"));
        $listViewer->setHeadlineProvider($headlineProvider);
        $listViewer->setContentProvider($contentProvider);
        $listViewer->setContent($display);
        $this->listViewer = $listViewer;

        $rawHtml = new \Widgets\RawHtml();
        if (count($display) > 0) {
            $html = "<br><div style=\"text-align:center;\"><a href=\"" . PATH_URL . "explorer/index/" . $portlet->get_attribute("PORTLET_FOLDERLIST_FOLDERID") . "/\">Gesamten Ordnerinhalt anzeigen</a></div><br>";
        } else {
            $html = "";
        }
        $rawHtml->setHtml($html . "</div></div>");
        $this->endHtml = $rawHtml;
    }

    public function idResponse(\IdResponseObject $idResponseObject) {
        $idResponseObject->addWidget($this->contentHtml);
        $idResponseObject->addWidget($this->listViewer);
        $idResponseObject->addWidget($this->endHtml);
        return $idResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $frameResponseObject->addWidget($this->contentHtml);
        $frameResponseObject->addWidget($this->listViewer);
        $frameResponseObject->addWidget($this->endHtml);
        return $frameResponseObject;
    }
}

class HeadlineProvider implements \Widgets\IHeadlineProvider {

    private $width;
    private $date;

    public function setDate($bool) {
        $this->date = $bool;
    }

    public function setWidth($width) {
        if ($this->date === "true") {
            $min = 170;
        } else {
            $min = 40;
        }
        if (($width - $min) < 0) {
            $this->width = $min;
        } else {
            $this->width = $width;
        }
    }

    public function getHeadlines() {
        return array("", "", "");
    }

    public function getHeadLineWidths() {
        if ($this->date === "true") {
            return array(22, $this->width-170, 130);
        } else {
            return array(22, $this->width-40, 0);
        }
    }

    public function getHeadLineAligns() {
        return array("left", "left", "right");
    }

}

class ContentProvider implements \Widgets\IContentProvider {

    private $rawImage = 0;
    private $rawName = 1;
    private $rawChangeDate = 2;
    private $valid = true;

    public function getId($contentItem) {
        return $contentItem->get_id();
    }

    public function setValid($bool) {
        $this->valid = $bool;
    }

    public function getCellData($cell, $contentItem) {
        if (!is_int($cell)) {
            throw new \Exception("cell must be an integer!!");
        }

        if ($contentItem instanceof \steam_link){
            $contentItemObject = $contentItem->get_source_object();
        } else {
            $contentItemObject = $contentItem;
        }

        if ($cell == $this->rawImage) {
            return "<img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/" . deriveIcon($contentItemObject) . "\"></img>";
        } else if ($cell == $this->rawName) {
            $url = \ExtensionMaster::getInstance()->getUrlForObjectId($contentItemObject->get_id(), "view");
            $desc = $contentItemObject->get_attribute("OBJ_DESC");
            $name = getCleanName($contentItemObject, 50);

            // check existence of link target
            $sourceObject = $contentItemObject;
            if (!(($sourceObject != null) && ($sourceObject instanceof \steam_object))) {
                return "<div style=\"color:red\">$name (Objekt gelöscht)</div>";
            }

            if (isset($url) && $url != "") {
                return "<a href=\"" . $url . "\" title=\"$desc\"> " . $name . "</a>";
            } else {
                return $name;
            }
        } else if ($cell == $this->rawChangeDate) {
            return getReadableDate($contentItemObject->get_attribute("OBJ_LAST_CHANGED"));
        }
    }

    public function getNoContentText() {
        if ($this->valid) {
            return "Keine Objekte vorhanden.";
        } else {
            return "Objekt ID ungültig oder keine Leserechte.";
        }
    }

    public function getOnClickHandler($contentItem) {
        return "";
    }

}
?>
