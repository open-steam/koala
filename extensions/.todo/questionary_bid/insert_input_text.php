<?php

  /****************************************************************************
  insert_input_text.php - create a text input field in a questionary
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

  Author: Patrick T�nnis
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


  //current steam objects 
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

  //add/edit description
  if($action != "" && isset($_POST["question"]))
  {
	//check if identifier is empty
    if(trim($_POST["question"])=="")
      $errors[] = "error_no_text";
    //check if width is a number
    if(!ereg('^[[:digit:]]*$', $_POST["width"]))
      $errors[] = "error_no_number_width";
    //check if maxlength is a number
    if(!ereg('^[[:digit:]]*$', $_POST["maxlength"]))
      $errors[] = "error_no_number_maxlength";
	//check if number of row is a number
    if(!ereg('^[[:digit:]]*$', $_POST["rows"]))
      $errors[] = "error_no_number_rows";
	if($_POST["rows"]<=0)
	  $errors[] = "error_bigger_zero_rows";
	
	//update according description
    if(!isset($errors))
    {
      if($action == "insert")
	  {
		//create new element in question folder
		$question = steam_factory::create_container( $steam, time()."", $question_folder );
		$question_id = $question->get_id();
	  }
	  
	  $geo = new questionary_geo();
	  if($_POST["rows"]>1)
	  {
		$geo->add_input_textarea(
			norm_post("question"),
			$_POST["question_position"],
			norm_post("width")==" "?400:norm_post("width"),
			norm_post("rows"),
			norm_post("value"),
			isset($_POST["must"]),
			isset($_POST["output"])
		  );
	  }
	  else
	  {
	  	$geo->add_input_text(
			norm_post("question"),
			$_POST["question_position"],
			norm_post("width")==" "?400:norm_post("width"),
			norm_post("maxlength"),
			norm_post("value"),
			isset($_POST["must"]),
			isset($_POST["output"])
		  );
	  }

      //save geometry in question
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
  $tpl->set_file("content", "insert_input_text.ihtml");
  $tpl->set_block("content", "error_no_number_width", "DUMMY");
  $tpl->set_block("content", "error_no_number_maxlength", "DUMMY");
  $tpl->set_block("content", "error_no_number_rows", "DUMMY");
  $tpl->set_block("content", "error_bigger_zero_rows", "DUMMY");
  $tpl->set_block("content", "error_no_text", "DUMMY");
  $tpl->set_block("content", "button_insert", "DUMMY");
  $tpl->set_block("content", "button_edit", "DUMMY");
  $tpl->set_block("content", "button_spacer", "DUMMY");
  $tpl->set_block("content", "button_mission", "BUTTON_MISSION_ROW");


  $tpl->set_var(array(
    "DUMMY" => "",
    "FEEDBACK" => "",
    "QUESTIONARY_ID" => $questionary->get_id(),

    "BUTTON_CANCEL_MISSION" => "",
    "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/questionary/edit.php"
  ));

  //button insert/edit
  if($action == "edit")	
  {
  	$button_action="edit";
	$button_description="button_edit";
	$url = "$config_webserver_ip/modules/questionary/insert_input_text.php?question_id=".$question_id;
  }
  else	
  {
  	$button_action="insert";
	$button_description="button_insert";
	$url = "$config_webserver_ip/modules/questionary/insert_input_text.php";
  }
  
  $tpl->set_var(array(
    "BUTTON_MISSION" => $button_action,
    "BUTTON_URL" => $url
  ));
  $tpl->parse("BUTTON_LABEL", $button_description);
  $tpl->parse("BUTTON_MISSION_ROW", "button_mission", true);

  //parse spacer
  $tpl->parse("BUTTON_MISSION_ROW", "button_spacer" ,true);


  //display error if there is one
  if(isset($errors))
    foreach($errors as $error)
      $tpl->parse("FEEDBACK", $error, true);


  //on edit display old value
  if($action == "edit")
    $tpl->set_var(array(
      "QUESTION" => trim($geo["question"]),
      "QUESTION_POSITION_LEFT" => (($geo["question_position"] == "left")?"CHECKED":""),
      "QUESTION_POSITION_TOP" => (($geo["question_position"] == "top")?"CHECKED":""),
      "WIDTH" => trim($geo["width"]),
      "MAXLENGTH" => trim($geo["maxlength"]),
      "VALUE" => trim($geo["value"]),
	  "ROWS" => isset($geo["height"]) ? trim($geo["height"]): 1,
      "MUST" => (($geo["must"])?"CHECKED":""),
      "OUTPUT" => (($geo["output"])?"CHECKED":"")
    ));

  //clear field if its a new one
  else
    $tpl->set_var(array(
      "QUESTION" => isset($_POST["question"])?$_POST["question"]:"",
      "QUESTION_POSITION_TOP" => isset($_POST["position"]) && $_POST["position"]=="left" ? "":"CHECKED",
      "QUESTION_POSITION_LEFT" => isset($_POST["position"]) && $_POST["position"]=="left" ? "CHECKED":"",
      "WIDTH" => isset($_POST["width"])?$_POST["width"]:"",
      "MAXLENGTH" => isset($_POST["maxlength"])?$_POST["maxlength"]:"",
      "VALUE" => isset($_POST["value"])?$_POST["value"]:"",
	  "ROWS" => isset($_POST["rows"])?$_POST["rows"]:"1",
      "MUST" => "CHECKED",
      "OUTPUT" => "CHECKED"
    ));


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