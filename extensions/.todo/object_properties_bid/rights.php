<?php

/****************************************************************************
 rights.php - display/set the user rights of an object
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

 Author: Thorsten Schäfer
 EMail: tms82@upb.de

 ****************************************************************************/

//include stuff:
require_once("./config/config.php");
require_once("$steamapi_doc_root/steam_connector.class.php");
require_once("$config_doc_root/classes/template.inc");
require_once("$config_doc_root/includes/sessiondata.php");

//******************************************************
//** Precondition
//******************************************************

$object_id = (isset($_GET["object"]))?$_GET["object"]:((isset($_POST["object"]))?$_POST["object"]:0);
$action = (isset($_POST["action"]))?$_POST["action"]:"";

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
/** object regarded in the rights dialog */
$object = ($object_id!=0)?steam_factory::get_object($steam, $object_id):$steamUser->get_workroom();

/** acquired rights (0=no rights are acquired) */
$acquire = $object->get_acquire(1);
/** sanctioned rights */
$sanction = $object->get_sanction(1);
/** creator of the object */
$creator = $object->get_creator(1);
/** user favourites */
$favorites = $steamUser->get_buddies(1);
/** additional required attributes */
$attrib = $object->get_attributes(array(OBJ_NAME, OBJ_DESC, "bid:doctype"),1);
/** the environment the steam object is contained in */
$environment = $object->get_environment();
/** rights of the environment */
$environmentSanction= ($environment == 0) ? 0 : $environment->get_sanction(1);

// flush the buffer
$result = $steam->buffer_flush();
$acquire = $result[$acquire];
$sanction = $result[$sanction];
$creator = $result[$creator];
$favorites = $result[$favorites];
$attrib = $result[$attrib];
$environmentSanction = ($environment == 0) ? 0 : $result[$environmentSanction];
/** name of the creator of the object */
$creatorName = $creator->get_name();
/** id of the creator of the object */
$creatorId = $creator->get_id();


/** groups of the logged in user */
$groups = $steamUser->get_groups();
/** group of all users */
$groupEveryone = steam_factory::groupname_to_object($steam, "everyone");
$groupEveryoneId = $groupEveryone == 0 ? 0 : $groupEveryone->get_id();
$groupEveryoneOnlyReadAccess = $groupEveryoneId != 0 && array_key_exists($groupEveryoneId, $sanction) && $sanction[$groupEveryoneId] == SANCTION_READ;

/**
 * name of the object from which rights are acquired.
 * Note: Bidowl does only support acquiring rights from the environment. If
 * the object acquires rights from another object, this will be overriden when
 * saving. Also the rights displayed in the dialog when the checkbox "rights
 * acquiration" is checked are always the rights of the environment even if
 * we do acquire rights from another object!
 */
$acquireName = ($environment == 0) ? "" : $environment->get_name() ;

// retrieve group details
$groupsMapping = array();
// add group everyone to the list of the user's groups */
if ($groupEveryone != 0 && !array_key_exists($groupEveryone->get_id(), $groupsMapping)) {
	$groupsMapping[$groupEveryone->get_id()] = getDisplayName($groupEveryone, 2);
}
foreach ($groups as $group)
{
	$groupsMapping[$group->get_id()] = getDisplayName($group, 2);
}

//  retrieve favorites details
//TODO: This for loop may be inefficient
$favoritesMapping = array();
foreach ($favorites as $favorite)
{
	$id = $favorite->get_id();
	//if (!array_key_exists($id, $groupsMapping))
	{
		$favoritesMapping[$favorite->get_id()] = $favorite->get_name();
	}
}

// prepare array for additional users
//TODO: This for loop may be inefficient.
$additionalMapping = array();
foreach ($sanction as $id => $sanct)
{
	if (!array_key_exists($id, $groupsMapping) &&
	!array_key_exists($id, $favoritesMapping) &&
	$id!=$creatorId && $id != 0 &&
	$id != $groupEveryoneId)
	{
		$additionalMapping[$id] = steam_factory::get_object($steam, $id)->get_name();
	}
}

