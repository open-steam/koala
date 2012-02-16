<?php
/*
 *
 * http://localhost/services/api/getMyCourseDetails.php?cid=1001&con=0001&sid=860
 *
 * param: cid = client id
 * param: con = course object name
 * param: sid = semester id
 */

require_once( "../../../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

require_once( PATH_LIB . "http_auth_handling.inc.php" );

require_once("error_handling.php");



function get_content( $content )
{
	$inventory = $content->get_inventory();
	$no_inventory = count($inventory);

	echo "<no_inventory>" . $no_inventory . "</no_inventory>"; //or use it only for debugging

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

			if ($sub_content instanceof steam_container) {
			
		//	if( $content_type === 0 )
			//{
				echo "<directory>";
				echo "<id>" . $sub_content->get_id() . "</id>";
				echo "<name><![CDATA[" . $sub_content->get_attribute( OBJ_NAME ) . "]]></name>";
				echo "<description><![CDATA[" . $sub_content->get_attribute( OBJ_DESC ) . "]]></description>";
				echo "<creation_time>" . $sub_content->get_attribute( OBJ_CREATION_TIME ) . "</creation_time>";
				echo "<last_changed>" . $sub_content->get_attribute( OBJ_LAST_CHANGED ) . "</last_changed>";

					/*
					 * Unused, at the moment only for testing
					 *
					 */
					//echo "<OBJ_KEYWORDS>" . $sub_content->get_attribute( OBJ_KEYWORDS ) . "</OBJ_KEYWORDS>";
					//echo "<DOC_ENCODING>" . $sub_content->get_attribute( DOC_ENCODING ) . "</DOC_ENCODING>";
					//echo "<DOC_TYPE>" . $sub_content->get_attribute( DOC_TYPE ) . "</DOC_TYPE>";

				get_content($sub_content);

				echo "</directory>";
			} else if ($sub_content instanceof steam_document) {
				echo "<file>";
				echo "<id>" . $sub_content->get_id() . "</id>";
				echo "<name><![CDATA[" . $sub_content->get_attribute( OBJ_NAME ) . "]]></name>";
				echo "<description><![CDATA[" . $sub_content->get_attribute( OBJ_DESC ) . "]]></description>";
				echo "<creation_time>" . $sub_content->get_attribute( OBJ_CREATION_TIME ) . "</creation_time>";
				echo "<last_changed>" . $sub_content->get_attribute( OBJ_LAST_CHANGED ) . "</last_changed>";

					/*
					 * Unused, at the moment only for testing
					 *
					 */
					//echo "<OBJ_KEYWORDS>" . $sub_content->get_attribute( OBJ_KEYWORDS ) . "</OBJ_KEYWORDS>";
					//echo "<DOC_ENCODING>" . $sub_content->get_attribute( DOC_ENCODING ) . "</DOC_ENCODING>";
					//echo "<DOC_TYPE>" . $sub_content->get_attribute( DOC_TYPE ) . "</DOC_TYPE>";

            	echo "</file>";
			} else if ($sub_content instanceof steam_docextern) {
				echo "<link>";
				echo "<id>" . $sub_content->get_id() . "</id>";
				echo "<name><![CDATA[" . $sub_content->get_attribute( OBJ_NAME ) . "]]></name>";
				echo "<description><![CDATA[" . $sub_content->get_attribute( OBJ_DESC ) . "]]></description>";
				echo "<creation_time>" . $sub_content->get_attribute( OBJ_CREATION_TIME ) . "</creation_time>";
				echo "<last_changed>" . $sub_content->get_attribute( OBJ_LAST_CHANGED ) . "</last_changed>";
				echo "<url><![CDATA[" . $sub_content->get_attribute( DOC_EXTERN_URL ) . "]]></url>";
				echo "</link>";
			}
			
		}
	}
}



header('Content-Type: text/xml; charset=utf-8');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";

if ( !(defined( "API_ENABLED" ) && API_ENABLED === TRUE) )
{
	xml_error("API_ENABLED not set");
	exit;
}

if ( !(defined( "API_CLIENT_ID" ) && isset($_GET["cid"]) && API_CLIENT_ID == $_GET["cid"]) )
{
	xml_error("API_CLIENT_ID not allowed");
	exit;
}


if( http_auth() )
{
	/**
	 * The required GET-paramater 'con' needs the course object name (OBJ_NAME)
	 *
	 */
	if( isset($_GET["con"]) && isset($_GET["sid"]) )
	{
		$debug_info = "Course object name: " . $_GET["con"];
		$debug_info = $debug_info . " # " . "Semester ID: " . $_GET["sid"];

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
		//$user_courses = lms_steam::user_get_booked_courses( $user_id, $_GET["sid"] );

		/**
		 * with caching
		 */
		$cache = get_cache_function( $user->get_name() );
		$user_courses = $cache->call( "lms_steam::user_get_booked_courses", $user_id, $_GET["sid"] );
		$all_courses = $cache->call( "lms_steam::semester_get_courses", $_GET["sid"] );

		$no_user_courses = count( $user_courses );
		$no_all_courses = count( $all_courses );

		$debug_info = $debug_info . " # " . "Number of found user courses in semester: " . $no_user_courses;


		echo "<course>";

		if ( $no_all_courses > 0 )
		{
			foreach( $all_courses as $course )
			{
				if( $course["OBJ_NAME"] == $_GET["con"] )
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

				if( $user_course["OBJ_NAME"] == $_GET["con"] )
				{
					//print_r($user_course);

					echo "<id>" . $user_course["OBJ_NAME"] . "</id>";
					echo "<title><![CDATA[" . $user_course["COURSE_NAME"] . "]]></title>";
					echo "<tutors><![CDATA[" . $course_tutors . "]]></tutors>";
					echo "<semester><![CDATA[" . $user_course["SEMESTER_NAME"] . "]]></semester>";
					echo "<link><![CDATA[" . $user_course["COURSE_LINK"] . "]]></link>";

					$group_name = "Courses." . $user_course["SEMESTER_NAME"] . "." . $_GET["con"] . ".learners";

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
							echo "<extension>";

					        foreach( $inventory as $category )
					        {
					            echo "<lection id=\"" . $category->get_id()
					            		 . "\" name=\"" . $category->get_attribute( OBJ_NAME )
					            		 . "\" description=\"" . $category->get_attribute( OBJ_DESC )
					            		 . "\" creation_time=\"" . $category->get_attribute( OBJ_CREATION_TIME )
					            		 . "\" last_changed=\"" . $category->get_attribute( OBJ_LAST_CHANGED ) . "\">";

								/*
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

								echo "</lection>";
							}

							echo "</extension>";
						}
					}
				}
			}
		}
		else
		{
			//echo "<error>No courses found.</error>";
		}

		/**
		 * (maybe) TODO: remove or comment out the following line with <debug> before using in productive system
		 */
		echo "<debug><![CDATA[" . $debug_info . "]]></debug>";

		echo "</course>";

	}
	else
	{
		xml_error("Error: Missing parameter");
	}

}
else
{
  exit;
}
?>