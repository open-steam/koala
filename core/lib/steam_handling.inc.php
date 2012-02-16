<?php

function steam_connect( $server = STEAM_SERVER, $port = STEAM_PORT, $login = STEAM_GUEST_LOGIN, $password = STEAM_GUEST_PW )
{
	$steam = $GLOBALS[ "STEAM" ];

	try
	{
		$steam->connect( $server, $port, $login, $password );
	}
	catch( Exception $e )
	{

		if ( ! $steam->get_socket_status() )
		{
			throw new Exception(
					"No connection to sTeam server ($server:$port).",
					E_CONNECTION
					);
		}
		if ( ! $steam->get_login_status() )
		{
			return FALSE;
		}
	}
}


function steam_is_connected()
{
	return $GLOBALS[ "STEAM" ]->get_socket_status();
}

function steam_is_logged_in()
{
	return $GLOBALS[ "STEAM" ]->get_login_status();
}

function steam_get_current_user()
{
	if ( ! steam_is_logged_in() )
	{
		throw new Exception( "Not logged in.", E_INVOCATION );
	}
	return $GLOBALS[ "STEAM" ]->get_current_steam_user();
}

function steam_get_current_environment()
{
	$steam_user = steam_get_current_user();
	return $steam_user->get_environment();
}

function steam_get_user_language()
{
	$steam_languages = array(
			"english" => "en_US",
			"german"  => "de_DE",
			"chinese" => "zh_TW"
			);
	$steam_user = steam_get_current_user();
	return $steam_languages[ $steam_user->get_attribute( "USER_LANGUAGE" ) ];
}

function steam_user_get_profile( $username )
{
	$steam_user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $username );
	$query = array( "USER_FULLNAME", "USER_FIRSTNAME", "USER_EMAIL", "OBJ_ICON" );
	$tmp = $steam_user->get_attributes( $query );
	$tmp[ "OBJ_ICON" ] = $tmp[ "OBJ_ICON" ]->get_id();
	return $tmp;
}

function steam_user_get_buddies( $username )
{
	$result = array();
	$steam_user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $username );
	$buddies = $steam_user->get_attribute( "USER_FAVOURITES" );
	$query = array( "OBJ_NAME", "USER_FIRSTNAME", "USER_FULLNAME", "OBJ_ICON" );
	$i = 0;
	foreach( $buddies as $buddy )
	{
		if ( $buddy instanceof steam_user )
		{
			$tmp = $buddy->get_attributes( $query );
			$result[ $i ] = $tmp;
			$result[ $i ][ "OBJ_ICON" ] = $result[ $i ][ "OBJ_ICON" ]->get_id();
			$i++;
		}
	}
	return $result;
}

function steam_user_get_groups( $username )
{
	$result = array();
	$steam_user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $username );
	$groups = $steam_user->get_groups();
	$query = array( "OBJ_NAME" );
	$i = 0;
	foreach( $groups as $group )
	{
		$result[ $i ] = $group->get_attributes( $query );
		$result[ $i ][ "OBJ_ID" ] = $group->get_id();
	}
	return $result;
}

function steam_disconnect()
{
	return $GLOBALS[ "STEAM" ]->disconnect();
}

?>
