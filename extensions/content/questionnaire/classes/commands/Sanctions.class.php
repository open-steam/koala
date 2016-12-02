<?php
namespace Questionnaire\Commands;

class Sanctions extends \AbstractCommand implements \IAjaxCommand {

  private $params;
	private $id;
  private $showDialog;
  private $checked;
  private $participate;
  private $admin;
  private $userId;
  private $groupId;
  private $object;
  private $questionnaire;
  private $surveys;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
      $this->params = $requestObject->getParams();
      isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
      isset($this->params["dialog"]) ? $this->showDialog = $this->params["dialog"]: "";
      isset($this->params["checked"]) ? $this->checked = $this->params["checked"]: "";
      isset($this->params["participate"]) ? $this->participate = $this->params["participate"]: "";
      isset($this->params["admin"]) ? $this->admin = $this->params["admin"]: "";
      isset($this->params["userId"]) ? $this->userId = $this->params["userId"]: "";
      isset($this->params["groupId"]) ? $this->groupId = $this->params["groupId"]: "";
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

      if($this->showDialog == "true"){
          return $this->createDialog($ajaxResponseObject);
      } else {
          return $this->saveSanctions($ajaxResponseObject);
      }
    }

    //display sanction dialog
    function createDialog(\AjaxResponseObject $ajaxResponseObject){
      $ajaxResponseObject->setStatus("ok");

      $questionnaire = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
      $user = \lms_steam::get_current_user();
      $QuestionnaireExtension = \Questionnaire::getInstance();
      $QuestionnaireExtension->addCSS();
      $QuestionnaireExtension->addJS();

      $dialog = new \Widgets\Dialog();
      $dialog->setTitle("Rechte von " . $questionnaire->get_attribute("OBJ_NAME"));

      $creator = $questionnaire->get_creator();

      // check if current user is admin
  		$staff = $questionnaire->get_attribute("QUESTIONNAIRE_STAFF");
  		$admin = 0;
  		$allowed = false;
      $creatorOrRoot = false;
  		if ($creator->get_id() == $user->get_id() || \lms_steam::is_steam_admin($user)) {
  			$admin = 1;
  			$allowed = true;
        $creatorOrRoot = true;
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

      if ($admin == 0) {
        $rawWidget = new \Widgets\RawHtml();
        $rawWidget->setHtml("<center>Die Bearbeitung dieses Fragebogens ist den Administratoren vorbehalten.</center>");
        $dialog->addWidget($rawWidget);
        $ajaxResponseObject->addWidget($dialog);
        return $ajaxResponseObject;
      }

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

      if(!$creatorOrRoot){
        $adminEdit->setReadOnly(true);
      }

      $dialog->addWidget($adminEdit);
      $dialog->addWidget($ownEdit);

      $raw = new \Widgets\RawHtml();
      $raw->setCSS('.dialog .widgets_radiobutton .widgets_label{width:395px;}');
      $dialog->addWidget($raw);

      $clear = new \Widgets\Clearer();
      $dialog->addWidget($clear);

      $content = $QuestionnaireExtension->loadTemplate("questionnaire_sanction.template.html");
      $content->setCurrentBlock("BLOCK_CONFIGURATION_TABLE");
      $content->setVariable("PARTICIPATE_LABEL", "Ausfüllen");
      $content->setVariable("EDIT_LABEL", "Bearbeiten & Auswerten");
      $content->setVariable("OWNER_LABEL", "Nutzer");
      $content->setVariable("GROUP_LABEL", "Gruppen");

      $content->setCurrentBlock("BLOCK_USER");
      $content->setVariable("USER_NAME", $creator->get_full_name());
      $content->setVariable("SEND_PARTICIPATE_REQUEST",  "sendRequest('Sanctions',{'id':" . $this->id . ",'showDialog':false,'checked':this.checked,'participate':true,'userId':" . $creator->get_id() . "},'','data',function(response){dataSaveFunctionCallback(response);},null,'Questionnaire');");
      $content->setVariable("SEND_ADMIN_REQUEST",  "sendRequest('Sanctions',{'id':" . $this->id . ",'showDialog':false,'checked':this.checked,'admin':true,'userId':" . $creator->get_id() . "},'','data',function(response){dataSaveFunctionCallback(response);},null,'Questionnaire');");
      $content->setVariable("PARTICIPATE_CHECKED", "checked");
      $content->setVariable("ADMIN_CHECKED", "checked");
      $content->setVariable("PARTICIPATE_DISABLED", "disabled");
      $content->setVariable("ADMIN_DISABLED", "disabled");
      $content->setVariable("IMAGE", "<div style='float:left; margin-right:5px;'><svg style='color:#3a6e9f; height:16px; width:16px;'><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/user.svg#user'/></svg></div>");
      $content->parse("BLOCK_USER");

      //get favorites as an array of user/group objects
      $rawFavorites = $creator->get_buddies();
      $participants = $questionnaire->get_attribute("QUESTIONNAIRE_GROUP");
      $admins = $questionnaire->get_attribute("QUESTIONNAIRE_STAFF");

      foreach ($rawFavorites as $favorite) {
        if ($favorite instanceof \steam_user) {
          $isSteamAdmin = \lms_steam::is_steam_admin($favorite);
          $content->setCurrentBlock("BLOCK_USER");
          $content->setVariable("USER_NAME", $favorite->get_full_name());
          $content->setVariable("IMAGE", "<div style='float:left; margin-right:5px;'><svg style='color:#ff8300; height:16px; width:16px;'><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/user.svg#user'/></svg></div>");
          $content->setVariable("SEND_PARTICIPATE_REQUEST",  "sendRequest('Sanctions',{'id':" . $this->id . ",'showDialog':false,'checked':this.checked,'participate':true,'userId':" . $favorite->get_id() . "},'','data',function(response){dataSaveFunctionCallback(response);},null,'Questionnaire');");
          $content->setVariable("SEND_ADMIN_REQUEST",  "sendRequest('Sanctions',{'id':" . $this->id . ",'showDialog':false,'checked':this.checked,'admin':true,'userId':" . $favorite->get_id() . "},'','data',function(response){dataSaveFunctionCallback(response);},null,'Questionnaire');");
          if (in_array($favorite, $participants) || $isSteamAdmin) {
            $content->setVariable("PARTICIPATE_CHECKED", "checked");
          }
          if (in_array($favorite, $admins) || $isSteamAdmin) {
            $content->setVariable("ADMIN_CHECKED", "checked");
          }
          if($isSteamAdmin){
            $content->setVariable("PARTICIPATE_DISABLED", "disabled");
            $content->setVariable("ADMIN_DISABLED", "disabled");
          }
          $content->parse("BLOCK_USER");
        }
      }

      $groups = $creator->get_groups();
      foreach ($groups as $group) {
        $content->setCurrentBlock("BLOCK_GROUP");
        $content->setVariable("GROUP_NAME", $group->get_name());
        $content->setVariable("IMAGE", "<div style='float:left; margin-right:5px;'><svg style='color:#3a6e9f; height:16px; width:16px;'><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/group.svg#group'/></svg></div>");
        $content->setVariable("SEND_PARTICIPATE_REQUEST",  "sendRequest('Sanctions',{'id':" . $this->id . ",'showDialog':false,'checked':this.checked,'participate':true,'groupId':" . $group->get_id() . "},'','data',function(response){dataSaveFunctionCallback(response);},null,'Questionnaire');");
        $content->setVariable("SEND_ADMIN_REQUEST",  "sendRequest('Sanctions',{'id':" . $this->id . ",'showDialog':false,'checked':this.checked,'admin':true,'groupId':" . $group->get_id() . "},'','data',function(response){dataSaveFunctionCallback(response);},null,'Questionnaire');");
        if (in_array($group, $participants)) {
          $content->setVariable("PARTICIPATE_CHECKED", "checked");
        }
        if (in_array($group, $admins)) {
          $content->setVariable("ADMIN_CHECKED", "checked");
        }
        if ($user->get_id() != $creator->get_id()) {
          $content->setVariable("PARTICIPATE_DISABLED", "disabled");
          $content->setVariable("ADMIN_DISABLED", "disabled");
        }
        $content->parse("BLOCK_GROUP");
      }

      foreach ($rawFavorites as $favorite) {
        if ($favorite instanceof \steam_group && !in_array($favorite, $groups)){
          $content->setCurrentBlock("BLOCK_GROUP");
          $content->setVariable("GROUP_NAME", $favorite->get_name());
          $content->setVariable("IMAGE", "<div style='float:left; margin-right:5px;'><svg style='color:#ff8300; height:16px; width:16px;'><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/group.svg#group'/></svg></div>");
          $content->setVariable("SEND_PARTICIPATE_REQUEST",  "sendRequest('Sanctions',{'id':" . $this->id . ",'showDialog':false,'checked':this.checked,'participate':true,'groupId':" . $favorite->get_id() . "},'','data',function(response){dataSaveFunctionCallback(response);},null,'questionnaire');");
          $content->setVariable("SEND_ADMIN_REQUEST",  "sendRequest('Sanctions',{'id':" . $this->id . ",'showDialog':false,'checked':this.checked,'admin':true,'groupId':" . $favorite->get_id() . "},'','data',function(response){dataSaveFunctionCallback(response);},null,'questionnaire');");
          if (in_array($favorite, $participants)) {
            $content->setVariable("PARTICIPATE_CHECKED", "checked");
          }
          if (in_array($favorite, $admins)) {
            $content->setVariable("ADMIN_CHECKED", "checked");
          }
          $content->parse("BLOCK_GROUP");
        }
      }

      $content->parse("BLOCK_CONFIGURATION_TABLE");
      $rawWidget = new \Widgets\RawHtml();
      $rawWidget->setHtml($content->get());
      $dialog->addWidget($rawWidget);

      $sanctionURL = "http://$_SERVER[HTTP_HOST]" . "/Sanction/Index/" . $this->id . "/";
      $admins = \steam_factory::groupname_to_object($GLOBALS[ "STEAM" ]->get_id(), "SchulAdmins");
      $isAdmin = false;
      if($admins instanceof \steam_group){
          $isAdmin = $admins->is_member($user);
      }
      $isAdmin2 = \lms_steam::is_steam_admin($user);
      if($isAdmin || $isAdmin2){
        $dialog->setCustomButtons(array(array("js" => "window.open('$sanctionURL', '_self')", "label" => "Erweiterte Ansicht öffnen")));
      }

      $ajaxResponseObject->addWidget($dialog);
      return $ajaxResponseObject;
    }

    //save sanctions selected in the dialog
    function saveSanctions(\AjaxResponseObject $ajaxResponseObject){
      $ajaxResponseObject->setStatus("ok");
      $this->questionnaire = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
      $user = \lms_steam::get_current_user();
      $QuestionnaireExtension = \Questionnaire::getInstance();
      $this->surveys = $this->questionnaire->get_inventory();
      $participants = $this->questionnaire->get_attribute("QUESTIONNAIRE_GROUP");
      $staff = $this->questionnaire->get_attribute("QUESTIONNAIRE_STAFF");

      if(!is_array($participants)) $participants = array();

      if(!is_array($staff)) $staff = array();

      if($this->userId) $this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->userId);

      if($this->groupId) $this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->groupId);

      $staffMember = 0;
      if(in_array($this->object, $staff)){
        $staffMember = 1;
      }
      else{
        foreach ($staff as $i) {
          if ($i instanceof steam_group && $i->is_member($this->object)) {
            $staffMember = 1;
            break;
          }
        }
      }

      $participant = 0;
      if(in_array($this->object, $participants)){
        $participant = 1;
      }
      else{
        foreach ($participants as $i) {
          if ($i instanceof steam_group && $i->is_member($this->object)) {
            $participant = 1;
            break;
          }
        }
      }

      if($this->participate == "true" && $participant && $this->checked == "false"){
        $key = array_search($this->object, $participants);
        unset($participants[$key]);

        if(!$staffMember) $this->removeSanctions();
      }

      if($this->participate == "true" && !$participant && $this->checked == "true"){
        array_push($participants, $this->object);

        if(!$staffMember) $this->setParticipantSanctions();
      }

      if($this->admin == "true" && $staffMember && $this->checked == "false"){
        $key = array_search($this->object, $staff);
        unset($staff[$key]);

        $this->removeSanctions();
        if($participant) $this->setParticipantSanctions();
      }

      if($this->admin == "true" && !$staffMember && $this->checked == "true"){
        array_push($staff, $this->object);
        $this->questionnaire->set_sanction($this->object, SANCTION_ALL);
      }

      $this->questionnaire->set_attribute("QUESTIONNAIRE_GROUP", $participants);
      $this->questionnaire->set_attribute("QUESTIONNAIRE_STAFF", $staff);

      return $ajaxResponseObject;
    }

    function removeSanctions(){
      $this->questionnaire->set_sanction($this->object, ACCESS_DENIED);
      if ($this->surveys[0] instanceof \steam_container) {
        $resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $this->surveys[0]->get_path() . "/results");
        if ($resultContainer instanceof \steam_container) {
            $resultContainer->set_sanction($this->object, ACCESS_DENIED);
        }
      }
    }

    function setParticipantSanctions(){
      $this->questionnaire->set_sanction($this->object, SANCTION_READ | SANCTION_WRITE);
      if ($this->surveys[0] instanceof \steam_container) {
        $resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $this->surveys[0]->get_path() . "/results");
        if ($resultContainer instanceof \steam_container) {
          $resultContainer->set_sanction($this->object, SANCTION_READ | SANCTION_WRITE | SANCTION_INSERT);
        }
      }
    }

}

?>
