<?php

  /****************************************************************************
  insert_input_tendency.php - create a tendency element 
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


  //get $_POST values
  if(isset($_POST["description"]) || $_POST['list'])
  {
    //single values
    $tendency = array(
        "type" => QUESTIONARY_INPUT_TENDENCY,
        "description" => $_POST["description"],
        "must" => (isset($_POST["must"])),
        "output" => (isset($_POST["output"])),
        "tendency_steps" => $_POST["tendency_steps"],
		"tendency_element_a" => "",
		"tendency_element_b" => "",
		"tendency_elements" => array()
		);

    //get all options sorted ans define new checked value
    if($action == "sort" && $_POST["list"]!="")
    {
      $list = explode(" ", trim($_POST["list"]));
      foreach($list as $option)
      {
		$tendency["tendency_elements"][] = array($_POST["tendency_element_a_$option"], $_POST["tendency_element_b_$option"]);
      }
    }
    else	//get all old elements
	{
      foreach($_POST as $key => $post)
        if(strchr($key, "tendency_element_a_"))
		{
        	if( in_array("tendency_element_b_".substr($key, 19), array_keys($_POST) ) )	
				$tendency["tendency_elements"][substr($key, 19)] = array($post, $_POST["tendency_element_b_".substr($key, 19)]);
		}
	}
  }
  else if($action == "edit" && isset($geo))	 $tendency = $geo;	//get stored values
  else	//get default values
    $tendency = array(
        "type" => QUESTIONARY_INPUT_TENDENCY,
        "description" => "",
        "tendency_steps" => 5,
		"must" => 1,
        "output" => 1,
		"tendency_element_a" => "",
		"tendency_element_b" => "",
        "tendency_elements" => array(),
    );
  $tendency_elements = $tendency["tendency_elements"];
  

  //add/edit description
  if($action == "save" && isset($_POST["description"]))
  {
    //check if identifier is empty
    if(trim($tendency["description"])=="")
      $errors[] = "error_no_text";
	
	//check if identifier is empty
    if(trim($tendency["tendency_steps"])=="" || !ereg('^[[:digit:]]*$', $tendency["tendency_steps"]) || $tendency["tendency_steps"]<2 || $tendency["tendency_steps"]>20)
      $errors[] = "error_no_number_steps";
	  
   //check if there is at least 1 option
    if(count($tendency["tendency_elements"]) <= 0)
      $errors[] = "error_no_options";

    if(!isset($errors))
    {
      //create new element in questions
  	  if(!isset($question)) $question = steam_factory::create_container( $steam, time()."", $question_folder );

      $geo = new questionary_geo();
      $geo->add_input_tendency(
          $tendency["description"],
          $tendency["tendency_elements"],
		  $tendency["tendency_steps"],
          $tendency["must"],
          $tendency["output"] );

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
  $tpl->set_file("content", "insert_input_tendency.ihtml");
  $tpl->set_block("content", "error_no_text", "DUMMY");
  $tpl->set_block("content", "error_no_options", "DUMMY");
  $tpl->set_block("content", "error_no_number_steps", "DUMMY");
  $tpl->set_block("content", "error_no_text_tendency", "DUMMY");
  $tpl->set_block("content", "button_insert", "DUMMY");
  $tpl->set_block("content", "button_sort", "DUMMY");
  $tpl->set_block("content", "button_save", "DUMMY");
  $tpl->set_block("content", "button_spacer", "DUMMY");
  $tpl->set_block("content", "button_cancel", "DUMMY");
  $tpl->set_block("content", "tendency_row", "DUMMY");
  $tpl->set_block("content", "tendency_no_row", "TENDENCY_ROW"); 
  $tpl->set_block("content", "button_mission", "BUTTON_MISSION_ROW");

  $tpl->set_var(array(
    "DUMMY" => "",
    "FEEDBACK" => "",
    "QUESTIONARY_ID" => $questionary->get_id(),
	"DESCRIPTION" => trim($tendency["description"]),
	"TENDENCY_STEPS" => $tendency["tendency_steps"],
	"TENDENCY_ELEMENT_A" => "",
	"TENDENCY_ELEMENT_B" => "",
    "MUST" => (($tendency["must"] == 1)?"CHECKED":""),
    "OUTPUT" => (($tendency["output"] == 1)?"CHECKED":"")
  ));


  //display error if there is one
  if(isset($errors))
    foreach($errors as $error)
      $tpl->parse("FEEDBACK", $error, true);


  //parse no option label
  if(count($tendency_elements) == 0)  $tpl->parse("TENDENCY_ROW", "tendency_no_row", true);
  else	//parse all options
  {
	$tendency_question_id = isset($question)?$question->get_id():0; 
	$i = 0;
    foreach($tendency_elements as $tendency_element_id => $tendency_element)
    {
      $tpl->set_var(array(
        "TENDENCY_ELEMENT_ID" => $i++,
        "TENDENCY_ELEMENT_NAME" => $tendency_element[0]." - ".$tendency_element[1],
		"TENDENCY_QUESTION_ID" => $tendency_question_id,
		"TENDENCY_ELEMENT_NAME_A" => $tendency_element[0],
		"TENDENCY_ELEMENT_NAME_B" => $tendency_element[1]
      ));
      $tpl->parse("TENDENCY_ROW", "tendency_row", true);
    }
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
    "BUTTON_URL_INSERT" => "$config_webserver_ip/modules/questionary/insert_input_tendency.php?questionary=".$questionary->get_id().$url_question
  ));
  $tpl->parse("BUTTON_LABEL_INSERT", "button_insert");
 
  //set sort icon url
  $tpl->set_var("SORT_URL", "$config_webserver_ip/modules/questionary/insert_input_tendency_sort.php?questionary=" . $questionary->get_id().$url_question);

  //parse spacer
  $tpl->parse("BUTTON_MISSION_ROW", "button_spacer" ,true);

  //parse in save button
  $button_save_label= $question_id==""?"button_insert":"button_save";
  $tpl->set_var(array(
    "BUTTON_MISSION" => "save",
    "BUTTON_URL" => "$config_webserver_ip/modules/questionary/insert_input_tendency.php?questionary=".$questionary->get_id().$url_question
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