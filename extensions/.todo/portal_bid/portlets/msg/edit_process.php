<?php

  /****************************************************************************
  edit_process.php - edit the messages specific data
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
require_once("$config_doc_root/includes/tools.php");

$message = (isset($_GET["message"]))?$_GET["message"]:((isset($_POST["message"]))?$_POST["message"]:"");

$content = $portlet_content;

//Display stuff

$tpl = new Template("./templates/$language", "keep");
$tpl->set_file("content", "edit_process.ihtml");
$tpl->set_block("content", "feedback_headline_null", "DUMMY");
$tpl->set_block("content", "feedback_content_null", "DUMMY");
$tpl->set_block("content", "url_no_picture", "DUMMY");
$tpl->set_block("content", "url_picture", "DUMMY");
if ($action == "new") {
  $tpl->set_var(array(
    "DUMMY" => "",
    "FEEDBACK" => "",
    "PORTAL_ID" => $portal->get_id(),
    "PORTAL_NAME" => $portal_name,
    "PORTLET_NAME" => $portlet_name,

    "BUTTON_MISSION" => "save new",
    "BUTTON_CANCEL_MISSION" => "",
    "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/portal2/portlets/msg/edit.php",
    "BUTTON_CANCEL_ACTION" => "javascript:form_submit('', '$config_webserver_ip/modules/portal2/portlets/msg/edit.php'); return false;"
  ));
}
if ($action == "") {
  $tpl->set_var(array(
    "DUMMY" => "",
    "FEEDBACK" => "",
    "PORTAL_ID" => $portal->get_id(),
    "PORTAL_NAME" => $portal_name,
    "PORTLET_NAME" => $portlet_name,

    "BUTTON_MISSION" => "save",
    "BUTTON_CANCEL_MISSION" => "",
    "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/portal2/portlets/msg/edit.php",
    "BUTTON_CANCEL_ACTION" => "javascript:form_submit('', '$config_webserver_ip/modules/portal2/portlets/msg/edit.php');"
  ));
}

if ($action == "save new")
{
//    //verify input
//    if(trim(norm_post("headline")) == "") {
//      $tpl->parse("FEEDBACK", "feedback_headline_null", 1);
//      $action = "save new";
//    }
//    if(trim(norm_post("content")) == "") {
//      $tpl->parse("FEEDBACK", "feedback_content_null", 1);
//      $action = "save new";
//    }

  if (trim(norm_post("headline")) != "") {
    $msg = steam_factory::create_document($steam, norm_post("headline"), "", "text/plain", $portlet );
    $msg->set_attribute("bid:doctype", "portlet:msg");

    if(is_array($content)) {
        $new_msg_location = $portlet->get_attribute("bid:portlet:msg:new_msg_location");
        // add new message at the top
        if ($new_msg_location == null || $new_msg_location == "top") {
          array_unshift($content, $msg->get_id());
        }
        // add new message at the bottom
        else if ($new_msg_location == "bottom") {
          array_push($content, $msg->get_id());
        }
        $message = $msg->get_id();
    }
      else
      {
        $content = array($msg->get_id());
        $message = $msg->get_id();
      }

    $action = "save";
  } else {
    $msg = steam_factory::create_document($steam, "NO HEADLINE", "", "text/plain", $portlet );
    $msg->set_attribute("bid:doctype", "portlet:msg");

    if(is_array($content)) {
        array_unshift($content, $msg->get_id());
        $message = $msg->get_id();
    }
      else
      {
        $content = array($msg->get_id());
        $message = $msg->get_id();
      }

    $action = "save";
  }
}

//Action Save, build new message and store in content
if($action == "save")
{
  //verify input
//    if(trim(norm_post("headline")) == "") {
//      $tpl->parse("FEEDBACK", "feedback_headline_null", 1);
//      $action = "save";
//    }
//    if(trim(norm_post("content")) == "") {
//      $tpl->parse("FEEDBACK", "feedback_content_null", 1);
//      $action = "save";
//    }
  if (!isset($msg)) {
       $msg = steam_factory::get_object($steam, $message);
  }
  $action = "save return(portlets/msg/edit.php)";

  //on file upload, upload picture to steam
  if(isset($_FILES["picture"]["size"]) && $_FILES["picture"]["size"] > 0)
  {
    //get temporary filename
    $filename = $_FILES["picture"]["tmp_name"];

    //get picture content
    ob_start();
    readfile($filename);
    $data = ob_get_contents();
    ob_end_clean();

    //upload picture
    $picture_id = steam_factory::create_document( $steam, time() . $_FILES["picture"]["name"], $data, "", $portlet );
   # $msg->upload($portlet, $data);

// Die folgende Attribtierung funktioniert seit der Umstellung auf PHP5 aus ungekl�rten
// Gr�nden nicht mehr: $picture_id hat hier keinen (vern�nftigen) Wert. Bei der Umstellung
// auf das neue API sollten die Attribute aber wieder gesetzt werden.

//    $picture = new steam_object($picture_id);
//    $steam->set_attribute($picture, "bid:doctype", "portlet:picture");
//    $steam->set_attribute($picture, "OBJ_DESC", $_FILES["picture"]["name"]);
  }

  if ($_POST["headline"] != "")
    $msg->set_attribute("OBJ_NAME", norm_post("headline"));
  else $msg->set_attribute("OBJ_NAME", (($language == "ge")?"KEINE &Uuml;berschrift gesetzt":"NO headline set"));
  $msg->set_attribute("OBJ_DESC", norm_post("subheadline"));
  $msg->set_attribute("bid:portlet:msg:picture_alignment", ((isset($_POST["picture_alignment"]))?$_POST["picture_alignment"]:"left"));

  /** Check the picture width */
  $picture_width = (isset($_POST["picture_width"]))
    ?$_POST["picture_width"]
    :"";
  $picture_width = check_width_string($picture_width, 5, 100, 5, $column_width-25, "");
  $msg->set_attribute("bid:portlet:msg:picture_width", $picture_width);

  $msg->set_attribute("bid:portlet:msg:link_url", norm_post("link_url"));
  $msg->set_attribute("bid:portlet:msg:link_url_label", norm_post("link_url_label"));
  $msg->set_attribute("bid:portlet:msg:link_open", ((isset($_POST["link_open"]))?(($_POST["link_open"]==open)?"checked":""):""));
  $msg->set_content( stripslashes($_POST["content"]) );

  $oldimage_id = $msg->get_attribute("bid:portlet:msg:picture_id");

  // new picture, no old one
  if (isset($picture_id) && ($oldimage_id == null || $oldimage_id == "")) {
    $msg->set_attribute("bid:portlet:msg:picture_id", $picture_id->get_id() );
    unset($oldimage_id);
  } // change picture
  else if (isset($picture_id) && $oldimage_id != $picture_id) {
    $msg->set_attribute("bid:portlet:msg:picture_id", $picture_id->get_id() );
  } // same picture
  else if (isset($picture_id) && $oldimage_id == $picture_id) {
    unset($oldimage_id);
  }
  else if(!isset($picture_id) && !(isset($_POST["imageaction"]) && $_POST["imageaction"] == "delete"))
    unset($oldimage_id);
  else {
    $msg->set_attribute("bid:portlet:msg:picture_id", "");
  }


  if (isset($oldimage_id)) {
    $picture = steam_factory::get_object($steam, $oldimage_id);
    $picture->delete();
  }

  $portlet_content = $content;
}

