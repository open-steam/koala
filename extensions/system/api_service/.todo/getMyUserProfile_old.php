<?php
/*
 *
 * http://localhost/services/api/getMyUserProfile.php?cid=1001
 *
 * param: cid = client id
 */

header('Content-Type: text/xml; charset=utf-8');

require_once( "../../../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

require_once( PATH_LIB . "http_auth_handling.inc.php" );

require_once("error_handling.php");

if ( !(defined( "API_ENABLED" ) && API_ENABLED === TRUE) ) {
	xml_error("API_ENABLED not set");
	exit;
}

if ( !(defined( "API_CLIENT_ID" ) && isset($_GET["cid"]) && API_CLIENT_ID == $_GET["cid"]) ) {
	xml_error("API_CLIENT_ID not allowed");
	exit;
}


if( http_auth() )
{
	$user = lms_steam::get_current_user();
	$user_name = $user->get_name();

	/**
	 * without caching
	 */
	//	$user_profile = lms_steam::user_get_profile( $user_name );

	/**
	 * with caching
	 */
	$cache = get_cache_function( $user_name, 86400 ); //$user->get_name()
	$user_profile = $cache->call( "lms_steam::user_get_profile", $user_name ); //$user->get_name()

	//	echo $user_profile[ "USER_FIRSTNAME" ];
	//	array(  "USER_FULLNAME", "USER_LAST_LOGIN", "USER_FIRSTNAME", "USER_EMAIL",
	//			"OBJ_ICON",	"OBJ_DESC", "USER_ACADEMIC_TITLE", "USER_ACADEMIC_DEGREE",
	//			"USER_PROFILE_GENDER", "USER_PROFILE_DSC", "USER_PROFILE_FOCUS", "USER_PROFILE_HOMETOWN",
	//			"USER_PROFILE_WANTS", "USER_PROFILE_HAVES",	"USER_PROFILE_ORGANIZATIONS", "USER_PROFILE_OTHER_INTERESTS",
	//			"USER_PROFILE_FACULTY",	"USER_PROFILE_ADDRESS",	"USER_PROFILE_TELEPHONE", "USER_PROFILE_PHONE_MOBILE",
	//			"USER_PROFILE_WEBSITE_URI",	"USER_PROFILE_WEBSITE_NAME", "USER_PROFILE_IM_ICQ", "USER_PROFILE_IM_MSN",
	//			"USER_PROFILE_IM_AIM", "USER_PROFILE_IM_YAHOO",	"USER_PROFILE_IM_SKYPE", "USER_LANGUAGE" );


	/**
	 * Start to build the XML-Ouput
	 */
	header("Content-type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";

	echo "<user>";

	echo "<username><![CDATA[$user_name]]></username>";
	echo "<name><![CDATA[" . $user_profile[ "USER_FIRSTNAME" ] . " " . $user_profile[ "USER_FULLNAME" ] . "]]></name>";
	echo "<user-icon><![CDATA[" . $user_profile[ "OBJ_ICON" ] . "]]></user-icon>";


	/**
	 * with caching
	 */
	$no_messages_unread = 0;
	$cache = get_cache_function( $user_name );
	$no_messages_unread = $cache->call( "lms_steam::user_count_unread_mails", $user_name );

	echo "<unread_messages>$no_messages_unread</unread_messages>";


	echo "<messengers>";
	echo "<im type=\"icq\"><![CDATA[" . $user_profile[ "USER_PROFILE_IM_ICQ" ] . "]]></im>";
	echo "<im type=\"msn\"><![CDATA[" . $user_profile[ "USER_PROFILE_IM_MSN" ] . "]]></im>";
	echo "<im type=\"aim\"><![CDATA[" . $user_profile[ "USER_PROFILE_IM_AIM" ] . "]]></im>";
	echo "<im type=\"yahoo\"><![CDATA[" . $user_profile[ "USER_PROFILE_IM_YAHOO" ] . "]]></im>";
	echo "<im type=\"skype\"><![CDATA[" . $user_profile[ "USER_PROFILE_IM_SKYPE" ] . "]]></im>";
	echo "</messengers>";

	echo "<contacts>";

	/**
	 * with caching
	 */
	$cache = get_cache_function( $user_name );
	$contacts = $cache->call( "lms_steam::user_get_buddies", $user_name );
	//	$cache->drop( "lms_steam::user_get_profile", $user_name );

	/**
	 * without caching
	 */
	//	$contacts = lms_steam::user_get_buddies($user_name);

	// Array
	// (
	//    [0] => Array
	//        (
	//            [OBJ_NAME] => admin01
	//            [OBJ_DESC] => sTeam Admin
	//            [USER_FIRSTNAME] => Moritz
	//            [USER_FULLNAME] => Mustermann
	//            [OBJ_ICON] => 179
	//            [USER_PROFILE_FOCUS] => 0
	//            [USER_PROFILE_FACULTY] => 0
	//            [USER_ACADEMIC_TITLE] => 0
	//            [OBJ_ID] => 837
	//        )
	// )

	$no_contacts = count( $contacts );
	if ( $no_contacts > 0 )
	{
	    for( $i = 0; $i < $no_contacts; $i++ )
	    {
	      $contact = $contacts[ $i ];
	      echo "<contact><![CDATA[" . $contact[ "USER_FIRSTNAME" ] ." " . $contact[ "USER_FULLNAME" ] . "]]></contact>";
	    }
	}

	echo "</contacts>";


	//echo "<courses current_semester_name=\"" . STEAM_CURRENT_SEMESTER . "\">";

	$cache = get_cache_function( "ORGANIZATION", 600 );
	$semesters = $cache->call( "lms_steam::get_semesters" );

	$no_semesters = count( $semesters );

	if ( $no_semesters > 0 )
	{
		foreach( $semesters as $semester )
		{
	      	if( $semester["OBJ_NAME"] == STEAM_CURRENT_SEMESTER )
	      	{
	      		$current_semester_obj_id = $semester["OBJ_ID"];
	      		$current_semester_obj_name = $semester["OBJ_NAME"];
	      	}
		}

		echo "<courses current_semester_name=\"" . $current_semester_obj_name . "\" current_semester_id=\"". $current_semester_obj_id . "\">";

		foreach( $semesters as $semester )
		{

	     	$memberships = lms_steam::semester_get_user_coursememberships( $semester["OBJ_ID"], $user );
			$no_memberships = count( $memberships );

			// Array
			// (
			//    [1040] => Array
			//        (
			//            [OBJ_ID] => 1040
			//            [OBJ_NAME] => 0001
			//            [COURSE_NAME] => Testkurs (0001)
			//            [COURSE_LINK] => http://localhost/semester/WS0809/0001/
			//            [SEMESTER_NAME] => WS0809
			//            [COURSE_UNITS_ENABLED] => TRUE
			//        )
			//
			// )

			if ( $no_memberships > 0 )
			{
				foreach( $memberships as $membership )
				{
					echo "<course semester_name=\"" . $membership["SEMESTER_NAME"] . "\" semester_id=\"" . $semester["OBJ_ID"] . "\">" . $membership[ "OBJ_NAME" ] . "</course>";
				}
			}
		}
	}
	else
	{
		echo "<courses>";
		echo "<error>No semester found.</error>";
      	//exit;
	}


	echo "</courses>";

	// Cache for 7 Minutes
	$cache = get_cache_function( $user_name, 420 );
	$feeds = $cache->call( "koala_user::get_news_feeds_static", 0, 0, FALSE, $user ); //0, 10, FALSE, $user

	$no_feeds = count( $feeds );

	echo "<abos>";

	if ( $no_feeds > 0 )
	{
		foreach ( $feeds as $feed )
		{
//	      echo "feed: " .
//		      $feed['title'] . ", " .
//		      strftime( '%x', $feed['date'] ) . ", " .
//		      $feed['obj']->get_id() . ", " .
//		      $feed['url'] . ", " .
//		      $feed['type'] . ", " .
//		      $feed['feed_obj']->get_id() . "<br>";

	      echo "<abo><![CDATA[" . $feed[ 'url' ] . "]]></abo>";
		}
	}

	echo "</abos>";
	echo "</user>";

} else {
  exit;
}
?>
