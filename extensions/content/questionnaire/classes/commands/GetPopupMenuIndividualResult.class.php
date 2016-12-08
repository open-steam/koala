<?php

namespace Questionnaire\Commands;

class GetPopupMenuIndividualResult extends \AbstractCommand implements \IAjaxCommand {

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
        $resultObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->resultId);
        $questionnaire = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $survey = $questionnaire->get_inventory()[0];
        $surveyId = $survey->get_id();
        $user = \lms_steam::get_current_user();
        $creator = $questionnaire->get_creator();

        if($resultObject->get_creator()->get_id() == $user->get_id()){
    			$ownResult = true;
    		}
    		else{
    			$ownResult = false;
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

				$items[] = array("raw" => "<a href=\"#\" onclick=\"window.open('" . PATH_URL . "questionnaire/view/" . $surveyId . "/1/" . $this->resultId . "/1" . "/', '_self'); return false;\"><svg><use xlink:href='{$questionnaireIcon}#questionnaire'/></svg> Anzeigen</a>");
        if ($creatorOrRoot || ($admin && $ownResult && $questionnaire->get_attribute("QUESTIONNAIRE_OWN_EDIT") == 1) || ($admin && $questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT") == 1)) {
          $items[] = array("raw" => "<a href=\"#\" onclick=\"window.open('" . PATH_URL . "questionnaire/view/" . $surveyId . "/1/" . $this->resultId . "/', '_self'); return false;\"><svg><use xlink:href='{$editIcon}#edit'/></svg> Bearbeiten</a>");
					$items[] = array("raw" => "<a href=\"#\" onclick=\"if(confirm('Die Abgabe wird unwiderruflich gelöscht. Wollen Sie wirklich fortfahren?')){deleteResult({$this->resultId}, {$surveyId}, {$this->id})}\"><svg><use xlink:href='{$trashIcon}#trash'/></svg> Löschen</a>");
				};

        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setItems($items);
        $popupMenu->setPosition(round($this->x + $this->width-85)  . "px", round($this->y + $this->height+5) . "px");
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);
        return $ajaxResponseObject;
    }

}

?>
