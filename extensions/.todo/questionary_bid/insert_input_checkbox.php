<?php

  /****************************************************************************
  insert_input_checkbox.php - create a field of checkboxes in a questionary
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
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("./classes/questionary_geo.php");
  require_once("$config_doc_root/classes/template.inc");
  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/norm_post.php");
  require_once("./classes/rights.php");


  //******************************************************
  //** Presumption
  //******************************************************

  $questionary_id = (isset($_GET["questionary"]))?$_GET["questionary"]:((isset($_POST["questionary"]))?$_POST["questionary"]:"");
  $action = (isset($_GET["mission"]))?$_GET["mission"]:((isset($_POST["mission"]))?$_POST["mission"]:"");
  $question_id = (isset($_GET["question_id"]))?$_GET["question_id"]:((isset($_POST["question_id"]))?$_POST["question_id"]:"");


  //******************************************************
  //** sTeam Stuff
  //******************************************************

  //login und $steam def. in "./includes/login.php"
  $steam = new steam_connector(	$config_server_ip,
  								$config_server_port,
  								$login_name,
  								$login_pwd);

  if( !$steam || !$steam->get_login_status() )
  {
    header("Location: $config_webserver_ip/accessdenied.html");
    exit();
  }


  //current room steam object
  if( (int) $questionary_id != 0 ) 
  {
  	$questionary = steam_factory::get_object( $steam, $questionary_id );
	$question_folder = $questionary->get_object_by_name('questions');
  	$answer_folder = $questionary->get_object_by_name('answers');
	if((int) $question_id != 0 ) $question = steam_factory::get_object( $steam, $question_id );
  }
  else  
  {
  	header("Location: $config_webserver_ip/index.php");
  }
  

  //create new RIGHTS object
  $rights = new rights($steam, $questionary, $question_folder, $answer_folder);


  //check author permission  
  $login_user = $steam->get_login_user();
  $login_user_id = $login_user->get_id();
  $login_user_groups = $login_user->get_groups();
  foreach($login_user_groups as $login_user_group)  $login_user_group_ids[]=$login_user_group->get_id();
  $is_author = $rights->check_access_edit($login_user, $login_user_group_ids); 
  if(!$is_author || count($answer_folder->get_inventory())>0)
  {
    //Disconnect & close
    $steam->disconnect();
    die("<html>\n<body onload='javascript:window.close();'>\n</body>\n</html>");
  }


  //load question
  if($action == "edit" && isset($question))
  {	
	$geo = $question->get_attribute("bid:question:geometry");
  }

  //get $_POST values
  if(isset($_POST["question"]))
  {
	//single values
    $check = array(
      "type" => QUESTIONARY_INPUT_CHECKBOX,
      "question" => $_POST["question"],
      "question_position" => $_POST["question_position"],
      "columns" => $_POST["columns"],
      "must" => (isset($_POST["must"])),
      "output" => (isset($_POST["output"])),
      "options" => array(),
      "checked" => ((isset($_POST["checked"]))?$_POST["checked"]:array())
    );


	//get all options sorted ans define new checked value
    if($action == "sort" && $_POST["list"]!="")
    {
	  $list = explode(" ", trim($_POST["list"]));
      foreach($list as $option)
      {
        $check["options"][] = $_POST["options_$option"];
        if(isset($_POST["checked_$option"]))
          $check["checked"][] = count($check["options"]) - 1;
      }
    }
    else	//get all options and checked values
    {
      foreach($_POST as $key => $post)
        if(strchr($key, "options_"))  $check["options"][substr($key, 8)] = $post;
        else if(strchr($key, "checked_")) $check["checked"][] = substr($key, 8);
      
	  $check["checked"] = array_unique($check["checked"]);
    }
  }
  else if($action == "edit" && isset($geo))	//get stored values
    	$check = $geo;
  else	//get default values
    $check = array(
      "type" => QUESTIONARY_INPUT_CHECKBOX,
      "question" => "",
      "question_position" => "top",
      "columns" => 1,
      "checked" => array(),
      "must" => 1,
      "output" => 1,
      "options" => array(),
    );
  $check_options = $check["options"];

  //add/edit description
  if($action == "save" && isset($_POST["question"]))
  {
    //check if identifier is empty
    if(trim($check["question"])=="")
      $errors[] = "error_no_text";
    //check if columns is a number
    if(!ereg('^[[:digit:]]*$', $check["columns"]))
      $errors[] = "error_no_number_col";
    //check if there is at least 1 option
    if(count($check["options"]) <= 0)
      $errors[] = "error_no_options";

    if(!isset($errors))
    {
      //create new element in questions
  	  if(!isset($question)) $question = steam_factory::create_container( $steam, time()."", $question_folder);
	  
	  $geo = new questionary_geo();
	  $geo->add_input_checkbox(
          $check["question"],
          $check["question_position"],
          $check["columns"],
          $check["options"],
          $check["checked"],
          $check["must"],
          $check["output"] );

	  //save
	  $result = $question->set_attribute("bid:question:geometry", $geo->get_last_element());
    }
  }


  //Disconnect
  $steam->disconnect();


  //redirect to edit page again
  if(isset($result) && $result)
  {
    header("Location: $config_webserver_ip/modules/questionary/edit.php?questionary=" . $questionary->get_id());
    exit();
  }


  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "insert_input_checkbox.ihtml");
  $tpl->set_block("content", "check_no_row", "CHECK_ROW");
  $tpl->set_block("content", "check_row", "DUMMY");
  $tpl->set_block("content", "error_no_number_col", "DUMMY");
  $tpl->set_block("content", "error_no_options", "DUMMY");
  $tpl->set_block("content", "error_no_text", "DUMMY");
  $tpl->set_block("content", "button_insert", "DUMMY");
  $tpl->set_block("content", "button_sort", "DUMMY");
  $tpl->set_block("content", "button_save", "DUMMY");
  $tpl->set_block("content", "button_spacer", "DUMMY");
  $tpl->set_block("content", "button_mission", "BUTTON_MISSION_ROW");

  $tpl->set_var(array(
    "DUMMY" => "",
    "FEEDBACK" => "",
    "QUESTIONARY_ID" => $questionary->get_id(),
    "QUESTION" => trim($check["question"]),
    "QUESTION_POSITION_LEFT" => (($check["question_position"] == "left")?"CHECKED":""),
    "QUESTION_POSITION_TOP" => (($check["question_position"] == "top")?"CHECKED":""),
    "COLUMNS" => $check["columns"],
    "MUST" => (($check["must"] == 1)?"CHECKED":""),
    "OUTPUT" => (($check["output"] == 1)?"CHECKED":"")
  ));


  //display error if there is one
  if(isset($errors))
    foreach($errors as $error)
      $tpl->parse("FEEDBACK", $error, true);

  
  //parse no option label
  if(count($check_options) == 0) $tpl->parse("CHECK_ROW", "check_no_row");
  else	//parse all options
  {    
	$check_question_id = isset($question)?$question->get_id():0; 
	$i = 0;
    foreach($check_options as $check_option_id => $check_option)
    {
      $tpl->set_var(array(
        "CHECK_OPTION_ID" => $i++,
        "CHECK_OPTION_NAME" => $check_option,
		"CHECK_QUESTION_ID" => $check_question_id,
        "CHECKED" => ((in_array($check_option_id, $check["checked"]))?"CHECKED":"")
      ));
      $tpl->parse("CHECK_ROW", "check_row", true);
    }
    $tpl->set_var("CHECKED", ((count($check["checked"]) == 0)?"CHECKED":""));
  }


  //******************************************************
  //** Buttons
  //******************************************************

  $url_question = isset($question)?"&question_id=".$question->get_id():"";

  //cancel button settings
  $tpl->set_var(array(
    "BUTTON_CANCEL_MISSION" => "",
    "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/questionary/edit.php?questionary=" . $questionary->get_id()
  ));

  //parse in insert button
  $tpl->set_var(array(
    "BUTTON_MISSION_INSERT" => $action=="save"?"":$action,
    "BUTTON_URL_INSERT" => "$config_webserver_ip/modules/questionary/insert_input_checkbox.php?questionary=".$questionary->get_id().$url_question
  ));
  $tpl->parse("BUTTON_LABEL_INSERT", "button_insert");
 
  //set sort icon url
  $tpl->set_var("SORT_URL", "$config_webserver_ip/modules/questionary/insert_input_checkbox_sort.php?questionary=" . $questionary->get_id().$url_question);


  //parse spacer
  $tpl->parse("BUTTON_MISSION_ROW", "button_spacer" ,true);

  //parse in save button
  $button_save_label= $question_id==""?"button_insert":"button_save";
  $tpl->set_var(array(
    "BUTTON_MISSION" => "save",
    "BUTTON_URL" => "$config_webserver_ip/modules/questionary/insert_input_checkbox.php?questionary=".$questionary->get_id().$url_question
  ));
  $tpl->parse("BUTTON_LABEL", $button_save_label);
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