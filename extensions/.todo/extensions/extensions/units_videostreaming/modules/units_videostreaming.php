<?php
if (!defined("PATH_TEMPLATES_UNITS_VIDEOSTREAMING")) {
	define( "PATH_TEMPLATES_UNITS_VIDEOSTREAMING", PATH_EXTENSIONS . "units_videostreaming/templates/" );
}

if ( !isset( $portal ) ) {
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}

if ( !isset( $html_handler_course ) ) {
	$html_handler_course = new koala_html_course( $course );
	$html_handler_course->set_context( "units", array( "subcontext" => "unit" ) );
}

$content = new HTML_TEMPLATE_IT();

$portal->add_javascript_src("unit_videostreaming", "/koala-2_1/styles/koala-lernszenarien/assets/flowplayer/flowplayer-3.2.2.min.js");
$portal->add_javascript_code("unit_videostreaming", "
		var account = {admin: false }, v = {
			ver: \"3.2.2\",
			core: \"/swf/flowplayer-3.2.2.swf\", 
			
			controls: \"flowplayer.controls-3.2.1.swf\",
			air:  \"flowplayer.controls-air-3.2.1.swf\",
			tube:  \"flowplayer.controls-tube-3.2.1.swf\",
			
			content: \"flowplayer.content-3.2.0.swf\",
			rtmp: \"flowplayer.rtmp-3.2.1.swf\",
			slowmotion: \"flowplayer.slowmotion-3.2.0.swf\",
			pseudostreaming: \"flowplayer.pseudostreaming-3.2.2.swf\"
		};");

		
$content->loadTemplateFile( PATH_TEMPLATES_UNITS_VIDEOSTREAMING . "units_videostreaming.template.html" );
$content->setVariable( "VALUE_DESC", "Videostreaming " .  $steam_unit->get_attribute( "OBJ_NAME" ));

/*$content->setVariable( "VALUE_DESC", $steam_unit->get_attribute( "OBJ_DESC" ) );
$content->setVariable( "VALUE_LONG_DESC", get_formatted_output( $steam_unit->get_attribute( "OBJ_LONG_DESC" ) ) );

$appearance = $steam_unit->get_attribute("UNIT_EXTERN_APPEARANCE");
if ($appearance == 0 || $appearance == "") $appearance = "direct";
$unit_url = $steam_unit->get_url();

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
  $content->setVariable("VALUE_LINK_TEXT", gettext( "Show external ressource" ) );
  $content->setVariable("VALUE_LINK_TARGET", "target='_blank'" );  
  $content->parse("BLOCK_LINK");  
}*/
$html_handler->set_html_left( $content->get() );
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html(), "");
$portal->show_html(); 
exit;
?>
