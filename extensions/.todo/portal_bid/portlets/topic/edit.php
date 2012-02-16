<?php

  /****************************************************************************
  edit.php - entry point to edit the topic portlet
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


//save stuff
if($action == "delete")
{
  if(isset($_GET["category"]) && !isset($_GET["topic"]))
    unset($content[(int) $_GET["category"]]);

  if(isset($_GET["category"]) && isset($_GET["topic"]))
    unset($content[(int) $_GET["category"]]["topics"][(int) $_GET["topic"]]);

  $portlet_content = $content;
  $action = "save";
}

//display stuff

$tpl = new Template("./templates/$language", "keep");
$tpl->set_file("content", "edit.ihtml");
$tpl->set_block("content", "button_label_new", "DUMMY");
$tpl->set_block("content", "button_label_sort", "DUMMY");
$tpl->set_block("content", "button_mission", "BUTTON_MISSION_ROW");
$tpl->set_block("content", "category_null", "DUMMY");
$tpl->set_block("content", "category_row", "CATEGORY_ROW");
$tpl->set_block("category_row", "topic_null", "DUMMY");
$tpl->set_block("category_row", "topic_row", "TOPIC_ROW");
$tpl->set_var(array(
  "DUMMY" => "",
  "PORTAL_ID" => $portal->get_id(),
  "PORTAL_NAME" => $portal_name,
  "PORTLET_NAME" => $portlet_name,
  "PORTLET_ID" => $portlet->get_id(),
  "CATEGORY_ROW" => "",
  "TOPIC_ROW" => "",
));

//parse "new" button
$tpl->set_var(array(
  "BUTTON_MISSION" => "new",
  "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/topic/category_edit.php",
  "BUTTON_CANCEL_ACTION" => "opener.top.location.reload();window.close();",
));
$tpl->parse("BUTTON_LABEL", "button_label_new");
$tpl->parse("BUTTON_MISSION_ROW", "button_mission", true);


if(is_array($content) && sizeof($content) > 0)
{
  //show all catogories
  foreach($content as $category_id => $category)
  {
    $tpl->set_var(array(
      "CATEGORY_ID" => $category_id,
      "CATEGORY_NAME" => $category["title"]
    ));

    //clear "TOPIC_ROW"
    $tpl->unset_var("TOPIC_ROW");

    if(is_array($category["topics"]) && sizeof($category["topics"]) > 0)
      //show all topics of a category
      foreach($category["topics"] as $topic_id => $topic)
      {
        $tpl->set_var(array(
          "TOPIC_ID" => $topic_id,
          "TOPIC_NAME" => $topic["title"]
        ));
        $tpl->parse("TOPIC_ROW", "topic_row", 1);
      }
    else
      $tpl->parse("TOPIC_ROW", "topic_null");

    $tpl->parse("CATEGORY_ROW", "category_row", 1);

  }

  //display sort button if there is enough content to sort something
  if(sizeof($content) > 1)
  {
    //parse "sort" button
    $tpl->set_var(array(
      "BUTTON_MISSION" => "sort",
      "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/topic/category_sort.php"
    ));
    $tpl->parse("BUTTON_LABEL", "button_label_sort");
    $tpl->parse("BUTTON_MISSION_ROW", "button_mission", true);
  }

}
else
  $tpl->parse("CATEGORY_ROW", "category_null");




$tpl->pparse("OUT", "content");


include("../../footer.php");

?>
