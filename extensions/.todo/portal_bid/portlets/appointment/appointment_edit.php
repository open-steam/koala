<?php

  /****************************************************************************
  appointment_edit.php - edit an appointment of the appointment portlet
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

  ****************************************************************************/

include("../../header.php");
include("appointment_sort.php");

$content = $portlet_content;

$appointment = (isset($_GET["appointment"]))?$_GET["appointment"]:((isset($_POST["appointment"]))?$_POST["appointment"]:"");


//display stuff

$tpl = new Template("./templates/$language", "keep");
$tpl->set_file("content", "appointment_edit.ihtml");
$tpl->set_block("content", "feedback_headline_null", "DUMMY");
$tpl->set_block("content", "feedback_startdate_wrong", "DUMMY");
$tpl->set_block("content", "feedback_enddate_wrong", "DUMMY");
$tpl->set_block("content", "feedback_time_wrong", "DUMMY");
$tpl->set_var(array(
  "DUMMY" => "",
  "PORTAL_ID" => $portal->get_id(),
  "PORTAL_NAME" => $portal_name,
  "PORTLET_NAME" => $portlet_name,
  "PORTLET_ID" => $portlet->get_id(),
  "FEEDBACK" => "",

  "BUTTON_CANCEL_MISSION" => "",
  "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/portal2/portlets/appointment/edit.php",
  "BUTTON_CANCEL_ACTION" => "javascript:form_submit('', '$config_webserver_ip/modules/portal2/portlets/appointment/edit.php'); return false;",

  "BUTTON_MISSION" => "save",
  "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/appointment/appointment_edit.php"
));


//action
if($action == "save")
{
  $action = "save return(portlets/appointment/edit.php)";

  //build new content element
  $tmp = array(
    "topic" => norm_post("topic"),
    "location" => norm_post("location"),
    "start_date" => array("day" => $_POST["start_day"], "month" => $_POST["start_month"], "year" => $_POST["start_year"]),
    "start_time" => array("hour" => $_POST["start_hour"], "minutes" => $_POST["start_minutes"]),
    "end_date" => array("day" => $_POST["end_day"], "month" => $_POST["end_month"], "year" => $_POST["end_year"]),
    "description" => norm_post("description"),
    "linkurl" => norm_post("linkurl")
  );


  //verify input
  if(trim($tmp["topic"]) == "") {
    $tpl->parse("FEEDBACK", "feedback_headline_null", 1);
    $action = "";
  }
  if(!ereg("([0-9]{4})", $_POST["start_year"]) ||
     !ereg("([0-9]{1,2})", $_POST["start_month"]) || (int) $_POST["start_month"] < 1 || (int) $_POST["start_month"] > 12 ||
     !ereg("([0-9]{1,2})", $_POST["start_day"]) || (int) $_POST["start_day"] < 1 || (int) $_POST["start_day"] > date("t", mktime(0,0,0,(int) $_POST["start_month"],1,(int) $_POST["start_year"]))) {
    $tpl->parse("FEEDBACK", "feedback_startdate_wrong", 1);
    $action = "";
  }
  if($_POST["end_day"] != "" && $_POST["end_month"] != "" && $_POST["end_year"] != "" &&
     (!ereg("([0-9]{4})", $_POST["end_year"]) ||
     !ereg("([0-9]{1,2})", $_POST["end_month"]) || (int) $_POST["end_month"] < 1 || (int) $_POST["end_month"] > 12 ||
     !ereg("([0-9]{1,2})", $_POST["end_day"]) || (int) $_POST["end_day"] < 1 || (int) $_POST["end_day"] > date("t", mktime(0,0,0,(int) $_POST["end_month"],1,(int) $_POST["end_year"])))) {
    $tpl->parse("FEEDBACK", "feedback_enddate_wrong", 1);
    $action = "";
  }
  if($_POST["start_hour"] != "" && $_POST["start_minutes"] != "" &&
     (!ereg("([0-9]{1,2})", $_POST["start_hour"]) || (int) $_POST["start_hour"] < 0 || (int) $_POST["start_hour"] > 23 ||
     !ereg("([0-9]{1,2})", $_POST["start_minutes"]) || (int) $_POST["start_minutes"] < 0 || (int) $_POST["start_minutes"] > 59)) {
    $tpl->parse("FEEDBACK", "feedback_time_wrong", 1);
    $action = "";
  }


  if($appointment == "")
    $appointment = array_push($content, $tmp) - 1;
  else
    $content[$appointment] = $tmp;

  $portlet_content = $content;
  sort_appointments();
}


if($action == "new")
{
  $tpl->set_var(array(
    "APPOINTMENT_ID" => "",
    "TOPIC" => "",
    "LOCATION" => "",
    "START_DAY" => date("d"),
    "START_MONTH" => date("m"),
    "START_YEAR" => date("Y"),
    "END_DAY" => "",
    "END_MONTH" => "",
    "END_YEAR" => "",
    "START_HOUR" => "",
    "START_MINUTES" => "",
    "DESCRIPTION" => "",
    "LINKURL" => ""
  ));
}
else
{
  $tpl->set_var(array(
    "APPOINTMENT_ID" => $appointment,
    "TOPIC" => trim($content[$appointment]["topic"]),
    "LOCATION" => trim($content[$appointment]["location"]),
    "START_DAY" => ((isset($_POST["start_day"]))?$_POST["start_day"]:$content[$appointment]["start_date"]["day"]),
    "START_MONTH" => ((isset($_POST["start_month"]))?$_POST["start_month"]:$content[$appointment]["start_date"]["month"]),
    "START_YEAR" => ((isset($_POST["start_year"]))?$_POST["start_year"]:$content[$appointment]["start_date"]["year"]),
    "END_DAY" => ((isset($_POST["end_day"]))?$_POST["end_day"]:$content[$appointment]["end_date"]["day"]),
    "END_MONTH" => ((isset($_POST["end_month"]))?$_POST["end_month"]:$content[$appointment]["end_date"]["month"]),
    "END_YEAR" => ((isset($_POST["end_year"]))?$_POST["end_year"]:$content[$appointment]["end_date"]["year"]),
    "START_HOUR" => ((isset($_POST["start_hour"]))?$_POST["start_hour"]:$content[$appointment]["start_time"]["hour"]),
    "START_MINUTES" => ((isset($_POST["start_minutes"]))?$_POST["start_minutes"]:$content[$appointment]["start_time"]["minutes"]),
    "DESCRIPTION" => trim($content[$appointment]["description"]),
    "LINKURL" => trim($content[$appointment]["linkurl"])
  ));
}


$tpl->pparse("OUT", "content");


include("../../footer.php");

?>
