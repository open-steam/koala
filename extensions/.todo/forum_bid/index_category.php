<?php
/****************************************************************************
 index_categorie.php - show the category-view with all messages
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

 Author: Stephanie Sarach
 EMail: haribo@upb.de

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
//** Presumption
//******************************************************
$category_id = isset($_GET["object"]) ? $_GET["object"] : 0;

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
/** the login user name */
$steamUser->get_attributes(array(OBJ_NAME), 1);

try {
	/** the current category */
	$category = ($category_id!=0)?steam_factory::get_object($steam, $category_id):NULL;
}
catch(Exception $e)
{
	echo "Keine Berechtigung";
	exit();
}

/** additional required attributes */
$categoryAttributes = $category->get_attributes(array(OBJ_NAME, OBJ_DESC,
			OBJ_CREATION_TIME, "bid:description","DOC_LAST_MODIFIED", "DOC_USER_MODIFIED"),1);
/** the content of the current category */
$categoryContent = $category->get_content(1);
/** the creater of the current category */
$categoryCreator = $category->get_creator(1);
/** the current forum */
$forum = $category->get_annotating(1);
$category_allowed_write = $category->check_access_write($steamUser, 1);
$category_allowed_read = $category->check_access_read($steamUser, 1);
$category_allowed_annotate = $category->check_access_annotate($steamUser, 1);

// flush the buffer
$result = $steam->buffer_flush();
$categoryAttributes = $result[$categoryAttributes];
$categoryContent = $result[$categoryContent];
$categoryCreator = $result[$categoryCreator];
$categoryAttributes["DOC_USER_MODIFIED"]->get_attributes(array(OBJ_NAME),1);
$forum = $result[$forum];
$category_allowed_write = $result[$category_allowed_write];
$category_allowed_read = $result[$category_allowed_read];
$category_allowed_annotate = $result[$category_allowed_annotate];

/** the environment of the forum object */
$forumEnvironment = $forum->get_environment();
/** additional required attributes */
$forumEnvironmentAttributes = $forumEnvironment->get_attributes(array(OBJ_NAME, OBJ_DESC),1);
/** additional required attributes */
$forumAttributes = $forum->get_attributes(array(OBJ_NAME, OBJ_DESC, "bid:description", "bid_forum_subtitle"),1);
/** the creator of the forum */
$forumCreator = $forum->get_creator(1);
/** attributes of the creator of the category */
$categoryCreatorAttributes = $categoryCreator->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_ICON), 1);

$result = $steam->buffer_flush();
$forumAttributes = $result[$forumAttributes];
$forumCreator = $result[$forumCreator];
$categoryCreatorAttributes = $result[$categoryCreatorAttributes];
$forumEnvironmentAttributes = $result[$forumEnvironmentAttributes];

/** attributes of the creator of the forum object */
$forumCreatorAttributes = $forumCreator->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_ICON), 1);

$result = $steam->buffer_flush();
$forumCreatorAttributes = $result[$forumCreatorAttributes];

if($category_allowed_read)
{
	$messages = $category->get_annotations(false, 1);
	$result = $steam->buffer_flush();
	$messages = $result[$messages];
	sort($messages);

	if (count($messages) > 0) {
		foreach ($messages as $message) {
			if (!empty($message)) {
				$id = $message->get_id();
				$messageAttributes[$id] = $message->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_CREATION_TIME, "DOC_LAST_MODIFIED", "DOC_USER_MODIFIED"), 1);
				$messageAccessWrite[$id] = $message->check_access_write($steamUser, 1);
				$messageContent[$id] = $message->get_content(1);
				$messageCreator[$id] = $message->get_creator(1);
			}
		}

		$result = $steam->buffer_flush();

		foreach ($messages as $message) {
			$id = $message->get_id();
			$messageAttributes[$id] = $result[$messageAttributes[$id]];
			$messageContent[$id] = $result[$messageContent[$id]];
			$messageCreator[$id] = $result[$messageCreator[$id]];
			$messageCreator[$id]->get_attributes(array(OBJ_NAME),1);
			$messageAttributes[$id]["DOC_USER_MODIFIED"]->get_attributes(array(OBJ_NAME),1);
			$messageAccessWrite[$id] = $result[$messageAccessWrite[$id]];
		}

		$result = $steam->buffer_flush();
	}
}

$steam->disconnect();

if(!$category_allowed_read)
{
	echo "Keine Berechtigung";
	exit();
}

//******************************************************
//** Display Stuff
//******************************************************
$t = new Template("./templates/$language", "keep");
$t->set_file(array("view_category" => "forum_view_category.ihtml"));
$t->set_block("view_category", "action_delete", "DUMMY");
$t->set_block("view_category", "action_edit", "DUMMY");
$t->set_block("view_category", "add_message", "DUMMY");
$t->set_block("view_category", "categorie_action_edit", "DUMMY");
$t->set_block("view_category", "categorie_action_delete", "DUMMY");
//$t->set_block("view_category", "creator_icon", "DUMMY");
$t->set_block("view_category", "message", "DUMMY");
$t->set_block("view_category", "no_access", "DUMMY");
$t->set_block("view_category", "edit_message_annotation", "DUMMY");
$t->set_block("view_category", "delete_message_annotation", "DUMMY");

$forum_subtitle = (($forumAttributes["bid:forum_subtitle"])?$forumAttributes["bid:forum_subtitle"]: "");

