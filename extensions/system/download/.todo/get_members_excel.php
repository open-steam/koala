<?php
	require_once( "../etc/koala.conf.php" );
	require_once( PATH_LIB . "steam_handling.inc.php" );

	if ( $_SESSION[ "LMS_USER" ] instanceof lmsuser && $_SESSION[ "LMS_USER" ]->is_logged_in() )
	{
		$login = $_SESSION[ "LMS_USER" ]->get_login();
		$password = $_SESSION[ "LMS_USER" ]->get_password();
	}
	else
	{
		$login = STEAM_GUEST_LOGIN;
		$password = STEAM_GUEST_PW;
	}
	steam_connect( STEAM_SERVER, STEAM_PORT, $login, $password );
	$document = steam_factory::get_object( $STEAM->get_id(), $_GET[ "id" ] );
	$doc_data = $document->get_attributes( array(
			"OBJ_NAME",
			"DOC_MIME_TYPE"
		) );
	header( "Pragma: private" );
	header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
	header( "Content-Type: application/octet-stream" );
	header( "Content-Length:" . $document->get_content_size() );
	header( "Content-Disposition: inline; filename=\"" . $document->get_name(). "\"");
	print $document->get_content();
?>
