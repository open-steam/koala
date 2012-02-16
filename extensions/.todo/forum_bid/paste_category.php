<?php

/****************************************************************************
 paste_category.php - paste a thread into a message board
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

 Author: Thorsten Schäfer <tms82@upb.de>

 ****************************************************************************/

//include stuff
require_once("../../config/config.php");
require_once("$steamapi_doc_root/steam_connector.class.php");
require_once("$config_doc_root/classes/template.inc");
require_once("$config_doc_root/includes/sessiondata.php");

//******************************************************
//** Presumption
//******************************************************
$forum_id = (isset($_GET["forumobject"]))?$_GET["forumobject"]:((isset($_POST["forumobject"]))?$_POST["forumobject"]:"");

//******************************************************
//** sTeam Stuff
//******************************************************

//login und $steam def. in "./includes/login.php"
$steam = new steam_connector(
	$config_server_ip,
	$config_server_port,
	$login_name,
	$login_pwd);

if( !$steam || !$steam->get_login_status() )
{
	header("Location: $config_webserver_ip/index.html");
	exit();
}

//current room steam object
$forum = ($forum_id != 0)?steam_factory::get_object($steam, $forum_id):0;
/* the logged-in user */
$steamUser = $steam->get_login_user();

//get permissions
$readable = $forum->check_access_read($steamUser);
$writable = $forum->check_access_write($steamUser);

if(!$writable || !isset($forum))
	die("Einf&uuml;gen nicht m&ouml;glich!<br>");

$backpack = $steamUser->get_inventory(CLASS_DOCUMENT);

/*
 * Determine if the backpack contains threads, which could be pasted into this 
 * message board. Paste only documents with OBJ_TYPE == "text_forumthread_bid"
 */
foreach($backpack as $entry) {
	$entry->get_attributes(array("OBJ_TYPE"), 1);
}
$result = $steam->buffer_flush();

foreach($backpack as $entry) {
	$entryObjType = $entry->get_attribute("OBJ_TYPE");
	if (is_string($entryObjType) && ($entryObjType === "text_forumthread_bid")) {
		$entry->move(False, 1);
		$forum->add_annotation($entry, 1);
		$entry->set_acquire($forum, 1);
	}
}
$steam->buffer_flush();

//Logout & Disconnect
$steam->disconnect();

// redirect to forum index
echo("<html><body onload='javascript:opener.top.location.reload();window.close();'></body></html");
exit;

?>
