<?php
  // Deactivate HISLSF Support
//	include( "bad_link.php" );
//	exit;

	require_once( "../etc/koala.conf.php" );
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
	$user = lms_steam::get_current_user();
	$all_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );

	if ( (! lms_steam::is_steam_admin( $user )) && ( ! lms_steam::is_semester_admin( $current_semester, $user ))  )
	{
		include( "bad_link.php" );
		exit;
	}

	$lsf_client = new hislsf_soap();
	$result = $lsf_client->get_available_courses( SYNC_HISLSF_SEMESTER, $path[ 1 ] );
	$content = new HTML_TEMPLATE_IT();
	$content->loadTemplateFile( PATH_TEMPLATES . "courses_create_hislsf.template.html" );
	$content->setVariable( "FORM_ACTION", PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name() . "/new/" );
	foreach( $result->veranstaltung as $course )
	{
		$content->setCurrentBlock( "LSFCOURSE" );
		$content->setVariable( "LSF_COURSE_ID", (string) $course->Veranstaltungsschluessel );
		$content->setVariable( "LSF_COURSE_NAME", (string) $course->Veranstaltungstyp . " " . (string) $course->Veranstaltungsname );
		$content->parse( "LSFCOURSE" );
	}

	$portal->set_page_main( 
		array( array( "link" => PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name(). "/", "name" => $current_semester->get_attribute( "OBJ_DESC" ) ), array( "linK" => "", "name" => gettext( "Create new Course via LSF" ) ) ),
		$content->get()

	);
	$portal->show_html();

?>
