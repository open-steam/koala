<?php

  /****************************************************************************
  dialognew.php - the dialog to create files
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
          Thorsten Schaefer <uni@thorstenschaefer.name>

  ****************************************************************************/

  //include stuff
  require_once("./config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
  //require_once("$config_doc_root/classes/debugHelper.php");
  $sessionLoginFailureAction = "closeSubWindow";
  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/derive_icon.php");
  require_once("$config_doc_root/includes/derive_mimetype.php");

  //******************************************************
  //** Presumption
  //******************************************************

  $case = (isset($_GET["case"]))?$_GET["case"]:((isset($_POST["case"]))?$_POST["case"]:"");
  $environmentId = (isset($_GET["environmentId"]))?$_GET["environmentId"]:((isset($_POST["environmentId"]))?$_POST["environmentId"]:0);
  $object = (isset($_GET["object"]))?$_GET["object"]:"";
  $action = (isset($_POST["action"]))?$_POST["action"]:"";

  //******************************************************
  //** sTeam Stuff
  //******************************************************

  //login und $steam def. in "./includes/login.php"
  $steam = new steam_connector( $config_server_ip,
                                $config_server_port,
                                $login_name,
                                $login_pwd);

  $error = array();
  $title = isset($_POST["title"])?$_POST["title"]:"";
  $text = isset($_POST["text"])?$_POST["text"]:"";
  $url = isset($_POST["url"])?$_POST["url"]:"";

  if( !$steam || !$steam->get_login_status()) {
      $do_login = True;
      $error[] = "error_not_logged_in";
  }
  else {
      $do_login = False;

      //current room steam object
      if( (int) $object != 0 )
          $current_room = steam_factory::get_object( $steam, $object );
      else
          $current_room = $steam->get_login_user()->get_workroom();
      $environmentId = $current_room->get_id();

      //get write permission
      $allowed = $current_room->check_access_write( $steam->get_login_user() );

      //action performed
      if($action != "")
      {
        //set correct "action" for the happening action
        if(!$allowed) $action = "error";

        $result = false;
        $title = rawurlencode(strip_tags($_POST["title"]));
        $desc = strip_tags(stripslashes($_POST["title"]), "<b><i><em><font><strong><small><big>");

        // relogin required
        if ($steam->get_login_user()->get_name() === "guest") {
            $do_login = True;
            $error[] = "error_not_logged_in";
        }
        else {
            //actions
            switch ($action)
            {
              case "folder":
                if( trim($_POST["title"]) != "" )
                {
                    $result = steam_factory::create_room(
                        $steam,
                        $title,
                        $current_room, $desc );
                    $result->set_attribute(
                        "bid:collectiontype",
                        $_POST["collectiontype"] );
                }
                else $error[] = "error_title";
                break;

              case "file":
                if( isset($_FILES["file"]) && $_FILES["file"]["name"] != "" ){
                  $filecontent = file_get_contents( $_FILES["file"]["tmp_name"] );
                  $mimetype = derive_mimetype( $_FILES["file"]["name"] );

                  $inventory = $current_room->get_inventory();
                  for( $i=0; $i < count($inventory); $i++ )
                    if( rawurlencode($_FILES["file"]["name"]) == $inventory[$i]->get_name() ){
                        $result = $inventory[$i];
                        $result->set_content( $filecontent );
                        $result->set_attribute( "DOC_MIME_TYPE", $mimetype );
                        break;
                    }

                  //create document
                  if( !$result )
                      $result = steam_factory::create_document(
                          $steam,
                          rawurlencode($_FILES["file"]["name"]),
                          $filecontent,
                          $mimetype,
                          $current_room );

                  if( trim($_POST["title"]) != "" )
                    $result->set_attribute( "OBJ_DESC", $desc );

                }
                else if( !isset($_FILES["file"]) || $_FILES["file"]["name"] != "" ) $error[] = "error_file";
                break;

              case "plain":
              case "html":
                if( trim($_POST["title"]) != "" ){

                    $mimetype = "text/$action";
                    $inventory = $current_room->get_inventory();
                    for( $i=0; $i < count($inventory); $i++ )
                        if( rawurlencode($_POST["title"]) == $inventory[$i]->get_name() ){
                            $result = $inventory[$i];
                            $result->set_content( stripslashes($_POST["text"]) );
                            $result->set_attribute( "DOC_MIME_TYPE", $mimetype );
                            break;
                        }

                    if( !$result )
                        $result = steam_factory::create_document(
                            $steam,
                            $title,
                            stripslashes($_POST["text"]),
                            $mimetype,
                            $current_room,
                            $desc );
                }
                else $error[] = "error_title";
                break;

              case "link":
                if( trim($_POST["title"]) != "" && trim($_POST["url"]) != "" ){
                    $result = steam_factory::create_docextern(
                        $steam,
                        $title,
                        $_POST["url"],
                        $current_room,
                        $desc);
                }
                else
                {
                  if( trim($_POST["title"]) == "" ) $error[] = "error_title";
                  if( trim($_POST["url"]) == "" ) $error[] = "error_url";
                }
                break;

              default:
                break;
            }
        }

        //close window on success
        if( $result !== false ){
          echo("<html><body onload='javascript:if (opener) opener.top.location.reload();window.close();'></body></html");
          exit;
        }

      } //if($action ....
  }

  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file(array(
    "content" => "dialognew.ihtml"
  ));
  $tpl->set_block("content", "folder_title", "DUMMY");
  $tpl->set_block("content", "folder_form", "DUMMY");
  $tpl->set_block("content", "folder_button", "DUMMY");
  $tpl->set_block("content", "file_title", "DUMMY");
  $tpl->set_block("content", "file_form", "DUMMY");
  $tpl->set_block("content", "file_button", "DUMMY");
  $tpl->set_block("content", "text_title", "DUMMY");
  $tpl->set_block("content", "text_form", "DUMMY");
  $tpl->set_block("content", "text_button", "DUMMY");
  $tpl->set_block("content", "link_title", "DUMMY");
  $tpl->set_block("content", "link_form", "DUMMY");
  $tpl->set_block("content", "link_button", "DUMMY");
  $tpl->set_block("content", "error_title", "DUMMY");
  $tpl->set_block("content", "error_file", "DUMMY");
  $tpl->set_block("content", "error_url", "DUMMY");
  $tpl->set_block("content", "error_not_logged_in", "DUMMY");
  $tpl->set_var(array(
     "DUMMY" => "",
     "ENVIRONMENT_ID" => $environmentId,
     "FORM_ACTION" => $case,
     "INPUT_TITLE" => $title,
     "INPUT_TEXT" => stripslashes($text),
     "INPUT_URL" => $url,
     "INPUT_CASE" => $case,
     "MIME_TYPE" => "",
     "DO_LOGIN" => "0",
     "BODY_ON_LOAD" => ""
  ));

  // if action has been done and error occured put out error feedback
  if( $action != "" && $result === false && isset($error) && count($error) > 0 ){
    foreach($error as $error_type)
      $tpl->parse("ERROR_FEEDBACK", $error_type, true);
  }
  else
    $tpl->set_var("ERROR_FEEDBACK", "");

  if ($do_login)
      $tpl->set_var(array(
          "DO_LOGIN" => "1",
          "BODY_ON_LOAD" => "document.getElementById('insertForm').submit();"
      ));

  //parse out the specific output
  switch ($case)
  {
    case "folder":
      $tpl->parse("TITLE", "folder_title");
      $tpl->parse("CONTENT", "folder_form");
      $tpl->parse("BUTTON_LABEL", "folder_button");
      break;
    case "file":
      $tpl->set_var("MAX_FILE_SIZE", ini_get("upload_max_filesize"));
      $tpl->parse("TITLE", "file_title");
      $tpl->parse("CONTENT", "file_form");
      $tpl->parse("BUTTON_LABEL", "file_button");
      break;
    case "plain":
      $tpl->set_var("MIME_TYPE", "text/plain");
      $tpl->parse("TITLE", "text_title");
      $tpl->parse("CONTENT", "text_form");
      $tpl->parse("BUTTON_LABEL", "text_button");
      break;
    case "html":
      $tpl->set_var("MIME_TYPE", "text/html");
      $tpl->parse("TITLE", "text_title");
      $tpl->parse("CONTENT", "text_form");
      $tpl->parse("BUTTON_LABEL", "text_button");
      break;
    case "link":
      $tpl->parse("TITLE", "link_title");
      $tpl->parse("CONTENT", "link_form");
      $tpl->parse("BUTTON_LABEL", "link_button");
      break;
    case "delete":
      foreach($names as $item)
      {
        $tpl->set_var(array(
          "OBJECT_ID" => $item->object->get_id(),
          "OBJECT_ICON" => derive_icon(array(
              "object" => $item->object,
              "name" => $item->arguments[OBJ_NAME],
              "bid:doctype" => $item->arguments["bid:doctype"])),
          "OBJECT_NAME" => $item->arguments[OBJ_NAME]
        ));
        $tpl->parse("DELETE_FORM_ROW", "delete_form_row", 1);
      }
      $tpl->parse("TITLE", "delete_title");
      $tpl->parse("CONTENT", "delete_form");
      $tpl->parse("BUTTON_LABEL", "delete_button");
      break;
    default:
      $tpl->parse("TITLE", "error_title");
      $tpl->parse("CONTENT", "error_form");
      $tpl->parse("BUTTON_LABEL", "error_button");
      break;
  }

  //parse all out
  $tpl->parse("OUT", "content");
  $tpl->p("OUT");

  //Logout & Disconnect
  $steam->disconnect();

?>
