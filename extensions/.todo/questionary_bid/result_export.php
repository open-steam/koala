<?php

  /****************************************************************************
  result_export.php - export all results of the questionary in .csv format
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
  require_once("./classes/questionary_geo.php");
  require_once("./classes/sort_array.php");
  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/norm_post.php");
  require_once("./classes/rights.php");


  //******************************************************
  //** Presumption
  //******************************************************

  $questionary_id = (isset($_GET["object"]))?$_GET["object"]:((isset($_POST["object"]))?$_POST["object"]:"");

  //save post vars
  $post = $_POST;


  //******************************************************
  //** sTeam Stuff
  //******************************************************

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


  //check permissions  
  $login_user = $steam->get_login_user();
  $login_user_id = $login_user->get_id();
  $login_user_groups = $login_user->get_groups();
  foreach($login_user_groups as $login_user_group)  $login_user_group_ids[]=$login_user_group->get_id();
  $is_editor = $rights->check_access_fillout($login_user, $login_user_group_ids); 
  $is_analyst = $rights->check_access_evaluate($login_user, $login_user_group_ids); 
  $is_author = $rights->check_access_edit($login_user, $login_user_group_ids); 
  if(!$is_author && !$is_analyst)
  {
    //Disconnect & close
    $steam->disconnect();
    die("<html><body></body></html>");
  }
  
  
  //get questionary attributes
  $attributes = $questionary->get_attributes(array(OBJ_NAME,
                                                   "bid:questionary:number",
                                                   "bid:questionary:fillout",
												   "bid:questionary:resultcreator",
												   "bid:questionary:resultcreationtime",												   
                                                   OBJ_CREATION_TIME,
												   "bid:questionary:editanswer",
												   "bid:questionary:editownanswer",
												   "bid:questionary:description",
												   "bid:questionary:edittime"												   
												   ));
  $questionary_name = $attributes[OBJ_NAME];
  $question_number = $attributes["bid:questionary:number"];
  $fillout = $attributes["bid:questionary:fillout"];
  $resultcreator = $attributes["bid:questionary:resultcreator"];
  $resultcreationtime = $attributes["bid:questionary:resultcreationtime"];
  $creation_time = $attributes[OBJ_CREATION_TIME];
  $editanswer = $attributes["bid:questionary:editanswer"];
  $editownanswer = $attributes["bid:questionary:editownanswer"];
  $description = $attributes["bid:questionary:description"];
  $edittime = $attributes["bid:questionary:edittime"];

  
  //check rights
  $forbidden = !($is_author || $is_analyst);


  //get questionary geometry
  $questions = $question_folder->get_inventory();
  $geo = new questionary_geo();
  foreach($questions as $question)
  {
	$question=$question->get_attribute("bid:question:geometry",1);
  }
  $buffer = $steam->buffer_flush();
  foreach($buffer as $question)
  {
 	$geo->insert($question);
  }
  $entities = $geo->get_all();


  //get all results
  $inventory = $answer_folder->get_inventory();
  $i=0;
  foreach($inventory as $item)
  {
    $values[$i]=$item->get_attributes(array("bid:questionary:input", OBJ_CREATION_TIME));
	$values[$i]["creator"]=$item->get_creator()->get_name();
	$i++;
  }


  //Disconnect
  $steam->disconnect();


  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "result_export.ihtml");
  $tpl->set_block("content", "cell_input_id", "CELL_INPUT_ID");
  $tpl->set_block("content", "cell_question", "CELL_QUESTION");
  $tpl->set_block("content", "cell_answer", "CELL_ANSWER");
  $tpl->set_block("content", "row", "ROW");
  $tpl->set_block("content", "fillout_1", "DUMMY");
  $tpl->set_block("content", "fillout_n", "DUMMY");
  $tpl->set_block("content", "label_creator", "DUMMY");
  $tpl->set_block("content", "label_creation_time", "DUMMY");
  $tpl->set_block("content", "label_yes", "DUMMY");
  $tpl->set_block("content", "label_no", "DUMMY");
  $tpl->set_block("content", "cell_edit_time", "CELL_EDIT_TIME");
  
  $tpl->set_var(array(
    "DUMMY" => "",
	"CELL_EDIT_TIME" => "",
	"ROW" => "",

    "QUESTIONARY_NAME" => $questionary_name,
	"DESCRIPTION" => $description,
	"OBJECT_ID" => $questionary->get_id(),
    "DATE" => " " . date("H:i:s - d.m.Y",$creation_time)
  ));
  $tpl->parse("EDIT_ANSWER", $editanswer == "1"?"label_yes":"label_no" );
  $tpl->parse("EDIT_OWN_ANSWER", $editownanswer == "1"?"label_yes":"label_no" );
  
  $tpl->parse("FILLOUT", (($fillout == "1")?"fillout_1":"fillout_n"));

  if($edittime[0])
  {
  	$tpl->set_var("EDIT_TIME", date("d.m.Y", $edittime[1])." - ".date("d.m.Y", $edittime[2]));
	$tpl->parse("CELL_EDIT_TIME", cell_edit_time);
  }


  //output header
  $count = 1;
  foreach($entities as $tkey => $tvalue)
  {
    $data = $tvalue;
    if(!isset($data["input_id"])) continue;

	switch($data["type"])
	{
		case QUESTIONARY_INPUT_GRADING:
			$number = $question_number?$count++ . ".":"";
			$decimal=1;
			foreach($data["grading_options"] as $grading_option)
			{
				$output=$question_number?$number.$decimal++:"";
				$tpl->set_var("QUESTION", $output." ".umlaute($grading_option) );
				$tpl->parse("CELL_QUESTION", "cell_question", true);
			}
			break;
		case QUESTIONARY_INPUT_TENDENCY:
			$number = $question_number?$count++ . ".":"";
			$decimal=1;
			foreach($data["tendency_elements"] as $tendency_element)
			{
				$output=$question_number?$number.$decimal++:"";
				$tpl->set_var("QUESTION", $output." ".umlaute($tendency_element[0]." - ".$tendency_element[1]) );
				$tpl->parse("CELL_QUESTION", "cell_question", true);
			}
			break;
		default:
			$tpl->set_var("QUESTION", ($question_number?$count++ . ". ":"").umlaute($data["question"]) );
    		$tpl->parse("CELL_QUESTION", "cell_question", true);
	}
  }
 
  //Creator header
  if($resultcreator)
  {
  	$tpl->parse("QUESTION", "label_creator");
  	$tpl->parse("CELL_QUESTION", "cell_question", true);
  }
  
  //Creation time header
  if($resultcreationtime)
  {
	  $tpl->parse("QUESTION", "label_creation_time");
	  $tpl->parse("CELL_QUESTION", "cell_question", true);
  }

  //output answers
  $count = 1;
  if($values!="") foreach($values as $tkey => $tvalue)
  {
    $tdata = $tvalue;

    $tpl->set_var("COUNT", $count++);
    $tpl->unset_var("CELL_ANSWER");

    foreach($entities as $ekey => $evalue)
    {
      $edata = $evalue;
      
	  if(!isset($edata["input_id"])) continue;
      $input_id = trim($edata["input_id"]);

      switch ($edata["type"])
      {
        case QUESTIONARY_INPUT_SELECT:
        case QUESTIONARY_INPUT_RADIO:
          $text = $edata["options"][$tdata["bid:questionary:input"][$input_id]];
          $text = umlaute($text);
		  $tpl->set_var("ANSWER", $text);
		  $tpl->parse("CELL_ANSWER", "cell_answer", true);
		  break;
        case QUESTIONARY_INPUT_CHECKBOX:
          $text = "";
          foreach($tdata["bid:questionary:input"][$input_id] as $option)
            $text .= $edata["options"][$option]." ";
		  $text = umlaute($text);
		  $tpl->set_var("ANSWER", $text);
		  $tpl->parse("CELL_ANSWER", "cell_answer", true);
          break;
		case QUESTIONARY_INPUT_TENDENCY:
		case QUESTIONARY_INPUT_GRADING:
		  foreach($tdata["bid:questionary:input"][$input_id] as $option)
		  {
		  	$text = $option;
			$tpl->set_var("ANSWER", $text);
			$tpl->parse("CELL_ANSWER", "cell_answer", true);
		  }
          break;
        default:
          $text = $tdata["bid:questionary:input"][$input_id];
		  $text = umlaute($text);
		  $tpl->set_var("ANSWER", $text);
		  $tpl->parse("CELL_ANSWER", "cell_answer", true);
          break;
      }
    }

    //creator
    if($resultcreator)
    {	
		$tpl->set_var("ANSWER", $tdata["creator"]);
		$tpl->parse("CELL_ANSWER", "cell_answer", true);
	}

    //creation time
	if($resultcreationtime)
    {	
		$tpl->set_var("ANSWER", " " . date("H:i:s - d.m.Y", $tdata[OBJ_CREATION_TIME]));
		$tpl->parse("CELL_ANSWER", "cell_answer", true);
	}

    $tpl->parse("ROW", "row", true);
  }


  //output as file download
  $tpl->parse("OUT", "content");
  $out = utf8_decode($tpl->get_var("OUT"));
  $out = strchr($out, "\"");

/**/
  //send header
  header('Pragma: public');
  header('Cache-Control: private');
  header('Cache-Control: no-cache, must-revalidate');
  header("Accept-Ranges: bytes");
  header("Content-Length: " . strlen($out));
  header('Connection: close');

  header("Content-type: application/ms-excel");
  header("Content-Disposition: inline; filename=$questionary_name.csv");
/**/

  //send data
  echo($out);


  function umlaute($text)
  {
    return str_replace(array("ä","ü","ö","ß","\""), array("ae","ue","oe","ss","\"\""), stripslashes($text));
  }
?>