<?php
// no direct call
if (!defined('_VALID_KOALA')) {
	header("location:/");
	exit;
}
require_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
$all_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );

if (!isset($current_semester)) {
	header("location:/");
	exit;
}

if ( (! lms_steam::is_steam_admin( $user )) && ( ! lms_steam::is_semester_admin( $current_semester, $user ))  )
{
	include( "bad_link.php" );
    exit;
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "courses_create_paul.template.html" );

$courses_create_paul_js = <<< END
    function check(checkbox)
    {
        if (checkbox.checked == true)
        {
            for(i=0; i< document.group_dsc.elements.length; i++)
            {
                if(document.group_dsc.elements[i].type == "checkbox")
                {
                    if( document.group_dsc.elements[i].id.substring(0,12) == "subextension" )
                    {
                        var pos;
                        var extension;
                        pos = document.group_dsc.elements[i].id.indexOf(":") + 1;
                        extension =  document.group_dsc.elements[i].id.substring(pos,document.group_dsc.elements[i].id.length);

                        if (checkbox.id == extension)
                        {
                            document.group_dsc.elements[i].disabled = false;
                        }

                        if (checkbox.id == "units_base")
                        {
                            document.getElementById("subextension_units_docpool:units_base").disabled = true;
                            document.getElementById("subextension_units_docpool:units_base").checked = true;
                        }
                    }
                }
            }
        }
        else
        {
            for(i=0; i< document.group_dsc.elements.length; i++)
            {
                if(document.group_dsc.elements[i].type == "checkbox")
                {
                    if( document.group_dsc.elements[i].id.substring(0,12) == "subextension" )
                    {
                        var pos;
                        var extension;
                        pos = document.group_dsc.elements[i].id.indexOf(":") + 1;
                        extension =  document.group_dsc.elements[i].id.substring(pos,document.group_dsc.elements[i].id.length);

                        if (checkbox.id == extension)
                        {
                            document.group_dsc.elements[i].disabled = true;
                        }

                        if (checkbox.id == "units_base")
                        {
                            document.getElementById("subextension_units_docpool:units_base").disabled = true;
                        }
                    }
                }
            }
        }
    }

    function storeDisabled()
    {
        var formular = document.group_dsc;
        var j = 0;
        for(i=0; i < formular.elements.length; i++)
        {
                var el = document.group_dsc.elements[i];
                if(el.type == "checkbox" && el.checked == true && el.disabled == true)
                    formular.elements['extensions_enabled_add'].value += el.value + "/";
        }
    }
END;
$portal->add_javascript_code("courses_create_paul", $courses_create_paul_js);



//$content->setVariable( "FORM_ACTION", PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name() . "/paul/" );
$content->setVariable( "VALUE_SEMESTER", h($current_semester->get_name()) );

$content->setVariable( "CONFIRMATION_TEXT", str_replace( "%SEMESTER", h($current_semester->get_attribute( "OBJ_DESC")), gettext( "You are going to create a new course via PAUL in <b>%SEMESTER</b>." ) ) . " " . gettext( "Please choose out the lecturer at first." ) . " " . gettext( "Either you can search by the first or last name, or by a part of the email-address or login." ) . " (" . gettext( "Hint: Use '%' as a wildcard in your search pattern." ) . ") " . gettext("The following search checks if the user exists in PAUL and will show the considering courses.") . " " . gettext( "In the second step you can choose out the course you want to create." ) );
$content->setVariable( "LABEL_GENERAL_INFORMATION", gettext( "General Information" ) );

$show_search_block = true;
$show_create_block = false;

$paul_client = new paul_soap();


function is_valid_pattern($pattern)
{
    $pattern = trim( $pattern );

    if ( empty( $pattern) )
    {
        return false;
    }

    $stripped_pattern = str_replace('%', '', $pattern);
    $stripped_pattern = str_replace('_', '', $stripped_pattern);
    if ( strlen( $stripped_pattern ) < 3 )
    {
        return false;
    }

    return true;
}


