<?php

namespace Questionnaire\Commands;

class EditResult extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $questionnaire = $survey->get_environment();
        $dialog = new \Widgets\Dialog();
        $dialog->setTitle("Einstellungen");
        $dialog->setCancelButtonLabel(null);
        $dialog->setSaveAndCloseButtonLabel("Speichern & Schließen");

        $showParticipants = new \Widgets\RadioButton();
        $showParticipants->setLabel("Teilnehmer anzeigen:");
        $showParticipants->setData($questionnaire);
        $showParticipants->setType("horizontal");
        $showParticipants->setContentProvider(\Widgets\DataProvider::attributeProvider("QUESTIONNAIRE_SHOW_PARTICIPANTS"));
        $showParticipants->setOptions(array(array("name" => "Ja", "value" => 1), array("name" => "Nein", "value" => 0)));

        $showCreationTime = new \Widgets\RadioButton();
        $showCreationTime->setLabel("Erstellungszeit anzeigen:");
        $showCreationTime->setData($questionnaire);
        $showCreationTime->setType("horizontal");
        $showCreationTime->setContentProvider(\Widgets\DataProvider::attributeProvider("QUESTIONNAIRE_SHOW_CREATIONTIME"));
        $showCreationTime->setOptions(array(array("name" => "Ja", "value" => 1), array("name" => "Nein", "value" => 0)));

        $adminEdit = new \Widgets\RadioButton();
        $adminEdit->setLabel("Administratoren dürfen Antworten bearbeiten:");
        $adminEdit->setData($questionnaire);
        $adminEdit->setType("horizontal");
        $adminEdit->setContentProvider(\Widgets\DataProvider::attributeProvider("QUESTIONNAIRE_ADMIN_EDIT"));
        $adminEdit->setOptions(array(array("name" => "Ja", "value" => 1), array("name" => "Nein", "value" => 0)));

        $ownEdit = new \Widgets\RadioButton();
        $ownEdit->setLabel("Teilnehmer dürfen eigene Antworten bearbeiten:");
        $ownEdit->setData($questionnaire);
        $ownEdit->setType("horizontal");
        $ownEdit->setContentProvider(\Widgets\DataProvider::attributeProvider("QUESTIONNAIRE_OWN_EDIT"));
        $ownEdit->setOptions(array(array("name" => "Ja", "value" => 1), array("name" => "Nein", "value" => 0)));

        // check if current user is admin
        $staff = $questionnaire->get_attribute("QUESTIONNAIRE_STAFF");
        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        $creator = $questionnaire->get_creator();
        $admin = 0;
        if ($creator->get_id() == $user->get_id() || \lms_steam::is_steam_admin($user)) {
          $admin = 1;
        }
        else{
          if(in_array($user, $staff)){
            $admin = 1;
          }
          else{
            foreach ($staff as $object) {
              if ($object instanceof steam_group && $object->is_member($user)) {
                $admin = 1;
                break;
              }
            }
          }
        }

        if(!admin){
          $showParticipants->setReadOnly(true);
          $showCreationTime->setReadOnly(true);
          $adminEdit->setReadOnly(true);
          $ownEdit->setReadOnly(true);
        }

        $dialog->addWidget($showParticipants);
        $dialog->addWidget($showCreationTime);
        $dialog->addWidget($adminEdit);
        $dialog->addWidget($ownEdit);

        $raw = new \Widgets\RawHtml();
        $raw->setCSS('.dialog .widgets_radiobutton .widgets_label{width:285px;}');
        $dialog->addWidget($raw);

        $dialog->setWidth(400);

        $ajaxResponseObject->addWidget($dialog);
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}

?>