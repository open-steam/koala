<?php

  /****************************************************************************
  footer.php - footer include of the portlet specific edit scripts
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

  //end output buffering
  $output = ob_get_contents();
  ob_end_clean();


  //on save action, set new portlet content
  if(strpos($action, "save") !== false)
  {
    array_walk($portlet_content, "_stripslashes");
    array_walk($portlet_content, "_addslashes");
    $success = $portlet->set_attribute("bid:portlet:content", $portlet_content);
//    $action = str_replace("save", "", $action);
  }

  //Logout & Disconnect
  $steam->disconnect();

  //On successfull save or return action, get back to portlet "edit.php"
  $location = "";
  if(isset($success) && $success && strpos($action, "return") === false)
    $location = "$config_webserver_ip/modules/portal2/portlets/$portlet_type/edit.php?portlet=" . $portlet->get_id() . "&type=edit";

//  if(strpos($action, "return") !== false)
//    $location = "$config_webserver_ip/modules/portal2/portlet_edit.php?portal=" . $portal->id . "&type=edit";

  if(ereg(".*return\((.*)\)",$action, $regs))
    $location = $config_webserver_ip . "/modules/portal2/" . $regs[1] . "?portal=" . $portal->get_id() . "&portlet=" . $portlet->get_id() . "&type=edit";

  //redirect on order
  if($location !== "")
  {
    echo("<html>\n<body onload='javascript:opener.top.location.reload();window.close();'>\n</body>\n</html>");
    exit;
  }

  //output all
  echo($output);

?>