// SEARCH
if ( !empty($_REQUEST[ "pattern" ]) && is_valid_pattern( $_REQUEST[ "pattern" ] ) )
{
    $cache = get_cache_function( $user->get_name(), 60 );
   	$result = $cache->call( "lms_steam::search_user", $_REQUEST[ "pattern" ], $_REQUEST[ "lookin" ] );

	$content->setVariable( "VALUE_PATTERN", $_REQUEST[ "pattern" ] );
    if ( $_REQUEST[ "lookin" ] == "login" )
    {
        $content->setVariable( "CHECKED_LOGIN", 'checked="checked"' );
    }
    else
    {
        $content->setVariable( "CHECKED_NAME", 'checked="checked"' );
    }

    $no_people = count( $result );
    //$html_people->setVariable( "LABEL_CONTACTS", gettext( "Results" ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_people), gettext( "%a-%z out of %s" ) ) . ")" );
    if ( $no_people > 0 )
    {
        $parse_show_search_result_block = true;
        $paul_error = false;
        $paul_person_found = false;

		foreach( $result as $person )
		{
			try
			{
				$paul_person_no = $paul_client->get_person_no_by_uid(strtolower($person[ "OBJ_NAME" ]));
			}
			catch( Exception $exception )
			{
				$paul_error = true;
				$problem = $exception->getMessage();
				error_log($problem);
			}

			if( !$paul_error )
			{
				try
				{
					$paul_person_id = $paul_client->get_person_id_by_person_no($paul_person_no);
				}
				catch( Exception $exception )
				{
					$paul_error = true;
					$problem = $exception->getMessage();
					error_log($problem);
				}
			}

			$content->setCurrentBlock( "BLOCK_SHOW_SEARCH_RESULT_PERSON" );
			$content->setVariable( "CONTACT_NAME", h($person[ "USER_FIRSTNAME" ]) . " " . h($person[ "USER_FULLNAME" ]) );
	        $content->setVariable( "CONTACT_LINK", PATH_URL . "user/" . h($person[ "OBJ_NAME" ]) . "/" );
	        $icon_link = ( $person[ "OBJ_ICON" ] == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . h($person[ "OBJ_ICON" ]) . "&type=usericon&width=30&height=40";
	        $content->setVariable( "CONTACT_IMAGE", $icon_link );
	        $content->setVariable( "CONTACT_NAME", h($person[ "USER_FIRSTNAME" ]) . " " . h($person[ "USER_FULLNAME" ]) );
	        $content->setVariable( "KOALA_USER_NAME", h($person[ "USER_FIRSTNAME" ]) . " " . h($person[ "USER_FULLNAME" ]) );
	        $content->setVariable( "KOALA_USER_OBJ_NAME", h($person[ "OBJ_NAME" ]) );
	        $content->setVariable( "PAUL_USER_ID", $paul_person_id );
	        $content->setVariable( "OBJ_DESC", h($person[ "OBJ_DESC" ]) );
	        $fof = $person[ "USER_PROFILE_FACULTY" ];
	        $fof .= ( empty( $person[ "USER_PROFILE_FOCUS" ] ) ) ? "" : ", " . $person[ "USER_PROFILE_FOCUS" ];
	        $content->setVariable( "FACULTY_AND_FOCUS", h($fof) );

			$parse_show_search_result_block = true;

			if( $paul_error )
			{
				//$parse_show_search_result_block = false;
				$content->setCurrentBlock( "BLOCK_SUBMIT_BUTTON_DISABLED" );
				$content->setVariable( "LABEL_SHOW_COURSES_DISABLED", gettext( "User not found in PAUL" ) );
				$content->parse( "BLOCK_SUBMIT_BUTTON_DISABLED" );
			}
			else
			{
				$paul_person_found = true;
				$content->setCurrentBlock( "BLOCK_SUBMIT_BUTTON" );
				$content->setVariable( "LABEL_SHOW_COURSES", gettext( "Show PAUL courses" ) );
				$content->parse( "BLOCK_SUBMIT_BUTTON" );
			}

			$paul_error = false;
			$content->parse( "BLOCK_SHOW_SEARCH_RESULT_PERSON" );
		}

		if( $parse_show_search_result_block )
		{
			$content->setCurrentBlock( "BLOCK_SHOW_SEARCH_RESULT" );
        	$content->setVariable( "LABEL_NAME_POSITION", gettext( "Name, position" ) );
        	$content->setVariable( "LABEL_SUBJECT_AREA", gettext( "Origin/Focus" ) );
        	$content->setVariable( "LABEL_SHOW_COURSE", gettext( "Action" ) );
			$content->parse( "BLOCK_SHOW_SEARCH_RESULT" );
		}
		else if ( !$paul_person_found )
		{
			$portal->set_problem_description( gettext( "Your search has no result." ) );
		}

    }
    else
    {
		$portal->set_problem_description( gettext( "Your search has no result." ) );
    }
}
else
{
    if ( isset($_REQUEST["process_formular"]) && !is_valid_pattern($_REQUEST[ "pattern" ]) )
    {
        $portal->set_problem_description( gettext( "Your search string is invalid. Make sure to enter at least 3 non wildcard characters." ) );
    }
}
//$portal->add_javascript_onload("courses_create_paul","document.getElementById('pattern').focus();");
// /SEARCH


// SHOW
if ( !empty($_REQUEST[ "paul_user_id" ]) )
{
	try
	{
		$paul_courses = $paul_client->get_all_courses_by_person($_REQUEST["paul_user_id"]);
	}
	catch( Exception $exception )
	{
		$paul_courses = null;
		$problem = $exception->getMessage();
		error_log($problem);
	}

	$no_courses = count( $paul_courses );

  
  if ( $no_courses > 0 ) {
    foreach( $paul_courses as $course_id ) {
			 /*
				Array
				(
				   [error_code] => 0 //wird zur zeit nicht ausgewertet
				   [Instructors] => Array
				       (
				           [0] => 333096745206094
				           [1] => 333104102275778
				       )

				   [course_name_german] => Physik D (Atom-, Molekül- und Kernphysik)
				   [course_name_english] =>
				   [short_description] => PhysD
				   [course_number] => L.128.34000
				   [semester_id] => 000000015009000
				   [semester_name] => SS 2009
				   [hours_in_week] => 8,00
				   [faculty_id] => 331966839707472
				   [faculty_name] => Physik
				   [small_groups] => true
				   [event_type] => Lehrveranstaltung
				   [name_small_group] => Physik D - Übungen 2 //falls Übung ansonsten null
				   [course_id] => Array
				       (
				           [0] => 333114056433170
				           [1] => 333114056447169
				       )

				)
			  */

			try
			{
				$paul_course_info = $paul_client->get_course_information($course_id);
        $paul_course_info["paul_id"] = $course_id;
        $paul_course_info["SORTKEY"] = $paul_course_info["course_number"];
        $paul_courses_data[] = $paul_course_info;
			}
			catch( Exception $exception )
			{
				$problem = $exception->getMessage();
				error_log($problem);
				throw new Exception( "PAUL_SOAP exception: " . $problem );
			}
    }
  }
usort( $paul_courses_data, "sort_courses" );
  
	if ( count( $paul_courses_data > 0 ) )
    {
		$content->setCurrentBlock( "BLOCK_SHOW_COURSES" );
		$content->setVariable( "FORM_SHOW_ACTION", PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name() . "/paul/" );
		$content->setVariable( "LABEL_LECTURER", gettext( "Dozent" ) );
		$content->setVariable( "LABEL_COURSE_NUMBER", gettext( "Course number" ) );
	    $content->setVariable( "LABEL_COURSE", gettext( "Course" ) );
	    $content->setVariable( "LABEL_FACULTY", gettext( "Faculty" ) );
	    $content->setVariable( "LABEL_ACTION", gettext( "Action" ) );

		$scg = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP);
		if( is_object($scg) )
		{
			$scg_name = $scg->get_identifier();
		}

		foreach( $paul_courses_data as $paul_course_info )
		{
      $course_id = $paul_course_info["paul_id"];
			$content->setCurrentBlock( "BLOCK_SHOW_COURSE" );
			$content->setVariable( "VALUE_LECTURER", $_REQUEST[ "koala_user_name" ] );
			$content->setVariable( "VALUE_LECTURER_LINK", PATH_URL . "user/" . $_REQUEST[ "koala_user_obj_name" ] . "/" );
			$content->setVariable( "VALUE_COURSE_NUMBER", $paul_course_info["course_number"] );
			$content->setVariable( "VALUE_COURSE_NUMBER_TITLE", $course_id );
			$content->setVariable( "VALUE_FACULTY", $paul_course_info["faculty_name"] );

			if( $paul_course_info["name_small_group"] != null )
			{
				$content->setVariable( "VALUE_COURSE_NAME_GERMAN", $paul_course_info["name_small_group"] );
				$content->setVariable( "PAUL_COURSE_NAME_GERMAN", $paul_course_info["name_small_group"] );
			}
			else
			{
				$content->setVariable( "VALUE_COURSE_NAME_GERMAN", $paul_course_info["course_name_german"] );
				$content->setVariable( "PAUL_COURSE_NAME_GERMAN", $paul_course_info["course_name_german"] );

				$associated_course_ids = "";
				if( $paul_course_info["course_id"] != null )
				{
					if( count($paul_course_info["course_id"]) > 1 )
					{
						foreach( $paul_course_info["course_id"] as $associated_course_id )
						{
							$associated_course_ids =  $associated_course_ids . $associated_course_id . "+";
						}

						$associated_course_ids = substr( $associated_course_ids, 0, -1);
					}
					else
					{
						$associated_course_ids = $paul_course_info["course_id"];
					}

				}
				$content->setVariable( "PAUL_ASSOCIATED_SMALL_GROUPS", $associated_course_ids );
			}

			// check if PAUL course already exists in koaLA
			$curr_sem = $current_semester->get_name();
			$course = steam_factory::groupname_to_object( $GLOBALS["STEAM"]->get_id(), $scg_name . "." . $curr_sem . "." . $course_id );
			if( is_object($course) && $course instanceof steam_group )
			{	// Kurs ist vorhanden!
				//$content->setVariable( "LABEL_CREATE_COURSE", gettext( "Course already exists." ) );
				$content->setVariable( "SHOW_ACTION_FIELD", "<a href=\"" . PATH_URL . SEMESTER_URL . "/" . $curr_sem . "/" . $course_id . "/" . "\">" . "<strong>" . gettext( "Course already exists." ) . "</strong></a>" );
			}
			else
			{	// Kurs ist noch nicht vorhanden!
				$course_instructors = "";

				$course_instructors_no = count($paul_course_info["Instructors"]);
				if( $course_instructors_no > 0 )
				{
					foreach( $paul_course_info["Instructors"] as $course_instructor )
					{
						$course_instructors = $course_instructors . $course_instructor . "+";
					}

					$course_instructors = substr( $course_instructors, 0, -1);
				}

				$content->setVariable( "PAUL_COURSE_INSTRUCTORS", $course_instructors );
				$content->setVariable( "PAUL_COURSE_ID", $course_id );
				$content->setVariable( "PAUL_COURSE_NAME_ENGLISH", $paul_course_info["course_name_english"] );
				$content->setVariable( "PAUL_SHORT_DESCRIPTION", $paul_course_info["short_description"] );
				$content->setVariable( "PAUL_COURSE_NUMBER", $paul_course_info["course_number"] );
				$content->setVariable( "PAUL_SEMESTER_ID", $paul_course_info["semester_id"] );
				$content->setVariable( "PAUL_SEMESTER_NAME", $paul_course_info["semester_name"] );
				$content->setVariable( "PAUL_HOURS_IN_WEEK", $paul_course_info["hours_in_week"] );
				$content->setVariable( "PAUL_FACULTY_ID", $paul_course_info["faculty_id"] );
				$content->setVariable( "PAUL_FACULTY_NAME", $paul_course_info["faculty_name"] );
				$content->setVariable( "PAUL_SMALL_GROUPS", $paul_course_info["small_groups"] );
				$content->setVariable( "PAUL_EVENT_TYPE", $paul_course_info["event_type"] );
				$content->setVariable( "INPUT_TYPE", "submit" );
				//$content->setVariable( "LABEL_CREATE_COURSE", gettext( "Create course via PAUL" ) );
				$content->setVariable( "SHOW_ACTION_FIELD", "<input type=\"submit\" value=\"" . gettext( "Create course via PAUL" ) . "\">" );
			}

			$content->parse( "BLOCK_SHOW_COURSE" );
		}

		$content->parse( "BLOCK_SHOW_COURSES" );
    }
	else
    {
		$portal->set_problem_description( gettext( "No available courses!" ) );
    }
}
// /SHOW



