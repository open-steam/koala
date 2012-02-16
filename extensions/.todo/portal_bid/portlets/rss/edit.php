<?php

  /****************************************************************************
  edit.php - edit the rss portlet
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

$tmpl = new Template("./templates/$language", "keep");
$tmpl->set_file("content", "edit.ihtml");
$tmpl->set_block("content", "feedback_address_null", "DUMMY");
$tmpl->set_var(array(
  "DUMMY" => "",

  "PORTAL_ID" => $portal->get_id(),
  "PORTAL_NAME" => $portal_name,

  "BUTTON_MISSION" => "save",
  "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/rss/edit.php",
  "BUTTON_CANCEL_ACTION" => "opener.top.location.reload();window.close();",
  "BUTTON_CANCEL_MISSION" => "",
  "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/portal2/portlet_edit.php",

  "FEEDBACK" => ""
));

//save action
if($action == "save")
{
  $action = "save return(portlet_edit.php)";

  $content = array(
    "address" => trim(norm_post("address")),
    "num_items" => trim(norm_post("num_items")),
    "desc_length" => trim(norm_post("desc_length")),
    "style" => norm_post("style"),
    "allow_html" => (isset($_POST["allow_html"])?"checked":"")
  );

  //verify input
  if($content["address"] == "")
  {
    $tmpl->parse("FEEDBACK", "feedback_address_null", 1);
    $action = "";
  }

  $portlet_content = $content;
}

//display stuff
$tmpl->set_var(array(
  "PORTAL_NAME" => $portal_name,
  "PORTLET_NAME" => $portlet_name,
  "OBJECT_ID" => $portlet->get_id(),
  "TITLE" => $portlet_name,
  "ADDRESS" => ((isset($content["address"]))?$content["address"]:""),
  "NUM_ITEMS" => ((isset($content["num_items"]))?$content["num_items"]:""),
  "DESC_LENGTH" => ((isset($content["desc_length"]))?$content["desc_length"]:""),
  "SELECTED_STYLE_RSS_FEED" => ((isset($content["style"]) && trim($content["style"]) == "rss_feed")?"SELECTED":""),
  "SELECTED_STYLE_MESSAGE" => ((isset($content["style"]) && trim($content["style"]) == "message")?"SELECTED":""),
  "ALLOW_HTML" => trim($content["allow_html"])
));


$tmpl->pparse("OUT", "content");


include("../../footer.php");

?>
