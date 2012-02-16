<?php

if (!defined("PATH_TEMPLATES_UNITS_PYRAMID")) define( "PATH_TEMPLATES_UNITS_PYRAMID", PATH_EXTENSIONS . "units_pyramiddiscussion/templates/" );

//$portal = lms_portal::get_instance();
//$portal->initialize( GUEST_ALLOWED );

$html_handler_course = new koala_html_course( $course );
$html_handler_course->set_context( "units", array( "subcontext" => "unit" ) );
$content = new HTML_TEMPLATE_IT();

$content->loadTemplateFile( PATH_TEMPLATES_UNITS_PYRAMID . "units_pyramiddiscussion.template.html" );

$content->setVariable( "VALUE_DESC", $unit->get_attribute( "OBJ_DESC" ) );
$content->setVariable( "VALUE_LONG_DESC", get_formatted_output( $unit->get_attribute( "OBJ_LONG_DESC" ) ) );

$appearance = $unit->get_attribute("UNIT_PYRAMIDDISCUSSION_APPEARANCE");
if ($appearance === 0 || $appearance === "") $appearance = "direct";

$unit_url = "https://" . $GLOBALS["STEAM"]->get_config_value("web_server") . ($GLOBALS["STEAM"]->get_config_value("web_port_http") != "443"?":" . $GLOBALS["STEAM"]->get_config_value("web_port_http"):"") . $unit->get_steam_object()->get_path() . "/";
$unit_url = str_replace("?", "%3f", $unit_url);

if ($appearance === "direct") {
  header( "Location: " . $unit_url );
	exit;
}
else if ( $appearance == "2" ) {
  $content->setCurrentBlock("BLOCK_IFRAME");
  $content->setVariable("VALUE_IFRAME_URL", $unit_url );
  $content->parse("BLOCK_IFRAME");
} else {
  $content->setCurrentBlock("BLOCK_LINK");
  $content->setVariable("VALUE_LINK_URL", $unit_url );
  $content->setVariable("VALUE_LINK_TEXT", gettext( "Show pyramiddiscussion" ) );
  $content->setVariable("VALUE_LINK_TARGET", "target='_blank'" );  
  $content->parse("BLOCK_LINK");  
}
$html_handler->set_html_left( $content->get() );
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html(), "");
$portal->show_html(); 
exit;
?>
