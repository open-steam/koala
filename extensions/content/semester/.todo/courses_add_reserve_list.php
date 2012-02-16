<?php

require_once( "../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$user = lms_steam::get_current_user();

if ( ! $steam_group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "course" ] ) )
	throw new Exception( "Course not found: " . $_GET[ "course" ] );
if ( ! $steam_group instanceof steam_group )
	throw new Exception( "Is not a group: " . $_GET[ "course" ] );
if ( ( string ) $steam_group->get_attribute( "OBJ_TYPE" ) != "course" )
	throw new Exception( "Is not a course: " . $_GET[ "course" ] );

$course = new koala_group_course( $steam_group );
$backlink = PATH_URL . SEMESTER_URL . "/" . $course->get_semester()->get_name() . "/" . $course->get_name() . "/";

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$values = $_POST[ "values" ];
	
	if ( empty( $values[ "sem_app_id" ] ) )
	{
		$problems = "Seminarapparat ID fehlt.";
		$hints    = "Bitte geben Sie eine g&uuml;ltige ID ein.";
	}
	
	if ( empty( $values[ "sem_app_token" ] ) )
	{
		$problems = "Seminarapparat Access-Token fehlt.";
		$hints    = "Bitte geben Sie einen g&uuml;ltigen Access-Token ein.";
	}
	
	if ( empty( $problems ) )
	{
		$course->set_attributes( array("SEM_APP_ID" => $values[ "sem_app_id" ]) );
		$course->set_attributes( array("SEM_APP_TOKEN" => $values[ "sem_app_token" ]) );
    	
		if ($rlid) 
    	{
    		$_SESSION[ "confirmation" ] = gettext( "Reserve list changed." );
    	}
    	else
    	{
    		$_SESSION[ "confirmation" ] = gettext( "Reserve list added." );
			header( "Location: " . $backlink . "reserve_list/" );
			exit;
    	}
	}
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}

$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES );
$content->loadTemplateFile( "courses_add_reserve_list.template.html" );

$content->setVariable( "SEM_APP_ID", $values[ "sem_app_id" ] );
$content->setVariable( "SEM_APP_TOKEN", $values[ "sem_app_token" ] );	

$content->setVariable( "LABEL_SEM_APP_ID", "Seminarapparat ID" );
$content->setVariable( "LABEL_SEM_APP_TOKEN", "Seminarapparat Access-Token" );
$content->setVariable( "LABEL_SAVE", gettext( "Save" ) );

$text = gettext( "Create reserve list" );
if ($rlid) $text = gettext("Edit reserve list");

$portal->set_page_main( array( array( "link" => $backlink, "name" => h($course->get_course_name()) ), array( "link" => "", "name" => $text ) ), $content->get() );
$portal->show_html();
?>
