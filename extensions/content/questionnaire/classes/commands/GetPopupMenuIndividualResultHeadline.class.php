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
        $user = \lms_steam::get_current_user();
        $creator = $questionnaire->get_creator();
    		$results = $result_container->get_inventory();
        $resultIds = array();
        $counter = 0;
    		foreach ($results as $result) {
    			if ($result instanceof \steam_object && $result->get_attribute("QUESTIONNAIRE_RELEASED") != 0) {
            $resultIds[] = $result->get_id();
            $counter++;
          }
        }

        // check if current user is admin
        $staff = $questionnaire->get_attribute("QUESTIONNAIRE_STAFF");
        $admin = 0;
        $creatorOrRoot = 0;
        if($creator->get_id() == $user->get_id() || \lms_steam::is_steam_admin($user)){
          $creatorOrRoot = 1;
        }
        else{
          if(in_array($user, $staff)){
            $admin = 1;
          }
          else{
            foreach ($staff as $object) {
              if ($object instanceof \steam_group && $object->is_member($user)) {
                $admin = 1;
                break;
              }
            }
          }
        }

        $items[] = array("name" => "<svg><use xlink:href='{$editIcon}#edit'/></svg> Bearbeiten", "command" => "EditResult", "namespace" => "questionnaire", "params" => "{'id':'{$this->surveyId}'}", "type" => "popup");

        if(($creatorOrRoot || ($admin && $questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT") == 1)) && $counter > 0){
          $items[] = array("raw" => "<a href=\"#\" onclick=\"if(confirm('Alle Abgaben werden unwiderruflich gelöscht. Wollen Sie wirklich fortfahren?')){deleteAllResults(" . json_encode($resultIds) . "," . $this->surveyId . "," . $this->id . ")}\"><div><svg><use xlink:href='{$trashIcon}#trash'/></svg> Alle Abgaben löschen</div></a>");
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