// CREATE
if ( !empty($_REQUEST[ "paul_course_id" ]) )
{
	$values[ "tutors" ] = "";
	$values[ "tutors_obj_name" ] = "";

	if( strlen($_POST[ "paul_course_instructors" ]) > 0 )
	{
		$tutors = "";
		$tutors_obj_name = "";

		$paul_course_instructors = explode( "+", $_POST[ "paul_course_instructors" ] );

		$instructors_notfound = array();

		//koaLA user informationen abrufen
		foreach( $paul_course_instructors as $paul_course_instructor )
		{
			try
			{
				$person_no = $paul_client->get_person_no_by_person_id( $paul_course_instructor );
			}
			catch( Exception $exception )
			{
				$problem = $exception->getMessage();
				error_log($problem);
			}

			try
			{
				$person_uid = $paul_client->get_uid_by_person_no( $person_no );
			}
			catch( Exception $exception )
			{
				$problem = $exception->getMessage();
				error_log($problem);
			}

			$koala_user = steam_factory::username_to_object( $GLOBALS["STEAM"]->get_id(), $person_uid );
			if( !is_object($koala_user) )
			{
				$instructors_notfound[] = $person_uid;
				error_log("User with uid=" . $person_uid . " not found in koaLA.");
				continue;
			}

			$user_title = $koala_user->get_attribute("USER_ACADEMIC_TITLE");
			if( $user_title == 0 )
			{
				$tutors = $tutors . $koala_user->get_attribute("USER_FIRSTNAME") . " " . $koala_user->get_attribute("USER_FULLNAME") . ", ";
			}
			else
			{
				$tutors = $tutors . $user_title . " " . $koala_user->get_attribute("USER_FIRSTNAME") . " " . $koala_user->get_attribute("USER_FULLNAME") . ", ";
			}

			$tutors_obj_name .= $koala_user->get_attribute("OBJ_NAME") . "+";
		}

		if(count($instructors_notfound) > 0)
		{
			$instructors_string = "";
			foreach($instructors_notfound as $instructor_notfound)
			{
				$instructors_string .= "'" . $instructor_notfound . "'" . ", ";
			}
			$instructors_string = substr($instructors_string, 0, -2);

			$portal->set_problem_description( gettext("Staff member not found"), gettext("The staff member $instructors_string could not be found in koaLA"). "<br/>" . gettext("Therefore some of the staff members aren't add to the staff group automatically. You may add them manually later.") );
		}

		$values[ "tutors" ] = substr( $tutors, 0, -2 );
		$values[ "tutors_obj_name" ] = substr( $tutors_obj_name, 0, -1 );
	}

	$values[ "semester" ] = h($current_semester->get_name());
	$values[ "id" ] = $_POST[ "paul_course_id" ];
	$values[ "name" ] = $_POST[ "paul_course_name_german" ];
	$values[ "name_en" ] = $_POST[ "paul_course_name_english" ];
	$values[ "short_dsc" ] = $_POST[ "paul_short_descr" ];
	$values[ "number" ] = $_POST[ "paul_course_number" ];
	$values[ "semester_id" ] = $_POST[ "paul_semester_id" ];
	$values[ "semester_name" ] = $_POST[ "paul_semester_name" ];
	$values[ "hours_in_week" ] = $_POST[ "paul_hours_in_week" ];
	$values[ "faculty_id" ] = $_POST[ "paul_faculty_id" ];
	$values[ "faculty_name" ] = $_POST[ "paul_faculty_name" ];
	$values[ "small_groups" ] = $_POST[ "paul_small_groups" ];
	$values[ "event_type" ] = $_POST[ "paul_event_type" ];
	$values[ "associated_small_groups" ] = $_POST[ "paul_associated_small_groups" ];

	$values[ "access" ] = PERMISSION_COURSE_PUBLIC; //öffentlicher Kurs ist voreingestellt

	//$values[ "maxsize" ] = 0; $max_members = 0;
	//$values[ "password" ] = "";
	//$values[ "get_lsf_infos" ]
	//$values[ "lsf_id" ] = "";
	//$values[ "hislsf" ] = "";

	$show_search_block = false;

	$content->setCurrentBlock( "BLOCK_CREATE_COURSE" );

	$content->setVariable( "FORM_CREATE_ACTION", PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name() . "/paul/" );
	$content->setVariable( "VALUE_CREATE_SEMESTER", h($current_semester->get_name()) );

	$content->setVariable( "COURSE_CONFIRMATION_TEXT", gettext( "Please check and fill now out the requested meta data at first." ) . " " . gettext( "At the bottom, you can determine the manner of participant management." ) . " " . gettext( "Also you can add further course admins later on." ) );
	$content->setVariable( "LABEL_COURSE_ID", gettext( "PAUL Course ID" ) );
	$content->setVariable( "VALUE_COURSE_ID", isset($values)?h($values[ "id" ]):'' );
	$content->setVariable( "LABEL_COURSE_NUMBER", gettext( "PAUL Course number" ) );
	$content->setVariable( "VALUE_COURSE_NUMBER", isset($values)?h($values[ "number" ]):'' );
	$content->setVariable( "LABEL_COURSE_NAME", gettext( "Name" ) );
	$content->setVariable( "VALUE_COURSE_NAME", isset($values)?h($values[ "name" ]):'' );
	$content->setVariable( "LABEL_COURSE_SHORT_INFORMATION", gettext( "Short Info" ) );
	$content->setVariable( "VALUE_SHORT_DSC", isset($values)?h($values[ "short_dsc" ]):'' );
	$content->setVariable( "SHORT_DSC_SHOW_UP", gettext( "This value will show up in the semester's courses list beside id, name and staff members." ) );

	$content->setVariable( "VALUE_COURSE_INSTRUCTORS_OBJ_NAME", $values[ "tutors_obj_name" ] );
	$content->setVariable( "VALUE_COURSE_NAME_EN", $values[ "name_en" ] );
	$content->setVariable( "VALUE_PAUL_SEMESTER_ID", $values[ "semester_id" ] );
	$content->setVariable( "VALUE_PAUL_SEMESTER_NAME", $values[ "semester_name" ] );
	$content->setVariable( "VALUE_HOURS_IN_WEEK", $values[ "hours_in_week" ] );
	$content->setVariable( "VALUE_FACULTY_ID", $values[ "faculty_id" ] );
	$content->setVariable( "VALUE_FACULTY_NAME", $values[ "faculty_name" ] );
	$content->setVariable( "VALUE_SMALL_GROUPS", $values[ "small_groups" ] );
	$content->setVariable( "VALUE_EVENT_TYPE", $values[ "event_type" ] );
	$content->setVariable( "VALUE_ASSOCIATED_SMALL_GROUPS", $values[ "associated_small_groups" ] );

	$content->setVariable( "LABEL_COURSE_TUTORS", gettext( "Staff members" ) );
	$content->setVariable( "VALUE_TUTORS", isset($values)?h($values[ "tutors" ]):'' );
	$content->setVariable( "LABEL_LONG_DSC", gettext( "Long description" ) );
	$content->setVariable( "LONG_DSC_SHOW_UP", gettext( "This is for your course page. Please add information about schedule and locations at least." ) );
	$content->setVariable( "VALUE_LONG_DSC", isset($values)?h($values[ "long_dsc" ]):'' );

	$content->setVariable( "LABEL_BB_BOLD", gettext( "B" ) );
	$content->setVariable( "HINT_BB_BOLD", gettext( "boldface" ) );
	$content->setVariable( "LABEL_BB_ITALIC", gettext( "I" ) );
	$content->setVariable( "HINT_BB_ITALIC", gettext( "italic" ) );
	$content->setVariable( "LABEL_BB_UNDERLINE", gettext( "U" ) );
	$content->setVariable( "HINT_BB_UNDERLINE", gettext( "underline" ) );
	$content->setVariable( "LABEL_BB_STRIKETHROUGH", gettext( "S" ) );
	$content->setVariable( "HINT_BB_STRIKETHROUGH", gettext( "strikethrough" ) );
	$content->setVariable( "LABEL_BB_IMAGE", gettext( "IMG" ) );
	$content->setVariable( "HINT_BB_IMAGE", gettext( "image" ) );
	$content->setVariable( "LABEL_BB_URL", gettext( "URL" ) );
	$content->setVariable( "HINT_BB_URL", gettext( "web link" ) );
	$content->setVariable( "LABEL_BB_MAIL", gettext( "MAIL" ) );
	$content->setVariable( "HINT_BB_MAIL", gettext( "email link" ) );

	$content->setVariable( "PARTICIPANT_MANAGEMENT", gettext( "Participant Management" ) );

	/* not used here
	if ( isset($values) && $values[ "lsf_id" ] > 1 )
	{
	        $content->setCurrentBlock( "HIS_LSF_PM" );
	        $content->setVariable( "LSF_COURSE_ID", isset($values)?h($values[ "lsf_id" ]):'' );
	        $content->setVariable( "LSF_COURSE", isset($values)?h($values[ "id" ]):'' . " " . isset($values)?h($values[ "name"]):'' . " (" . isset($values)?h($values[ "short_dsc" ]):'' . ")" );
	        if ( $values[ "hislsf"] )
	        {
	                $content->setVariable( "HISLSF_CHECKED", "CHECKED" );
	        }
	        $content->setVariable( "LABEL_HISLSF", "Ja, es soll die Teilnehmerverwaltung des HIS LSF verwendet werden." );
	        $content->setVariable( "HISLSF_INFO", "Wenn gesetzt, k&ouml;nnen sich Studenten f&uuml;r diesen Kurs nur &uuml;ber das HIS LSF anmelden." );
	        $content->parse( "HIS_LSF_PM" );
	}
	*/

	//if (!isset($values) /*|| !$values["hislsf"]*/ )
	{
	  $content->setCurrentBlock("BLOCK_MAXSIZE");
	  $content->setVariable("LABEL_MAXSIZE", gettext("Max number of participants"));
	  $content->setVariable("LABEL_MAXSIZE_DSC", gettext("To limit the max number of participants for your course enter a number greater than 0. Leave this field blank or enter a '0' for no limitation."));

	  if ( isset( $values["maxsize"] ) )
		  $content->setVariable("VALUE_MAXSIZE", h($values["maxsize"]));
	  $content->parse("BLOCK_MAXSIZE");

	  $content->setCurrentBlock("BLOCK_ACCESS");
	  $content->setVariable( "PARTICIPANT_MANAGEMENT", gettext( "Participant Management" ) );
	}


	$access = koala_group_course::get_access_descriptions( );
	$access_default = PERMISSION_COURSE_PUBLIC;
	if (is_array($access) /*&& (!isset($values) || !$values["hislsf"])*/ ) {
	  $content->setCurrentBlock("BLOCK_ACCESS");
	  foreach($access as $key => $array) {
	    if ( ($key != PERMISSION_UNDEFINED) || ((isset($values) && (int)$values[ "access" ] == PERMISSION_UNDEFINED ))) {
	      $content->setCurrentBlock("ACCESS");
	      $content->setVariable("LABEL", $array["summary_short"] . ": " . $array["label"]);
	      $content->setVariable("VALUE", $key);
	      if ((isset($values) && $key == (int)$values[ "access" ]) || (empty($values) && $key == $access_default)) {
	        $content->setVariable("CHECK", "checked=\"checked\"");
	      }
	      if ($key == PERMISSION_COURSE_PASSWORD) {
	        $content->setVariable("ONCHANGE", "onchange=\"document.getElementById('passworddiv').style.display='block'\"");
	        $content->setCurrentBlock("ACCESS_PASSWORD");
	        $content->setVariable("LABEL_PASSWORD", gettext("Password"));
	        if (!empty($values["password"])) $content->setVariable("VALUE_PASSWORD", $values["password"]);
	        if ((isset($values["access"]) && $values["access"] == PERMISSION_COURSE_PASSWORD)) {
	          $content->setVariable("PASSWORDDIV_DISPLAY", "block");
	        } elseif (!isset($values["access"]) && $access_default == PERMISSION_COURSE_PASSWORD) {
	          $content->setVariable("PASSWORDDIV_DISPLAY", "block");
	        } else {
	          $content->setVariable("PASSWORDDIV_DISPLAY", "none");
	        }
	        $content->parse("ACCESS_PASSWORD");
	      }
	      else {
	        $content->setVariable("ONCHANGE", "onchange=\"document.getElementById('passworddiv').style.display='none'\"");
	      }
	      $content->parse("ACCESS");
	    }
	  }
	  $content->parse("BLOCK_ACCESS");
	}

	// extensions:
	$extensions = lms_steam::get_extensionmanager()->get_extensions_by_class( 'koala_group_course' );
	if ( count( $extensions ) > 0 ) {
		$content->setCurrentBlock( "BLOCK_EXTENSIONS" );
		$content->setVariable( "LABEL_EXTENSIONS", gettext( "Extensions" ) );
		$extension_list = array();
		foreach ( $extensions as $extension ) {
			if( $extension->get_requirements() === array() )
			{
			$extension_name = $extension->get_name();
			$content->setCurrentBlock( "BLOCK_EXTENSION" );
			$content->setVariable( "EXTENSION_ID", $extension_name );
			$content->setVariable( "EXTENSION_NAME", $extension->get_display_name() );
			$content->setVariable( "EXTENSION_DESC", $extension->get_display_description() );
				$subextensions = lms_steam::get_extensionmanager()->get_dependent_extensions($extension);
				if( count( $subextensions ) > 0 )
				{
					$content->setCurrentBlock( "BLOCK_SUBEXTENSIONS" );
					$content->setVariable( "LABEL_SUBEXTENSIONS", str_replace( "%EXTENSION", h($extension->get_display_name()), gettext( "The following sub-extensions are available for %EXTENSION" ) ));
					foreach($subextensions as $subextension)
					{
						$subextension_name = $subextension->get_name();
						$content->setCurrentBlock( "BLOCK_SUBEXTENSION" );
						$content->setVariable( "PARENT_EXTENSION_ID", $extension_name );
						$content->setVariable( "SUBEXTENSION_ID", $subextension_name );
						$content->setVariable( "SUBEXTENSION_NAME", $subextension->get_display_name() );
						$content->setVariable( "SUBEXTENSION_DESC", $subextension->get_display_description() );
						$content->setVariable( "SUBEXTENSION_DISABLED", "disabled=\"disabled\"" );
						$content->parse( "BLOCK_SUBEXTENSION" );
						$extension_list[] = $subextension_name;
					}
					$content->parse( "BLOCK_SUBEXTENSIONS" );
				}
			$content->parse( "BLOCK_EXTENSION" );
			$extension_list[] = $extension_name;
			}
		}
		$content->setVariable( "VALUE_EXTENSIONS", implode( "/", $extension_list ) );
		$content->parse( "BLOCK_EXTENSIONS" );
	}
	/*
	if ($current_semester->get_identifier() !== SYNC_KOALA_SEMESTER) {
	  $content->setVariable("LSF_INFOTEXT", gettext("The Option to get the Informations from LSF is only available in semester " . SYNC_KOALA_SEMESTER));
	  $content->setVariable("LSF_DISABLED", "disabled=\"disabled\"");
	}
	*/

	$content->setVariable( "LABEL_CREATE_COURSE", gettext( "Create and finish" ) );
	$content->setVariable( "LABEL_CREATE_ADD_ADMIN", gettext( "Create and add further admins" ) );

	$content->parse( "BLOCK_CREATE_COURSE" );
}
// /CREATE


