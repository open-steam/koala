<?php

  /****************************************************************************
  insert_input_checkbox_sort.php - sort the checkbox options in a questionary
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

  Author: Patrick Tönnis
  EMail: toennis@uni-paderborn.de
  
  Author: Henrik Beige
  EMail: hebeige@gmx.de
  
  ****************************************************************************/


  //include stuff
  require_once("../../config/config.php");
  require_once("$config_doc_root/classes/template.inc");
  require_once("$config_doc_root/includes/sessiondata.php");


  //******************************************************
  //** Presumption
  //******************************************************

  $questionary = (isset($_GET["questionary"]))?$_GET["questionary"]:((isset($_POST["questionary"]))?$_POST["questionary"]:"");
  $action = (isset($_GET["mission"]))?$_GET["mission"]:((isset($_POST["mission"]))?$_POST["mission"]:"");
  $question_id = (isset($_GET["question_id"]))?$_GET["question_id"]:((isset($_POST["question_id"]))?$_POST["question_id"]:"");


  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "insert_input_checkbox_sort.ihtml");
  $tpl->set_block("content", "checked_row", "CHECKED_ROW");
  $tpl->set_block("content", "must", "MUST");
  $tpl->set_block("content", "output", "OUTPUT");
  $tpl->set_block("content", "radio", "RADIO");
  $tpl->set_block("content", "select_row", "SELECT_ROW");
  $tpl->set_block("content", "button_save", "DUMMY");
  $tpl->set_block("content", "button_spacer", "DUMMY");
  $tpl->set_block("content", "button_mission", "BUTTON_MISSION_ROW");

  $tpl->set_var(array(
    "DUMMY" => "",
    "QUESTIONARY_ID" => $questionary,
    "QUESTION_ID" => $question_id,
    "CHECKED_ROW" => "",
    "INPUT_ID" => $_POST["input_id"],
    "QUESTION" => $_POST["question"],
    "QUESTION_POSITION" => $_POST["question_position"],
    "COLUMNS" => $_POST["columns"],
    "CHECKED" => ((isset($_POST["checked"]))?$_POST["checked"]:""),
	"RADIO" => "",
    "MUST" => "",
    "OUTPUT" => ""
  ));

  //parse must abd output values
  if(isset($_POST["must"]))
    $tpl->parse("MUST", "must");
  if(isset($_POST["output"]))
    $tpl->parse("OUTPUT", "output");

  //parse options in selectbox
  foreach($_POST as $key => $post)
    if(strchr($key, "options_"))
    {
      $tpl->set_var(array(
        "RADIO_ID" => substr($key, 8),
        "RADIO_NAME" => $post
      ));
      $tpl->parse("RADIO", "radio", true);
      $tpl->parse("SELECT_ROW", "select_row", true);
    }
    else if(strchr($key, "checked_"))
    {
      $tpl->set_var("CHECKED_ID", substr($key, 8));
      $tpl->parse("CHECKED_ROW", "checked_row", true);
    }

  
  //******************************************************
  //** Buttons
  //******************************************************
  
  //cancel button settings
  $tpl->set_var(array(
    "BUTTON_CANCEL_MISSION" => "",
    "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/questionary/insert_input_checkbox.php"
  ));

  //parse save button
  $tpl->set_var(array(
    "BUTTON_MISSION" => "sort",
    "BUTTON_URL" => "$config_webserver_ip/modules/questionary/insert_input_checkbox.php"
  ));
  $tpl->parse("BUTTON_LABEL", "button_save");
  $tpl->parse("BUTTON_MISSION_ROW", "button_mission", true);

  //parse spacer
  $tpl->parse("BUTTON_MISSION_ROW", "button_spacer" ,true);
  

  out();

  function out()
  {
    //parse all out
    global $tpl;
    $tpl->parse("OUT", "content");
    $tpl->p("OUT");

    exit;
  }


?>