// prepare array for additional users of the environment
//TODO: This for loop may be inefficient.
$additionalMappingEnvironment = array();
if ($environmentSanction) {
	foreach ($environmentSanction as $id => $sanct) {
		if (!array_key_exists($id, $groupsMapping) &&
		!array_key_exists($id, $favoritesMapping) &&
		$id!=$creatorId && $id != 0 &&
		$id != $groupEveryoneId)
		{
			$additionalMappingEnvironment[$id] = steam_factory::get_object($steam, $id)->get_name();
		}
	}
}

//******************************************************
//** Preparation for correct setting and displaying
//** of rights
//******************************************************
$bid_doctype = isset($attrib["bid:doctype"]) ? $attrib["bid:doctype"] : "";
$docTypeQuestionary = strcmp($attrib["bid:doctype"], "questionary") == 0;
$docTypeMessageBoard = $object instanceof steam_messageboard;

// in questionaries the write right is limited to insert rights only
if ($docTypeQuestionary) {
	$SANCTION_WRITE_FOR_CURRENT_OBJECT = SANCTION_INSERT;
}
// In message boards only annotating is allowed. The owner
// is the only one who can also write and change message
// board entries.
else if ($docTypeMessageBoard) {
	$SANCTION_WRITE_FOR_CURRENT_OBJECT = SANCTION_ANNOTATE;
}
// normal documents
else {
	$SANCTION_WRITE_FOR_CURRENT_OBJECT = SANCTION_WRITE | SANCTION_EXECUTE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE;
}
//TODO: Evaluate correct write rights for the environment
$SANCTION_WRITE_FOR_ENVIRONMENT_OBJECT = SANCTION_WRITE | SANCTION_EXECUTE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE;

$radioButtonPrivateChecked = !$acquire && (sizeof($sanction) == 1 && array_key_exists($creatorId, $sanction));
$radioButtonPublicChecked = !$acquire && (sizeof($sanction) == 2 ) && $groupEveryoneOnlyReadAccess;
//******************************************************
//** Save rights
//******************************************************
if($action == "save")
{
	//get the right arrays from the post data
	$post = $_POST;

	// publish is the radio button group which can have the values public, private or user
	$postPublish = (isset($post["publish"]))?$post["publish"]:array();

	// are the rights acquired from another object, i.e. is the acquire checkbox checked?
	$postAcquire = (isset($post["acquire"]))?$post["acquire"]:"";

	// unset post data (we do we have to do this?)
	unset($post["action"]);
	unset($post["publish"]);
	unset($post["acquire"]);

	// no rights are acquired hence we want to set the rights as defined in the dialog
	if (strcmp($postAcquire, "acquire") != 0) {
		// Grant full access to the creator of the object
		$object->sanction(SANCTION_READ | SANCTION_SANCTION | $SANCTION_WRITE_FOR_CURRENT_OBJECT, new steam_object($steam, $creatorId, CLASS_OBJECT), 1);
		$object->sanction_meta(SANCTION_ALL, new steam_object($steam, $creatorId, CLASS_OBJECT), 1);

		// merge the ids for all groups, favorites and the additional users with rights into one array
		if ($acquire) {
			$whole = array_merge (array_keys($groupsMapping), array_keys($favoritesMapping), array_keys($additionalMappingEnvironment));
		}
		else {
			$whole = array_merge (array_keys($groupsMapping), array_keys($favoritesMapping), array_keys($additionalMapping));
		}

		// Iterate over all entities in the array. Start with no rights
		// and then reassign rights according to the settings in the dialog.
		foreach ($whole as $id) {
			// don't change the rights of the owner, i.e. creator of the object
			if ($id==$creatorId) {
				continue;
			}

			// evaluate the new sanction to be set
			// by default no rights are granted
			$newSanct = ACCESS_DENIED;
			$newSanctMeta = ACCESS_DENIED;

			// grant read rights
			if (isset($post["read_". $id])) {
				$newSanct = SANCTION_READ;
			}

			// grant sanction rights and also consider the meta sanctions
			// i.e. which rights can be sanctioned
			if (isset($post["sanction_" . $id])) {
				$newSanct |= SANCTION_SANCTION;
				$newSanctMeta = SANCTION_ALL;
			}

			// grant write rights and consider special cases
			if (isset($post["write_" . $id])) {
				$newSanct |= $SANCTION_WRITE_FOR_CURRENT_OBJECT;
			}

			// set the new rights
			$object->sanction($newSanct, new steam_object($steam, $id, CLASS_OBJECT), 1);
			// set the new meta rights
			$object->sanction_meta($newSanctMeta, new steam_object($steam, $id, CLASS_OBJECT), 1);
		}
		// disable acquiration of rights from the environment
		$object->set_acquire(false, 1);
	}
	// if the "acquire" checkbox is checked we want to acquire
	// rights from the current environment
	else if (strcmp($postAcquire, "acquire") == 0) {
		// enable rights acquiration
		$object->set_acquire_from_environment(1);

		// remove all existing rights from the current object
		foreach ($sanction as $id => $sanct) {
			$object->sanction(ACCESS_DENIED, new steam_object($steam, $id, CLASS_OBJECT), 1);
			$object->sanction_meta(ACCESS_DENIED, new steam_object($steam, $id, CLASS_OBJECT), 1);
		}
	}

	// flush the buffers, i.e. set all the rights now and then
	// disconnect from the server
	$steam->buffer_flush();
	$steam->disconnect();

	// output the correct redirection / reload
	echo("<html>\n<body onload='javascript:window.location.href=\"$config_webserver_ip/properties.php?properties=" . $object->get_id () . "\";'>\n</body>\n</html>");
	exit;
}

