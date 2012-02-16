<?php
/****************************************************************************
 add_categorie.php - add a new categorie to a forum
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
$object_id = isset($_GET["forumobject"]) ? $_GET["forumobject"] : 0;
$object_id = isset($_POST["forumobject"]) ? $_POST["forumobject"]: $object_id;
$action = isset($_POST["mission"]) ? $_POST["mission"]:"";

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

  /** the current forum */
  $object = ($object_id!=0)?steam_factory::get_object($steam, $object_id, CLASS_MESSAGEBOARD):$steamUser->get_workroom();
  /** additional required attributes */
  $attrib = $object->get_attributes(array(OBJ_NAME, OBJ_DESC, "bid:description"),1);
  /** get permissions of the log-in user */
  $allowed_annotate = $object->check_access_annotate($steamUser, 1);

  // flush the buffer
  $result = $steam->buffer_flush();
  $attrib = $result[$attrib];
  $allowed_annotate = $result[$allowed_annotate];
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

	if($allowed_annotate && trim($_POST["title"])!="" && trim($_POST["content"])!="") {		
		$thread = $object->add_thread(rawurlencode($_POST["title"]), stripslashes($_POST["content"]));
				
		if(!empty($thread)) {			
			$thread->set_attributes(array(
				OBJ_DESC => norm_post($_POST["title"]), 
				"OBJ_TYPE" => "text_forumthread_bid",
				"bid:description" => $_POST["description"]), 0);
			
			//close window on success
			echo("<html><body onload='javascript:if (opener) opener.top.location.reload();window.close();'></body></html");			
			$steam->disconnect();
			exit;
		}
	}
}

$steam->disconnect();

$title= ((isset($_POST['title']) )?$_POST["title"] : "");
$description= ((isset($_POST['description']) )?$_POST['description'] : "");
$content= ((isset($_POST['content']) )?$_POST['content'] : "");

//******************************************************
//** Display Stuff
//******************************************************
$t = new Template("./templates/$language", "keep");
$t->set_file(array("add_categorie" => "forum_add_categorie.ihtml"));
$t->set_block("add_categorie", "add", "DUMMY");
$t->set_block("add_categorie", "no_access", "DUMMY");
$t->set_block("add_categorie", "error", "DUMMY");
$t->set_block("add_categorie", "error_content", "DUMMY");
$t->set_block("add_categorie", "error_title", "DUMMY");
$t->set_block("add_categorie", "error_description", "DUMMY");
$t->set_block("add_categorie", "error_not_logged_in", "DUMMY");
$t->set_block("add_categorie", "button_mission", "BUTTON_MISSION_ROW");
$t->set_block("add_categorie", "button_label_save", "DUMMY");

$t->set_var(array(
    "CURRENT_FORUM" => norm_post($attrib[OBJ_DESC]),
    "FORUM_OBJECT" => $object_id,
    "DUMMY" => "",
    "ERROR_FEEDBACK" => "",
    "BUTTON_CANCEL_MISSION" => "close",
    "BUTTON_CANCEL_URL" => "",
    "BUTTON_MISSION_ROW" => "",
    "FORM_CONTENT" => htmlentities($content),
    "FORM_TITLE" => norm_post($title),
    "FORM_DESCRIPTION" => norm_post($description),
    "BUTTON_URL" => "$config_webserver_ip/modules/forum/add_categorie.php",
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
if(isset($thread) && !$thread){
  $error[] = "error";
}
//no access to write
if(isset($allowed_annotate) && !$allowed_annotate){
  $error[] = "no_access";
}
//error title
if(isset($_POST["title"]) && trim($_POST["title"])=="" ){
  $error[] = "error_title";
}
//error content
if(isset($_POST["content"]) && trim($_POST["content"])=="" ){
  $error[] = "error_content";
}
//if action has been done and error occured put out error feedback
if( $action != "" && isset($error) && count($error) > 0 ){
  foreach($error as $error_type)
    $t->parse("ERROR_FEEDBACK", $error_type, true);
}

$t->parse("BUTTON_LABEL", "button_label_save");
$t->parse("BUTTON_MISSION_ROW", "button_mission", true);
$t->pparse("OUT", "add_categorie");
?>
