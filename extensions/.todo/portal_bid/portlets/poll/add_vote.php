<?php
  /****************************************************************************
  add_vote.php - add a vote in the poll portlet
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

  //include stuff
  require_once("../../../../config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("../../../../classes/template.inc");
  require_once("$config_doc_root/config/language_map.php");
  require_once("../../../../includes/sessiondata.php");

  $portlet_id = (isset($_REQUEST["portlet"]))?$_REQUEST["portlet"]:"";
  $vote = (isset($_REQUEST["action"]))?$_REQUEST["action"]:"";

  //login und $steam def. in "./includes/login.php"
  $steam = new steam_connector(	$config_server_ip,
  								$config_server_port,
  								$login_name,
  								$login_pwd);
  								
  if( !$steam || !$steam->get_login_status() )
  {
    header("Location: $config_webserver_ip/index.html");
    exit();
  }

  // $steamUser = $steam->get_login_user();
  if( $portlet_id != 0 ) $portlet = steam_factory::get_object($steam, $portlet_id);
  $votes = $portlet->get_attribute("bid:portlet:content");
  if ($votes["options_votecount"][$vote] == "") $votes["options_votecount"][$vote] = 0;
  $votes["options_votecount"][$vote] += 1;
  $portlet->set_attribute("bid:portlet:content", $votes);
  
print ('Die Stimme wurde gez&auml;hlt.');
print ('<form><button onclick="window.close();">OK</button></form>');

return;

if(sizeof($content) > 0)
{
  $start_date = $content["start_date"];
  $end_date = $content["end_date"];

  if (time() > mktime(0, 0, 0, $start_date["month"], $start_date["day"], $start_date["year"]) &&
      time() < mktime(24, 0, 0, $end_date["month"], $end_date["day"], $end_date["year"]))
    $poll_active = true;
  else
    $poll_active = false;

  $options = $content["options"];
  $options_votecount = $content["options_votecount"];
  $options_votecount[0] = 6;
  $options_votecount[1] = 12;
  $options_votecount[3] = 8;
  $max_votecount = 1;
  foreach($options_votecount as $option_votecount)
    if ($option_votecount > $max_votecount) $max_votecount = $option_votecount;

  $tmpl->set_var(array(
    "DUMMY" => "",
    "EDIT_BUTTON" => "",
    "VOTE_BUTTON" => "",
    "PORTLET_ROOT" => $config_webserver_ip . "/modules/portal2/portlets/poll",
    "PORTLET_ID" => $portlet->get_id(),
    "POLL_NAME" => $portlet_name,
    "POLL_TOPIC" => $content["poll_topic"]
  ));

  if ($poll_active) {
    $tmpl->parse("RESULT", "", 1);
    $tmpl->parse("VOTE_BUTTON", "vote_button");
    $i=0;
    foreach($options as $option) {
      if ($option != "") {
        $tmpl->set_var(array(
          "OPTION" => $option,
          "OPTION_NUMBER" => $i,
        ));
        $tmpl->parse("CHOICE", "choice", 1);
      }
      $i++;
    }
  }
  else  {
    $tmpl->parse("CHOICE", "", 1);
    $i=0;
    foreach($options as $option) {
      if ($option != "") {
        $tmpl->set_var(array(
          "OPTION" => $option,
          "OPTION_VOTECOUNT" => $options_votecount[$i],
          "OPTION_NUMBER" => $i,
          "WIDTH" => $options_votecount[$i] / $max_votecount * 100
        ));
        $tmpl->parse("RESULT", "result", 1);
      }
      $i++;
    }
  }

  // we show the edit button only if the user has write access to the portal
  // because all portal readers need write access in order to vote
  if ($portal->check_access_write($steam->get_login_user()))
    $tmpl->parse("EDIT_BUTTON", "edit_button");
}

$tmpl->parse("OUT", "content");

$tmpl->p("OUT");

?>
