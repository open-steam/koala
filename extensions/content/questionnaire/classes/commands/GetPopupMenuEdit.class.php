<?php

namespace Questionnaire\Commands;

class GetPopupMenuEdit extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $selection;
    private $x, $y, $height, $width;
    private $logged_in;
    private $questionId;
    private $layoutElement;

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
        $this->questionId = $this->params["questionId"];
        $this->layoutElement = $this->params["layoutElement"];
        $portal = \lms_portal::get_instance();
        $lms_user = $portal->get_user();
        $this->logged_in = $lms_user->is_logged_in();
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $explorerUrl = \Explorer::getInstance()->getAssetUrl();
        $editIcon = $explorerUrl . "icons/menu/svg/edit.svg";
        $trashIcon = $explorerUrl . "icons/menu/svg/trash.svg";
        $copyIcon = $explorerUrl . "icons/menu/svg/copy.svg";

        $editMethod = "editQuestion";
        if($this->layoutElement) $editMethod = "editLayoutElement";

				$items = array(
          array("raw" => "<a href=\"#\" onclick=\"{$editMethod}({$this->questionId});return false;\"><svg><use xlink:href='{$editIcon}#edit'/></svg> Bearbeiten</a>"),
					array("raw" => "<a href=\"#\" onclick=\"copyElement({$this->id}, {$this->questionId});return false;\"><svg><use xlink:href='{$copyIcon}#copy'/></svg> Duplizieren</a>"),
					array("raw" => "<a href=\"#\" onclick=\"deleteElement({$this->questionId});return false;\"><svg><use xlink:href='{$trashIcon}#trash'/></svg> Löschen</a>")
				);

        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setItems($items);
        $popupMenu->setPosition(round($this->x + $this->width-85)  . "px", round($this->y + $this->height+5) . "px");
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);
        return $ajaxResponseObject;
    }

}

?>
