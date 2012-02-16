<?php
/****************************************************************************
 index.php - show a forum and their first annotations
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
//** Presumption
//******************************************************
$forum_id = isset($_GET["object"]) ? $_GET["object"] : 0;
$class = isset($_GET["class"]) ? $_GET["class"] : 0;
$category_id = isset($_GET["object_cat"]) ? $_GET["object_cat"] : 0;

//redirect to the index_category.php side, if a object_cat is true
if(isset($category_id) && $category_id != 0 && $category_id!=""){
  header('Location: index_category.php?object='.$category_id);
  exit;
}

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
$steamUserId = (gettype($steamUser) == "object") ? $steamUser->get_id() : 0;
/** the login user name */
$steamUserAttributes = $steamUser->get_attributes(array(OBJ_NAME, "bid:last_session_time"), 1);
/** the forum object */
$forum = ($forum_id!=0)?steam_factory::get_object($steam, $forum_id):NULL;
/** additional required attributes */
$forumAttributes = $forum->get_attributes(array(
  OBJ_NAME,
  OBJ_DESC,
  OBJ_CREATION_TIME,
  "bid:forum_subtitle",
  "bid:description",
  "bid:forum_subscription"
),1);
/** the creator of the forum */
$forumCreator = $forum->get_creator(1);
/** the environment of the forum object */
$forumEnvironment = $forum->get_environment();
/** additional required attributes */
$forumEnvironmentAttributes = $forumEnvironment->get_attributes(array(OBJ_NAME, OBJ_DESC),1);
/** check the rights of the log-in user */
$forum_allowed_write = $forum->check_access_write($steamUser, 1);
$forum_allowed_read = $forum->check_access_read($steamUser, 1);
$forum_allowed_annotate = $forum->check_access_annotate($steamUser, 1);

$result = $steam->buffer_flush();
$forumAttributes = $result[$forumAttributes];
$forumCreator = $result[$forumCreator];
$forumEnvironmentAttributes = $result[$forumEnvironmentAttributes];
$forum_allowed_write = $result[$forum_allowed_write];
$forum_allowed_read = $result[$forum_allowed_read];
$forum_allowed_annotate = $result[$forum_allowed_annotate];

/** attributes of the login user */
$steamUserAttributes = $result[$steamUserAttributes];
/** attributes of the creator of the forum object */
$forumCreatorAttributes = $forumCreator->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_ICON), 1);

$result = $steam->buffer_flush();
$forumCreatorAttributes = $result[$forumCreatorAttributes];

if(!$forum_allowed_read)
{
  echo "Keine Berechtigung";
  exit();
}

$categories = $forum->get_annotations(false, 1);
$result = $steam->buffer_flush();
$categories = $result[$categories];

foreach ($categories as $category) {
  $id = $category->get_id();
  $categoryAttributes[$id] = $category->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_CREATION_TIME, "bid:description"), 1);
  $messages[$id] = $category->get_annotations(false, 1);
  $categoryCreator[$id] = $category->get_creator(1);
}

$result = $steam->buffer_flush();

foreach ($categories as $category) {
  $id = $category->get_id();
  $messages[$id] = $result[$messages[$id]];
  $categoryMessageCount[$id] = count($messages[$id]);
  sort($messages[$id]);
  if ($categoryMessageCount[$id] > 0) {
    $categoryLastMessageAttributes[$id] = end($messages[$id])->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_CREATION_TIME), 1);
    $categoryLastMessageCreator[$id] = end($messages[$id])->get_creator(1);
  }
  $categoryAttributes[$id] = $result[$categoryAttributes[$id]];
  $categoryCreator[$id] = $result[$categoryCreator[$id]];
  $categoryCreator[$id]->get_attributes(array(OBJ_NAME), 1);
}

$result = $steam->buffer_flush();

foreach ($categories as $category) {
  $id = $category->get_id();
  if ($categoryMessageCount[$id] > 0) {
    $categoryLastMessageAttributes[$id] = $result[$categoryLastMessageAttributes[$id]];
    $categoryLastMessageCreator[$id] = $result[$categoryLastMessageCreator[$id]];
    $categoryLastMessageCreator[$id]->get_attributes(array(OBJ_NAME), 1);
  }
}

/*
 * Determine if the backpack contains threads, which could be pasted into this 
 * message board. The paste button will be displayed accordingly
 */
$backpack = $steam->get_login_user()->get_inventory(CLASS_DOCUMENT);
$displayPasteCategoryButton = false;
foreach($backpack as $entry) {
  $entry->get_attributes(array("OBJ_TYPE"), 1);
}
$result = $steam->buffer_flush();

foreach($backpack as $entry) {
  $entryObjType = $entry->get_attribute("OBJ_TYPE");
  if (is_string($entryObjType) && ($entryObjType === "text_forumthread_bid")) {
    $displayPasteCategoryButton = true;
    break;
  }
}

$steam->buffer_flush();
$steam->disconnect();

//******************************************************
//** Display Stuff
//******************************************************
$t = new Template("./templates/$language", "keep");
$t->set_file(array("view_forum" => "forum_view_forum.ihtml"));
$t->set_block("view_forum", "actions", "DUMMY");
//$t->set_block("view_forum", "edit_forum", "DUMMY");
//$t->set_block("view_forum", "add_categorie", "DUMMY");
$t->set_block("view_forum", "categorie", "DUMMY");
$t->set_block("view_forum", "last_post", "DUMMY");
$t->set_block("view_forum", "no_entries", "DUMMY");
$t->set_block("view_forum", "no_access", "DUMMY");
$t->set_block("view_forum", "add_category_button", "DUMMY");
$t->set_block("view_forum", "paste_category_button", "DUMMY");
$t->set_block("view_forum", "edit_forum_button", "DUMMY");
$t->set_block("view_forum", "subscribe_forum_button", "DUMMY");
$t->set_block("view_forum", "unsubscribe_forum_button", "DUMMY");
$t->set_block("view_forum", "new_message_info", "DUMMY");

