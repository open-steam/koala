<?php

  /****************************************************************************
  edit_file.php - edit one file on steam server
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

  Author: Stephanie Sarach <stephanie_sarach@web.de>
          Bastian Schröder <bastian@upb.de>
          Thorsten Schaefer <uni@thorstenschaefer.name>

  ****************************************************************************/

  //include stuff

  require_once("./config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
  //require_once("$config_doc_root/classes/debugHelper.php");
  $sessionLoginFailureAction = "closeSubWindow";
  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/norm_post.php");


  //******************************************************
  //** Precondition
  //******************************************************

  $document = (isset($_GET["object"]))?$_GET["object"]:((isset($_POST["object"]))?$_POST["object"]:0);
  $documentId = (isset($_GET["documentId"]))?$_GET["documentId"]:((isset($_POST["documentId"]))?$_POST["documentId"]:0);
  $action = (isset($_POST["action"]))?$_POST["action"]:"";
  $content = (isset($_POST["content"]))?$_POST["content"]:"";
  $mimeType = (isset($_POST["mimeType"]))?$_POST["mimeType"]:"";

  //******************************************************
  //** sTeam Stuff
  //******************************************************

  //login und $steam def. in "./includes/login.php"
  $steam = new steam_connector( $config_server_ip, $config_server_port, $login_name, $login_pwd);

  if( !$steam || !$steam->get_login_status() )
  {
    $do_login = True;
    $error[] = "error_not_logged_in";
  }
  else {
      $document = steam_factory::get_object( $steam, $document );
      $documentId = $document->get_id();
      $access_write = $document->check_access_write( $steam->get_login_user() );
      $access_read = $document->check_access_read( $steam->get_login_user() );
      $do_login = False;
  }

  $error = array();

  if($action == "save") {
    if(!$access_write) {
        $do_login = True;
        $error[] = "error_not_logged_in";
    }
    else {
        $result = $document->set_content( stripslashes ($content) );

        //close window on success and reload page
        if($result !== false)
        {
          echo("<html><body onload='javascript:if (opener) opener.top.location.reload();window.close();'></body></html>");
          exit;
        }
    }
  }

  if ($access_read) {
      $mimeType = $document->get_attribute(DOC_MIME_TYPE);
      //derive proper HTML output
      $content = $document->get_content();
      if (mb_detect_encoding($content, 'UTF-8, ISO-8859-1') !== 'UTF-8')
          $content = utf8_encode($content);

      // temporary replacements because of server name change
      $content = str_replace ('http://www2.bid-owl.de', 'http://www.bid-owl.de', $content);
      $content = str_replace ('http://www2.schulen-gt.de', 'http://www.schulen-gt.de', $content);
  }

  //Logout & Disconnect
  $steam->disconnect();

  //******************************************************
  //** Display Stuff
  //******************************************************
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("edit_file", "edit_file.ihtml");
  $tpl->set_block("edit_file", "error_not_logged_in", "DUMMY");

  $tpl->set_var(array(
         "DUMMY" => "",
         "DOCUMENT_ID" => $documentId,
         "MIME_TYPE" => $mimeType,
         "CONTENT_OF_FILE" => stripslashes($content),
         "DO_LOGIN" => "0",
         "ERROR_FEEDBACK" => "",
         "BODY_ON_LOAD" => ""
         ));

  if ($do_login)
      $tpl->set_var(array(
          "DO_LOGIN" => "1",
          "BODY_ON_LOAD" => "document.getElementById('edit_file').submit();"
      ));

  //if action has been done and error occured put out error feedback
  if( $action != "" && isset($error) && count($error) > 0 ){
    foreach($error as $error_type)
      $tpl->parse("ERROR_FEEDBACK", $error_type, true);
  }

  out();

  function out()
  {
    //parse all out
    global $tpl;
    $tpl->parse("OUT", "edit_file");
    $tpl->p("OUT");

    exit;
  }
?>
