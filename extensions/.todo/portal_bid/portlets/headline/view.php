<?php
  /****************************************************************************
  view.php - view the headline portlet
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

if(sizeof($content) > 0)
{
  $tmpl = new Template("./portlets/headline/templates/$language", "keep");
  $tmpl->set_file("content", "view.ihtml");
  $tmpl->set_block("content", "edit_button", "DUMMY");

  $tmpl->set_var(array(
    "DUMMY" => "",
    "EDIT_BUTTON" => "",
    "PORTLET_ROOT" => $config_webserver_ip . "/modules/portal2/portlets/headline",
    "PORTLET_ID" => $portlet->get_id(),
    "ALIGNMENT" => trim($content["alignment"]),
    "HEADLINE" => $UBB->encode($content["headline"]),
    "SIZE" => trim($content["size"])
  ));

  if ($portlet->check_access_write($steam->get_login_user()))
    $tmpl->parse("EDIT_BUTTON", "edit_button");

  $tmpl->parse("OUT", "content");

  $tmpl->p("OUT");
}
else
 echo("");

?>
