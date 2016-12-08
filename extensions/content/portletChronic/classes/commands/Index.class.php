<?php

namespace PortletChronic\Commands;

class Index extends \AbstractCommand implements \IIdCommand, \IFrameCommand {

    private $contentHtml;
    private $endHtml;
    private $listViewer;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $objectId = $requestObject->getId();
        $portlet = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        $params = $requestObject->getParams();
        $elements = $portlet->get_attribute("PORTLET_CHRONIC_COUNT");

        $this->getExtension()->addCSS();

        if (intval($elements) <= 0) {
            $elements = 5;
        }

        //reference handling
        if (isset($params["referenced"]) && $params["referenced"] == true) {
            if (!$portlet->check_access_read()) {
                $this->listViewer = new \Widgets\RawHtml();
                $this->listViewer->setHtml("");
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

        $portletName = $portlet->get_attribute(OBJ_DESC);
        $portletPath = \PortletChronic::getInstance()->getExtensionPath();

        $tmpl = new \HTML_TEMPLATE_IT();
        $tmpl->loadTemplateFile($portletPath . "/ui/html/index.template.html");
        $tmpl->setVariable("PORTLET_ID", $portlet->get_id());

        //headline
        $tmpl->setCurrentBlock("BLOCK_CHRONIC_HEADLINE");
        $tmpl->setVariable("HEADLINE", $portletName);

        //if the title is empty the headline will not be displayed (only in edit mode)
        if (empty($portletName)) {
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
            $popupmenu->setNamespace("PortletChronic");
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

        $tmpl->parse("BLOCK_CHRONIC_HEADLINE");

        $chronic = \Chronic::getInstance()->getChronic();
        $display = array();
        if (count($chronic) > $elements) {
            $display = array_slice($chronic, 0, $elements);
        } else {
            $display = $chronic;
        }

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($tmpl->get());
        $this->contentHtml = $rawHtml;

        $listViewer = new \Widgets\ListViewer();
        $headlineProvider = new HeadlineProvider();
        $headlineProvider->setWidth($width);
        $listViewer->setHeadlineProvider($headlineProvider);
        $contentProvider = new ContentProvider();
        $contentProvider->setId($objectId);
        $listViewer->setContentProvider($contentProvider);
        $listViewer->setFilterHidden(FALSE);
        $listViewer->setContent($display);
        $this->listViewer = $listViewer;

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml("</div></div>");
        $this->endHtml = $rawHtml;
    }

    public function idResponse(\IdResponseObject $idResponseObject) {
        $idResponseObject->addWidget($this->contentHtml);
        $idResponseObject->addWidget($this->listViewer);
        $idResponseObject->addWidget($this->endHtml);
        return $idResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $idResponseObject->addWidget($this->contentHtml);
        $idResponseObject->addWidget($this->listViewer);
        $idResponseObject->addWidget($this->endHtml);
        return $frameResponseObject;
    }

}

class HeadlineProvider implements \Widgets\IHeadlineProvider {

    private $width;

    public function setWidth($width) {
        if (($width - 40) < 0) {
            $this->width = 40;
        } else {
            $this->width = $width;
        }
    }

    public function getHeadlines() {
        return array("", "", "");
    }

    public function getHeadLineWidths() {
        return array(22, $this->width - 40, 0);
    }

    public function getHeadLineAligns() {
        return array("left", "left", "right");
    }

}

class ContentProvider implements \Widgets\IContentProvider {

    private $rawImage = 0;
    private $rawName = 1;
    private $rawChangeDate = 2;
    private $id;

    public function setId($id) {
        $this->id = $id;
    }

    public function getId($contentItem) {
        return $contentItem["id"] . $this->id;
    }

    public function getCellData($cell, $contentItem) {
        if (!is_int($cell)) {
            throw new \Exception("cell must be an integer!!");
        }

        if ($cell == $this->rawImage) {
            return $contentItem["image"];
        } else if ($cell == $this->rawName) {
            $url = $contentItem["link"];
            $name = $contentItem["name"];

            if (isset($url) && $url != "") {
                return "<a href=\"" . $url . "\" title=\"$name\"> " . $name . "</a>";
            } else {
                return $name;
            }
        } else if ($cell == $this->rawChangeDate) {
            return "";
        }
    }

    public function getNoContentText() {
        return "Ihr Verlauf ist leer.";
    }

    public function getOnClickHandler($contentItem) {
        return "";
    }

}

?>
