<?php

require_once( "../etc/koala.conf.php" );
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();

if ( (! lms_steam::is_steam_admin( $user )) && ( ! lms_steam::is_semester_admin( $current_semester, $user )) && ( ! $course->is_admin( $user ) )  )
{
	include( "bad_link.php" );
	exit;
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
		if ( $_POST[ "id" ] == $unit->get_id() )
		{
			try
			{
        lms_steam::delete( $unit );
			}
			catch( Exception $exception )
			{
				$problems = $exception->get_message();
			}
		}
		
		if ( empty( $problems ) )
		{
			$_SESSION[ "confirmation" ] = str_replace( "%NAME", $unit_name, gettext( "Unit %NAME deleted." ) );
			header( "Location: " . PATH_URL . SEMESTER_URL . "/" . $course->get_semester()->get_name() . "/" . $course->get_name() . "/units/" );
	   		exit;
		}
		
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "courses_unit_delete.template.html" );
$content->setVariable( "UNIT_ID", $unit->get_id() );
$content->setVariable( "BACK_LINK", $backlink );
$content->setVariable( "INFO_TEXT", gettext( "If you go on , this unit will be deleted." ) );
$content->setVariable( "LABEL_OK", gettext( "OK" ) );

$portal->set_page_main( 
	array( array( "link" => PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name(). "/" . $group->get_name() . "/units/" . $unit->get_id() . "/", "name" => $unit->get_name() ), array( "linK" => "", "name" => gettext( "Delete this unit" ) ) ),
	$content->get()
);
$portal->show_html();

?>