// Disconnect
$steam->disconnect();

//******************************************************
//** Parse and display data
//******************************************************
$tpl = new Template("./templates/$language", "keep");
$tpl->set_file(array("content" => "rights.ihtml"));
$tpl->set_block("content", "group_none", "DUMMY");
$tpl->set_block("content", "group_none_acquire", "DUMMY");
$tpl->set_block("content", "group_row", "GROUP_ROW");
$tpl->set_block("content", "group_row_acquire", "GROUP_ROW_ACQUIRE");
$tpl->set_block("content", "favourite_none", "DUMMY");
$tpl->set_block("content", "favourite_none_acquire", "DUMMY");
$tpl->set_block("content", "favourite_row", "FAVOURITE_ROW");
$tpl->set_block("content", "favourite_row_acquire", "FAVOURITE_ROW_ACQUIRE");
$tpl->set_block("content", "favourite_row_double", "DUMMY");
$tpl->set_block("content", "additional_none", "DUMMY");
$tpl->set_block("content", "additional_none_acquire", "DUMMY");
$tpl->set_block("content", "additional_row", "ADDITIONAL_ROW");
$tpl->set_block("content", "additional_row_acquire", "ADDITIONAL_ROW_ACQUIRE");
$tpl->set_block("content", "additional_row_double", "DUMMY");
$tpl->set_block("content", "set_crude", "SET_CRUDE");
$tpl->set_block("content", "unset_crude", "UNSET_CRUDE");
$tpl->set_block("content", "test_specific_checked", "TEST_SPECIFIC_CHECKED");
$tpl->set_block("content", "test_specific_unchecked", "TEST_SPECIFIC_UNCHECKED");
$tpl->set_block("content", "set_everyone", "SET_EVERYONE");
$tpl->set_block("content", "unset_everyone", "UNSET_EVERYONE");
$tpl->set_block("content", "test_everyone_checked", "TEST_EVERYONE_CHECKED");
$tpl->set_block("content", "test_everyone_unchecked", "TEST_EVERYONE_UNCHECKED");

$tpl->set_var(array(
    "DUMMY" => "",
    "OBJECT_NAME" => getDisplayName($object),
	"OBJECT_ID" => $object_id,
	"ACQUIRE" => ($acquire == 0)?"":"CHECKED",
	"ACQUIRE_OBJECT_NAME" => $acquireName,
	"OWNER" => $creatorName,
    "SET_CRUDE" => "",
    "UNSET_CRUDE" => "",
    "TEST_SPECIFIC_CHECKED" => "",
    "TEST_SPECIFIC_UNCHECKED" => "",
	"SET_EVERYONE" => "",
	"UNSET_EVERYONE" => "",
    "TEST_EVERYONE_CHECKED" => "",
    "TEST_EVERYONE_UNCHECKED" => ""));

