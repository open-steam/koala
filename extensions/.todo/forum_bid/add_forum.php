<?php
/****************************************************************************
 add_forum.php - insert a new forum
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

 Author: Thorsten Schaefer
 EMail: uni@thorstenschaefer.name

 ****************************************************************************/

//******************************************************
//** includes
//******************************************************
require_once("../../config/config.php");
require_once("$steamapi_doc_root/steam_connector.class.php");
require_once("$config_doc_root/classes/template.inc");
$sessionLoginFailureAction = "closeSubWindow";
require_once("$config_doc_root/includes/sessiondata.php");
require_once("../../includes/norm_post.php");

//******************************************************
//** Get Post data
//******************************************************
$room_id = isset($_GET["forumobject"]) ? $_GET["forumobject"] : 0;
$room_id = isset($_POST["forumobject"]) ? $_POST["forumobject"]: $room_id;
$action = isset($_POST["mission"]) ? $_POST["mission"] :"";
$title = ((isset($_POST['title']) )?$_POST["title"] : "");
$subtitle = ((isset($_POST['subtitle']) )?$_POST["subtitle"] : "");
$description = ((isset($_POST['description']) )?$_POST['description'] : "");

//******************************************************
//** sTeam Server Connection
//******************************************************
$steam = new steam_connector($config_server_ip, $config_server_port, $login_name, $login_pwd);

$error = array();
if( !$steam || !$steam->get_login_status()) {
  $do_login = True;
  $error[] = "error_not_logged_in";
}
else {
  /** log-in user */
  $steamUser =  $steam->get_login_user();
  /** id of the log-in user */
  $steamUserId = $steamUser == 0 ? 0 : $steamUser->get_id();

  /** the current room */
  $object = ($room_id!=0)?steam_factory::get_object($steam, $room_id):$steamUser->get_workroom();
  /** additional required attributes */
  $attrib = $object->get_attributes(array(OBJ_NAME, OBJ_DESC, "bid:forum_subtitle", "bid:description"),1);
  $allowed_write = $object->check_access_write($steamUser, 1);

  // flush the buffer
  $result = $steam->buffer_flush();
  $attrib = $result[$attrib];
  $allowed_write = $result[$allowed_write];

  $subtitle= ((isset($_POST['subtitle']) )?$_POST["subtitle"] : "");
  $title= ((isset($_POST['title']) )?$_POST["title"] : "");
  $description= ((isset($_POST['description']) )?$_POST['description'] : "");
}

//Action save
if($action == "save"){
  $do_login = False;
  if (!$allowed_write) {
    if ($steamUser->get_name() === "guest") {     
      $do_login = True;
      $error[] = "error_not_logged_in";
    }
    else {
      $error[] = "no_access";
    }
  }
  if ((count($error) == 0) && ($allowed_write && trim($_POST["title"])!="" )) {
    $messageboard = steam_factory::create_messageboard(
      $steam, 
      rawurlencode($title), 
      $object, 
      norm_post($title)
    );
    
    if(!empty($messageboard)) {   
      $messageboard->set_attribute("bid:forum_subtitle", norm_post(trim($subtitle))); 
      $messageboard->set_attribute("bid:description", stripslashes($description));
      //close window on success
      echo("<html><body onload='javascript:if (opener) opener.top.location.reload();window.close();'></body></html");
      $steam->disconnect();
      exit;
    }
  }
}

$steam->disconnect();

//******************************************************
//** Display Stuff
//******************************************************
$t = new Template("./templates/$language", "keep");
$t->set_file(array("add_forum" => "forum_add_forum.ihtml"));
$t->set_block("add_forum", "add", "DUMMY");
$t->set_block("add_forum", "no_access", "DUMMY");
$t->set_block("add_forum", "error", "DUMMY");
$t->set_block("add_forum", "error_title", "DUMMY");
$t->set_block("add_forum", "error_not_logged_in", "DUMMY");
$t->set_block("add_forum", "button_mission", "BUTTON_MISSION_ROW");
$t->set_block("add_forum", "button_label_save", "DUMMY");

$t->set_var(array(
    "BUTTON_CANCEL_MISSION" => "close",
    "BUTTON_CANCEL_URL" => "",
    "BUTTON_MISSION_ROW" => "",
    "CURRENT_FORUM" => norm_post($attrib[OBJ_DESC]),
    "DUMMY" => "",
    "ERROR_TITLE" => "",
    "FORUM_ID" => $room_id,
    "ERROR_FEEDBACK" => "",
    "FORM_SUBTITLE" => norm_post($subtitle),
    "FORM_TITLE" => norm_post($title),
    "FORM_DESCRIPTION" => htmlentities($description),
    "BUTTON_URL" => "$config_webserver_ip/modules/forum/add_forum.php",
    "BUTTON_MISSION" => "save",
    "DO_LOGIN" => "0",
    "BODY_ON_LOAD" => "if (document.form_blueprint.title) document.form_blueprint.title.focus();"
));

if ($do_login)
    $t->set_var(array(
        "DO_LOGIN" => "1",
        "BODY_ON_LOAD" => "document.getElementById('form_blueprint').submit();"
    ));

/* 
 * Error handling
 */
if(isset($messageboard) && !$messageboard->get_id()){
  $error[] = "error";
}
if(isset($_POST["title"]) && trim($_POST["title"])=="" ) {
  $error[] = "error_title";
}
//if action has been done and error occured put out error feedback
if( $action != "" && isset($error) && count($error) > 0 ){
  foreach($error as $error_type)
    $t->parse("ERROR_FEEDBACK", $error_type, true);
}

$t->parse("BUTTON_LABEL", "button_label_save");
$t->parse("BUTTON_MISSION_ROW", "button_mission", true);
$t->pparse("OUT", "add_forum");
?>
