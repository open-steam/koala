<?php
/*
 *
 * http://localhost/services/api/getMyDetailedProfile.php?cid=1001
 *
 * param: cid = client id
 */

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

function check_online($last_login) {
	$delay = time() - $last_login; //in seconds
	if ($delay < 180) {
		return "TRUE";
	} else {
		return "FALSE";
	}
}

function get_content( $content )
{
	try {
		$inventory = $content->get_inventory();
	} catch (Exception $e) {
		echo "<no_inventory></no_inventory>"."\n";
		return;
	}
	$no_inventory = count($inventory);

	echo "<no_inventory>" . $no_inventory . "</no_inventory>"."\n"; //or use it only for debugging

	if ( $no_inventory > 0 )
	{
		foreach( $inventory as $sub_content )
		{
			//Array ( [OBJ_CREATION_TIME] => 1219325712
			//		  [OBJ_KEYWORDS] => Array ( )
			//		  [DOC_ENCODING] => 0
			//		  [OBJ_NAME] => example.xml
			//		  [OBJ_DESC] => Hallo
			//		  [DOC_TYPE] =>  | 0
			//		  [DOC_LAST_ACCESSED] => 0
			//		  [OBJ_LAST_CHANGED] => 1219325712 )

			//$content_type = $sub_content->get_attribute( DOC_TYPE );

			//if( $content_type === 0 )
			//{
			if ($sub_content instanceof steam_container) {
				echo "<directory>"."\n";
				echo "<id>" . $sub_content->get_id() . "</id>"."\n";
				echo "<name><![CDATA[" . $sub_content->get_attribute( OBJ_NAME ) . "]]></name>"."\n";
				echo "<description><![CDATA[" . $sub_content->get_attribute( OBJ_DESC ) . "]]></description>"."\n";
				echo "<creation_time>" . $sub_content->get_attribute( OBJ_CREATION_TIME ) . "</creation_time>"."\n";
				echo "<last_changed>" . $sub_content->get_attribute( OBJ_LAST_CHANGED ) . "</last_changed>"."\n";

					/*
					 * Unused, at the moment only for testing
					 *
					 */
					//echo "<OBJ_KEYWORDS>" . $sub_content->get_attribute( OBJ_KEYWORDS ) . "</OBJ_KEYWORDS>";
					//echo "<DOC_ENCODING>" . $sub_content->get_attribute( DOC_ENCODING ) . "</DOC_ENCODING>";
					//echo "<DOC_TYPE>" . $sub_content->get_attribute( DOC_TYPE ) . "</DOC_TYPE>";

				get_content($sub_content);

				echo "</directory>"."\n";
			} else if ($sub_content instanceof steam_document) {
				echo "<file>"."\n";
				echo "<id>" . $sub_content->get_id() . "</id>"."\n";
				echo "<name><![CDATA[" . $sub_content->get_attribute( OBJ_NAME ) . "]]></name>"."\n";
				echo "<description><![CDATA[" . $sub_content->get_attribute( OBJ_DESC ) . "]]></description>"."\n";
				echo "<creation_time>" . $sub_content->get_attribute( OBJ_CREATION_TIME ) . "</creation_time>"."\n";
				echo "<last_changed>" . $sub_content->get_attribute( OBJ_LAST_CHANGED ) . "</last_changed>"."\n";

					/*
					 * Unused, at the moment only for testing
					 *
					 */
					//echo "<OBJ_KEYWORDS>" . $sub_content->get_attribute( OBJ_KEYWORDS ) . "</OBJ_KEYWORDS>";
					//echo "<DOC_ENCODING>" . $sub_content->get_attribute( DOC_ENCODING ) . "</DOC_ENCODING>";
					//echo "<DOC_TYPE>" . $sub_content->get_attribute( DOC_TYPE ) . "</DOC_TYPE>";

            	echo "</file>"."\n";
			} else if ($sub_content instanceof steam_docextern) {
				echo "<link>"."\n";
				echo "<id>" . $sub_content->get_id() . "</id>"."\n";
				echo "<name><![CDATA[" . $sub_content->get_attribute( OBJ_NAME ) . "]]></name>"."\n";
				echo "<description><![CDATA[" . $sub_content->get_attribute( OBJ_DESC ) . "]]></description>"."\n";
				echo "<creation_time>" . $sub_content->get_attribute( OBJ_CREATION_TIME ) . "</creation_time>"."\n";
				echo "<last_changed>" . $sub_content->get_attribute( OBJ_LAST_CHANGED ) . "</last_changed>"."\n";
				echo "<url><![CDATA[" . $sub_content->get_attribute( DOC_EXTERN_URL ) . "]]></url>"."\n";
				echo "</link>"."\n";
			}
		}
	}
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
	 *
	 */
	header('Content-Type: text/xml; charset=utf-8');
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"."\n";

	echo "<user>"."\n";
	
	echo "<server>"."\n";
	echo "<version>1.0.0</version>"."\n";
	echo "<updateInterval>1800000</updateInterval>"."\n";
	echo "</server>"."\n";
	
	echo "<username><![CDATA[$user_name]]></username>\n";
	echo "<name><![CDATA[" . $user_profile['USER_FIRSTNAME'] . " " . $user_profile['USER_FULLNAME'] . "]]></name>\n";
	echo "<user-icon><![CDATA[" . $user_profile[ "OBJ_ICON" ] . "]]></user-icon>\n";


	/**
	 * with caching
	 */
	$no_messages_unread = 0;
	$cache = get_cache_function( $user_name );
	$no_messages_unread = $cache->call( "lms_steam::user_count_unread_mails", $user_name );

	echo "<unread_messages>$no_messages_unread</unread_messages>"."\n";


	echo "<messengers>"."\n";
	echo "<im type=\"icq\"><![CDATA[" . $user_profile[ "USER_PROFILE_IM_ICQ" ] . "]]></im>"."\n";
	echo "<im type=\"msn\"><![CDATA[" . $user_profile[ "USER_PROFILE_IM_MSN" ] . "]]></im>"."\n";
	echo "<im type=\"aim\"><![CDATA[" . $user_profile[ "USER_PROFILE_IM_AIM" ] . "]]></im>"."\n";
	echo "<im type=\"yahoo\"><![CDATA[" . $user_profile[ "USER_PROFILE_IM_YAHOO" ] . "]]></im>"."\n";
	echo "<im type=\"skype\"><![CDATA[" . $user_profile[ "USER_PROFILE_IM_SKYPE" ] . "]]></im>"."\n";
	echo "</messengers>"."\n";

	echo "<contacts>"."\n";

	/**
	 * with caching
	 */
	$cache = get_cache_function( $user_name );
	$contacts = $cache->call( "lms_steam::user_get_buddies", $user_name ,TRUE , array( "USER_LAST_LOGIN" ));
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
	      echo "<contact uid=\"" . $contact[ "OBJ_NAME" ] . "\" online=\"" . check_online($contact[ "USER_LAST_LOGIN" ]) . "\"><![CDATA[" . $contact[ "USER_FIRSTNAME" ] ." " . $contact[ "USER_FULLNAME" ] . "]]></contact>"."\n";
	    }
	}

	echo "</contacts>"."\n";


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

		echo "<courses current_semester_name=\"" . $current_semester_obj_name . "\" current_semester_id=\"". $current_semester_obj_id . "\">"."\n";

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
					echo "<course semester_name=\"" . $membership["SEMESTER_NAME"] . "\" semester_id=\"" . $semester["OBJ_ID"] . "\">"."\n";


					// getMyCourseDetails.php *****************************************************************************************

					/**
					 * Required parameters are the course object name and the semester id.
					 *
					 */
					if( !empty($membership[ "OBJ_NAME" ]) && !empty($semester["OBJ_ID"]) )
					{
						$debug_info = "Course object name: " . $membership[ "OBJ_NAME" ];
						$debug_info = $debug_info . " # " . "Semester ID: " . $semester["OBJ_ID"];

						$user = lms_steam::get_current_user();
						$user_id = $user->get_id();

						$debug_info = $debug_info . " # " . "User ID: " . $user_id;

							//only lms_steam::semester_get_courses has the COURSE_TUTORS

							//lms_steam::semester_get_courses
							//Array
							//(
							//    [COURSE_SEMESTER] => WS0809
							//    [COURSE_LONG_DSC] =>
							//    [OBJ_NAME] => 333143828428108
							//    [COURSE_NUMBER] => L.128.37210
							//    [OBJ_DESC] => Optoelektronische Halbleiter-Bauelemente I - Übungen
							//    [COURSE_HISLSF_ID] => 0
							//    [COURSE_PARTICIPANT_MNGMNT] => 0
							//    [COURSE_TUTORS] => Donatas User //aus PAUL
							//    [COURSE_SHORT_DSC] => Swp-1
							//    [KOALA_GROUP_ACCESS] => 1
							//    [OBJ_ID] => 1475
							//    [COURSE_MAX_PARTICIPANTS] => 0
							//    [COURSE_NO_PARTICIPANTS] => 1
							//    [SORTKEY] => L.128.37210
							//)
							//
							//lms_steam::user_get_booked_courses
							//Array
							//(
							//    [OBJ_ID] => 1475
							//    [OBJ_NAME] => 333143828428108
							//    [COURSE_NAME] => Optoelektronische Halbleiter-Bauelemente I - Übungen (L.128.37210)
							//    [COURSE_LINK] => http://localhost/semester/WS0809/333143828428108/
							//    [SEMESTER_NAME] => WS0809
							//    [COURSE_UNITS_ENABLED] => FALSE
							//)

						/**
						 * without caching
						 */
						//$user_courses = lms_steam::user_get_booked_courses( $user_id, $semester["OBJ_ID"] );

						/**
						 * with caching
						 */
						$cache = get_cache_function( $user->get_name() );
						$user_courses = $cache->call( "lms_steam::user_get_booked_courses", $user_id, $semester["OBJ_ID"] );
						$all_courses = $cache->call( "lms_steam::semester_get_courses", $semester["OBJ_ID"] );

						$no_user_courses = count( $user_courses );
						$no_all_courses = count( $all_courses );

						$debug_info = $debug_info . " # " . "Number of found user courses in semester: " . $no_user_courses;

						//echo "<details>";

						if ( $no_all_courses > 0 )
						{
							foreach( $all_courses as $course )
							{
								if( $course["OBJ_NAME"] == $membership[ "OBJ_NAME" ] )
								{
									$course_tutors = $course["COURSE_TUTORS"];
								}
							}
						}


						if ( $no_user_courses > 0 )
						{
							foreach( $user_courses as $user_course )
							{
								$debug_info = $debug_info . " # " . "User course object name: " . $user_course["OBJ_NAME"];

								if( $user_course["OBJ_NAME"] == $membership[ "OBJ_NAME" ] )
								{
									echo "<id>" . $user_course["OBJ_NAME"] . "</id>"."\n";
									echo "<title><![CDATA[" . $user_course["COURSE_NAME"] . "]]></title>"."\n";
									echo "<tutors><![CDATA[" . $course_tutors . "]]></tutors>"."\n";
									echo "<semester><![CDATA[" . $user_course["SEMESTER_NAME"] . "]]></semester>"."\n";
									echo "<link><![CDATA[" . $user_course["COURSE_LINK"] . "]]></link>"."\n";

									$group_name = "Courses." . $user_course["SEMESTER_NAME"] . "." . $membership[ "OBJ_NAME" ] . ".learners";

									$debug_info = $debug_info . " # " . "Group name path: " . $group_name;

									$courses_group = steam_factory::get_group( $GLOBALS["STEAM"]->get_id(), $group_name );

									$no_courses_group = count($courses_group);

									$debug_info = $debug_info . " # " . "Number of found course groups: " . $no_courses_group;
									$debug_info = $debug_info . " # " . "Course group object: " . is_object($courses_group);

									if (  ($no_courses_group > 0) && (is_object($courses_group)) )
									{
										$workroom = $courses_group->get_workroom();
										$inventory = $workroom->get_inventory(CLASS_CONTAINER);

										$no_inventory = $workroom->count_inventory();

										$debug_info = $debug_info . " # " . "Number of found course group workroom inventory: " . $no_inventory;

										if ( $no_inventory > 0 )
										{
											echo "<extension>"."\n";

									        foreach( $inventory as $category )
									        {
									            echo "<lection id=\"" . $category->get_id()
									            		 . "\" name=\"" . $category->get_attribute( OBJ_NAME )
									            		 . "\" description=\"" . $category->get_attribute( OBJ_DESC )
									            		 . "\" creation_time=\"" . $category->get_attribute( OBJ_CREATION_TIME )
									            		 . "\" last_changed=\"" . $category->get_attribute( OBJ_LAST_CHANGED ) . "\">"."\n";

												/*
												//only for testing
									            echo "<LECTION_ID>" . $category->get_id() . "</LECTION_ID>";
									            echo "<LECTION_NAME>" . $category->get_attribute( OBJ_NAME ) . "</LECTION_NAME>";
									            echo "<LECTION_DESC>" . $category->get_attribute( OBJ_DESC ) . "</LECTION_DESC>";
									            echo "<LECTION_CREATION_TIME>" . $category->get_attribute( OBJ_CREATION_TIME ) . "</LECTION_CREATION_TIME>";
									            echo "<LECTION_LAST_CHANGED>" . $category->get_attribute( OBJ_LAST_CHANGED ) . "</LECTION_LAST_CHANGED>";
									            echo "<LECTION_KEYWORDS>" . $category->get_attribute( OBJ_KEYWORDS ) . "</LECTION_KEYWORDS>";
									            echo "<LECTION_TYPE>" . $category->get_attribute( DOC_TYPE ) . "</LECTION_TYPE>";
									            echo "<LECTION_LAST_ACCESSED>" . $category->get_attribute( DOC_LAST_ACCESSED ) . "</LECTION_LAST_ACCESSED>";
									            echo "<LECTION_ENCODING>" . $category->get_attribute( DOC_ENCODING ) . "</LECTION_ENCODING>";
									            */

									            get_content($category);

												echo "</lection>"."\n";
											}

											echo "</extension>"."\n";
										}
									}
								}
							}
						}
						else
						{
							//echo "<error>No courses found.</error>";
						}

						echo "<debug><![CDATA[" . $debug_info . "]]></debug>"."\n";  //TODO (maybe): remove or comment out this line before using in productive system

						//echo "</details>";

					}
					else
					{
						xml_error("Error: Missing parameter.");
						exit;
					}

					// /getMyCourseDetails.php *****************************************************************************************

					echo "</course>"."\n";
				}
			}
		}
	}
	else
	{
		xml_error("No semester found.");
        exit;
	}


	echo "</courses>"."\n";

	// Cache for 7 Minutes
	$cache = get_cache_function( $user_name, 420 );
	$feeds = $cache->call( "koala_user::get_news_feeds_static", 0, 0, FALSE, $user ); //0, 10, FALSE, $user

	$no_feeds = count( $feeds );

	echo "<abos>"."\n";

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

	      echo "<abo><![CDATA[" . $feed[ 'url' ] . "]]></abo>\n";
		}
	}

	echo "</abos>\n";
	echo "</user>\n";

} else {
  exit;
}
?>