<?php

  /****************************************************************************
  contentframe.php - display content of container and rooms as filelist
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

  //include stuff

  require_once("./config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
  require_once("$config_doc_root/classes/doc_content.php");
  //require_once("$config_doc_root/classes/debugHelper.php");

  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/derive_menu.php");
  require_once("$config_doc_root/includes/derive_url.php");
  require_once("$config_doc_root/config/ascii_math_svg_inclusion.php");


  //******************************************************
  //** Precondition
  //******************************************************

  $document = (isset($_GET["object"]))?$_GET["object"]:((isset($_POST["object"]))?$_POST["object"]:0);

  //******************************************************
  //** sTeam Stuff
  //******************************************************

  //login und $steam def. in "./includes/login.php"
  $steam = new steam_connector( $config_server_ip,
                  $config_server_port,
                  $login_name,
                  $login_pwd);

  if( !$steam || !$steam->get_login_status() )
  {
    header("Location: $config_webserver_ip/index.html");
    exit();
  }

  $document = steam_factory::get_object( $steam, $document );

  //derive proper HTML output
  $content = new doc_content($steam, $document);
  $doc_content = trim($content->get_content($config_webserver_ip));


  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "document.ihtml");
  $tpl->set_block("content", "plain_header", "DUMMY");
  $tpl->set_block("content", "plain_footer", "DUMMY");

  $tpl->set_var(array(
    "DUMMY" => "",
    "OBJECT_ID" => $document->get_id(),
    "DOC_ROOT" => $config_webserver_ip,
    //"OBJECT_NAME" => $attributes,
    //"MENU" => derive_menu("contentframe", $document, $document_path),
    //"REDIRECT" => "",
    "PLAIN_HEADER" => "",
    "PLAIN_FOOTER" => ""
  ));

  $mimetype = $document->get_attribute(DOC_MIME_TYPE);
  if($mimetype === "text/plain" || $mimetype === "application/vnd.google-earth.kml+xml")
  {
    $tpl->parse("PLAIN_HEADER","plain_header");
    $tpl->parse("PLAIN_FOOTER","plain_footer");
  }
  else if ($mimetype === "text/html") {
    $doc_content = add_ascii_math_svg_include($doc_content);
  }

  $tpl->set_var(array(
    "DOCUMENT" => $doc_content
  ));

  //Logout & Disconnect
  $steam->disconnect();

  //parse all out
  $tpl->parse("OUT", "content");
  $tpl->p("OUT");

?>
