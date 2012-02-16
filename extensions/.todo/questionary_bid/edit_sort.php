<?php

  /****************************************************************************
  edit_sort.php - sort the input elements of a questionary
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
  require_once("$config_doc_root/classes/template.inc");
  require_once("./config/config.php");
  require_once("./classes/questionary_geo.php");
  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("./classes/rights.php");


  //******************************************************
  //** Presumption
  //******************************************************

  $questionary_id = (isset($_GET["questionary"]))?$_GET["questionary"]:((isset($_POST["questionary"]))?$_POST["questionary"]:"");
  $action = (isset($_GET["mission"]))?$_GET["mission"]:((isset($_POST["mission"]))?$_POST["mission"]:"");
  $option = (isset($_GET["option"]))?$_GET["option"]:"";


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

  //action save in new order
  if($action == "save")
  {
    //get new order and all segments in old order
    $order = split(" ", trim($_POST["list"]));

    foreach($order as $position => $object_id)
	{
		$object = steam_factory::get_object( $steam, $object_id);
		$result=$question_folder->swap_inventory( $object, $position);
	} 
  }


  //get questionary geometry
  $questions = $question_folder->get_inventory();
  $geo = new questionary_geo();
  $ids=array();
  foreach($questions as $question)
  {
	$ids[]=$question->get_id();
	$question=$question->get_attribute("bid:question:geometry",1);
  }
  $buffer = $steam->buffer_flush();
  foreach($buffer as $question)
  {
	$question["question_id"]=array_shift($ids);
	$geo->insert($question);
  }
  
  
  //load attribute number
  $question_number=$questionary->get_attribute("bid:questionary:number");
  
  
  //redirect to edit_layout page again
  if(isset($result) && $result)
  {
    header("Location: $config_webserver_ip/modules/questionary/edit.php?questionary=" . $questionary->get_id());
    exit();
  }


  //******************************************************
  //** Display Stuff
  //******************************************************

  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "edit_sort.ihtml");
  $tpl->set_block("content", "button_label_save", "DUMMY");
  $tpl->set_block("content", "button_spacer", "DUMMY");
  $tpl->set_block("content", "button_mission", "BUTTON_MISSION_ROW");
  $tpl->set_block("content", "block_empty_line", "DUMMY");
  $tpl->set_block("content", "block_full_line", "DUMMY");
  $tpl->set_block("content", "block_new_page", "DUMMY");
  $tpl->set_block("content", "select_row", "SELECT_ROW");
  $tpl->set_var(array(
    "DUMMY" => "",
    "QUESTIONARY_ID" => $questionary->get_id(),
    "QUESTIONARY_NAME" => $questionary->get_name()
  ));

  //get all elements in top->bottom, left->right order
  $elements = $geo->get_all();


  //build sort select box
  foreach($elements as $id => $element)
  {
    $data = $element;
    $type = $element["type"];
	$question_id = $element["question_id"];	

    $tpl->set_var("ELEMENT_ID", $question_id);

    //parse type dependent identifier
    if($type == QUESTIONARY_EMPTY_LINE)
      $tpl->parse("ELEMENT_NAME", "block_empty_line");
    else if($type == QUESTIONARY_FULL_LINE)
      $tpl->parse("ELEMENT_NAME", "block_full_line");
    else if($type == QUESTIONARY_NEW_PAGE)
      $tpl->parse("ELEMENT_NAME", "block_new_page");
    else if($type == QUESTIONARY_DESCRIPTION)
      $tpl->set_var("ELEMENT_NAME", ((strlen($data["text"]) > 30)?substr($data["text"], 0, 30) . " ...":$data["text"]));
	else if($type == QUESTIONARY_CAPTION)
      $tpl->set_var("ELEMENT_NAME", ((strlen($data["text"]) > 30)?substr($data["text"], 0, 30) . " ...":$data["text"]));
	else if($type == QUESTIONARY_INPUT_GRADING || $type == QUESTIONARY_INPUT_TENDENCY)
		 {
      		$question =  ((strlen($data["description"]) > 30)?substr($data["description"], 0, 30) . " ...":$data["description"]);
	   		$tpl->set_var("ELEMENT_NAME", (($question_number > 0)?$question_number++ . ". $question":$question));
		 }
    else
    {
      $question = ((strlen($data["question"]) > 30)?substr($data["question"], 0, 30) . " ...":$data["question"]);
      $tpl->set_var("ELEMENT_NAME", (($question_number > 0)?$question_number++ . ". $question":$question));
    }

    $tpl->parse("SELECT_ROW", "select_row", true);
  }

  //******************************************************
  //** Buttons
  //******************************************************

  //cancel button settings
  $tpl->set_var(array(
    "BUTTON_CANCEL_MISSION" => "",
    "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/questionary/edit.php?questionary=" . $questionary->get_id()
  ));


  //parse in save button
  $tpl->set_var(array(
    "BUTTON_MISSION" => "save",
    "BUTTON_URL" => "$config_webserver_ip/modules/questionary/edit_sort.php?questionary=" . $questionary->get_id()
  ));
  $tpl->parse("BUTTON_LABEL", "button_label_save");
  $tpl->parse("BUTTON_MISSION_ROW", "button_mission", true);

  //parse spacer
  $tpl->parse("BUTTON_MISSION_ROW", "button_spacer" ,true);


  //Disconnect
  $steam->disconnect();


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