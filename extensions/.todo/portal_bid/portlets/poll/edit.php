<?php

  /****************************************************************************
  edit.php - edit the poll portlet
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

include("../../header.php");

$content = $portlet_content;

$tmpl = new Template("./templates/$language", "keep");
$tmpl->set_file("content", "edit.ihtml");
$tmpl->set_block("content", "feedback_poll_topic_null", "DUMMY");
$tmpl->set_block("content", "poll_row", "OPTION_ROWS");
$tmpl->set_var(array(
  "DUMMY" => "",
  "PORTLET_ROOT" => $config_webserver_ip . "/modules/portal2/portlets/poll",
  "PORTAL_ID" => $portal->get_id(),
  "PORTAL_NAME" => $portal->get_attribute(OBJ_NAME),
  "BUTTON_MISSION" => "save",
  "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/poll/edit.php",
  "BUTTON_CANCEL_MISSION" => "",
  "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/portal2/portlet_edit.php",
  "BUTTON_CANCEL_ACTION" => "opener.top.location.reload();window.close();",
  "FEEDBACK" => ""
));

//save action
if($action == "save")
{
  $action = "save return(portlet_edit.php)";

  $options = array(); // the options the user may choose between
  $options_votecount = array(); // how often each of the options has been chosen so far

  for ($i=0;$i<6;$i++) {
    $options[$i] = norm_post($_POST["option$i"]);
    $options_votecount[$i] = norm_post($_POST["option_votecount$i"]);
  }
  
  $poll_topic = trim($_POST["poll_topic"]);
  $content = array(
    "poll_topic" => norm_post("$poll_topic"),
    "start_date" => array("day" => $_POST["start_day"], "month" => $_POST["start_month"], "year" => $_POST["start_year"]),
    "end_date" => array("day" => $_POST["end_day"], "month" => $_POST["end_month"], "year" => $_POST["end_year"]),
    "options" => $options,
    "options_votecount" => $options_votecount
  );

  //verify input
  if(trim($content["poll_topic"]) == "")
  {
    $tmpl->parse("FEEDBACK", "feedback_poll_topic_null", 1);
    $action = "";
  }

  $portlet_content = $content;
  $groupEveryone = steam_factory::groupname_to_object($steam, "everyone");
  $portlet->sanction(SANCTION_WRITE, $groupEveryone);
  
}

//display stuff
$tmpl->set_var(array(
  "PORTAL_NAME" => $portal_name,
  "PORTLET_NAME" => $portlet_title,
  "OBJECT_ID" => $portlet->get_id(),
  "TITLE" => $portlet_title,
  "START_DAY" => ((isset($content["start_date"]))?norm_post($content["start_date"]["day"]):""),
  "START_MONTH" => ((isset($content["start_date"]))?norm_post($content["start_date"]["month"]):""),
  "START_YEAR" => ((isset($content["start_date"]))?norm_post($content["start_date"]["year"]):""),
  "END_DAY" => ((isset($content["end_date"]))?norm_post($content["end_date"]["day"]):""),
  "END_MONTH" => ((isset($content["end_date"]))?norm_post($content["end_date"]["month"]):""),
  "END_YEAR" => ((isset($content["end_date"]))?norm_post($content["end_date"]["year"]):""),
  "POLL_TOPIC" => ((isset($content["poll_topic"]))?norm_post($content["poll_topic"]):""),
  "OPTION0" => ((isset($content["options"][0]))?norm_post($content["options"][0]):""),
  "OPTION1" => ((isset($content["options"][1]))?norm_post($content["options"][1]):""),
  "OPTION2" => ((isset($content["options"][2]))?norm_post($content["options"][2]):""),
  "OPTION3" => ((isset($content["options"][3]))?norm_post($content["options"][3]):""),
  "OPTION4" => ((isset($content["options"][4]))?norm_post($content["options"][4]):""),
  "OPTION5" => ((isset($content["options"][5]))?norm_post($content["options"][5]):""),
  "OPTION_VOTECOUNT0" => ((isset($content["options_votecount"][0]))?norm_post($content["options_votecount"][0]):0),
  "OPTION_VOTECOUNT1" => ((isset($content["options_votecount"][1]))?norm_post($content["options_votecount"][1]):0),
  "OPTION_VOTECOUNT2" => ((isset($content["options_votecount"][2]))?norm_post($content["options_votecount"][2]):0),
  "OPTION_VOTECOUNT3" => ((isset($content["options_votecount"][3]))?norm_post($content["options_votecount"][3]):0),
  "OPTION_VOTECOUNT4" => ((isset($content["options_votecount"][4]))?norm_post($content["options_votecount"][4]):0),
  "OPTION_VOTECOUNT5" => ((isset($content["options_votecount"][5]))?norm_post($content["options_votecount"][5]):0)
));


$tmpl->pparse("OUT", "content");


include("../../footer.php");

?>