//******************************************************
//** Display groups
//******************************************************
if (sizeof($groupsMapping) > 0) {
	foreach ($groupsMapping as $id=>$name) {
		$tpl->set_var(array(
			"ITEM_ID" => $id,
			"GROUP_ID" => $id,
			"GROUP" => $name));	

		// display the rights of the environment in the disabled checkboxes which will be shown
		// if rights acquiration is enabled
		if ($environmentSanction) {
			$tpl->set_var(array(
          		"CHECKED_READ_ACQUIRE" => checkSanction($environmentSanction[$id], SANCTION_READ)?"CHECKED":"",
          		"CHECKED_WRITE_ACQUIRE" => checkSanction($environmentSanction[$id], $SANCTION_WRITE_FOR_ENVIRONMENT_OBJECT)?"CHECKED":"",
          		"CHECKED_SANCTION_ACQUIRE" => checkSanction($environmentSanction[$id], SANCTION_SANCTION)?"CHECKED":""));
		}

		// as we currently do acquire the rights from the environment, set the checkboxes accordingly.
		// should the user disable rights acquiration, the checkboxes will then display the rights
		// of the environment as a default value
		if ($acquire) {
			$tpl->set_var(array(
          		"CHECKED_READ" => checkSanction($environmentSanction[$id], SANCTION_READ)?"CHECKED":"",
          		"CHECKED_WRITE" => checkSanction($environmentSanction[$id], $SANCTION_WRITE_FOR_ENVIRONMENT_OBJECT)?"CHECKED":"",
          		"CHECKED_SANCTION" => checkSanction($environmentSanction[$id], SANCTION_SANCTION)?"CHECKED":""));			
		}
		// no rights acquiration, therefore set the checkboxes to the rights found in the sanctions array
		// of the current object
		else {
			$tpl->set_var(array(
				"CHECKED_READ" => checkSanction($sanction[$id], SANCTION_READ)?"CHECKED":"",
				"CHECKED_WRITE" => checkSanction($sanction[$id], $SANCTION_WRITE_FOR_CURRENT_OBJECT)?"CHECKED":"",
				"CHECKED_SANCTION" => checkSanction($sanction[$id], SANCTION_SANCTION)?"CHECKED":""));			
		}

		//parse group row
		$tpl->parse("GROUP_ROW", "group_row", true);
		$tpl->parse("GROUP_ROW_ACQUIRE", "group_row_acquire", true);

		//parse javascript
		if ($id != $groupEveryoneId && $id != $steamUserId) {
			$tpl->parse("SET_CRUDE", "set_crude", true);
			$tpl->parse("UNSET_CRUDE", "unset_crude", true);
			$tpl->parse("TEST_SPECIFIC_CHECKED", "test_specific_checked", true);
			$tpl->parse("TEST_SPECIFIC_UNCHECKED", "test_specific_unchecked", true);
		}
		else if ($id == $groupEveryoneId) {
			$tpl->parse("SET_EVERYONE", "set_everyone", true);
			$tpl->parse("UNSET_EVERYONE", "unset_everyone", true);
			$tpl->parse("TEST_EVERYONE_CHECKED", "test_everyone_checked", true);
			$tpl->parse("TEST_EVERYONE_UNCHECKED", "test_everyone_unchecked", true);
		}
	}
}
else
{
	$tpl->parse("GROUP_ROW", "group_none");
	$tpl->parse("GROUP_ROW_ACQUIRE", "group_none_acquire");
}

