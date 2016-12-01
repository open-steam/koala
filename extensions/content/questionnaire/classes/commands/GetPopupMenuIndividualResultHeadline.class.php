<?php

namespace Questionnaire\Commands;

class GetPopupMenuIndividualResultHeadline extends \AbstractCommand implements \IAjaxCommand { 

    private $params;
    private $id;
    private $surveyId;
    private $selection;
    private $x, $y, $height, $width;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        $this->surveyId = $this->params["survey"];
        $this->x = $this->params["x"];
        $this->y = $this->params["y"];
        $this->height = $this->params["height"];
        $this->width = $this->params["width"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $explorerUrl = \Explorer::getInstance()->getAssetUrl();
        $editIcon = $explorerUrl . "icons/menu/svg/edit.svg";
        $trashIcon = $explorerUrl . "icons/menu/svg/trash.svg";
        $survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->surveyId);
        $result_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
        $questionnaire = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        $creator = $questionnaire->get_creator();
        $questionnaireContainer = $questionnaire->get_environment();
    		$results = $result_container->get_inventory();
        $resultIds = array();
        $counter = 0;
    		foreach ($results as $result) {
    			if ($result instanceof \steam_object && $result->get_attribute("QUESTIONNAIRE_RELEASED") != 0) {
            $resultIds[] = $result->get_id();
            $counter++;
          }
        }

        $items[] = array("name" => "<svg><use xlink:href='{$editIcon}#edit'/></svg> Bearbeiten", "command" => "EditResult", "namespace" => "questionnaire", "params" => "{'id':'{$this->id}'}", "type" => "popup");

        if(($creator->get_id() == $user->get_id() || \lms_steam::is_steam_admin($user)) && $counter > 0){
          $items[] = array("raw" => "<a href=\"#\" onclick=\"deleteAllResults(" . json_encode($resultIds) . "," . $this->surveyId . "," . $questionnaireContainer->get_id() . ")\"><div><svg><use xlink:href='{$trashIcon}#trash'/></svg> Alle Abgaben löschen</div></a>");
          $offset = 150;
        }
        else{
          $offset = 85;
        }

        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setItems($items);
        $popupMenu->setPosition(round($this->x + $this->width-$offset)  . "px", round($this->y + $this->height+5) . "px");
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);
        return $ajaxResponseObject;
    }

}

?>
