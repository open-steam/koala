<?php
  /****************************************************************************
  view.php - view the media portlet
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

if(sizeof($content) > 0)
{
  $tmpl = new Template("./portlets/media/templates/$language", "keep");
  $tmpl->set_file("content", "view.ihtml");
  $tmpl->set_block("content", "edit_button", "DUMMY");
  $tmpl->set_block("content", "image", "DUMMY");
  $tmpl->set_block("content", "movie", "DUMMY");
  $tmpl->set_block("content", "audio", "DUMMY");

  $tmpl->set_var(array(
    "DUMMY" => "",
    "EDIT_BUTTON" => "",
    "PORTLET_ROOT" => $config_webserver_ip . "/modules/portal2/portlets/media",
    "PORTLET_ID" => $portlet->get_id(),
    "HEADLINE" => $content["headline"],
    "URL" => $content["url"],
    "DESCRIPTION" => $content["description"]
  ));

  $media_type = $content["media_type"];
  
  if ($media_type == "image")
    $tmpl->parse("MEDIA_ELEMENT", "image");
  else if ($media_type == "movie") {
  $media_player = $config_webserver_ip . '/tools/mediaplayer.swf';
    $tmpl->set_var("MEDIA_PLAYER", $media_player);
    $tmpl->parse("MEDIA_ELEMENT", "movie");
  }
  else if ($media_type == "audio") {
  $media_player = $config_webserver_ip . '/tools/emff_lila_info.swf';
    $tmpl->set_var("MEDIA_PLAYER", $media_player);
    $tmpl->parse("MEDIA_ELEMENT", "audio");
  }
  
  if ($portlet->check_access_write($steam->get_login_user()))
    $tmpl->parse("EDIT_BUTTON", "edit_button");

  $tmpl->parse("OUT", "content");

  $tmpl->p("OUT");
}
else
 echo("");

?>
