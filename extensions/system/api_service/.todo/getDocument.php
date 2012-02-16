<?php
/*
 * http://localhost/services/api/getDocument.php?id=1735&name=Bildschirmfoto.png
 *
 * param: id = document id
 * param: name = document name
 */

require_once( "../../../etc/koala.conf.php" );
require_once( PATH_LIB . "http_auth_handling.inc.php" );

require_once("error_handling.php");

if( http_auth() )
{
	if ( !(defined( "API_ENABLED" ) && API_ENABLED === TRUE) )
	{
		header('Content-Type: text/xml; charset=utf-8');
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		xml_error("API_ENABLED not set");
		exit;
	}
	
	if ( !(defined( "API_CLIENT_ID" ) && isset($_GET["cid"]) && API_CLIENT_ID == $_GET["cid"]) )
	{
		header('Content-Type: text/xml; charset=utf-8');
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		xml_error("API_CLIENT_ID not allowed");
		exit;
	}
	
	if( isset($_GET["id"]) && isset($_GET["name"]) )
	{
		$download_url = "/download/" . $_GET["id"] . "/" . $_GET["name"];
		header("Location: " . $download_url);
	} else {
		header('Content-Type: text/xml; charset=utf-8');
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		xml_error("Parameter id or name is missing.");
		exit;
	}
} else {
	header('Content-Type: text/xml; charset=utf-8');
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
	xml_error("No access");
	exit;
}
?>