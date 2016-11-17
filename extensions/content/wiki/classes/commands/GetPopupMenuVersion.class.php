<?php

namespace Wiki\Commands;

class GetPopupMenuVersion extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $docId;
    private $wikiDocId;
    private $docVersion;
    private $markedfordiff;
    private $prevVersionId;
    private $x, $y, $height;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        $this->docId = $this->params["docId"];
        $this->docVersion = $this->params["docVersion"];
        $this->wikiDocId = $this->params["wikiDocId"];
        isset($this->params["markedForDiff"]) ? $this->markedForDiff = $this->params["markedForDiff"] : "";
        isset($this->params["prevVersionId"]) ? $this->prevVersionId = $this->params["prevVersionId"] : "";
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $explorerAssetUrl = \Explorer::getInstance()->getAssetUrl();
        $restoreIcon = $explorerAssetUrl . "icons/menu/svg/restore.svg";
        $markIcon = $explorerAssetUrl . "icons/unsubscribe.svg";
        $compareIcon = $explorerAssetUrl . "icons/menu/svg/sort_horizontal.svg";
        $trashIcon = $explorerAssetUrl . "icons/menu/svg/trash.svg";

        $wiki_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $doc = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->docId);
        $notCurrentVersion = ($this->docId !== $this->wikiDocId);
        $items = array();

        if ($doc instanceof \steam_document) {
            if ($notCurrentVersion) {
                if ($wiki_container->check_access_write()) {
                    array_push($items, array("raw" => "<a href=\"#\" onclick=\"if(confirm('Wollen Sie diese Version wirklich wiederherstellen?')){window.open('" . PATH_URL . "wiki/recover/" . $this->wikiDocId . "/" . $this->docId . "/', '_self');}\";><div><svg><use xlink:href=\"{$restoreIcon}#restore\"/></svg> Wiederherstellen</div></a>"));
                }
            }

            if (!isset($this->markedForDiff) || (isset($this->markedForDiff) && $this->markedForDiff != $this->docId)) {
                array_push($items, array("raw" => "<a href=\"#\" onclick=\"window.open('" . PATH_URL . "wiki/versions/" . $this->wikiDocId . "/?markedfordiff=" . $this->docId . "', '_self');\";><div><svg style='color:#ff8300;'><use xlink:href=\"{$markIcon}#unsubscribe\"/></svg> Für Vergleich markieren</div></a>"));
            }

            if (isset($this->markedForDiff) && $this->markedForDiff != $this->docId) {
                array_push($items, array("raw" => "<a href=\"#\" onclick=\"window.open('" . PATH_URL . "wiki/compare/" . $this->wikiDocId . "/" . $this->docId . "/" . $this->markedForDiff . "', '_self');\";><div><svg style='color:#ff8300;'><use xlink:href=\"{$compareIcon}#sort_horizontal\"/></svg> Mit markierter Version vergleichen</div></a>"));
            }

            if ($this->docVersion != 1) {
                array_push($items, array("raw" => "<a href=\"#\" onclick=\"window.open('" . PATH_URL . "wiki/compare/" . $this->wikiDocId . "/" . $this->docId . "/" . $this->prevVersionId . "', '_self');\";><div><svg><use xlink:href=\"{$compareIcon}#sort_horizontal\"/></svg> Mit vorheriger Version vergleichen</div></a>"));
            } elseif (!isset($this->markedForDiff)) {
            }
        }

        if ($wiki_container->check_access_write() && $notCurrentVersion) {
            array_push($items, array("raw" => "<a href=\"#\" onclick=\"if(confirm('Wollen Sie diese Version wirklich löschen?')){window.open('" . PATH_URL . "wiki/delete/version/" . $this->docId . "/', '_self');}\";><div><svg><use xlink:href=\"{$trashIcon}#trash\"/></svg> Löschen</div></a>"));
        }
        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setItems($items);

        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);

        return $ajaxResponseObject;
    }

}

?>
