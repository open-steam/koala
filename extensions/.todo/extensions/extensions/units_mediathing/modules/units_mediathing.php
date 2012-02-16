<?php
if (!defined("PATH_TEMPLATES_UNITS_MEDIATHING")) {
	define( "PATH_TEMPLATES_UNITS_MEDIATHING", PATH_EXTENSIONS . "units_mediathing/templates/" );
}

if ( !isset( $portal ) ) {
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}


//if ( !isset( $html_handler_course ) ) {
	$html_handler_course = new koala_html_course( $course );
	$html_handler_course->set_context( "units_mediathing", array( "subcontext" => $action ));
//}

	if ($action == "create_exit") {
		$steam_exit = steam_factory::create_exit($GLOBALS[ "STEAM" ]->get_id(), $steam_unit);
		$steam_exit->move($GLOBALS[ "STEAM" ]->get_current_steam_user()->get_attribute("USER_WORKROOM"));
		$steam_exit->set_attribute("OBJ_POSITION_X", 150.1);
		$steam_exit->set_attribute("OBJ_POSITION_Y", 100.1);
		$portal->set_confirmation("Die Verknüpfung wurde erstellt. Du kannst jetzt mit dem Mediarena Composer arbeiten.");
		
	}

$content = new HTML_TEMPLATE_IT();

$content->loadTemplateFile( PATH_TEMPLATES_UNITS_MEDIATHING . "units_mediathing.template.html" );

$content->setVariable( "VALUE_DESC", $steam_unit->get_attribute( "OBJ_DESC" ) );

$html_handler_course->set_html_left( $content->get() );
$portal->set_page_main( $html_handler_course->get_headline(), $html_handler_course->get_html(), "");
$portal->show_html(); 
exit;
?>
