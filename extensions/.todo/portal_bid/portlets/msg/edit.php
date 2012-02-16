<?php

  /****************************************************************************
  edit.php - entry point to edit the messages portlet
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

  Author: Henrik Beige <hebeige@gmx.de>
          Bastian Schröder <bastian@upb.de>

  ****************************************************************************/

include("../../header.php");

$content = $portlet_content;
//delete message if wanted
if ($action == "save") {
  $portlet->set_attribute("bid:portlet:msg:new_msg_location", 
    $_POST["new_msg_location"]);
}
if($action == "delete" && isset($_GET["message"]) /*&& isset($content[$_GET["message"]])*/)
{
  //delete index
  $tmp = array_search($_GET["message"], $content);

// Beim Arbeiten mit unset geriet bei L�schungen die Sortierung des Arrays
// durcheinander. Wenn array_splice verwendet wird, scheint das Problem nicht
// zu bestehen. Bei der Umstellung auf das neue API sollten wir das pr�fen!

//  unset($content[$tmp]);
  array_splice($content, $tmp, 1);

	$action = "save";

  $tmp = steam_factory::get_object($steam, $_GET["message"]);
  //delete image if set
  if( $tmp->get_attribute("bid:portlet:msg:picture_id") != null && $tmp->get_attribute("bid:portlet:msg:picture_id") != "") {
    $tmp_pic = steam_factory::get_object( $steam, $tmp->get_attribute("bid:portlet:msg:picture_id") );
    $tmp_pic->delete();
  }
  //delete msg object
  $tmp->delete();
  $portlet_content = $content;
}
else if ($action == "new") {
  $portlet->set_attribute("bid:portlet:msg:new_msg_location", 
    $_POST["new_msg_location"]);
  echo("<html><body onload=\"window.location.href='$config_webserver_ip/modules/portal2/portlets/msg/edit_process.php?mission=new';\"></body></html>");
  exit;
}

//display stuff

$tpl = new Template("./templates/$language", "keep");
$tpl->set_file("content", "edit.ihtml");
$tpl->set_block("content", "button_mission", "BUTTON_MISSION_ROW");
$tpl->set_block("content", "button_label_new", "DUMMY");
$tpl->set_block("content", "button_label_sort", "DUMMY");
$tpl->set_block("content", "message_null", "MESSAGE_NULL");
$tpl->set_block("content", "message_row", "MESSAGE_ROW");
$tpl->set_var(array(
  "DUMMY" => "",

  "PORTAL_ID" => $portal->get_id(),
  "PORTAL_NAME" => $portal_name,
  "PORTLET_NAME" => $portlet_name,

  "BUTTON_CANCEL_MISSION" => "save",
  "BUTTON_CANCEL_URL" => "",
  "BUTTON_CANCEL_ACTION" => "javascript:form_submit('save','$config_webserver_ip/modules/portal2/portlets/msg/edit.php'); return false;",

  "MESSAGE_NULL" => "",
  "MESSAGE_ROW" => "",
  "MESSAGE_ACTION" => "",

  "NEW_MSG_LOCATION_TOP_CHECKED" => "",
  "NEW_MSG_LOCATION_BOTTOM_CHECKED" => ""
));


//parse "new" button
$tpl->set_var(array(
  "BUTTON_MISSION" => "new",
  "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/msg/edit.php"
));
$tpl->parse("BUTTON_LABEL", "button_label_new");
$tpl->parse("BUTTON_MISSION_ROW", "button_mission", true);

//parse messages list
if(is_array($content) && sizeof($content) > 0)
{
  foreach($content as $msg_id)
  {
    $message = steam_factory::get_object($steam, $msg_id);
    $tpl->set_var(array(
      "MESSAGE_ID" => $message->get_id(),
      "MESSAGE_HEADLINE" => $message->get_attribute(OBJ_NAME)
    ));
    $tpl->parse("MESSAGE_ROW", "message_row", true);
  }

  //display sort button if there is enough content to sort something
  if(sizeof($content) > 1)
  {
    //parse "sort" button
    $tpl->set_var(array(
      "BUTTON_MISSION" => "",
      "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/msg/edit_sort.php"
    ));
    $tpl->parse("BUTTON_LABEL", "button_label_sort");
    $tpl->parse("BUTTON_MISSION_ROW", "button_mission", true);
  }
}
else
{
  $tpl->parse("MESSAGE_NULL", "message_null");
}

// Set radio button for adding new messages at the top or bottom
$new_msg_location = $portlet->get_attribute("bid:portlet:msg:new_msg_location");
if ($new_msg_location == null || $new_msg_location == "top") {
  $tpl->set_var(array(
    "NEW_MSG_LOCATION_TOP_CHECKED" => "checked"
  ));
}
else if ($new_msg_location == "bottom") {
  $tpl->set_var(array(
    "NEW_MSG_LOCATION_BOTTOM_CHECKED" => "checked"
  ));
}

$tpl->pparse("OUT", "content");

include("../../footer.php");

?>
