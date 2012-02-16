<?php

  /****************************************************************************
  edit.php - edit a questionary
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
  $question_id = (isset($_GET["question_id"]))?$_GET["question_id"]:((isset($_POST["question_id"]))?$_POST["question_id"]:"");
  $combo_id = (isset($_GET["option"]))?$_GET["option"]:((isset($_POST["option"]))?$_POST["option"]:"");


  //******************************************************
  //** Actions redirect for insert a new element
  //******************************************************

  if($action != "" && isset($insert_element_map[$combo_id]))
  {
	header("Location: $config_webserver_ip/modules/questionary/" . $insert_element_map[$combo_id] . "?questionary=$questionary_id&mission=insert");
    exit();
  }


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
  	$steam->disconnect();
	header("Location: $config_webserver_ip/index.php");
	exit();
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


  //action delete: delete a single line
  if($action == "delete" && isset($question))
  {
    $question->delete();
	$steam->disconnect();
	header("Location: $config_webserver_ip/modules/questionary/edit.php?questionary=".$questionary->get_id());
	exit();
  }
  
  
  //disable enable questionary to fill out
  if($action == "enable")
  {
    $questionary->set_attribute("bid:questionary:enabled", true);
  }
  if($action == "disable")
  {
    $questionary->set_attribute("bid:questionary:enabled", false);
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
  

  //load Attributes
  $attributes = $questionary->get_attributes(array(	OBJ_NAME, 
  													"bid:questionary:number", 
													"bid:questionary:enabled", 
													"bid:questionary:edittime"));
  $questionary_name = $attributes[OBJ_NAME];
  $number = $attributes["bid:questionary:number"];
  $enabled = $attributes["bid:questionary:enabled"];
  $edittime = $attributes["bid:questionary:edittime"][0];


  //Disconnect
  $steam->disconnect();


  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "edit.ihtml");
  $tpl->set_block("content", "block_empty_line", "DUMMY");
  $tpl->set_block("content", "block_full_line", "DUMMY");
  $tpl->set_block("content", "block_caption", "DUMMY");
  $tpl->set_block("content", "block_new_page", "DUMMY");
  $tpl->set_block("content", "block_description", "DUMMY");
  $tpl->set_block("content", "block_input_text", "DUMMY");
  $tpl->set_block("content", "block_input_textarea", "DUMMY");
  $tpl->set_block("content", "block_input_checkbox", "DUMMY");
  $tpl->set_block("content", "block_input_radiobutton", "DUMMY");
  $tpl->set_block("content", "block_input_selectbox", "DUMMY");
  $tpl->set_block("content", "block_input_grading", "DUMMY");
  $tpl->set_block("content", "block_input_tendency", "DUMMY");
  $tpl->set_block("content", "option_checked", "DUMMY");
  $tpl->set_block("content", "option_unchecked", "DUMMY");
  $tpl->set_block("content", "button_mission", "BUTTON_MISSION_ROW");
  $tpl->set_block("content", "no_question_row", "QUESTION_ROW");
  $tpl->set_block("content", "question_row_edit", "QUESTION_ROW_EDIT");
  $tpl->set_block("content", "question_row", "DUMMY");
  $tpl->set_block("content", "button_label_general", "DUMMY");
  $tpl->set_block("content", "button_label_insert", "DUMMY");
  $tpl->set_block("content", "button_label_sort", "DUMMY");
  $tpl->set_block("content", "button_img_sort", "BUTTON_IMG_SORT");
  $tpl->set_block("content", "button_label_finish", "DUMMY");
  $tpl->set_block("content", "button_spacer", "DUMMY");
  $tpl->set_block("content", "enabling_row", "ENABLING_ROW");
  $tpl->set_block("content", "enable", "DUMMY");
  $tpl->set_block("content", "disable", "DUMMY");
  
 
  $tpl->set_var(array(
    "DUMMY" => "",
	"ENABLING_ROW" => "",
	"BUTTON_IMG_SORT" => "",
    "QUESTIONARY_ID" => $questionary->get_id(),
    "QUESTIONARY_NAME" => $questionary_name
  ));


  //parse out questionary
  $options = $geo->get_all();
  

  //if options are available parse list
  $question_number = 1;
  if(count($options) > 0)
  {
    //build array for option types blocks to parse out
    $blocks = array(
      QUESTIONARY_DESCRIPTION          => "description",
      QUESTIONARY_EMPTY_LINE           => "empty_line",
      QUESTIONARY_FULL_LINE            => "full_line",
	  QUESTIONARY_CAPTION 	           => "caption",
      QUESTIONARY_INPUT_CHECKBOX       => "input_checkbox",
      QUESTIONARY_INPUT_RADIO          => "input_radiobutton",
      QUESTIONARY_INPUT_SELECT         => "input_selectbox",
      QUESTIONARY_INPUT_TEXT           => "input_text",
      QUESTIONARY_INPUT_TEXTAREA       => "input_textarea",
      QUESTIONARY_NEW_PAGE             => "new_page",
	  QUESTIONARY_INPUT_GRADING        => "input_grading",
	  QUESTIONARY_INPUT_TENDENCY       => "input_tendency"
    );


    //parse list
    foreach($options as $id => $option)
    {
      //get line data
      $data = $option;

      //set generell output values
      $type = $data["type"];
	  if($type==QUESTIONARY_INPUT_TEXTAREA) $type=QUESTIONARY_INPUT_TEXT;	//textarea is the same formular like text	  
      $tpl->set_var(array(
        "QUESTION_EDIT_URL" => $blocks[$type],
        "QUESTION_MUST" => "",
        "QUESTION_OUTPUT" => "",
		"QUESTION_ID" => $data["question_id"]
      ));

      //some types are not editable because there is no data worth to edit
      if($type == QUESTIONARY_EMPTY_LINE || $type == QUESTIONARY_FULL_LINE || $type == QUESTIONARY_NEW_PAGE)
        $tpl->set_var(array(  "QUESTION_QUESTION" => "",
							  "QUESTION_ROW_EDIT" => ""
							));
      else //parse edit icon
      {
        if($type == QUESTIONARY_DESCRIPTION || $type == QUESTIONARY_CAPTION)
		{
          $question= strlen($data["text"]) > 30?substr($data["text"], 0, 30) . " ...":$data["text"];
		  $question = str_replace("\r\n", "", $question);	//otherwise the js question to delete the element doesnt work with breaks
		  $tpl->set_var("QUESTION_QUESTION", $question);
		}
        else
        {
		  if($type == QUESTIONARY_INPUT_GRADING || $type == QUESTIONARY_INPUT_TENDENCY)  
		  {
		  	$question = ((strlen($data["description"]) > 30)?substr($data["description"], 0, 30) . " ...":$data["description"]);
			$question = str_replace("\r\n", "", $question);	//otherwise the js question to delete the element doesnt work with breaks
		  }
		  else
		  {
		  	$question = ((strlen($data["question"]) > 30)?substr($data["question"], 0, 30) . " ...":$data["question"]);
		  }
		  $tpl->set_var( "QUESTION_QUESTION", $number?$question_number++ . ". $question":$question );
        }
        $tpl->parse("QUESTION_ROW_EDIT", "question_row_edit");
      }

      //parse must and output checkboxes
      if($type != QUESTIONARY_EMPTY_LINE &&
         $type != QUESTIONARY_FULL_LINE &&
         $type != QUESTIONARY_NEW_PAGE &&
         $type != QUESTIONARY_DESCRIPTION &&
		 $type != QUESTIONARY_CAPTION)
      {
        $tpl->parse("QUESTION_MUST", (($data["must"])?"option_checked":"option_unchecked"));
        $tpl->parse("QUESTION_OUTPUT", (($data["output"])?"option_checked":"option_unchecked"));
      }

      $tpl->parse("QUESTION_TYPE", "block_" . $blocks[$type]);
      $tpl->parse("QUESTION_ROW", "question_row", true);
    }
  }
  //if there are no options in list parse message
  else
    $tpl->parse("QUESTION_ROW", "no_question_row");


  //if no edittime is set, display enable disable questionary
  if($edittime==0)
  {
  	$tpl->set_var(array(
    	"BUTTON_ENABLING" => $enabled==true?"disable":"enable",
    	"BUTTON_URL_ENABLING" => "$config_webserver_ip/modules/questionary/edit.php?questionary=" . $questionary->get_id()
  		));
	$tpl->parse("BUTTON_LABEL_ENABLING",$enabled==true?"disable":"enable"); 
	$tpl->parse("ENABLING_ROW", "enabling_row");
  }


  //******************************************************
  //** Buttons
  //******************************************************

  //cancel button settings
  $tpl->set_var(array(
    "BUTTON_CANCEL_MISSION" => "close",
    "BUTTON_CANCEL_URL" => ""
  ));
  
  
  //insert button
  $tpl->set_var(array(
    "BUTTON_INSERT" => "insert",
    "BUTTON_URL_INSERT" => "$config_webserver_ip/modules/questionary/edit.php?questionary=" . $questionary->get_id()
  ));
  $tpl->parse("BUTTON_LABEL_INSERT", "button_label_insert");


  //parse in general button
  $tpl->set_var(array(
    "BUTTON_MISSION" => "general",
    "BUTTON_URL" => "$config_webserver_ip/modules/questionary/edit_general.php?questionary=" . $questionary->get_id()
  ));
  $tpl->parse("BUTTON_LABEL", "button_label_general");
  $tpl->parse("BUTTON_MISSION_ROW", "button_mission", true);


  //parse spacer
  $tpl->parse("BUTTON_MISSION_ROW", "button_spacer" ,true);


  //parse sort button in case there is enough to sort
  if(count($options) > 1)    $tpl->parse("BUTTON_IMG_SORT", "button_img_sort");


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