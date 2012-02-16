<?php

  /****************************************************************************
  edit.php - edit the media portlet
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

  Author: Harald Selke
  EMail: hase@uni-paderborn.de

  ****************************************************************************/

include("../../header.php");

$content = $portlet_content;

$tmpl = new Template("./templates/$language", "keep");
$tmpl->set_file("content", "edit.ihtml");
$tmpl->set_block("content", "feedback_url_null", "DUMMY");
$tmpl->set_var(array(
  "DUMMY" => "",

  "PORTAL_ID" => $portal->get_id(),
  "PORTAL_NAME" => $portal->get_attribute(OBJ_NAME),

  "BUTTON_MISSION" => "save",
  "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/media/edit.php",
  "BUTTON_CANCEL_MISSION" => "",
  "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/portal2/portlet_edit.php",

  "FEEDBACK" => ""
));

//save action
if($action == "save")
{
  $action = "save return(portlet_edit.php)";

  $content = array(
    "headline" => norm_post("headline"),
    "url" => norm_post("url"),
    "media_type" => norm_post("media_type"),
    "description" => norm_post("description")
  );

  //verify input
  if(trim($content["url"]) == "")
  {
    $tmpl->parse("FEEDBACK", "url", 1);
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
  "HEADLINE" => ((isset($content["headline"]))?norm_post($content["headline"]):""),
  "URL" => ((isset($content["url"]))?norm_post($content["url"]):""),
  "MEDIA_TYPE" => ((isset($content["media_type"]))?norm_post($content["media_type"]):""),
  "DESCRIPTION" => ((isset($content["description"]))?norm_post($content["description"]):""),
  "SELECTED_IMAGE" => ((isset($content["media_type"]) && trim($content["media_type"]) == "image")?"SELECTED":""),
  "SELECTED_MOVIE" => ((isset($content["media_type"]) && trim($content["media_type"]) == "movie")?"SELECTED":""),
  "SELECTED_AUDIO" => ((isset($content["media_type"]) && trim($content["media_type"]) == "audio")?"SELECTED":"")
));


$tmpl->pparse("OUT", "content");


include("../../footer.php");

?>
