<?php

  /****************************************************************************
  category_edit.php - edit a category of the topic portlet
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

$category = (isset($_GET["category"]))?$_GET["category"]:((isset($_POST["category"]))?$_POST["category"]:"");

$content = $portlet_content;


//display stuff

$tpl = new Template("./templates/$language", "keep");
$tpl->set_file("content", "category_edit.ihtml");
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
  "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/topic/category_edit.php",
  "BUTTON_CANCEL_ACTION" => "javascript:form_submit('', '$config_webserver_ip/modules/portal2/portlets/topic/edit.php'); return false;"
));


//save stuff
if($action == "save")
{
  $action = "save return(portlets/topic/edit.php)";

  if($category == "")
    $category = array_push($content, array("title" => norm_post("title"), "topics" => array())) - 1;
  else
    $content[$category]["title"] = norm_post("title");

  //verify input
  if($_POST["title"] == "") {
    $tpl->parse("FEEDBACK", "feedback_headline_null");
    $action = "";
  }

  $portlet_content = $content;
}


//if new category then leave all empty
if($action == "new")
{
  $tpl->set_var(array(
    "CATEGORY_ID" => "",
    "CATEGORY_TITLE" => ""
  ));
}
else
{
  $tpl->set_var(array(
    "CATEGORY_ID" => $category,
    "CATEGORY_TITLE" => trim($content[$category]["title"])
  ));
}


$tpl->pparse("OUT", "content");


include("../../footer.php");

?>
