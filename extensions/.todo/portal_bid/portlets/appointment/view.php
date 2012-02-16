<?php

  /****************************************************************************
  view.php - view the appointment portlet
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

  Author: Henrik Beige, Harald Selke
  EMail: hebeige@gmx.de, hase@uni-paderborn.de

  ****************************************************************************/

$tmpl = new Template("./portlets/appointment/templates/$language", "keep");
$tmpl->set_file("content", "view.ihtml");
$tmpl->set_block("content", "edit_button", "DUMMY");
$tmpl->set_block("content", "linkurl", "DUMMY");
$tmpl->set_block("content", "separator", "DUMMY");
$tmpl->set_block("content", "enddate_row", "ENDDATE_ROW");
$tmpl->set_block("content", "time_row", "TIME_ROW");
$tmpl->set_block("content", "location_row", "LOCATION_ROW");
$tmpl->set_block("content", "description_row", "DESCRIPTION_ROW");
$tmpl->set_block("content", "appointment_row", "APPOINTMENT_ROW");
$tmpl->set_var(array(
  "DUMMY" => "",
  "EDIT_BUTTON" => "",
  "PORTLET_ROOT" => "$config_webserver_ip/modules/portal2/portlets/appointment",
  "PORTLET_ID" => $portlet->get_id(),
  "APPOINTMENT_NAME" => $portlet_name
));

if ($portlet->check_access_write($steam->get_login_user()))
  $tmpl->parse("EDIT_BUTTON", "edit_button");

if(sizeof($content) > 0)
{
  foreach($content as $appointment)
  {
    $tmpl->set_var(array(
      "TOPIC" => $UBB->encode($appointment["topic"]),
      "LINK" => "",
      "LINKURL" => derive_url($appointment["linkurl"]),
      "STARTDATE" => $appointment["start_date"]["day"] . "." . $appointment["start_date"]["month"] . "." . $appointment["start_date"]["year"],
      "ENDDATE" => $appointment["end_date"]["day"] . "." . $appointment["end_date"]["month"] . "." . $appointment["end_date"]["year"],
      "ENDDATE_ROW" => "",
      "TIME" => $appointment["start_time"]["hour"]. "." . $appointment["start_time"]["minutes"],
      "TIME_ROW" => "",
      "LOCATION" => $UBB->encode($appointment["location"]),
      "LOCATION_ROW" => "",
      "DESCRIPTION" => $UBB->encode($appointment["description"]),
      "DESCRIPTION_ROW" => ""
    ));

    if(trim($appointment["location"]) != "")
      $tmpl->parse("LOCATION_ROW", "location_row");

    if($appointment["end_date"]["day"] != "")
      $tmpl->parse("ENDDATE_ROW", "enddate_row");

    if($appointment["start_time"]["hour"] != "")
      $tmpl->parse("TIME_ROW", "time_row");

    if(trim($appointment["description"]) != "")
      $tmpl->parse("DESCRIPTION_ROW", "description_row");

    if(trim($appointment["linkurl"]) != "")
      $tmpl->parse("LINK", "linkurl");

    $tmpl->parse("APPOINTMENT_ROW", "appointment_row", 1);
  }

}
else
 $tmpl->set_var("APPOINTMENT_ROW", "");

$tmpl->pparse("OUT", "content");

?>