<?php

  /****************************************************************************
  session_data.php - do the login, get all data, store them in session
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

/* 
 * TS: Due to the necessity to save bid-owl specific session times, 
 * sessiondata.php requires that the invoking php file first includes 
 * config.php
 */

require_once("$steamapi_doc_root/steam_connector.class.php");

session_name("bidowl_session");
session_start();

// logout
if( isset($_POST["logout"]) && $_POST["logout"] ){
	session_destroy();
	header("Location: $config_webserver_ip/index.html");
    setBidLastSessionTime($config_server_ip, $config_server_port, $_SESSION["login_name"], $_SESSION["login_pwd"], false);
    exit();
}
// login
else if( isset($_POST["login"]) && $_POST["login"] ){
	if( isset($_SESSION["performe_login"]) && $_SESSION["performe_login"] == "do" && strlen($_SERVER["PHP_AUTH_USER"]) > 0 ){
		$_SESSION["performe_login"] = "done";
		$_SESSION["login_name"] = $_SERVER["PHP_AUTH_USER"];
 		$_SESSION["login_pwd"] = $_SERVER["PHP_AUTH_PW"];

		setBidLastSessionTime($config_server_ip, $config_server_port, $_SESSION["login_name"], $_SESSION["login_pwd"]);
	}
	else{
		$_SESSION["performe_login"] = "do";
		Header( "WWW-Authenticate: Basic realm=\"bid-owl\"" );
		Header( "HTTP/1.0 401 Unauthorized" );

        if (isset($sessionLoginFailureAction) && $sessionLoginFailureAction === "closeSubWindow") {
          echo("<html><body onload='javascript:if (opener) opener.top.location.reload();window.close();'></body></html>");
          exit;
        }
        else {
            die("<html>\n" .
                "<head>\n" .
                "  <meta http-equiv=\"refresh\" content=\"1\"; URL=\"$config_webserver_ip/index.html\">\n" .
                "</head>\n" .
                "<body>\n" .
                "<script type=\"text/javascript\">\n" .
                "  location.href = \"$config_webserver_ip/index.html\"\n" .
                "</script>\n" .
                "</body>\n" .
                "</html>");
        }
	}
}

$login_name = isset($_SESSION["login_name"])?$_SESSION["login_name"]:"guest";
$login_pwd = isset($_SESSION["login_pwd"])?$_SESSION["login_pwd"]:"guest";

###
# user is loged in or not

//Current Object
if(isset($_GET["object"]) && $_GET["object"] != "0")
  $object = $_GET["object"];
else if(isset($_SESSION["object"]) && $_SESSION["object"] != "0")
  $object = $_SESSION["object"];
else
  $object = "0";

$_SESSION["object"] = $object;


// Language Settings
if(isset($_GET["language"]) && is_dir("./templates/" . $_GET["language"]))
  $language = $_GET["language"];
else if(isset($_SESSION["language"]) && is_dir("./templates/" . $_SESSION["language"]))
  $language = $_SESSION["language"];
else
  $language = "ge";

$_SESSION["language"] = $language;

//Treeview Status
if(isset($_GET["treeview"]))
  $treeview = ($_GET["treeview"] == "on")?true:false;
else if(isset($_SESSION["treeview"]))
  $treeview = $_SESSION["treeview"];

if(isset($treeview))
  $_SESSION["treeview"] = $treeview;


//Treeview Mininmal Display
if(isset($_GET["treeview_mini"]))
  $treeview_mini = ($_GET["treeview_mini"] == "on")?true:false;
else if(isset($_SESSION["treeview_mini"]))
  $treeview_mini = $_SESSION["treeview_mini"];

if(isset($treeview_mini))
  $_SESSION["treeview_mini"] = $treeview_mini;


//Show Hidden Status
if(isset($_GET["show_hidden"]))
  $show_hidden = ($_GET["show_hidden"] == "on")?true:false;
else if(isset($_SESSION["show_hidden"]))
  $show_hidden = $_SESSION["show_hidden"];

if(isset($show_hidden))
  $_SESSION["show_hidden"] = $show_hidden;

/* 
 * This function saves the last time the user actively used the system. This 
 * is calculated as follows: The attribute "bid:last_session_time" contains a 
 * two-dimensional array. If the user is logging-off ($connect=false) then both 
 * bid:session_time entries are set to the current unix time stamp. If the 
 * user is logging-on ($connect=true) the second time stamp replaces the first 
 * one and is set to the current unix time stamp afterwards. 
 *
 * bid:session_time[0] contains either the last time the user logged-off or in 
 * the case he didn't log-off correctly the last time he logged-in before the 
 * current log-in.
 */
function setBidLastSessionTime($config_server_ip,$config_server_port,$login_name,$login_pwd,$connect=true) {
  try {
    //login und $steam def. in "./includes/login.php"
    $steam = new steam_connector($config_server_ip,
		  		$config_server_port,
				$login_name,
				$login_pwd);

    if ($steam->get_login_status()) {
      $steamUser = $steam->get_login_user();
      $bidLastSessionTime = $steamUser->get_attribute("bid:last_session_time");
      if (!$bidLastSessionTime || !$connect) {
	$bidLastSessionTime = array();
	$bidLastSessionTime[0] = $bidLastSessionTime[1] = time();
      } else {
	$bidLastSessionTime[0] = $bidLastSessionTime[1];
	$bidLastSessionTime[1] = time();
      }
      $steamUser->set_attribute("bid:last_session_time", $bidLastSessionTime);
      $steam->disconnect();
    }
  }
  catch (Exception $e) {}
}

?>
