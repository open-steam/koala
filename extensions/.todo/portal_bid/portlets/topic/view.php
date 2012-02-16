<?php

  /****************************************************************************
  view.php - view the topic portlet
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

  Author: Henrik Beige, Harald Selke
  EMail: hebeige@gmx.de, hase@uni-paderborn.de

  ****************************************************************************/

$tmpl = new Template("./portlets/topic/templates/$language", "keep");
$tmpl->set_file("content", "view.ihtml");
$tmpl->set_block("content", "edit_button", "DUMMY");
$tmpl->set_block("content", "topic_display_title", "DUMMY");
$tmpl->set_block("content", "topic_display_title_link", "DUMMY");
$tmpl->set_block("content", "topic_display_description", "TOPIC_DISPLAY_DESCRIPTION");
$tmpl->set_block("content", "topic_entry", "TOPIC_ENTRY");
$tmpl->set_block("content", "category", "CATEGORY");

 $tmpl->set_var(array(
    "DUMMY" => "",
    "EDIT_BUTTON" => "",
    "PORTLET_ROOT" => $config_webserver_ip . "/modules/portal2/portlets/topic",
    "PORTLET_ID" => $portlet->get_id(),
    "PORTLET_NAME" => $portlet_name
 ));

if ($portlet->check_access_write($steam->get_login_user()))
  $tmpl->parse("EDIT_BUTTON", "edit_button");

if(sizeof($content) > 0)
{
  foreach($content as $category)
  {
    $tmpl->set_var("CATEGORY_TITLE", $UBB->encode($category["title"]));

    $tmpl->set_var("TOPIC_ENTRY", "");
    if(isset($category["topics"]))
    {
      foreach($category["topics"] as $topic)
      {
        $tmpl->set_var(array(
          "TOPIC_TITLE" => $UBB->encode($topic["title"]),
          "TOPIC_DESCRIPTION" => $UBB->encode($topic["description"]),
          "TOPIC_LINK_URL" => derive_url($topic["link_url"], $portlet->get_path() . "/../../../"),
          "TOPIC_LINK_TARGET" => ($topic["link_target"]=="checked"?"_blank":"_top")
        ));

        //if there is a url parse headline as link
        if(trim($topic["link_url"]) == "")
          $tmpl->parse("TOPIC_DISPLAY_TITLE", "topic_display_title");
        else
          $tmpl->parse("TOPIC_DISPLAY_TITLE", "topic_display_title_link");

        //if there is a description parse out
        if(trim($topic["description"]) == "")
          $tmpl->set_var("TOPIC_DISPLAY_DESCRIPTION", "");
        else
          $tmpl->parse("TOPIC_DISPLAY_DESCRIPTION", "topic_display_description");

        //parse out every topic
        $tmpl->parse("TOPIC_ENTRY", "topic_entry", 1);
      }
    }

    //parse out category
    $tmpl->parse("CATEGORY", "category", 1);
  }
}
else
  $tmpl->set_var("CATEGORY", "");

$tmpl->pparse("OUT", "content");

?>