$forum_subtitle = (($forumAttributes["bid:forum_subtitle"])?$forumAttributes["bid:forum_subtitle"]: "");

$t->set_var(array(
//"ADD_CATEGORIE"=> "",
//"ACTION_EDIT_FORUM" => "",
    "ACTION_LINKS" => "",
    "DUMMY" => "",
    "ADD_CATEGORY_BUTTON" => "",
    "PASTE_CATEGORY_BUTTON" => "",
    "EDIT_FORUM_BUTTON" => "",
    "SUBSCRIBE_FORUM_BUTTON" => "",
    "FORUM_CREATION_TIME" => date("d.m.Y", $forumAttributes["OBJ_CREATION_TIME"]),
    "FORUM_DESC" => $forumAttributes["bid:description"],
    "FORUM_ENVIRONMENT_ID" => $forumEnvironment->get_id(),
    "FORUM_ENVIRONMENT_NAME" => norm_post($forumEnvironmentAttributes[OBJ_DESC]),
    "FORUM_NAME" => norm_post($forumAttributes[OBJ_DESC]),
    "FORUM_OBJECT" => $forum->get_id(),
    "FORUM_OWNER" => $forumCreatorAttributes[OBJ_NAME],
    "FORUM_SUBTITLE" => norm_post($forum_subtitle),
    "ITEM" => "",
    "NEW_MESSAGE_INFO" => "",
));

//no categories in forum
if(empty($categories) ){
  $t->parse("ITEM", "no_entries");
}
else{
  foreach ($categories as $category) {
    $id = $category->get_id();
    $t->set_var(array(
         "CATEGORIE_COUNT_MESSAGES" => $categoryMessageCount[$id]+1,
         "CATEGORIE_DESCRIPTION" => norm_post($categoryAttributes[$id]["bid:description"]),
         "CATEGORIE_LAST_POST_TIME" => "",
         "CATEGORIE_NAME" => norm_post($categoryAttributes[$id][OBJ_DESC]),
         "CATEGORIE_OBJECT" => $id,
         "CATEGORIE_OWNER" => $categoryCreator[$id]->get_name(),
    ));

    if($categoryMessageCount[$id] > 0){
      $t->set_var(array(
          "CATEGORIE_LAST_POST_MESSAGE" => $categoryLastMessageAttributes[$id][OBJ_DESC],
          "CATEGORIE_LAST_POST_TIME" => date("d.m.Y G:i", $categoryLastMessageAttributes[$id][OBJ_CREATION_TIME]),
          "CATEGORIE_LAST_POSTER" => $categoryLastMessageCreator[$id]->get_name(),
          "CATEGORIE_LAST_POST_ID"=> end($messages[$id])->get_id()
          ));
          $t->parse("CATEGORIE_LAST_POST", "last_post", true);

      // required for notification icon
      $lastPostTime = intval($categoryLastMessageAttributes[$id][OBJ_CREATION_TIME]);
    }
    else {
      // required for notification icon
      $lastPostTime = intval($categoryAttributes[$id][OBJ_CREATION_TIME]);
    }

    /* 
     * Display a notification icon, if new contributions
     * to a thread are available, which have been added
     * after the last user login.
     */

    $lastSessionTime = $steamUserAttributes["bid:last_session_time"];
    $lastSessionTime = is_array($lastSessionTime) ? intval($lastSessionTime[0]) : intval(time());
    if ($lastPostTime > $lastSessionTime) {
            $t->parse("NEW_MESSAGE_INFO", "new_message_info", true);
    }

    $t->parse("ITEM", "categorie", true);
    $t->set_var(array(
      "CATEGORIE_LAST_POST" => "",
      "NEW_MESSAGE_INFO" => ""
    ));
  }
}

//permission to edit forum only for forum_owner
if(isset($forum_allowed_write) && $forum_allowed_write && $steamUserId == $forumCreator->get_id()){
  $t->parse("ADD_CATEGORY_BUTTON", "add_category_button");
  $t->parse("EDIT_FORUM_BUTTON", "edit_forum_button");

  // render paste category button if backpack contains any categories which 
  // could be inserted
  if($displayPasteCategoryButton) {
    $t->parse("PASTE_CATEGORY_BUTTON", "paste_category_button");
  }
}

//permission to annotate /create a  categorie
if(isset($forum_allowed_annotate) && $forum_allowed_annotate){
  $t->parse("ADD_CATEGORY_BUTTON", "add_category_button");
}

//permision to subscribe to the forum to be updated about any changes
if(isset($forum_allowed_write) && $forum_allowed_write) {
  $subscribe = true;
  if($forumAttributes["bid:forum_subscription"])
    foreach($forumAttributes["bid:forum_subscription"] as $key => $value) {
      if($value->get_id() == $steamUser->get_id()) {
        $subscribe = false;
        break;
      }
    }

  if($subscribe)
    $t->parse("SUBSCRIBE_FORUM_BUTTON", "subscribe_forum_button");
  else
    $t->parse("SUBSCRIBE_FORUM_BUTTON", "unsubscribe_forum_button");
} 

$t->pparse("OUT", "view_forum");

?>
