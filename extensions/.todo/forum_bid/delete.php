<?php

/****************************************************************************
 delete.php -
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

 Author: Stephanie Sarach
 EMail: haribo@upb.de

 ****************************************************************************/

//******************************************************
//** includes
//******************************************************
require_once("../../config/config.php");
require_once("$steamapi_doc_root/steam_connector.class.php");
require_once("$config_doc_root/classes/template.inc");
require_once("$config_doc_root/includes/sessiondata.php");
require_once("../../includes/norm_post.php");

//******************************************************
//** Get Post data
//******************************************************
$object_id = isset($_GET["object"]) ? $_GET["object"]: 0;
$object_id = isset($_POST["object"]) ? $_POST["object"]: $object_id;
$action = isset($_GET["action"]) ? $_GET["action"] : 0;
$forum_id = isset($_GET["forumobject"]) ? $_GET["forumobject"]: 0;
$forum_id = isset($_POST["forumobject"]) ? $_POST["forumobject"]: $forum_id;
$redirect_to_forum = isset($_GET["redirect_to_forum"]) ? $_GET["redirect_to_forum"]: false;
$redirect_to_forum = isset($_POST["redirect_to_forum"]) ? $_POST["redirect_to_forum"]: $redirect_to_forum;

//******************************************************
//** sTeam Server Connection
//******************************************************
$steam = new steam_connector($config_server_ip, $config_server_port, $login_name, $login_pwd);

if( !$steam || !$steam->get_login_status())
{
	header("Location: $config_webserver_ip/index.html");
	exit();
}

/** log-in user */
$steamUser =  $steam->get_login_user();
/** id of the log-in user */
$steamUserId = $steamUser == 0 ? 0 : $steamUser->get_id();

/** the current object */
$object = ($object_id!=0)?steam_factory::get_object($steam, $object_id):0;
$annotations = $object->get_annotations();
$trash = $steamUser->get_attribute(USER_TRASHBIN, 1);
$annotating = $object->get_annotating(1);
$result = $steam->buffer_flush();
$trash = $result[$trash];
$annotating = $result[$annotating];

if($action=="delete"){
	if(!empty($annotations)){
		foreach($annotations as $annotation){
			$annotation->delete(1);		
		}
	}
	$object->delete(1);
	$steam->buffer_flush();
}

if($action=="trash"){
	//move objects to trashbin
	if (is_object($trash)) {
		if ($annotating) {
			$annotating->remove_annotation($object, 1);
			$annotating->set_acquire(False, 1);
		}
		$object->move($trash,1);
		$steam->buffer_flush();
	}
}

$steam->disconnect();

if ($redirect_to_forum)
{
	echo("<html>\n<body onload='javascript:opener.top.location.replace(\"$config_webserver_ip/index.php?object=" .$forum_id . "\");window.close();'>\n</body>\n</html>");	
}
else
{
	echo("<html><body onload='javascript:opener.top.location.reload();window.close();'></body></html");
}	
exit;

?>
