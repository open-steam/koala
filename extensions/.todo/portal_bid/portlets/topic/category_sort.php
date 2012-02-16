<?php

  /****************************************************************************
  category_sort.php - sort the categroies of the topic portlet
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


//Action Save, build new message and store in content
if($action == "save" && isset($_POST["list"]))
{
  $action = "save return(portlets/topic/edit.php)";

  $list = explode(" ", trim($_POST["list"]));

  //if lists have same size do the reorder
  if(sizeof($list) == sizeof($content))
  {
    //build new order array
    $new_content = array();
    foreach($list as $category)
      $new_content[] = $content[$category];

    //store new order in content
    $content = $new_content;
    $portlet_content = $content;
  }
}


//Display stuff
$tpl = new Template("./templates/$language", "keep");
$tpl->set_file("content", "category_sort.ihtml");
$tpl->set_block("content", "select_row", "SELECT_ROW");
$tpl->set_var(array(
  "PORTAL_ID" => $portal->get_id(),
  "PORTAL_NAME" => $portal_name,
  "PORTLET_NAME" => $portlet_name,

  "BUTTON_CANCEL_MISSION" => "",
  "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/portal2/portlets/topic/edit.php",

  "BUTTON_MISSION" => "save",
  "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/topic/category_sort.php"
));

//build sort select box
foreach($content as $id => $category)
{
  $tpl->set_var(array(
    "CATEGORY_ID" => $id,
    "CATEGORY_TITLE" => $category["title"]
  ));
  $tpl->parse("SELECT_ROW", "select_row", true);
}


$tpl->pparse("OUT", "content");


include("../../footer.php");

?>