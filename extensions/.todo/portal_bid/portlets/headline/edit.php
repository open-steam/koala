<?php

  /****************************************************************************
  edit.php - edit the headline portlet
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
$tmpl->set_block("content", "feedback_headline_null", "DUMMY");
$tmpl->set_var(array(
  "DUMMY" => "",

  "PORTAL_ID" => $portal->get_id(),
  "PORTAL_NAME" => $portal->get_attribute(OBJ_NAME),

  "BUTTON_MISSION" => "save",
  "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/headline/edit.php",
  "BUTTON_CANCEL_MISSION" => "",
  "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/portal2/portlet_edit.php",
  "BUTTON_CANCEL_ACTION" => "opener.top.location.reload();window.close();",

  "FEEDBACK" => ""
));

//save action
if($action == "save")
{
  $action = "save return(portlet_edit.php)";

  $content = array(
    "headline" => norm_post("headline"),
    "alignment" => norm_post("alignment"),
    "size" => norm_post("size")
  );

  //verify input
  if(trim($content["headline"]) == "")
  {
    $tmpl->parse("FEEDBACK", "feedback_headline_null", 1);
    $action = "";
  }

  $portlet_content = $content;
}

//display stuff
$tmpl->set_var(array(
  "PORTAL_NAME" => $portal_name,
  "PORTLET_NAME" => $portlet_title,
  "OBJECT_ID" => $portlet->get_id(),
  "TITLE" => $portlet_title,
  "HEADLINE" => ((isset($content["headline"]))?norm_post($content["headline"]):""),
  "SELECTED_LEFT" => ((isset($content["alignment"]) && trim($content["alignment"]) == "left")?"SELECTED":""),
  "SELECTED_CENTER" => ((isset($content["alignment"]) && trim($content["alignment"]) == "center")?"SELECTED":""),
  "SELECTED_RIGHT" => ((isset($content["alignment"]) && trim($content["alignment"]) == "right")?"SELECTED":""),

  "SELECTED_15" => ((isset($content["size"]) && trim($content["size"]) == 15)?"SELECTED":""),
  "SELECTED_20" => ((isset($content["size"]) && trim($content["size"]) == 20)?"SELECTED":""),
  "SELECTED_25" => ((isset($content["size"]) && trim($content["size"]) == 25)?"SELECTED":""),
  "SELECTED_30" => ((isset($content["size"]) && trim($content["size"]) == 30)?"SELECTED":""),
  "SELECTED_35" => ((isset($content["size"]) && trim($content["size"]) == 35)?"SELECTED":""),
  "SELECTED_40" => ((isset($content["size"]) && trim($content["size"]) == 40)?"SELECTED":""),
  "SELECTED_50" => ((isset($content["size"]) && trim($content["size"]) == 50)?"SELECTED":""),
  "SELECTED_60" => ((isset($content["size"]) && trim($content["size"]) == 60)?"SELECTED":"")
));


$tmpl->pparse("OUT", "content");


include("../../footer.php");

?>