//******************************************************
//** Display favourites
//******************************************************
if (sizeof($favoritesMapping) > 0) {
	foreach ($favoritesMapping as $id=>$name) {
		$tpl->set_var(array(
			"ITEM_ID" => $id,
			"FAVOURITE_ID" => $id,
			"FAVOURITE" => $name));

		// display the rights of the environment in the disabled checkboxes which will be shown
		// if rights acquiration is enabled
		if ($environmentSanction) {
			$tpl->set_var(array(
          		"CHECKED_READ_ACQUIRE" => checkSanction($environmentSanction[$id], SANCTION_READ)?"CHECKED":"",
          		"CHECKED_WRITE_ACQUIRE" => checkSanction($environmentSanction[$id], $SANCTION_WRITE_FOR_ENVIRONMENT_OBJECT)?"CHECKED":"",
          		"CHECKED_SANCTION_ACQUIRE" => checkSanction($environmentSanction[$id], SANCTION_SANCTION)?"CHECKED":""));
		}

		// as we currently do acquire the rights from the environment, set the checkboxes accordingly.
		// should the user disable rights acquiration, the checkboxes will then display the rights
		// of the environment as a default value
		if ($acquire) {
			$tpl->set_var(array(
          		"CHECKED_READ" => checkSanction($environmentSanction[$id], SANCTION_READ)?"CHECKED":"",
          		"CHECKED_WRITE" => checkSanction($environmentSanction[$id], $SANCTION_WRITE_FOR_ENVIRONMENT_OBJECT)?"CHECKED":"",
          		"CHECKED_SANCTION" => checkSanction($environmentSanction[$id], SANCTION_SANCTION)?"CHECKED":""));			
		}
		// no rights acquiration, therefore set the checkboxes to the rights found in the sanctions array
		// of the current object
		else {
			$tpl->set_var(array(
				"CHECKED_READ" => checkSanction($sanction[$id], SANCTION_READ)?"CHECKED":"",
				"CHECKED_WRITE" => checkSanction($sanction[$id], $SANCTION_WRITE_FOR_CURRENT_OBJECT)?"CHECKED":"",
				"CHECKED_SANCTION" => checkSanction($sanction[$id], SANCTION_SANCTION)?"CHECKED":""));			
		}



		if ($id == $creatorId || array_key_exists($id, $groupsMapping)) {
			$tpl->parse("FAVOURITE_ROW", "favourite_row_double", true);
			$tpl->parse("FAVOURITE_ROW_ACQUIRE", "favourite_row_double", true);
		}
		else {
			// parse favourite row
			$tpl->parse("FAVOURITE_ROW", "favourite_row", true);
			$tpl->parse("FAVOURITE_ROW_ACQUIRE", "favourite_row_acquire", true);

			//parse javascript
			if ($id != $groupEveryoneId && $id != $steamUserId) {
				$tpl->parse("SET_CRUDE", "set_crude", true);
				$tpl->parse("UNSET_CRUDE", "unset_crude", true);
				$tpl->parse("TEST_SPECIFIC_CHECKED", "test_specific_checked", true);
				$tpl->parse("TEST_SPECIFIC_UNCHECKED", "test_specific_unchecked", true);
			}
		}
	}
}
else
{
	$tpl->parse("FAVOURITE_ROW", "favourite_none");
	$tpl->parse("FAVOURITE_ROW_ACQUIRE", "favourite_none_acquire");
}

//******************************************************
//** Display additional rights section
//******************************************************

// As we don't acquire rights from the environment, show
// the sanctioned rights of the object. Only additional groups
// found in the sanction array of the current object will be
// displayed.
if (!$acquire) {
	if (sizeof($additionalMapping) > 0) {
		foreach ($additionalMapping as $id=>$name) {
			if ($id != $creatorId) {
				$tpl->set_var(array(
			"ITEM_ID" => $id,
			"ADDITIONAL_ID" => $id,
			"ADDITIONAL" => $name,
			"CHECKED_READ" => checkSanction($sanction[$id], SANCTION_READ)?"CHECKED":"",
			"CHECKED_WRITE" => checkSanction($sanction[$id], $SANCTION_WRITE_FOR_CURRENT_OBJECT)?"CHECKED":"",
			"CHECKED_SANCTION" => checkSanction($sanction[$id], SANCTION_SANCTION)?"CHECKED":""));

				// parse additional row
				$tpl->parse("ADDITIONAL_ROW", "additional_row", true);

				//parse javascript
				if ($id != $steamUserId && $id != $groupEveryoneId) {
					$tpl->parse("SET_CRUDE", "set_crude", true);
					$tpl->parse("UNSET_CRUDE", "unset_crude", true);
					$tpl->parse("TEST_SPECIFIC_CHECKED", "test_specific_checked", true);
					$tpl->parse("TEST_SPECIFIC_UNCHECKED", "test_specific_unchecked", true);
				}
			}
		}
	}
	else
	{
		$tpl->parse("ADDITIONAL_ROW", "additional_none");
	}
}

