<?php

namespace Wiki\Commands;

class GetPopupMenuEntry extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $x, $y, $height, $width;
    private $versionDocId;
    private $isPrevVersion;
    private $wikiDocId;
    private $numberOfVersions;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        $this->x = $this->params["x"];
        $this->y = $this->params["y"];
        $this->height = $this->params["height"];
        $this->width = $this->params["width"];
        isset($this->params["versionDocId"]) ? $this->versionDocId = $this->params["versionDocId"] : "";
        $this->isPrevVersion = $this->params["isPrevVersion"];
        $this->wikiDocId = $this->params["wikiDocId"];
        $this->numberOfVersions = $this->params["numberOfVersions"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $explorerAssetUrl = \Explorer::getInstance()->getAssetUrl();
        $restoreIcon = $explorerAssetUrl . "icons/menu/svg/restore.svg";
        $versionControlIcon = $explorerAssetUrl . "icons/mimetype/svg/chronic.svg";
        $trashIcon = $explorerAssetUrl . "icons/menu/svg/trash.svg";

        $wiki_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $user = \lms_steam::get_current_user();
        $items = array();

        if ($this->isPrevVersion) {
            array_push($items, array("raw" => "<a href=\"#\" onclick=\"if(confirm('Wollen Sie diese Version wirklich wiederherstellen?')){window.open('" . PATH_URL . "wiki/recover/" . $this->wikiDocId . "/" . $this->versionDocId . "/', '_self');}\";><div><svg><use xlink:href=\"{$restoreIcon}#restore\"/></svg> Wiederherstellen</div></a>"));
            if ($wiki_container->check_access_move($user)) {
                array_push($items, array("raw" => "<a href=\"#\" onclick=\"if(confirm('Wollen Sie diese Version wirklich löschen?')){window.open('" . PATH_URL . "wiki/delete/version/" . $this->versionDocId . "', '_self');}\";><div><svg><use xlink:href=\"{$trashIcon}#trash\"/></svg> Version löschen</div></a>"));
            }
        } else {
            if ($wiki_container->check_access_move($user)) {
                array_push($items, array("raw" => "<a href=\"#\" onclick=\"if(confirm('Wollen Sie diesen Eintrag wirklich löschen?')){window.open('" . PATH_URL . "wiki/delete/" . $this->id . "/" . $this->wikiDocId . "', '_self');}\";><div><svg><use xlink:href=\"{$trashIcon}#trash\"/></svg> Eintrag löschen</div></a>"));
            }
        }

        if ($this->numberOfVersions > 0) {
            array_push($items, array("raw" => "<a href=\"#\" onclick=\"window.open('" . PATH_URL . "wiki/versions/" . $this->wikiDocId . "', '_self');\";><div><svg><use xlink:href=\"{$versionControlIcon}#chronic\"/></svg> " . gettext("enter version management") . "</div></a>"));
            $width = 160;
        } else {
            $width = 110;
        }

        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setItems($items);

        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);

        return $ajaxResponseObject;
    }

}

?>
