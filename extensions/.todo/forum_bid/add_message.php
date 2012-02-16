<?php
/****************************************************************************
 add_message.php - add a new message to a categorie or forum
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
$object_id = isset($_GET["c_id"]) ? $_GET["c_id"] : 0;
$object_id = isset($_POST["c_id"]) ? $_POST["c_id"] : $object_id;
$action = isset($_POST["mission"]) ? $_POST["mission"] :"";
$title = ((isset($_POST['title']) )?$_POST["title"] : "");
$content = ((isset($_POST['content']) )?$_POST['content'] : "");

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
  $steamUser =  $steam->get_login_user();
  /** id of the log-in user */
  $steamUserId = $steamUser == 0 ? 0 : $steamUser->get_id();

  /** the current category object */
  $object = ($object_id!=0)?steam_factory::get_object($steam, $object_id):NULL;
  /** the forum object */
  $forum = $object->get_annotating(1);
  /** additional required attributes */
  $attrib = $object->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_CREATION_TIME, OBJ_LAST_CHANGED),1);
  /** check the rights of the log-in user */
  $allowed_annotate = $object->check_access_annotate($steamUser, 1);

  // flush the buffer
  $result = $steam->buffer_flush();
  $attrib = $result[$attrib];
  $allowed_annotate = $result[$allowed_annotate];
  $guestUser = $result[$guestUser];
  $forum = $result[$forum];
}

//Action save
if($action == "save"){
  $do_login = False;
  if (!$allowed_annotate) {
    if ($steamUser->get_name() === "guest") {     
      $do_login = True;
      $error[] = "error_not_logged_in";
    }
    else {
      $error[] = "no_access";
    }
  }
  if (trim($_POST["title"])=="" || stripslashes($_POST["title"])=="") {
    $error[] = "error_title";
  }
  if(isset($_POST['content']) && trim($_POST["content"])=="" ){
    $error[] = "error_content";
  }
  if (count($error) == 0) {
    $new_annotation = steam_factory::create_textdoc(
      $steam,
      rawurlencode($_POST["title"]),
      stripslashes($_POST["content"])
    );
    
    $new_annotation->set_attributes(array(OBJ_DESC => norm_post($_POST["title"])));
    
    $object->add_annotation( $new_annotation );
    // set acquiring
    $new_annotation->set_acquire($object);

    $subscription = $forum->get_attribute("bid:forum_subscription");
    if ($subscription) {
      foreach($subscription as $key => $user) {
        $user->get_attributes(array("USER_EMAIL"),1);
      }
      $result = $steam->buffer_flush();

      foreach($subscription as $key => $user) {
        $recipient = $user->get_attribute("USER_EMAIL");
        $steam->send_mail_from(
          $recipient,
          "New message in forum " . $forum->get_name . ", thread: " . $object->get_name(),
          "",
          "postmaster",
          1,
          "text/plain"
        );
      }
      $steam->buffer_flush();
       
    }
          
    //close window on success
    echo("<html><body onload='javascript:if (opener) opener.top.location.reload();window.close();'></body></html>");
    exit();
 }
}

$steam->disconnect();

//******************************************************
//** Display Stuff
//******************************************************
$t = new Template("./templates/$language", "keep");
$t->set_file(array("add_message" => "forum_add_message.ihtml"));
$t->set_block("add_message", "add", "DUMMY");
$t->set_block("add_message", "no_access", "DUMMY");
$t->set_block("add_message", "delete_message_annotation","DUMMY");
$t->set_block("add_message", "edit_message_annotation","DUMMY");
$t->set_block("add_message", "error", "DUMMY");
$t->set_block("add_message", "error_title", "DUMMY");
$t->set_block("add_message", "error_content", "DUMMY");
$t->set_block("add_message", "error_not_logged_in", "DUMMY");
$t->set_block("add_message", "button_mission", "BUTTON_MISSION_ROW");
$t->set_block("add_message", "button_label_save", "DUMMY");

$t->set_var(array(
    "BUTTON_CANCEL_MISSION" => "close",
    "BUTTON_CANCEL_URL" => "",
    "BUTTON_MISSION_ROW" => "",
    "CURRENT_FORUM" => norm_post($attrib[OBJ_DESC]),
    "CATEGORIE_OBJECT" => $object_id,
    "DUMMY" => "",
    "ERROR_FEEDBACK" => "",
    "FORM_TITLE" => norm_post($title),
    "FORM_CONTENT" => htmlentities($content),
    "BUTTON_URL" => "$config_webserver_ip/modules/forum/add_message.php",
    "BUTTON_MISSION" => "save",
    "DO_LOGIN" => "0",
    "BODY_ON_LOAD" => "if (document.form_blueprint.title) document.form_blueprint.title.focus();"
));
$t->parse("BUTTON_LABEL", "button_label_save");
$t->parse("BUTTON_MISSION_ROW", "button_mission");

if ($do_login)
    $t->set_var(array(
        "DO_LOGIN" => "1",
        "BODY_ON_LOAD" => "document.getElementById('form_blueprint').submit();"
    ));

/* 
 * Error handling
 */
if(isset($new_annotation) && empty($new_annotation)){
  $error[] = "error";
}
//if action has been done and error occured put out error feedback
if( $action != "" && isset($error) && count($error) > 0 ){
  foreach($error as $error_type)
    $t->parse("ERROR_FEEDBACK", $error_type, true);
}

$t->pparse("OUT", "add_message");
?>