// If we consider the rights of the environment, there may be
// different "additional rights" set as in the sanction array of
// the current object. These have to be displayed if rights acquiration
// is active when the dialog is opened, or if it is activated later on.
if (sizeof($additionalMappingEnvironment) > 0) {
	foreach ($additionalMappingEnvironment as $id=>$name) {
		if ($id != $creatorId) {
			$tpl->set_var(array(
			"ITEM_ID" => $id,
			"ADDITIONAL_ID" => $id,
			"ADDITIONAL" => $name,
			"CHECKED_READ_ACQUIRE" => checkSanction($environmentSanction[$id], SANCTION_READ)?"CHECKED":"",
			"CHECKED_WRITE_ACQUIRE" => checkSanction($environmentSanction[$id], $SANCTION_WRITE_FOR_ENVIRONMENT_OBJECT)?"CHECKED":"",
			"CHECKED_SANCTION_ACQUIRE" => checkSanction($environmentSanction[$id], SANCTION_SANCTION)?"CHECKED":""));

			// as we currently do acquire the rights from the environment, set the checkboxes accordingly.
			// should the user disable rights acquiration, the checkboxes will then display the rights
			// of the environment as a default value
			if ($acquire) {
				$tpl->set_var(array(
				"CHECKED_READ" => checkSanction($environmentSanction[$id], SANCTION_READ)?"CHECKED":"",
				"CHECKED_WRITE" => checkSanction($environmentSanction[$id], $SANCTION_WRITE_FOR_ENVIRONMENT_OBJECT)?"CHECKED":"",
				"CHECKED_SANCTION" => checkSanction($environmentSanction[$id], SANCTION_SANCTION)?"CHECKED":""));			
				$tpl->parse("ADDITIONAL_ROW", "additional_row", true);
			}

			// parse additional row
			$tpl->parse("ADDITIONAL_ROW_ACQUIRE", "additional_row_acquire", true);

			//parse javascript
			if ($acquire && $id != $steamUserId && $id != $groupEveryoneId) {
				$tpl->parse("SET_CRUDE", "set_crude", true);
				$tpl->parse("UNSET_CRUDE", "unset_crude", true);
				$tpl->parse("TEST_SPECIFIC_CHECKED", "test_specific_checked", true);
				$tpl->parse("TEST_SPECIFIC_UNCHECKED", "test_specific_unchecked", true);
			}
		}
	}
}
else
{
	$tpl->parse("ADDITIONAL_ROW_ACQUIRE", "additional_none_acquire");
	if ($acquire) {
		$tpl->parse("ADDITIONAL_ROW", "additional_none");
	}
}

out();

function out()
{
	//parse all out
	global $tpl;
	$tpl->parse("OUT", "content");
	$tpl->p("OUT");

	exit;
}

function checkSanction($sanctionDec, $SANCTION) {
	return ($sanctionDec & $SANCTION) == $SANCTION;
}

/**
 * getDisplayName: Evaluates the name of the object as it should be
 * displayed by the Bidowl GUI
 * obj should be already buffered
 * TODO: Replace "Lesezeichen, Papierkorb" with a language template variable
 */
function getDisplayName($obj, $TYPE = 0) {
	if ($TYPE == 0) {
		// first try to use the description as display name
		$result = getDisplayName($obj, 1);
		// if the description is empty, use the object name
		if ($result == "") {
			$result = getDisplayName($obj, 2);
		}
		return $result;
	}
	else if ($TYPE == 1) {
		$result = $obj->get_attribute(OBJ_DESC);
		$result = str_replace("s workroom.", "", $result);
		$result = str_replace("s workroom", "", $result);
		$result = str_replace("Trashbin", "Papierkorb", $result);
		$result = str_replace("Everyone", "Jeder", $result);
		return $result;
	}
	else if ($TYPE == 2) {
		$result = $obj->get_attribute(OBJ_NAME);
		$result = str_replace("'s workarea", "", stripslashes($result));
		$result = preg_replace("/.*'s bookmarks/", "Lesezeichen", $result);
		$result = str_replace("trashbin", "Papierkorb", $result);
		$result = str_replace("Everyone", "Jeder", stripslashes($result));
		$result = str_replace("sTeam", "Registrierte Benutzer", stripslashes($result));
		return $result;
	}
}

?>