$t->set_var(array(
    "ADD_MESSAGE" => "",
    "ACTION_DELETE" => "",
    "ACTION_EDIT" => "",
    "CATEGORIE_ACTION_EDIT" => "",
    "CATEGORIE_ACTION_DELETE" => "",
    "DUMMY" => "",
    "FORUM_NAME" => norm_post($forumAttributes["OBJ_DESC"]),
    "FORUM_ID" => $forum->get_id(),
    "FORUM_ENVIRONMENT_ID" => $forum->forum_environment->id,
    "FORUM_ENVIRONMENT_NAME" => norm_post($forum->forum_environment_name),
    "FORUM_SUBTITLE" => norm_post($forum_subtitle),
    "FORUM_OWNER" => $forumCreatorAttributes[OBJ_NAME],
    "ITEM" => "",
));

//show details and messages of a category
if(isset($category) && !empty($category) ){
	$t->set_var(array(
     "CATEGORIE_DESCRIPTION" => $categoryAttributes["bid:description"],
     "CATEGORIE_CONTENT" => $categoryContent,
     "CATEGORIE_CREATOR" => $categoryCreatorAttributes[OBJ_NAME],
	//"CATEGORIE_CREATOR_ICON"=>"",
     "CATEGORIE_CREATION_TIME" => date("j.m.Y G:i", $categoryAttributes['OBJ_CREATION_TIME']),
     "CATEGORIE_NAME" => norm_post($categoryAttributes[OBJ_DESC]),
     "CATEGORIE_OBJECT" => $category->get_id()
	));

	if(is_array($messages) && isset($messages))
	{
		foreach($messages as $message){
			$id = $message->get_id();
			$t->set_var(array(
       			"MESSAGE_CONTENT" => $messageContent[$id],
       			"MESSAGE_CREATOR" => $messageCreator[$id]->get_name(),
			// "CREATOR_ICON" => $categorie_entries[$item]['OBJ_CREATOR'][2]["OBJ_ICON"]->id,
       			"MESSAGE_CREATION_TIME" =>date("j.m.Y G:i", $messageAttributes[$id][OBJ_CREATION_TIME]),
       			"MESSAGE_ID" => $id,
       			"MESSAGE_NAME" => norm_post($messageAttributes[$id][OBJ_DESC]),
     			"AUTHOR" => $messageAttributes[$id]["DOC_USER_MODIFIED"]->get_name(),
				"TIMESTAMP" => date("j.m.Y G:i", $messageAttributes[$id]["DOC_LAST_MODIFIED"])
			));

			//if($categorie_entries[$item]['OBJ_CREATOR'][2]["OBJ_ICON"]->id != 0 ){
			//$t->parse("MESSAGE_CREATOR_ICON", "creator_icon");
			//}

			//add action_links
			if($messageAccessWrite[$id]){

				//delete message if login-user is forum-creator
				if($steamUser->get_id() == $forumCreator->get_id()){
					$t->parse("ACTION_DELETE", "action_delete");
				}
				//edit message if login-user is forum-creator or message-creator
				if($steamUser->get_id() == $forumCreator->get_id() || $steamUser->get_id() == $messageCreator[$id]->get_id()){
					$t->parse("ACTION_EDIT", "action_edit");
				}

			}

			//add message link if allowed
			if(isset($category_allowed_annotate) && $category_allowed_annotate){
				$t->parse("ADD_MESSAGE", "add_message");
			}
			
			// add footer if content of the message was changed or deleted
			if ($messageAttributes[$id][OBJ_CREATION_TIME] != $messageAttributes[$id]["DOC_LAST_MODIFIED"]) {
				if (strlen(trim($messageContent[$id])) > 0) {
					$t->parse("MESSAGE_MODIFICATION_FOOTER", "edit_message_annotation");
				}
				else {
					$t->parse("MESSAGE_MODIFICATION_FOOTER", "delete_message_annotation");				
				}				
			}
			else {
				$t->parse("MESSAGE_MODIFICATION_FOOTER", "DUMMY");
			}

			$t->parse("ITEM", "message", true);

			$t->set_var(array(
        		"ACTION_EDIT" => "",
        		"ACTION_DELETE" => "",
        		"ACTION_EMPTY_CONTENT" => "",
			//"MESSAGE_CREATOR_ICON"=>"",
				"ADD_MESSAGE" => ""
				));
		}

		//delete and edit categorie if login-user is forum-creator
		if($steamUser->get_id() == $forumCreator->get_id()) {
			$t->parse("CATEGORIE_ACTION_DELETE", "categorie_action_delete");
			$t->parse("CATEGORIE_ACTION_EDIT", "categorie_action_edit");
		}

		//edit categorie if login-user is category creator
		if($steamUser->get_id() == $categoryCreator->get_id()){
			$t->parse("CATEGORIE_ACTION_EDIT", "categorie_action_edit");
		}

		//add message link if allowed
		if(isset($category_allowed_annotate) && $category_allowed_annotate){
			$t->parse("ADD_MESSAGE", "add_message");
		}

		// add footer if content of the thread (i.e. first entry) was changed or deleted
		$t->set_var(array(
			"AUTHOR" => $categoryAttributes["DOC_USER_MODIFIED"]->get_name(),
			"TIMESTAMP" => date("j.m.Y G:i", $categoryAttributes["DOC_LAST_MODIFIED"])
		));
		if ($categoryAttributes[OBJ_CREATION_TIME] != $categoryAttributes["DOC_LAST_MODIFIED"]) {
			if (strlen(trim($categoryContent)) > 0) {
				$t->parse("CATEGORY_MODIFICATION_FOOTER", "edit_message_annotation");
			}
			else {
				$t->parse("CATEGORY_MODIFICATION_FOOTER", "delete_message_annotation");				
			}				
		}
		else {
			$t->parse("CATEGORY_MODIFICATION_FOOTER", "DUMMY");
		}
	}
}

//persmission to write
if(isset($category_allowed_write) && $category_allowed_write){
}
//permisson to annotate
elseif(isset($category_allowed_annotate) && $category_allowed_annotate){
}

$t->pparse("OUT", "view_category");

?>