//display edit
if($message != "")
{
  $msg = steam_factory::get_object($steam, $message);
  $picture_id = (($msg->get_attribute("bid:portlet:msg:picture_id") != "")?$msg->get_attribute("bid:portlet:msg:picture_id"):0);
  $picture_width = (($msg->get_attribute("bid:portlet:msg:picture_width") != "")?trim($msg->get_attribute("bid:portlet:msg:picture_width")):"");
  $tpl->set_var(array(
    "MESSAGE_ID" => $message,
    "VALUE_HEADLINE" => trim($msg->get_attribute(OBJ_NAME)),
    "VALUE_SUBHEADLINE" => trim($msg->get_attribute(OBJ_DESC)),
    "VALUE_CONTENT" => trim($msg->get_content()),
    "VALUE_LINK_URL_LABEL" => trim($msg->get_attribute("bid:portlet:msg:link_url_label")),
    "VALUE_LINK_URL" => trim($msg->get_attribute("bid:portlet:msg:link_url")),
    "VALUE_LINK_OPEN" => trim($msg->get_attribute("bid:portlet:msg:link_open")),
    "PICTURE_ID" => trim($msg->get_attribute("bid:portlet:msg:picture_id")),
    "VALUE_PICTURE_ALIGNMENT_LEFT" => (($msg->get_attribute("bid:portlet:msg:picture_alignment"))?"CHECKED":""),
    "VALUE_PICTURE_ALIGNMENT_RIGHT" => (($msg->get_attribute("bid:portlet:msg:picture_alignment") == "right")?"CHECKED":""),
    "VALUE_PICTURE_ALIGNMENT_NONE" => (($msg->get_attribute("bid:portlet:msg:picture_alignment") == "none")?"CHECKED":""),
    "VALUE_PICTURE_WIDTH" => $picture_width
  ));

  $tpl->parse("VALUE_PICTURE_URL", (($picture_id != 0)?"url_picture":"url_no_picture"));
}

//new
else
{
  $tpl->set_var(array(
    "VALUE_HEADLINE" => "",
    "VALUE_SUBHEADLINE" => "",
    "VALUE_CONTENT" => "",
    "VALUE_LINK_URL_LABEL" => "",
    "VALUE_LINK_URL" => "",
    "VALUE_LINK_OPEN" => checked,
    "PICTURE_ID" => "",
    "VALUE_PICTURE_ALIGNMENT_LEFT" => "",
    "VALUE_PICTURE_ALIGNMENT_RIGHT" => "",
    "VALUE_PICTURE_ALIGNMENT_NONE" => "",
    "VALUE_PICTURE_WIDTH" => ""
  ));
  $tpl->parse("VALUE_PICTURE_URL", "url_no_picture");
}

$tpl->pparse("OUT", "content");

include("../../footer.php");

?>
