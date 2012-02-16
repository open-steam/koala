<?php
require_once( "../etc/koala.conf.php" );
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();

if ( (! $course->is_admin( $user )) && (! lms_steam::is_steam_admin( $user )) && ( ! lms_steam::is_semester_admin( $current_semester, $user ))  )
{
	include( "bad_link.php" );
	exit;
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$values = $_POST[ "values" ];
	$problems = "";
	$hints    = "";
	
	$values = $_POST[ "values" ];
	
	if ( empty( $values[ "OBJ_NAME" ] ) )
	{
		$problems = gettext( "The unit name is missing." ) . " ";
	}

	if ( empty( $values[ "OBJ_DESC" ] ) )
	{
		$problems .= gettext( "A short description is missing." ) . " ";
	}
	
  if ( strpos($values[ "OBJ_NAME" ], "/" )) {
   $problems .= gettext("Please don't use the \"/\"-char in the the forum name.");
  }
	
	if ( empty( $problems ) )
	{
		$unit_name = $unit->get_name();
		$unit->set_attributes( $values );
		$_SESSION[ "confirmation" ] = str_replace( "%NAME", $unit_name, gettext( "Unit %NAME changed successfully." ) );
		header( "Location: " . PATH_URL . SEMESTER_URL . "/" . $course->get_semester()->get_name() . "/" . $course->get_name() . "/units/" . $unit->get_id() . "/" );
	   exit;
		
		
	}
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}
else
{
	$values = $unit->get_attributes( array( "OBJ_NAME", "OBJ_DESC", "OBJ_LONG_DESC" ) );
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "courses_unit_edit.template.html" );

$content->setVariable( "LABEL_EDIT_UNIT_DESCRIPTION", gettext( "Edit Unit Description" ) );

$content->setVariable( "LABEL_UNIT_NAME", gettext( "Name" ) );
$content->setVariable( "VALUE_UNIT_NAME", h($values[ "OBJ_NAME" ]) );
$content->setVariable( "LABEL_UNIT_DESC", h(gettext( "Short Info" )) );
$content->setVariable( "VALUE_UNIT_DESC", $values[ "OBJ_DESC" ] );

$content->setVariable( "LABEL_UNIT_LONG_DESC", gettext( "Long description" ) );
$content->setVariable( "LONG_DSC_SHOW_UP", gettext( "This is for your lists of units. Please add information about schedule at least." ) );
$content->setVariable( "VALUE_UNIT_LONG_DESC", $values[ "OBJ_LONG_DESC" ] );

$content->setVariable( "LABEL_SAVE_CHANGES", gettext("Save changes") );

$portal->set_page_main( 
	array( array( "link" => PATH_URL . SEMESTER_URL . "/" .h($current_semester->get_name()). "/" . h($group->get_name()) . "/", "name" => h($values["OBJ_DESC"]) ), array( "linK" => "", "name" => gettext( "Edit a unit" ) ) ),
	$content->get()
);
$portal->show_html();

?>
