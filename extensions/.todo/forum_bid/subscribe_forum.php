<?php
/****************************************************************************
 subscribe_forum.php - Subscribe to a forum to get notified about changes
 Copyright (C)

 This program is free software; you can redistribute it and/or modify it
 under the terms of the GNU General Public License as published by the
 Free Software Foundation; either version 2 of the License,
 or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 See the GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software Foundation,
 Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

 Author: Thorsten Schäfer
 EMail: tms82@upb.de

 ****************************************************************************/

//******************************************************
//** includes
//******************************************************
require_once("../../config/config.php");
require_once("$steamapi_doc_root/steam_connector.class.php");
require_once("$config_doc_root/classes/template.inc");
require_once("$config_doc_root/includes/sessiondata.php");
require_once("../../includes/norm_post.php");

//******************************************************
//** Get Post data
//******************************************************
$object_id = isset($_GET["forumobject"]) ? $_GET["forumobject"] : 0;
$object_id = isset($_POST["forumobject"]) ? $_POST["forumobject"] : $object_id;
$action = isset($_GET["action"]) ? $_GET["action"] : "";
$action = isset($_POST["action"]) ? $_POST["action"] : $action;

//******************************************************
//** sTeam Server Connection
//******************************************************
$steam = new steam_connector($config_server_ip, $config_server_port, $login_name, $login_pwd);

if( !$steam || !$steam->get_login_status())
{
  header("Location: $config_webserver_ip/index.html");
  exit();
}

/** log-in user */
$steamUser =  $steam->get_login_user();
/** id of the log-in user */
$steamUserId = $steamUser == 0 ? 0 : $steamUser->get_id();
/** the current messageboard */
$object = ($object_id!=0)?steam_factory::get_object($steam, $object_id, CLASS_MESSAGEBOARD):$steamUser->get_workroom();
/** the creator of the messageboard */
$creator = $object->get_creator(1);
/** additional required attributes */
$subscription = $object->get_attribute("bid:forum_subscription",1);
$user_subscription = $steamUser->get_attribute("bid:forum_subscription", 1);
/** check the rights of the log-in user */
$allowed_write = $object->check_access_write($steamUser, 1);

// flush the buffer
$result = $steam->buffer_flush();
$subscription = $result[$subscription];
$user_subscription = $result[$user_subscription];
$creator = $result[$creator];
$allowed_write = $result[$allowed_write];

//Action subscribe
if($action == "subscribe"){
  if($allowed_write) {
    // Double check if user is not already subscribed to this forum
    if(!$subscription) 
      $subscription = array($steamUser);
    else {
      $duplicate = false;
      foreach($subscription as $key => $value) {
        if ($value->get_id() == $steamUser->get_id()) {
          $duplicate = true;
          break;
        }
      }

      if(!$duplicate)
        array_push($subscription, $steamUser);
    }
    $object->set_attribute("bid:forum_subscription",$subscription);

    if(!$user_subscription)
      $user_subscription = array($object);
    else {
      $duplicate = false;
      foreach($user_subscription as $key => $value) {
        if ($value->get_id() == $object->get_id()) {
          $duplicate = true;
          break;
        }
      }

      if($duplicate)
        array_push($user_subscription, $object);
    }
    $steamUser->set_attribute("bid:forum_subscription", $user_subscription);
    
    //close window on success
    echo("<html><body onload='javascript:opener.top.location.reload();window.close();'></body></html");
    $steam->disconnect();
    exit;
  }
}
//Action unsubscribe
else if($action == "unsubscribe") {
  if($allowed_write){
    if ($subscription) {
      foreach($subscription as $key => $value) {
        if($steamUser->get_id() == $value->get_id()) {
          unset($subscription[$key]);
          break;
        }
      }
      $subscription = array_values($subscription);
    }
    else {
      $subscription = array();
    }
    $object->set_attribute("bid:forum_subscription",$subscription);

    if ($user_subscription){
      foreach($user_subscription as $key => $value){
        if($value->get_id() == $object->get_id()){
          unset($user_subscription[$key]);
          break;
        }
      }
      $user_subscription = array_values($user_subscription);
    }
    else {
      $user_subscription = array();
    }
    $steamUser->set_attribute("bid:forum_subscription", $user_subscription);
    
    //close window on success
    echo("<html><body onload='javascript:opener.top.location.reload();window.close();'></body></html");
    $steam->disconnect();
    exit;
  }
}

$steam->disconnect();

//******************************************************
//** Display Stuff
//******************************************************
$t = new Template("./templates/$language", "keep");
$t->set_file(array("edit_forum" => "forum_edit_forum.ihtml"));
$t->set_block("edit_forum", "add", "DUMMY");
$t->set_block("edit_forum", "no_access", "DUMMY");
$t->set_block("edit_forum", "error", "DUMMY");
$t->set_block("edit_forum", "error_title", "DUMMY");
$t->set_block("edit_forum", "button_mission", "BUTTON_MISSION_ROW");
$t->set_block("edit_forum", "button_label_save", "DUMMY");

$t->set_var(array(
    "BUTTON_CANCEL_MISSION" => "close",
    "BUTTON_CANCEL_URL" => "",
    "BUTTON_MISSION_ROW" => "",
    "CURRENT_FORUM" => norm_post($attrib[OBJ_DESC]),
    "DUMMY" => "",
    "FORUM_ID" => $object->get_id(),
    "ERROR_FEEDBACK" => "",
));

$subtitle = ($attrib["bid:forum_subtitle"]) ? $attrib["bid:forum_subtitle"] : "";
$title= ((isset($_POST['title']))? $_POST["title"] : $attrib[OBJ_DESC]);
$description = ($attrib["bid:description"]) ? $attrib["bid:description"] : "";

/*
//no access to write
elseif(isset($allowed_write) && !$allowed_write){
  $t->parse("ERROR_FEEDBACK", "no_access");
}


$t->pparse("OUT", "edit_forum");
 */
?>
