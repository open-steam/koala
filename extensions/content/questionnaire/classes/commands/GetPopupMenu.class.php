<?php

namespace Questionnaire\Commands;

class GetPopupMenu extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $selection;
    private $x, $y, $height, $width;
    private $logged_in;
    private $resultId;

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
        $this->resultId = $this->params["resultId"];
        $portal = \lms_portal::get_instance();
        $lms_user = $portal->get_user();
        $this->logged_in = $lms_user->is_logged_in();
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $explorerUrl = \Explorer::getInstance()->getAssetUrl();
        $editIcon = $explorerUrl . "icons/menu/svg/edit.svg";
        $trashIcon = $explorerUrl . "icons/menu/svg/trash.svg";
        $questionnaireIcon = $explorerUrl . "icons/mimetype/svg/questionnaire.svg";

        $questionnaire = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $survey = $questionnaire->get_inventory()[0];
        $surveyId = $survey->get_id();

        $adminEdit = FALSE;
				if($questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT") == 1) $adminEdit = TRUE;

				$items = array(
          array("raw" => "<a href=\"#\" onclick=\"window.open('" . PATH_URL . "questionnaire/view/" . $surveyId . "/1/" . $this->resultId . "/1" . "/', '_self'); return false;\"><svg><use xlink:href='{$questionnaireIcon}#questionnaire'/></svg> Anzeigen</a>"),
					($adminEdit) ? array("raw" => "<a href=\"#\" onclick=\"window.open('" . PATH_URL . "questionnaire/view/" . $surveyId . "/1/" . $this->resultId . "/', '_self'); return false;\"><svg><use xlink:href='{$editIcon}#edit'/></svg> Bearbeiten</a>") : "",
					($adminEdit) ? array("raw" => "<a href=\"#\" onclick=\"deleteResult({$this->resultId}, {$surveyId}, {$this->id})\"><svg><use xlink:href='{$trashIcon}#trash'/></svg> Löschen</a>") : ""
				);




//edit
        //$QuestionnaireExtension->getExtensionUrl() . "view/" . $this->id . "/1/" . $result->get_id() . "/");

        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setItems($items);
        $popupMenu->setPosition(round($this->x + $this->width-85)  . "px", round($this->y + $this->height+5) . "px");
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);
        return $ajaxResponseObject;
    }

}

?>
