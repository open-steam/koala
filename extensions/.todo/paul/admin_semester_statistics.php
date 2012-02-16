<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
if( !lms_steam::is_koala_admin($user) )
{
	header("location:/");
	exit;
}

$portal_user = $portal->get_user();
$path = url_parse_rewrite_path( (isset($_GET["path"])?$_GET[ "path" ]:"") );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "admin_semester_statistics.template.html" );

$content->setVariable( "LABEL_SHOW_SEMESTER_STATISTICS", gettext( "Show semester statistics" ) );


if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	// semester id
	$semester_post = explode(",", $_POST[ "semester" ]);
	$current_semester_obj_id = $semester_post[0];
	$current_semester_obj_name = $semester_post[1];

	// semester kurse
	$cache = get_cache_function( "ORGANIZATION", 600 );
	$courses = $cache->call( "lms_steam::semester_get_courses", $current_semester_obj_id );
	$count_semester_courses = count( $courses );

	$count_semester_occupancy = 0;
    $count_semester_occupancy_ext = 0;
	$count_semester_koala_courses = 0;
	$count_semester_hislsf_courses = 0;
	$count_semester_paul_courses = 0;
	$count_semester_paul_synchronized_courses = 0;
	$count_semester_synchronized_courses = 0;
	$count_semester_participants_hislfs = 0;
	$count_semester_participants_paul = 0;
        $count_semester_participants_paul_sync = 0;
	$count_semester_participants_paul_nosync = 0;
	$count_semester_participants_koala = 0;
	$count_entire_group_members = 0;
	$count_private_group_members = 0;
	$count_public_group_members = 0;

	foreach( $courses as $course )
	{
		//if( is_object($course) )
		//{
	      	if( $course["COURSE_HISLSF_ID"] > 0 ) //HIS-LSF course
		{
	        	$count_semester_hislsf_courses++;
	        	$count_semester_participants_hislfs += $course["COURSE_NO_PARTICIPANTS"];
	      	}
	      	elseif( koala_group_course::is_paul_course($course["COURSE_NUMBER"]) ) //PAUL course
	      	{
	      		$count_semester_paul_courses++;
	      		$count_semester_participants_paul += $course["COURSE_NO_PARTICIPANTS"];
	                if( $course["KOALA_GROUP_ACCESS"] == PERMISSION_COURSE_PAUL_SYNC ) //PAUL participant synchronized courses
                         {
                           $count_semester_paul_synchronized_courses++;
			    $count_semester_participants_paul_sync += $course["COURSE_NO_PARTICIPANTS"];
			 } else {
			    $count_semester_participants_paul_nosync +=  $course["COURSE_NO_PARTICIPANTS"];
			 }
	      	}
	      	else //koaLA course
	      	{
	      		$count_semester_koala_courses++;
	      		$count_semester_participants_koala += $course["COURSE_NO_PARTICIPANTS"];
	      	}

    	//}
	}

	// private gruppen
	$cache = get_cache_function( STEAM_PRIVATE_GROUP, CACHE_LIFETIME_STATIC );
	$private_subgroups = $cache->call( "lms_steam::group_get_subgroups", STEAM_PRIVATE_GROUP );
	$count_semester_private_groups = count($private_subgroups);
	foreach( $private_subgroups as $private_subgroup )
	{
		$count_private_group_members += $private_subgroup["NO_MEMBERS"];
	}

	// öffentliche gruppen
	$cache = get_cache_function( STEAM_PUBLIC_GROUP, CACHE_LIFETIME_STATIC );
	$public_subgroups = $cache->call( "lms_steam::group_get_subgroups", STEAM_PUBLIC_GROUP );
	$count_semester_public_groups = count($public_subgroups);
	foreach( $public_subgroups as $public_subgroup )
	{
		$count_public_group_members += $public_subgroup["NO_MEMBERS"];
	}

	// alle gruppen
	$count_semester_groups = $count_semester_private_groups + $count_semester_public_groups;

	// gesamte gruppen mitglieder
	$count_entire_group_members = $count_private_group_members + $count_public_group_members;

	// alle kurs mitglieder
	$count_semester_course_participants = $count_semester_participants_hislfs + $count_semester_participants_paul + $count_semester_participants_koala;

	// alle synchronisierten kurse
	$count_semester_synchronized_courses = $count_semester_paul_synchronized_courses;


	$content->setCurrentBlock( "BLOCK_SHOW" );

	$content->setVariable( "LABEL_SEMESTER_INFORMATION", gettext( "Semester information" ) . " " . $current_semester_obj_name );

	$content->setVariable( "LABEL_COURSES", gettext( "Courses" ) );
	$content->setVariable( "LABEL_NUMBER", gettext( "Number" ) );
	$content->setVariable( "LABEL_SYNCHRONIZED_COURSES", gettext( "Participant synchronization usage" ) );
	$content->setVariable( "LABEL_OVERALL", gettext( "Over-all" ) );
	$content->setVariable( "LABEL_HISLSF_COURSES", gettext( "Imported HIS-LSF courses" ) );
	$content->setVariable( "LABEL_PAUL_COURSES", gettext( "Imported PAUL courses" ) );
	$content->setVariable( "LABEL_KOALA_COURSES", gettext( "koaLA courses" ) );
	$content->setVariable( "LABEL_COURSE_PARTICIPANTS", gettext( "Course participants" ) );

	$content->setVariable( "LABEL_GROUPS", gettext( "Groups" ) );
	$content->setVariable( "LABEL_GROUP_MEMBERS", gettext( "Members" ) );
	$content->setVariable( "LABEL_ALL_GROUPS", gettext( "All Groups" ) );
	$content->setVariable( "LABEL_PUBLIC_GROUPS", gettext( "Public Groups" ) );
	$content->setVariable( "LABEL_PRIVATE_GROUPS", gettext( "Private Groups" ) );

	$content->setVariable( "COUNT_SEMESTER_COURSE_PARTICIPANTS", $count_semester_course_participants );
	$content->setVariable( "COUNT_SEMESTER_HISLSF_COURSE_PARTICIPANTS", $count_semester_participants_hislfs );
	$content->setVariable( "COUNT_SEMESTER_PAUL_COURSE_PARTICIPANTS", $count_semester_participants_paul . " (" . $count_semester_participants_paul_nosync . "/" . $count_semester_participants_paul_sync . ")");
	$content->setVariable( "COUNT_SEMESTER_KOALA_COURSE_PARTICIPANTS", $count_semester_participants_koala );
	$content->setVariable( "COUNT_SEMESTER_COURSES", $count_semester_courses );
	$content->setVariable( "COUNT_SEMESTER_HISLSF_COURSES", $count_semester_hislsf_courses );
	$content->setVariable( "COUNT_SEMESTER_PAUL_COURSES", $count_semester_paul_courses );
	$content->setVariable( "COUNT_SEMESTER_KOALA_COURSES", $count_semester_koala_courses );
	$content->setVariable( "COUNT_SEMESTER_PAUL_SYNCHRONIZED_COURSES", $count_semester_paul_synchronized_courses );
	$content->setVariable( "COUNT_SEMESTER_SYNCHRONIZED_COURSES", $count_semester_synchronized_courses );
	$content->setVariable( "COUNT_SEMESTER_GROUPS", $count_semester_groups );
	$content->setVariable( "COUNT_SEMESTER_ENTIRE_GROUP_MEMBERS", $count_entire_group_members );
	$content->setVariable( "COUNT_SEMESTER_PUBLIC_GROUPS", $count_semester_public_groups );
	$content->setVariable( "COUNT_SEMESTER_PRIVATE_GROUPS", $count_semester_private_groups );
	$content->setVariable( "COUNT_SEMESTER_PUBLIC_GROUP_MEMBERS", $count_public_group_members );
	$content->setVariable( "COUNT_SEMESTER_PRIVATE_GROUP_MEMBERS", $count_private_group_members );

	$content->parse( "BLOCK_SHOW" );
}

$cache     = get_cache_function( "ORGANIZATION", 600 );
$semesters = $cache->call( "lms_steam::get_semesters" );

$select_option_no = 0;
for( $i=0; $i < count($semesters); $i++ )
{
	$content->setCurrentBlock( "BLOCK_SELECT_OPTION" );
	$content->setVariable( "SELECT_OPTION_NO", $select_option_no );
	$content->setVariable( "SEMESTER_ID", $semesters[$i]["OBJ_ID"] );
	$content->setVariable( "SEMESTER_NAME", $semesters[$i]["OBJ_NAME"] );

	if( isset($_POST) && isset($_POST["semester"]) )
	{
		$semester_post = explode(",", $_POST[ "semester" ]);
		$selected_semester = $semester_post[2];
	}
	else $selected_semester = -1;

	if( $i == $selected_semester )
	{
		$content->setVariable("SEMESTER_SELECTED", "selected='selected'");
	}

	$content->parse( "BLOCK_SELECT_OPTION" );

	$select_option_no++;
}

$portal->set_page_main( "", $content->get(), "" );
$portal->show_html();
?>
