<?php
if (!defined("PATH_TEMPLATES_UNITS_PYRAMIDDISCUSSION")) define( "PATH_TEMPLATES_UNITS_PYRAMIDDISCUSSION", PATH_EXTENSIONS . "units_pyramiddiscussion/templates/" );

require_once( "../etc/koala.conf.php" );
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();

$unitname = $unit->get_display_name();

if ( (! lms_steam::is_steam_admin( $user )) && ( ! lms_steam::is_semester_admin( $current_semester, $user )) && ( ! $course->is_admin( $user ) ) )
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
				$unit->delete();
				$owner = koala_object::get_koala_object( steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $_POST["owner"] ) );
				$backlink = $owner->get_url() . "units/";
			}
			catch( Exception $exception )
			{
				$problems = $exception->get_message();
			}
		}
		
		if ( empty( $problems ) )
		{
			$_SESSION[ "confirmation" ] = str_replace( "%NAME", $unitname, gettext( "The unit '%NAME' has been deleted." ) );
			header( "Location: " . $backlink );
	   		exit;
		}
		
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES_UNITS_PYRAMIDDISCUSSION . "units_pyramiddiscussion_delete.template.html" );
$content->setVariable( "UNIT_ID", $unit->get_id() );
$content->setVariable( "OWNER_ID", $owner->get_id() );
$content->setVariable( "BACK_LINK", $backlink );
$content->setVariable( "INFO_TEXT", gettext( "Do you really want to delete this unit?" ) );
$content->setVariable( "LABEL_OK", gettext( "Delete unit" ) );
$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );

$semester = $course->get_semester()->get_name();
$breadcrumb = array (
						array( "name" => $semester , "link" => PATH_URL . SEMESTER_URL . "/" . $semester . "/" ),
						array( "name" => $course->get_display_name(), "link" => PATH_URL . SEMESTER_URL . "/" . $semester . "/" . $course->get_name() . "/" ),
						array( "name" => gettext("Units"), "link" =>  PATH_URL . SEMESTER_URL . "/" . $semester . "/" . $course->get_name() . "/units/"),
						$unit->get_link(),
						array( "name" => gettext( "Delete this unit" ) )
					);
					
$portal->set_page_main( 
	$breadcrumb,
	$content->get()
);
$portal->show_html();
exit;
?>