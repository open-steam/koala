<?php
/****************************************************************************
 edit_categorie.php - modified an annotation as a categorie
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
$action = isset($_POST["mission"]) ? $_POST["mission"] : "";

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

  /** the current category object */
  $object = ($object_id!=0)?steam_factory::get_object($steam, $object_id):NULL;
  /** the content of the category object */
  $object_content = $object->get_content(1);
  /** additional required attributes */
  $attrib = $object->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_CREATION_TIME, OBJ_LAST_CHANGED, "bid:description"),1);
  /* get the annotating forum object */
  $forum = $object->get_annotating(1);
  /** check the rights of the log-in user */
  $allowed_write = $object->check_access_write($steamUser, 1);

  // flush the buffer
  $result = $steam->buffer_flush();
  $object_content = $result[$object_content];
  $attrib = $result[$attrib];
  $forum = $result[$forum];
  $allowed_write = $result[$allowed_write];

  $forum_attrib = $forum->get_attributes(array(OBJ_NAME, OBJ_DESC, "bid:description"), 1);
  $result = $steam->buffer_flush();
  $forum_attrib = $result[$forum_attrib];
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
  if($allowed_write && trim($_POST["title"])!="" && stripslashes($_POST["content"])!="") {
    $attributes = array(
      /* object name cannot be change leave the comment intentionally
       *"OBJ_NAME" => $_POST["title"],
       */
      OBJ_DESC => norm_post($_POST["title"]),
      "bid:description" => $_POST["description"]);
    $object->set_attributes($attributes, 0);
    $object->set_content(stripslashes($_POST["content"]));

    echo("<html><body onload='javascript:if (opener) opener.top.location.reload();window.close();'></body></html");

    $steam->disconnect();
    exit;
  }
}

$steam->disconnect();

$content= ((isset($_POST['content']))?$_POST['content'] : $object_content);
$title= ((isset($_POST['title']) )?$_POST["title"] : $attrib[OBJ_DESC]);
$description = isset($_POST['description']) ? $_POST['description'] : ($attrib["bid:description"]) ? $attrib["bid:description"] : "";

//******************************************************
//** Display Stuff
//******************************************************
$t = new Template("./templates/$language", "keep");
$t->set_file(array("edit_categorie" => "forum_edit_categorie.ihtml"));
$t->set_block("edit_categorie", "add", "DUMMY");
$t->set_block("edit_categorie", "no_access", "DUMMY");
$t->set_block("edit_categorie", "error", "DUMMY");
$t->set_block("edit_categorie", "error_content", "DUMMY");
$t->set_block("edit_categorie", "error_title", "DUMMY");
$t->set_block("edit_categorie", "error_description", "DUMMY");
$t->set_block("edit_categorie", "error_not_logged_in", "DUMMY");
$t->set_block("edit_categorie", "button_mission", "BUTTON_MISSION_ROW");
$t->set_block("edit_categorie", "button_label_save", "DUMMY");

$t->set_var(array(
    "CURRENT_FORUM" => norm_post($forum_attrib[OBJ_DESC]),
    "CATEGORIE_OBJECT" => $object_id,
    "DUMMY" => "",
    "ERROR_FEEDBACK" => "",
    "BUTTON_CANCEL_MISSION" => "close",
    "BUTTON_CANCEL_URL" => "",
    "BUTTON_MISSION_ROW" => "",
    "FORM_CONTENT" => htmlentities($content),
    "FORM_TITLE" => norm_post($title),
    "FORM_DESCRIPTION" => norm_post($description),
    "BUTTON_URL" => "$config_webserver_ip/modules/forum/edit_categorie.php",
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
if(isset($edit_categorie) && !$edit_categorie){
  $error[] = "error";
}
//no access to write
if(isset($allowed_write) && !$allowed_write){
  $error[] = "no_access";
}
//error content
if(isset($_POST["content"]) && trim($_POST["content"]) ==="" ){
  $error[] = "error_content";
}
//error title
if(isset($_POST["title"]) && trim($_POST["title"])=="" ){
  $error[] = "error_title";
}
//if action has been done and error occured put out error feedback
if( $action != "" && isset($error) && count($error) > 0 ){
  foreach($error as $error_type)
    $t->parse("ERROR_FEEDBACK", $error_type, true);
}

$t->parse("BUTTON_LABEL", "button_label_save");
$t->parse("BUTTON_MISSION_ROW", "button_mission", true);
$t->pparse("OUT", "edit_categorie");

?>
