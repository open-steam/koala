<?php

  /****************************************************************************
  topic_edit.php - edit the topics of the topic portlet
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

$category = (int) (isset($_GET["category"]))?$_GET["category"]:((isset($_POST["category"]))?$_POST["category"]:"");
$topic = (int) (isset($_GET["topic"]))?$_GET["topic"]:((isset($_POST["topic"]))?$_POST["topic"]:"");

$content = $portlet_content;


//display stuff
$tpl = new Template("./templates/$language", "keep");
$tpl->set_file("content", "topic_edit.ihtml");
$tpl->set_block("content", "feedback_headline_null", "DUMMY");
$tpl->set_var(array(
  "DUMMY" => "",
  "PORTAL_ID" => $portal->get_id(),
  "PORTAL_NAME" => $portal_name,
  "PORTLET_NAME" => $portlet_name,
  "PORTLET_ID" => $portlet->get_id(),
  "FEEDBACK" => "",

  "BUTTON_CANCEL_MISSION" => "",
  "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/portal2/portlets/topic/edit.php",

  "BUTTON_MISSION" => "save",
  "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/topic/topic_edit.php"
));


//save stuff
if($action == "save" && isset($content[$category]))
{
  $action = "save return(portlets/topic/edit.php)";

  $new_content = array(
    "title" => norm_post("title"),
    "description" => norm_post("description"),
    "link_url" => norm_post("link_url"),
    "link_target" => (isset($_POST["link_target"])?"checked":"")
  );

  if($topic == "")
    $topic = array_push($content[$category]["topics"], $new_content) - 1;
  else
    $content[$category]["topics"][$topic] = $new_content;

  //verify input
  if($_POST["title"] == "")
  {
    $tpl->parse("FEEDBACK", "feedback_headline_null");
    $action = "";
  }
  $portlet_content = $content;
}


//if new category then leave all empty
$tpl->set_var("CATEGORY_ID", $category);
if($action == "new")
{
  $tpl->set_var(array(
    "TOPIC_ID" => "",
    "TOPIC_TITLE" => "",
    "TOPIC_DESCRIPTION" => "",
    "TOPIC_LINK_URL" => "",
    "TOPIC_LINK_TARGET" => ""
  ));
}
else
{
  $tpl->set_var(array(
    "TOPIC_ID" => $topic,
    "TOPIC_TITLE" => trim($content[$category]["topics"][$topic]["title"]),
    "TOPIC_DESCRIPTION" => trim($content[$category]["topics"][$topic]["description"]),
    "TOPIC_LINK_URL" => trim($content[$category]["topics"][$topic]["link_url"]),
    "TOPIC_LINK_TARGET" => trim($content[$category]["topics"][$topic]["link_target"])
  ));
}


$tpl->pparse("OUT", "content");

include("../../footer.php");

?>