// FINISH
if ( !empty($_REQUEST[ "values" ]) )
{
	$values = $_POST[ "values" ];

/*
	//$values[ "hislsf" ] = "";
	//$values[ "lsf_id" ] = "";
*/

        $problems = "";
        $hints    = "";

        if ( empty( $values[ "semester" ] ) )
        {
                throw new Exception( "Semester is not given." );
        }

        if ( empty( $values[ "id" ] ) )
        {
                $problems .= gettext( "The course ID is missing." ) . " ";
                $hints    .= gettext( "The ID is necessary for unique identification, ordering and finding the course. Please fill this out." ) . " ";
        }


        if ( !empty( $values[ "access" ] ) && $values["access"] == PERMISSION_COURSE_PASSWORD && empty($values["password"]) ) {
                $problems .= gettext( "The course password is missing." ) . " ";
                $hints    .= gettext( "You chose to password protect your course. Please provide a password." ) . " ";
        }

        if ( empty( $values[ "number" ] ) )
        {
                $problems .= gettext( "The course number is missing." ) . " ";
                $hints    .= gettext( "The number is necessary for identification, ordering and finding the course. Please fill this out." ) . " ";
        }


        if ( empty( $problems ) )
        {
                if ( empty( $values[ "name" ] ) )
                {
                        $problems .= gettext( "The course name is missing." ) . " ";
                        $hints    .= gettext( "A name is necessary for identification." ) . " ";
                }

                if ( strpos($values[ 'id' ], '.' ) )
                {
                  $problems .= gettext("Please don't use the \".\"-char in the course ID.") . ' ';
                }

                if ( empty( $values[ "tutors" ] ) )
                {
                        $values[ "tutors" ] = "NN";
                }

                if ( empty( $problems ) )
                {
                        //not used here anymore
                        /*
                        if ( !isset($values["hislsf"]) || ! $values[ "hislsf" ] )
                        {
                                $values[ "lsf_id" ] = "";
                        }
                        */

                        $max_members = -1;
                        //if ($values["lsf_id"] === "")
                        //{
                          if ( !empty( $values[ "maxsize" ] ) && trim($values[ "maxsize" ]) != "" && preg_match('/[^-.0-9]/', trim($values[ "maxsize" ])) )
                          {
                            $problems .= gettext( "Invalid max number of participants." ) . " ";
                            $hints    .= gettext( "Please enter a valid number for the max number of participants."). " " . gettext("Please note that the input of a '0' or to leave the field blank means no limitation." ) . " ";
                          }
                          else
                          {
                            if ( !empty( $values[ "maxsize" ] ) && trim($values[ "maxsize" ]) != "" && trim($values[ "maxsize" ]) < 0 )
                            {
                              $problems .= gettext( "Invalid max number of participants." ) . " ";
                              $hints    .= gettext( "Please enter a number equal or greater than '0' for the max number of participants.") . " " . gettext("Please note that the input of a '0' or to leave the field blank means no limitation." ) . " ";
                            }
                            else
                            {
                              if (isset( $values[ "maxsize" ] ))
                              {
                                if (trim($values[ "maxsize" ]) === "") $max_members = 0;
                                else $max_members = (int)trim($values["maxsize"]);
                              }
                            }
                          }
                        //}


                        if (empty($problems))
                        {
                        	if( !empty($values[ "associated_small_groups" ]) )
                        	{
                        		$course_associated_small_groups = explode("+", $values[ "associated_small_groups" ]);
                        	}
                        	else
                        	{
                        		$course_associated_small_groups = "";
                        	}

                            $new_course = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), $values[ "id" ], $current_semester, FALSE, $values[ "name" ] );
							$new_course->set_attributes( array(
                                                  "OBJ_TYPE"                     => "course",
                                                  "COURSE_PARTICIPANT_MNGMNT"    => $obj_type,  // TODO: $obj_type seems not to be set...?
                                                  "COURSE_SEMESTER"              => $values[ "semester" ],
                                                  "COURSE_TUTORS"                => $values[ "tutors" ],
                                                  "COURSE_SHORT_DSC"             => $values[ "short_dsc" ],
                                                  "COURSE_LONG_DSC"              => $values[ "long_dsc" ],
                                                  "COURSE_NAME_EN"               => $values[ "name_en" ],
                                                  "COURSE_NUMBER"                => $values[ "number" ],
                                                  "COURSE_PAUL_SEMESTER_ID"      => $values[ "paul_semester_id" ],
                                                  "COURSE_PAUL_SEMESTER_NAME"    => $values[ "paul_semester_name" ],
                                                  "COURSE_HOURS_IN_WEEK"         => $values[ "hours_in_week" ],
                                                  "COURSE_FACULTY_ID"            => $values[ "faculty_id" ],
                                                  "COURSE_FACULTY_NAME"          => $values[ "faculty_name" ],
                                                  "COURSE_SMALL_GROUPS"          => $values[ "small_groups" ],
                                                  "COURSE_EVENT_TYPE"            => $values[ "event_type" ],
                                                  "COURSE_ASSOCIATED_SMALL_GROUPS" => $course_associated_small_groups
                                                  /* , "COURSE_HISLSF_ID" => $values[ "lsf_id" ]*/
                                                  ));
                          $learners = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), "learners", $new_course, FALSE, "Participants of course '" . $values[ "name" ]. "'" );
                          $learners->set_attribute( "OBJ_TYPE", "course_learners" );
                          $staff    = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), "staff", $new_course, FALSE, "Tutors of course '" . $values[ "name" ] . "'");
                          $admins   = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), "admins", $new_course, FALSE, "Admins of course '" . $values[ "name" ] . "'");
                          $staff->set_attribute( "OBJ_TYPE", "course_staff" );
                          $staff->set_attribute( "OBJ_TYPE", "course_admins" );
                          $staff->add_member( $user );

						  $hide_user = true;
						  //add the instructors/tutors
						  if( strlen($values[ "tutors_obj_name" ]) > 0 )
						  {
	                          $paul_tutors_obj_name = explode( "+", $values[ "tutors_obj_name" ]);
	                          foreach( $paul_tutors_obj_name as $paul_tutor_obj_name )
	                          {
	                          	$paul_tutor = steam_factory::username_to_object( $GLOBALS["STEAM"]->get_id(), $paul_tutor_obj_name );

	                          	if( $paul_tutor->get_id() === $user->get_id() ) $hide_user = false;

	                          	$staff->add_member( $paul_tutor );

  // Falls konfiguriert, automatische E-Mail-Benachrichtigung der Instructors
                    if ( defined( "PAUL_INSTRUCTOR_NOTIFICATION" ) && PAUL_INSTRUCTOR_NOTIFICATION === TRUE )
						        {
						        	$message = str_replace( "%NAME", $paul_tutor->get_attribute( "USER_FIRSTNAME" ) . " " . $paul_tutor->get_attribute( "USER_FULLNAME" ), gettext( "Hallo %NAME," ) ). "\n\n";
						            $message .= str_replace( "%GROUP", $course = $values[ "name" ], gettext( "You have been automatically added to the staff of course '%GROUP' because of your membership in that course in the PAUL system." ) ) . "\n\n";
						            $message .= gettext( "This is an automatically generated email." );
						            lms_steam::mail($paul_tutor, lms_steam::get_current_user(), PLATFORM_NAME . ": " . str_replace( "%GROUP", $course = $values[ "name" ], gettext( "You have been added to the staff of course '%GROUP'." ) ) , $message);
						        }
	                          }
						  }
						  if( $hide_user ) $new_course->set_attribute( "COURSE_HIDDEN_STAFF", array($user->get_id()) );
                          // uncomment below if koala can handle admins vs tutors
                          //$admins->add_member( $user );

                          // RIGHTS MANAGEMENT =======================================
                          $course_calendar = $new_course->get_calendar();
                          $learners_workroom = $learners->get_workroom();
                          $course_workroom = $new_course->get_workroom();

                          $staff->set_sanction_all( $staff );
                          $staff->sanction_meta( SANCTION_ALL, $staff);
                          $learners->set_sanction_all( $staff );
                          $learners->sanction_meta( SANCTION_ALL, $staff );
                          $new_course->set_sanction_all( $staff );
                          $new_course->sanction_meta( SANCTION_ALL, $staff );

                          $admins->set_sanction_all( $admins );
                          $admins->sanction_meta( SANCTION_ALL, $admins);
                          $staff->set_sanction_all( $admins );
                          $staff->sanction_meta( SANCTION_ALL, $admins);
                          $learners->set_sanction_all( $admins );
                          $learners->sanction_meta( SANCTION_ALL, $admins );
                          $new_course->set_sanction_all( $admins );
                          $new_course->sanction_meta( SANCTION_ALL, $admins );

                          $course_calendar->set_acquire( FALSE );
                          $course_calendar->set_sanction_all( $staff );
                          $course_calendar->sanction_meta(SANCTION_ALL, $staff);
                          $course_calendar->set_sanction_all( $admins );
                          $course_calendar->sanction_meta(SANCTION_ALL, $admins);
                          $course_calendar->set_read_access( $learners, TRUE );
                          $course_calendar->set_write_access( $new_course, FALSE );
                          $course_calendar->set_insert_access( $new_course, FALSE );
                          $course_calendar->set_insert_access( $all_users, FALSE );
                          // Course workroom
                          $course_workroom->set_sanction($new_course, SANCTION_READ | SANCTION_EXECUTE | SANCTION_ANNOTATE);
                          $course_workroom->set_sanction_all( $staff );
                          $course_workroom->set_sanction_all( $admins );
                          $course_workroom->sanction_meta(SANCTION_ALL, $staff);
                          $course_workroom->sanction_meta(SANCTION_ALL, $admins);
                          // Learners workroom
                          $learners_workroom->set_read_access( $all_users, TRUE );
                          $learners_workroom->set_sanction($learners, SANCTION_READ | SANCTION_EXECUTE | SANCTION_ANNOTATE);
                          $learners_workroom->set_sanction_all( $staff );
                          $learners_workroom->set_sanction_all( $admins );
                          $learners_workroom->sanction_meta(SANCTION_ALL, $staff);
                          $learners_workroom->sanction_meta(SANCTION_ALL, $admins);

                          $koala_course = new koala_group_course($new_course);
                          //if ( !isset($values[ "hislsf" ]) || !$values[ "hislsf"]) //not used here anymore
                          //{
	                            $access = $values["access"];
	                            $koala_course->set_access($access, $learners, $staff, $admins, KOALA_GROUP_ACCESS );
                            	if (isset($values["password"]) && $access == PERMISSION_COURSE_PASSWORD) $koala_course->get_group_learners()->set_password( $values["password"]);
                            	else $koala_course->get_group_learners()->set_password("");
                          //}
                          /*else
                          {
                            	$koala_course->set_access(PERMISSION_COURSE_HISLSF, $learners, $staff, $admins, KOALA_GROUP_ACCESS);
                          }
                          */
                          if ($max_members > -1) $learners->set_attribute(GROUP_MAXSIZE, $max_members);
                          // RIGHTS MANAGEMENT =======================================

						// extensions:
						if ( isset( $_POST["extensions_available"] ) && !empty( $_POST["extensions_available"] ) )
						{
							$extensions_available = explode( "/", $_POST["extensions_available"] );
							if ( isset( $_POST["extensions_enabled"] ) )
								$extensions_enabled = $_POST["extensions_enabled"];
							else
								$extensions_enabled = array();
							if ( isset( $_POST["extensions_enabled_add"]))
								$extensions_enabled = array_merge($extensions_enabled, explode("/", $_POST["extensions_enabled_add"]));
							if ( is_array( $extensions_available ) )
							{
								foreach ( $extensions_available as $extension_name )
								{
									$extension = lms_steam::get_extensionmanager()->get_extension( $extension_name );
									if ( !is_object( $extension ) ) continue;
									if ( array_search( $extension_name, $extensions_enabled ) === FALSE )
										$extension->disable_for( $koala_course );
									else
										$extension->enable_for( $koala_course );
								}
							}
						}

                          $cache = get_cache_function( "ORGANIZATION" );
                          $cache->drop( "lms_steam::semester_get_courses", $current_semester->get_id() );
                          header( "Location: " . PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name(). "/" . $new_course->get_name() . "/" );
                          exit;
                        }
                }
        }
        if ( ! empty( $problems ) )
        {
                $portal->set_problem_description( $problems, $hints );
                // $_session["error"] = "test";  // FOR REDIRECT HEADER
                //$portal->set_confirmation( "test");
                // $_session["confirmation"] = "test";  // FOR REDIRECT HEADER
        }

}
// /FINISH


