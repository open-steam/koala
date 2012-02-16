<?php
  /****************************************************************************
  backpack.php - all function that include cut/copy/paste of files in the CMS
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

  Modifications by hase, 29.04.2005:
  Line 118: "Link to " replaced by German translation
  To do: Integrate into multi-language environment
  ****************************************************************************/

  //include stuff
  require_once("./config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
//  require_once("$config_doc_root/classes/debugHelper.php");

  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/modules/portal/copy.php");

  //******************************************************
  //** Presumption
  //******************************************************

  $object = (isset($_GET["object"]))?$_GET["object"]:"";
  $type = (isset($_POST["type"]))?$_POST["type"]:"";

  //******************************************************
  //** sTeam Stuff
  //******************************************************

  //login und $steam def.
  $steam = new steam_connector(	$config_server_ip,
  								$config_server_port,
  								$login_name,
  								$login_pwd);
  								
  if( !$steam || !$steam->get_login_status() )
  {
    header("Location: $config_webserver_ip/index.html");
    exit();
  }


  //create steam_object
  $object = steam_factory::get_object( $steam, $object );


  //build array of marked objects
  $objects = array();
  foreach($_POST as $marked => $tmp)
  {
    if(strstr($marked, "marked_") !== false) {
	  if (!(strpos($marked, "up") != false ||  strpos($marked, "down") != false))	{
        $obj = steam_factory::get_object( $steam, substr($marked, 7) );
	$obj->get_attributes(array(OBJ_NAME, OBJ_DESC),1);
	$objects[] = $obj;
	  }
	}
  }
  $result = $steam->buffer_flush();

  //process actions
  switch($type)
  {
    case "copy":
      //duplicate objects
      foreach($objects as $obj)
      {
        if( $obj->get_attribute("bid:doctype") === "portal" )
        {
            $copy = copy_portal($steam, $obj );
            $copy->move( $steam->get_login_user() );
        }
        else
		  if ($obj instanceof steam_link) { 
			$copy = steam_factory::create_link( $steam, $obj->get_link_object() );
		  }
		  else {
            $copy = steam_factory::create_copy( $steam, $obj );
		  }
          $copy->move( $steam->get_login_user() );
      }
      break;

    case "move":
      //move objects to backpack
      foreach($objects as $obj)
      	$obj->move( $steam->get_login_user() );
      break;

    case "delete":
      //move objects to trashbin
      $user = $steam->get_login_user();
      $trash = $user->get_attribute(USER_TRASHBIN);
      if (is_object($trash))
        foreach($objects as $obj)
          $obj->move( $trash );
      break;

    case "destroy":
      //really delete objects
      foreach($objects as $obj)
        rec_delete_object( $obj );
      break;

    case "reference":
      //get object names
      foreach($objects as $obj){
      	$link = steam_factory::create_link( $steam, $obj );
	$link->set_attributes(array(OBJ_DESC => $obj->get_attribute(OBJ_DESC)));
      	$link->move( $steam->get_login_user() );
      }
      break;
      
    case "bookmark":
      //get object names
      foreach($objects as $obj){
		    if($obj instanceof steam_link) {
				$link = steam_factory::create_link( $steam, $obj->get_link_object() );
			}
			else if ($obj instanceof steam_docextern) {
			    $link = steam_factory::create_copy( $steam, $obj );
			}
			else {
	  		$link = steam_factory::create_link( $steam, $obj );
			}
			$link->set_attribute(OBJ_DESC,  $obj->get_attribute( OBJ_DESC ) );
			$link->set_attribute(DOC_MIME_TYPE,  $obj->get_attribute( DOC_MIME_TYPE ) );
			$link->move( $steam->get_login_user()->get_attribute(USER_BOOKMARKROOM) );
	  }
      break;

    case "drop":
      //move objects to current room
      $inventory = $steam->get_login_user()->get_inventory();
      foreach( $inventory as $item )
      {
        if( $item->get_attribute("bid:portlet") === 0 ){
        	$item->move( $object );
        	steamUniqObjName($item);
        }
      }
      break;
      
  }

  //Logout & Disconnect
  $steam->disconnect();


  //******************************************************
  //** Display Stuff
  //******************************************************

?>


<html>

<script type="text/javascript">
<!--
function urefresh()
{
  parent.top.location.href="<?php echo $config_webserver_ip . "/index.php?object=" . $object->get_id() ; ?>";
}
//-->
</script>

<body onload='javascript:urefresh();'>
</body>

</html>

<?php

//delete a single object and all linked objects
function rec_delete_object( $object )
{
  //get all references from the object
  $referencing = $object->get_references();

  //if there are reference do recursive delete
  if(count($referencing) > 0) {
    foreach($referencing as $ref_object)
      rec_delete_object( $ref_object );
  }

  // Guard the object deletion. If s.th. goes wrong when deleting a reference
  // further down the call stack, the referencing object shall be deleted
  // nevertheless.
  try {
    $object->delete();
  }
  catch (Exception $e) {
  }
}

//
function steamUniqObjName( $obj ){
	global $object;
	
	$inventory = $object->get_inventory();
	// Initialize names array
	$names = array();
	for( $i=0; $i < count($inventory); $i++ ){
		if( $obj->get_id() == $inventory[$i]->get_id() ) continue;
		$names[] =  $inventory[$i]->get_name();
	}
	
	$uniqCount = "";
	while( in_array( $obj->get_name().$uniqCount, $names) ){
		if( $uniqCount == "" ) $uniqCount = 1;
		else $uniqCount ++;
	}
	
	if( $uniqCount != "" ) $obj->set_attribute( "OBJ_NAME", $obj->get_name().$uniqCount );
}

?>
