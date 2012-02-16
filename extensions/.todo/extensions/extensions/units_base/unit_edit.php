<?php
define("PATH_TEMPLATES_UNITS_BASE", PATH_EXTENSIONS . "units_base/templates/");

if (!isset($portal)) {
  $portal = lms_portal::get_instance();
  $portal->initialize( GUEST_NOT_ALLOWED );
} else $portal->set_guest_allowed( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();

if ( ! $owner->is_admin( $user ) )
{
	throw new Exception( "No group admin!", E_ACCESS );
}

$include_path = $unit->get_unit()->get_path() . "/modules/" . $unit->get_unit()->get_name() . "_edit.php";
if ( ! is_file( $include_path ) ) {
	include( "bad_link.php" );
	exit;
}

include( $include_path );

if ( ! isset($_POST["values"]) || (isset( $have_problems ) && $have_problems) ) {
	$portal->set_page_title( gettext( "Preferences" ) );
	
	$semester = $course->get_semester()->get_name();
	$breadcrumb = array (
							array( "name" => $semester , "link" => PATH_URL . SEMESTER_URL . "/" . $semester . "/" ),
							array( "name" => $course->get_display_name(), "link" => PATH_URL . SEMESTER_URL . "/" . $semester . "/" . $course->get_name() . "/" ),
							array( "name" => gettext("Units"), "link" =>  PATH_URL . SEMESTER_URL . "/" . $semester . "/" . $course->get_name() . "/units/"),
							$unit->get_link(),
							array( "name" => gettext( "Preferences" ) )
						);
	
	
	
	$portal->set_page_main(
		$breadcrumb,
		$unit_new_html,
		""
	);
	
	$portal->show_html();
	exit;
}

?>