// Search
if( $show_search_block )
{
	$content->setCurrentBlock( "BLOCK_SEARCH" );
	$content->setVariable( "FORM_SEARCH_ACTION", PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name() . "/paul/" );
	$content->setVariable( "HEAD_SEARCH", gettext( "Search" ) );
	$content->setVariable( "LABEL_SEARCH", gettext( "Search" ) );
	//$content->setVariable( "COURSE_CREATE_INFO_TEXT", str_replace( "%SEMESTER", h($current_semester->get_attribute( "OBJ_DESC")), gettext( "You are going to create a new PAUL course in <b>%SEMESTER</b>." ) ) . " " . gettext( "Please choose out the lecturer at first." ) . " " . gettext( "Either you can search by the first or last name, or by a part of the email-address or login." ) . " " . gettext( "In the second step you can choose out the course you want to create." . " <br/><br/>" . gettext( "Hint: Use '%' as a wildcard in your search pattern." ) ) );
	$content->setVariable( "LABEL_CHECK_NAME", gettext( "Name" ) );
	$content->setVariable( "LABEL_CHECK_LOGIN", gettext( "Email address or login" ) );
	$content->parse( "BLOCK_SEARCH" );
}
// /Search



$portal->set_page_main(
                array( array( "link" => PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name(). "/", "name" => h($current_semester->get_attribute( "OBJ_DESC" )) ), array( "link" => "", "name" => gettext( "Create new course via PAUL" ) ) ),
                $content->get()

                );
$portal->show_html();

?>
