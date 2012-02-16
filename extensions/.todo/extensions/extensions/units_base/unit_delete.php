<?php
require_once("classes/unitmanager.class.php");
if ( !isset( $portal ) ) {
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}

if ( !isset( $html_handler ) ) {
	//$html_handler_course = new koala_html_course( $course );
	//$html_handler_course->set_context( "units", array( "subcontext" => "unit" ) );
	$html_handler = $owner->get_html_handler();
	$html_handler->set_context( "units", array( "subcontext" => "unit" ) );
}

$unitmanager = unitmanager::create_unitmanager( $course );

$akt_unit = $unitmanager->get_unittype($unit->get_attribute("UNIT_TYPE"));

include( $akt_unit->get_path() . "/modules/" . $akt_unit->get_name() . "_delete.php");


//$html_handler_course->set_html_left( $content->get());
$portal->set_page_main(
	array(
		array( "link" => $backlink . "units/", "name" => str_replace( "%COURSE" , $course->get_course_name(), gettext( "All units of '%COURSE'" ) ) ), 
		array( "name" => str_replace( "%UNIT", $unit->get_name(), gettext( "Unit '%UNIT'" ) ) )
	),
	$html_handler->get_html(),
	""
);
$portal->show_html();
?>
