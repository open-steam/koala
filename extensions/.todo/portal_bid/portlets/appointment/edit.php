<?php
  /****************************************************************************
  edit.php - entry point to edit the appointment portlet
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

//save stuff
print $action;
if($action == "save")
{
  $old_order = $portlet->get_attribute("bid:portlet:app:app_order");
  $portlet->set_attribute("bid:portlet:app:app_order",
    $_POST["app_order"]);
  if (isset($old_order) && $old_order != $_POST["app_order"]) {
    if(is_array($content) && sizeof($content) > 0) {
      sort_appointments();
    }
  }
}
if($action == "delete")
{
  echo("DELETEEEEEE<br>");
  if(isset($_GET["appointment"]))
    unset($content[(int) $_GET["appointment"]]);

  $portlet_content = $content;
  $action = "save";
}


//display stuff
$tpl = new Template("./templates/$language", "keep");
$tpl->set_file("content", "edit.ihtml");
$tpl->set_block("content", "button_label_new", "DUMMY");
$tpl->set_block("content", "appointment_null", "DUMMY");
$tpl->set_block("content", "appointment_row", "APPOINTMENT_ROW");
$tpl->set_block("content", "button_mission", "BUTTON_MISSION_ROW");
$tpl->set_var(array(
  "DUMMY" => "",
  "PORTAL_ID" => $portal->get_id(),
  "PORTAL_NAME" => $portal_name,
  "PORTLET_ID" => $portlet->get_id(),
  "PORTLET_NAME" => $portlet_name,

  "BUTTON_CANCEL_MISSION" => "save",
  "BUTTON_CANCEL_URL" => "",
  "BUTTON_CANCEL_ACTION" => "javascript:form_submit('save','$config_webserver_ip/modules/portal2/portlets/appointment/edit.php'); return false;",

  "APPOINTMENT_ORDERING_EARLIEST_FIRST_CHECKED" => "",
  "APPOINTMENT_ORDERING_LATEST_FIRST_CHECKED" => ""
));

//parse "new" button
$tpl->set_var(array(
  "BUTTON_MISSION" => "new",
  "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/appointment/appointment_edit.php"
));
$tpl->parse("BUTTON_LABEL", "button_label_new");
$tpl->parse("BUTTON_MISSION_ROW", "button_mission", true);


if(is_array($content) && sizeof($content) > 0)
{
  foreach($content as $key => $appointment)
  {
    $tpl->set_var(array(
      "APPOINTMENT_ID" => $key,
      "APPOINTMENT_NAME" => $appointment["topic"]
    ));
    $tpl->parse("APPOINTMENT_ROW", "appointment_row", 1);
  }
}
else
  $tpl->parse("APPOINTMENT_ROW", "appointment_null");

// Set radio button for ordering of appointments
$app_order = $portlet->get_attribute("bid:portlet:app:app_order");
if ($app_order == null || $app_order == "earliest_first") {
  $tpl->set_var(array(
    "APPOINTMENT_ORDERING_EARLIEST_FIRST_CHECKED" => "checked"
  ));
}
else if ($app_order == "latest_first") {
  $tpl->set_var(array(
    "APPOINTMENT_ORDERING_LATEST_FIRST_CHECKED" => "checked"
  ));
}

$tpl->pparse("OUT", "content");


include("../../footer.php");

?>
