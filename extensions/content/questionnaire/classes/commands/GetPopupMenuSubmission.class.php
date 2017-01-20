<?php

namespace Questionnaire\Commands;

class GetPopupMenuSubmission extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $x, $y, $height, $width;
    private $result;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        $this->result = $this->params["result"];
        $this->x = $this->params["x"];
        $this->y = $this->params["y"];
        $this->height = $this->params["height"];
        $this->width = $this->params["width"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $explorerUrl = \Explorer::getInstance()->getAssetUrl();
        $viewIcon = $explorerUrl . "icons/mimetype/svg/questionnaire.svg";
        $editIcon = $explorerUrl . "icons/menu/svg/edit.svg";
        $trashIcon = $explorerUrl . "icons/menu/svg/trash.svg";

        $resultObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->result);
        $QuestionnaireExtension = \Questionnaire::getInstance();
        $survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $questionnaire = $survey->get_environment();
        $active = $QuestionnaireExtension->isActive($questionnaire->get_id());
        $user = \lms_steam::get_current_user();
        $creator = $questionnaire->get_creator();

        // check if current user is admin
        $staff = $questionnaire->get_attribute("QUESTIONNAIRE_STAFF");
        $admin = 0;
        $root = 0;
        if(\lms_steam::is_steam_admin($user)){
          $root = 1;
        }
        else if($creator->get_id() == $user->get_id()){
          $isCreator = 1;
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

        $items[] = array("raw" => "<a href='" . $QuestionnaireExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/1/" . $this->result . "/1" . "/'><div><svg><use xlink:href='" . $viewIcon . "#questionnaire'/></svg> Anzeigen</div></a>");
        if($root || $isCreator || ($active && $resultObject->get_attribute("QUESTIONNAIRE_RELEASED") == 0) || ($active && $questionnaire->get_attribute("QUESTIONNAIRE_OWN_EDIT") == 1) || ($admin && $questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT") == 1)){
          $items[] = array("raw" => "<a href='" . $QuestionnaireExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/1/" . $this->result . "/'><div><svg><use xlink:href='" . $editIcon . "#edit'/></svg> Bearbeiten</div></a>");
          $items[] = array("raw" => "<a href=\"#\" onclick=\"if(confirm('Die Abgabe wird unwiderruflich gelöscht. Wollen Sie wirklich fortfahren?')){deleteResult(" . $this->result . "," . $survey->get_id() . "," . $questionnaire->get_id() . ")}\"><div><svg><use xlink:href='{$trashIcon}#trash'/></svg> Löschen</div></a>");
        }

        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setItems($items);
        $popupMenu->setPosition(round($this->x + $this->width-85)  . "px", round($this->y + $this->height+5) . "px");
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);
        return $ajaxResponseObject;
    }

}

?>
