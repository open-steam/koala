<?php
define("PATH_TEMPLATES_UNITS_BASE", PATH_EXTENSIONS . "units_base/templates/");

require_once("classes/unitmanager.class.php");

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();

if ( !isset( $owner ) && isset( $_POST["owner"] ) ) {
	$steam_owner = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), (int)$_POST["owner"] );
	$owner = koala_object::get_koala_object( $steam_owner );
}

if ( ! $owner->is_admin( $user ) ) {
	throw new Exception( "No group admin!", E_ACCESS );
}

$um = unitmanager::create_unitmanager( $owner );

$content_ready = FALSE;

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$problems = "";
	$hints    = "";
	if ( empty( $problems ) )
	{
		if (isset($_POST["unit_new"]) && is_array($_POST["unit_new"]) )
		{
			$unitname = array_keys($_POST["unit_new"]);
			$unitname = $unitname[0];

			$akt_unit = $um->get_unittype( $unitname );
	   
			if ( $akt_unit != FALSE )
			{
				$akt_unit->set_course( $owner );
	    
				if (isset($_POST["values"]))
				{
					include($akt_unit->get_path() . "/modules/" . $akt_unit->get_name() . "_new.php");
					$content_ready = TRUE;
					$html_ready = $unit_new_html;
				}
				else
				{
					$owner_name = $owner->get_display_name();  // if we call $owner->get_display_name() below, an error occurs...!?
					$unit_content = $akt_unit->get_html_unit_new();
					
					$semester = $course->get_semester()->get_name();
					$breadcrumb = array (
											array( "name" => $semester , "link" => PATH_URL . SEMESTER_URL . "/" . $semester . "/" ),
											array( "name" => $owner_name, "link" => PATH_URL . SEMESTER_URL . "/" . $semester . "/" . $course->get_name() . "/" ),
											array( "name" => gettext("Units"), "link" =>  PATH_URL . SEMESTER_URL . "/" . $semester . "/" . $course->get_name() . "/units/"),
											array( "name" => gettext( "Create a new Unit" ), "link" => $backlink . "new/" ),
											array( "name" => str_replace( "%UNITTYPE", $akt_unit->get_display_name(), gettext( "Create a new Unit of type '%UNITTYPE''" ) ) )
										);	
					
					$portal->set_page_title( gettext( "Create unit" ) );
					$portal->set_page_main( $breadcrumb, $unit_content , "" );
					$portal->show_html();
					exit;
				}
			}
			else {
				$portal->set_problem_description( "Einheiten des Typs '" . $unitname . "' sind auf diesem Server nicht verfügbar.", "Bitte kontaktieren Sie den Administrator." );
			}
		} else {
			$portal->set_problem_description( "Konnte die zu erstellende Einheit nicht ermitteln.", "Bitte kontaktieren Sie einen Administrator." );
		}
	} else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}

if (!$content_ready) {
	$content = new HTML_TEMPLATE_IT();
	$content->loadTemplateFile( PATH_TEMPLATES_UNITS_BASE . "unit_new.template.html" );

	$content->setVariable( "HEADING", gettext("Selection of unit type"));
	$content->setVariable( "TEXT", gettext("Different kinds of units are available to support different learning scenarios in a course. To choose a specific type of unit for your course just klick its 'create'-button.") );
	$content->setVariable( "OWNER", $owner->get_id() );

	$content->setVariable( "NAME_DESC", gettext("Name/Description"));
	$content->setVariable( "ACTION", gettext("Action"));
	$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );

	$installed_units = $um->get_installed_unittypes();

	foreach( $installed_units as $unit ) {
		if ( !$unit->is_enabled( $owner ) ) continue;
		$content->setCurrentBlock( "BLOCK_NEW_UNIT" );
		$content->setVariable( "UNIT_CHOOSE", gettext("Create Unit") );
		$content->setVariable( "UNIT_NAME", $unit->get_display_name() );
		$content->setVariable( "UNIT", $unit->get_name());
		$content->setVariable("UNIT_DESCRIPTION", $unit->get_display_description() );
		$content->setVariable("UNIT_ICON", $unit->get_icon() );
		$content->parse( "BLOCK_NEW_UNIT" );
	}

	$content_html = $content->get();
} else {
	$content_html = $html_ready;
}

$semester = $course->get_semester()->get_name();
$breadcrumb = array (
						array( "name" => $semester , "link" => PATH_URL . SEMESTER_URL . "/" . $semester . "/" ),
						array( "name" => $course->get_display_name(), "link" => PATH_URL . SEMESTER_URL . "/" . $semester . "/" . $course->get_name() . "/" ),
						array( "name" => gettext("Units"), "link" =>  PATH_URL . SEMESTER_URL . "/" . $semester . "/" . $course->get_name() . "/units/"),
						array( "name" => gettext( "Create a new Unit" ) )
					);
					
$portal->set_page_title( gettext( "Create unit" ) );

$portal->set_page_main(
	$breadcrumb, 
	$content_html,
	"" );

$portal->show_html();
?>
