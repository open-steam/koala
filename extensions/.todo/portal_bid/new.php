<?php

  /****************************************************************************
  new.php - the dialog to create a new portal
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

  Author: Harald Selke <hase@uni-paderborn.de>

  ****************************************************************************/

  //include stuff
  require_once("../../config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
  //require_once("$config_doc_root/classes/debugHelper.php");

  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/derive_icon.php");

  //******************************************************
  //** Presumption
  //******************************************************

  $object = (isset($_GET["object"]))?$_GET["object"]:((isset($_POST["object"]))?$_POST["object"]:"");
  $action = (isset($_POST["mission"]))?$_POST["mission"]:"";

  //******************************************************
  //** sTeam Stuff
  //******************************************************
  $steam = new steam_connector( $config_server_ip,
                                $config_server_port,
                                $login_name,
                                $login_pwd);

  if( !$steam || !$steam->get_login_status() )
  {
    header("Location: $config_webserver_ip/index.html");
    exit();
  }

  //fetch current room steam object
  if( (int) $object != 0 ) $current_room = steam_factory::get_object( $steam, $object );
  else $current_room = $steam->get_login_user()->get_workroom();

  //check if user has write access
  $allowed = $current_room->check_access_write( $steam->get_login_user() );

  //action performed
  if($action != "")
  {
    //set correct "action" for the happening action
    if(!$allowed) $action = "error";

    $error = array();
    $portal = false;

    //actions
    switch ($action)
    {
      case "new":
        if( trim($_POST["title"]) != "" )
        {
            $portal = steam_factory::create_container( $steam, rawurlencode($_POST["title"]), $current_room, $_POST["title"] );
            $portal->set_attribute( "OBJ_TYPE", "container_portal_bid" );
        }
        else { 
            $error[] = "error_title";
            break;
        }

        $columnWidth = array("1" => "800px", "2" => "155px;600px", "3" =>
                "155px;445px;155px");
        $columnCount = $_POST["columns"];
        $columnWidth = explode( ';', $columnWidth[$columnCount] );
        $columns = array();

        for($i = 1; $i <= $columnCount ; $i++) {
          $columns[$i] = steam_factory::create_container( $steam, ''.$i, $portal, '' . $i );
          $columns[$i]->set_attributes( array ("OBJ_TYPE" =>
                      "container_portalColumn_bid", "bid:portal:column:width" =>
                      $columnWidth[$i-1] ));
        }

        // include all name definitions for all portlets
        // Open a known directory, and proceed to read its contents
        $dir = "./portlets";
        if(is_dir($dir) && $dh = opendir($dir)) {
            while(($file = readdir($dh)) !== false)
                if($file !== "." && $file !== ".." && $file !== "CVS" && $file !== ".svn")
                    // get language dependent portletnames
                    include("$dir/$file/name.php");
            closedir($dh);
        }
        
        // populate columns with default portlets
        switch (count($columns)) {
            case 1:
                createHeadline($steam, 
                                        $columns[1],
                                        $_POST["title"], 
                                        $headline_version);
                createMsg($steam, 
                                        $columns[1], 
                                        $msg_name[$language], 
                                        $msg_version);
                break;
            case 2:
                createTopic($steam, 
                                        $columns[1], 
                                        $topic_name[$language], 
                                        $topic_version);
                createHeadline($steam, 
                                        $columns[2],
                                        $_POST["title"], 
                                        $headline_version);
                createMsg($steam, 
                                        $columns[2], 
                                        $msg_name[$language], 
                                        $msg_version);
                break;
            case 3:
                createTopic($steam, 
                                        $columns[1], 
                                        $topic_name[$language], 
                                        $topic_version);
                createHeadline($steam, 
                                        $columns[2],
                                        $_POST["title"],
                                        $headline_version);
                createMsg($steam, 
                                        $columns[2], 
                                        $msg_name[$language], 
                                        $msg_version);
                createAppointment($steam, 
                                        $columns[3], 
                                        $appointment_name[$language], 
                                        $appointment_version);
                break;
        }

        $steam->buffer_flush();
        break;

      default:
        break;
    }

    //close window on success and reload opener
    if( $portal !== false ){
      echo("<html><body onload='javascript:opener.top.location.reload();window.close();'></body></html");
      exit;
    }

  }

  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file(array(
    "content" => "new.ihtml"
  ));
  $tpl->set_block("content", "new_title", "DUMMY");
  $tpl->set_block("content", "error_title", "DUMMY");
	$tpl->set_block("content", "button_mission", "BUTTON_MISSION_ROW");
	$tpl->set_block("content", "button_label_new", "DUMMY");
  $tpl->set_var(array(
    "DUMMY" => "",
    "ENVIRONMENT_ID" => $current_room->get_id(),
    "BUTTON_MISSION" => "new",
    "PORTAL_TITLE" => "",
    "BUTTON_URL" => "$config_webserver_ip/modules/portal2/new.php",
    "BUTTON_CANCEL_ACTION" => "window.close();",
  ));
	$tpl->parse("BUTTON_LABEL", "button_label_new");
	$tpl->parse("BUTTON_MISSION_ROW", "button_mission", true);

  //if action has been done and error occured put out error feedback
  if( $action != "" && $portal === false && isset($error) && count($error) > 0 ){
    foreach($error as $error_type)
      $tpl->parse("ERROR_FEEDBACK", $error_type, true);

    $tpl->set_var(array(
        "PORTAL_TITLE" => ((isset($_POST["title"]))?$_POST["title"]:""),
        "CHECKED_3_COL" => (($_POST["columns"] == "3")?"checked":""),
        "CHECKED_2_COL" => (($_POST["columns"] == "2")?"checked":""),
        "CHECKED_1_COL" => (($_POST["columns"] == "1")?"checked":""),
    ));

    $tpl->parse("TITLE", "error_title");
  }
  else {
    $tpl->set_var(array(
        "ERROR_FEEDBACK" => "",
        "CHECKED_3_COL" => "checked",
        "CHECKED_2_COL" => "",
        "CHECKED_1_COL" => "",
    ));
  }


  $tpl->parse("TITLE", "portal_title");

  //parse all out
  $tpl->parse("OUT", "content");
  $tpl->p("OUT");

  //Logout & Disconnect
  $steam->disconnect();
  exit;

function createHeadline($steam, $column, $name, $version) {
    $headline = steam_factory::create_container($steam, $name, $column);
    $headline_content = array(
        "headline" => $name,
        "alignment" => "center",
        "size" => 15,
    );
    $headline->set_attributes(array(
        OBJ_DESC => $name,
        OBJ_TYPE => "container_portlet_bid",
        "bid:portlet" => "headline",
        "bid:portlet:version" => $version,
        "bid:portlet:content" => $headline_content,
    ));
}

function createTopic($steam, $column, $name, $version) {
    $topic = steam_factory::create_container($steam, $name, $column);
    $topic->set_attributes(array(
            OBJ_DESC => $name,
            OBJ_TYPE => "container_portlet_bid",
            "bid:portlet:version" => $version,
            "bid:portlet" => "topic",
    ));
}

function createAppointment($steam, $column, $name, $version) {
    $appointment = steam_factory::create_container($steam, $name, $column);
    $appointment->set_attributes(array(
            OBJ_DESC => $name,
            OBJ_TYPE => "container_portlet_bid",
            "bid:portlet:version" => $version,
            "bid:portlet" => "appointment",
    ));
}

function createMsg($steam, $column, $name, $version) {
    $msg = steam_factory::create_container($steam, $name, $column);
    $msg->set_attributes(array(
            OBJ_DESC => $name,
            OBJ_TYPE => "container_portlet_bid",
            "bid:portlet:version" => $version,
            "bid:portlet" => "msg",
    ));
}
?>
