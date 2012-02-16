<?php

  /****************************************************************************
  view.php - view the messages portlet
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

$tmpl = new Template("./portlets/msg/templates/$language", "keep");
$tmpl->set_file("content", "view.ihtml");
$tmpl->set_block("content", "edit_button", "DUMMY");
$tmpl->set_block("content", "message_none", "DUMMY");
$tmpl->set_block("content", "message_picture", "DUMMY");
$tmpl->set_block("content", "message_link", "DUMMY");
$tmpl->set_block("content", "message", "DUMMY");
$tmpl->set_block("content", "separator", "DUMMY");
$tmpl->set_block("content", "rss_link", "DUMMY");

$tmpl->set_var(array(
  "DUMMY" => "",
  "EDIT_BUTTON" => "",
  "PORTLET_ROOT" => $config_webserver_ip . "/modules/portal2/portlets/msg",
  "PORTLET_ID" => $portlet->get_id()
));

if ($portlet->check_access_write($steam->get_login_user()))
  $tmpl->parse("EDIT_BUTTON", "edit_button");

if(sizeof($content) > 0)
{
  /*  
   * Convert old messages which save its content as UBB code to new messages
   * using a html representation 
   */
  $convertUBB = false;
  $version = $portlet->get_attribute("bid:portlet:version");
  if (!$version) {
    $convertUBB = true;
    require_once("name.php");
    $portlet->set_attribute("bid:portlet:version", $msg_version);
  }

  $separator = false;
  foreach($content as $msg_id)
  {
    //get obj and attributes
    $message = steam_factory::get_object($steam, $msg_id);
    $message->get_attributes(array("OBJ_DESC", "bid:portlet:msg:picture_id",
        "bid:portlet:msg:picture_alignment", "bid:portlet:msg:link_url",
        "bid:portlet:msg:link_url_label", "bid:portlet:msg:link_open"));

    /* 
     * Convert old messages which save its content as UBB code to new messages
     * using a html representation
     */
    if ($convertUBB)
    {
      $message->set_content($UBB->encode($message->get_content()));
    }

    $tmpl->set_var(array(
      "MESSAGE_PICTURE" => "",
      "MESSAGE_LINK" => "",
      "MESSAGE_HEADLINE" => $UBB->encode( $message->get_attribute("OBJ_NAME") ),
      "MESSAGE_SUBHEADLINE" => $UBB->encode( $message->get_attribute("OBJ_DESC") ),
      "MESSAGE_CONTENT" => $message->get_content()
    ));

    // parse in picture if it exists
    if( $message->get_attribute("bid:portlet:msg:picture_id") != "")
    {
      $picture_width = (($message->get_attribute("bid:portlet:msg:picture_width") != "")
        ?trim($message->get_attribute("bid:portlet:msg:picture_width"))
        :"");
      if (extract_percentual_length($picture_width) == "") {
        $bare_picture_width = extract_length($picture_width);
        if ($bare_picture_width == "") {
          $picture_width = "";
        }
        else if ($bare_picture_width > $column_width-25) {
          $picture_width = $column_width-25;
        }
      } 
      $tmpl->set_var(array(
        "MESSAGE_PICTURE_ID" => $message->get_attribute("bid:portlet:msg:picture_id"),
        "MESSAGE_PICTURE_ALIGNMENT" => $message->get_attribute("bid:portlet:msg:picture_alignment"),
        "MESSAGE_PICTURE_WIDTH" => $picture_width
      ));
      $tmpl->parse("MESSAGE_PICTURE", "message_picture");
    }

    //parse in link if it exists
    if(trim($message->get_attribute("bid:portlet:msg:link_url")) != "")
    {
      if (trim($message->get_attribute("bid:portlet:msg:link_open")) != "checked") { $tmpl->set_var(array(
        "MESSAGE_LINK_URL_LABEL" => (($message->get_attribute("bid:portlet:msg:link_url_label") !== "")?$UBB->encode($message->get_attribute("bid:portlet:msg:link_url_label")):$message->get_attribute("bid:portlet:msg:link_url")),
        "MESSAGE_LINK_URL" => derive_url($message->get_attribute("bid:portlet:msg:link_url")),
        "MESSAGE_LINK_TARGET" => "_top"
      ));
      } else {
        $tmpl->set_var(array(
        "MESSAGE_LINK_URL_LABEL" => (($message->get_attribute("bid:portlet:msg:link_url_label") !== "")?$UBB->encode($message->get_attribute("bid:portlet:msg:link_url_label")):$message->get_attribute("bid:portlet:msg:link_url")),
        "MESSAGE_LINK_URL" => derive_url($message->get_attribute("bid:portlet:msg:link_url")),
        "MESSAGE_LINK_TARGET" => "_blank"
      ));
      }
      $tmpl->parse("MESSAGE_LINK", "message_link");
    }

    if($separator)
      $tmpl->parse("MESSAGES", "separator", true);
    $tmpl->parse("MESSAGES", "message", true);

    $separator = true;
  }
}

else
{
   $tmpl->set_var(array(
      "PORTLET_ROOT" => $config_webserver_ip . "/modules/portal2/portlets/msg",
      "MESSAGE_PICTURE" => "",
      "MESSAGE_LINK" => "",
      "MESSAGE_HEADLINE" => "",
      "MESSAGE_SUBHEADLINE" => "",
      "MESSAGE_CONTENT" => ""
    ));
  $tmpl->parse("MESSAGE_SUBHEADLINE", "message_none");
  $tmpl->parse("MESSAGES", "message");
}
  $tmpl->parse("RSS_LINK", "rss_link");

$tmpl->pparse("OUT", "content");

?>
