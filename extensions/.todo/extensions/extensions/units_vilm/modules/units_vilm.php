<?php
if (!defined("PATH_TEMPLATES_UNITS_VILM")) {
	define( "PATH_TEMPLATES_UNITS_VILM", PATH_EXTENSIONS . "units_vilm/templates/" );
}

if ( !isset( $portal ) ) {
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}


//if ( !isset( $html_handler_course ) ) {
	$html_handler_course = new koala_html_course( $course );
	$html_handler_course->set_context( "units_vilm", array( "subcontext" => $action ));
//}

// add swfobject.js to html head
$portal->add_javascript_src("ViLM", PATH_SERVER . "/styles/koala-lernszenarien/assets/units_vilm/swfobject.js");

$portal->add_javascript_onload("ViLM", '
	var so = new SWFObject("'. PATH_SERVER . '/styles/koala-lernszenarien/assets/units_vilm/Launch_VCC.swf", "badge", "250", "75", "9.0.115", "#FFFFFF");
	so.addVariable( "applicationID", "de.upb.ddi.vilm.ControlCenter" );
	so.addVariable( "publisherID", "8B8F7C2CC76B094E5EC8F97033257635443FD8AA.1" );
	so.addVariable( "arguments", "Application Launched from Browser" );
	so.write("launch-VCC-Container");
');

$content = new HTML_TEMPLATE_IT();

$content->loadTemplateFile( PATH_TEMPLATES_UNITS_VILM . "units_vilm.template.html" );

$content->setVariable( "VALUE_DESC", $steam_unit->get_attribute( "OBJ_DESC" ) );

$content->setVariable("hello_world", "HALLO WELT");

// get projects from steam (steam_room = project)
$projects = $steam_unit->get_inventory(get_class("steam_room"));

// are there any projects?
if ( count( $projects ) )
{
	// display projects
	
	$content->setCurrentBlock( "BLOCK_VILM_PROJECTS" );
	$content->setVariable( "LABEL_NAME", gettext( "Projektname" ) );
	$content->setVariable( "LABEL_OWNER", gettext( "Eigentümer" ) );
	$content->setVariable( "LABEL_STATUS", gettext( "Status" ) );
	$content->setVariable( "LABEL_DATE", gettext( "Letzte Änderung" )  );
	$content->setVariable( "LABEL_ACTION_EDIT", gettext( "VCC" )  );
	$content->setVariable( "LABEL_ACTION_PLAY", gettext( "Player" )  );
	$content->setVariable( "LABEL_SCENARIO", gettext( "Szenario" ) );
	
	foreach( $projects as $project )
	{
		
		if($project instanceof steam_room)
		{
			// set current block
			$content->setCurrentBlock( "BLOCK_ITEM" );
	
			// set template variable
	
			// set project name
			$content->setVariable('ITEM_NAME', $project->get_attribute('OBJ_NAME'));
	
			// set project creator (link to profile)
			$projectCreator = $project->get_attribute('PROJECT_CREATOR');
			$content->setVariable('ITEM_OWNER', "<a href=\"" . PATH_URL . "user/" . $projectCreator .  "/\">" . $projectCreator . "</a>");
	
			// set project status
			$content->setVariable('ITEM_STATUS', $project->get_attribute('PROJECT_STATE'));
	
			// set last changed
			$lastChanged = $project->get_attribute('OBJ_LAST_CHANGED');
			$content->setVariable('ITEM_LAST_CHANGE', strftime( "%x", $lastChanged) . strftime(", %R", $lastChanged ));
		
			// set project status
			$content->setVariable('ITEM_SCENARIO', $project->get_attribute('PROJECT_SCENARIO'));
		
		
			$content->parse("BLOCK_ITEM");
		}

	}
}
else
{
	// no projects => show message
	$content->setVariable( "NO_RESULTS", gettext( "Bisher sind keine Videoannotationen vorhanden." )  );
}

$html_handler_course->set_html_left( $content->get() );
$portal->set_page_main( $html_handler_course->get_headline(), $html_handler_course->get_html(), "");
$portal->show_html(); 

exit;

?>
