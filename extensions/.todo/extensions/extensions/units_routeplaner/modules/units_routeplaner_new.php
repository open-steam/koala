<?php
if (!defined("PATH_TEMPLATES_UNITS_ROUTEPLANER")) define( "PATH_TEMPLATES_UNITS_ROUTEPLANER", PATH_EXTENSIONS . "units_routeplaner/templates/" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();

if ( ! $course->is_admin( $user ) )
{
	throw new Exception( "No course admin!", E_ACCESS );
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && isset($_POST["values"]))
{
	$values = $_POST[ "values" ];
	
	// ABFRAGEN

	$problems = "";
	$hints    = "";

  if ( !is_object($GLOBALS[ "STEAM" ]->get_module("package:routeplan")) ) {
    $problems = "The required package 'package:routeplan' was not found on the sTeam server. Please install the required package to be able to use the routeplaner.";
  } else {
    $unit_xsl = steam_factory::path_to_object($GLOBALS[ "STEAM" ]->get_id(), "/styles/routeplan/routeplan.xsl");
    if ( !is_object($unit_xsl) ) {
      $problems = "The required file '/styles/routeplan/routeplan.xsl' was not found on the sTeam server. Please make sure that the routeplan package was installed properly in order to be able to use the routeplan.";
    }
    $content_xsl = steam_factory::path_to_object($GLOBALS[ "STEAM" ]->get_id(), "/stylesheets/content.xsl");
    if ( !is_object($content_xsl) ) {
      $problems = "The required file '/stylesheets/content.xsl' was not found on the sTeam server. Please make sure that the webinterface package was installed properly in order to be able to use the routeplan.";
    }
  }
  if (!$values["name"] || !$values["project_name"] || !$values["startgate_name"] || !$values["endgate_name"])
  	$problems .= gettext("One of the required fields is missing.");
  	$hints .= gettext("Please provide a name for the unit, a name for the projekt and names for startgate and endgate.");

	if ( empty( $problems ) )
	{
		$all_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
		$staff     = $course->steam_group_staff;
		$learners  = $course->steam_group_learners;
		
    	if ( ! isset($unit) )
    	{
			$env = $course->get_workroom();
			$new_unit = steam_factory::create_room( $GLOBALS[ "STEAM" ]->get_id(), $values[ "name" ], $env, $values[ "dsc" ] );
			$new_unit->set_attributes(array(
    								'UNIT_TYPE' => "units_routeplaner",
    								'OBJ_TYPE' => "room_routeplaner_unit_koala",
									'UNIT_DISPLAY_TYPE' => gettext("Routeplaner"),
									'OBJ_LONG_DESC'=> $values[ "long_dsc" ],
    								));
    								
    		$new_unit->set_attribute("xsl:content", array( $all_users->get_id() => $unit_xsl, $staff->get_id() => $content_xsl, "_OBJECT_KEYS" => "TRUE" ));
 
    		$new_unit->set_attribute( "UNIT_ROUTEPLANER_APPEARANCE", $values[ "appearance" ] );
    		
    		$koala_unit = new koala_container_routeplaner( $new_unit, new units_routeplaner( $course->get_steam_object() ) );
    		
    		$akt_unit->initialize_routeplan($values, $new_unit, $learners);
    	} else
    	{
    		$new_unit = $unit->get_steam_object();
    		$koala_unit = $unit;
 			$attrs = $new_unit->get_attributes( array( OBJ_NAME, OBJ_DESC, 'OBJ_LONG_DESC', OBJ_TYPE,
 													   	'UNIT_TYPE', 'UNIT_DISPLAY_TYPE', 'UNIT_ROUTEPLANER_APPEARANCE',
 														'ROUTEPLAN_PROJECT_NAME', 'ROUTEPLAN_PROJECT_DESCRIPTION', 'ROUTEPLAN_START_NAME',
 														'ROUTEPLAN_START_DESCRIPTION', 'ROUTEPLAN_DESTINATION_NAME', 'ROUTEPLAN_DESTINATION_DESCRIPTION'
 			 									) );
			if ( $attrs[OBJ_NAME] !== $values['name'] )
				$new_unit->set_name( $values['name'] );
			$changes = array();
			if ( $attrs['OBJ_TYPE'] !== 'room_routeplaner_unit_koala' )
				$changes['OBJ_TYPE'] = 'room_routeplaner_unit_koala';
			if ( $attrs['UNIT_TYPE'] !== 'units_routeplaner' )
				$changes['UNIT_TYPE'] = 'units_routeplaner';
			if ( $attrs['UNIT_DISPLAY_TYPE'] !== gettext('Routeplaner') )
				$changes['UNIT_DISPLAY_TYPE'] = gettext('Routeplaner');
			if ( $attrs[ OBJ_DESC ] !== $values['dsc'] )
				$changes[ OBJ_DESC ] = $values["dsc"];
			if ( $attrs[ 'OBJ_LONG_DESC' ] !== $values['long_dsc'] )
				$changes[ 'OBJ_LONG_DESC' ] = $values['long_dsc'];
			if ( $attrs[ 'UNIT_ROUTEPLANER_APPEARANCE' ] !== $values['appearance'] )
				$changes[ 'UNIT_ROUTEPLANER_APPEARANCE' ] = $values['appearance'];
			if ( $attrs[ 'ROUTEPLAN_PROJECT_NAME' ] !== $values['project_name'] )
				$changes[ 'ROUTEPLAN_PROJECT_NAME' ] = $values['project_name'];
			if ( $attrs[ 'ROUTEPLAN_PROJECT_DESCRIPTION' ] !== $values['project_dsc'] )
				$changes[ 'ROUTEPLAN_PROJECT_DESCRIPTION' ] = $values['project_dsc'];
			if ( $attrs[ 'ROUTEPLAN_START_NAME' ] !== $values['startgate_name'] )
				$changes[ 'ROUTEPLAN_START_NAME' ] = $values['startgate_name'];
			if ( $attrs[ 'ROUTEPLAN_START_DESCRIPTION' ] !== $values['startgate_dsc'] )
				$changes[ 'ROUTEPLAN_START_DESCRITION' ] = $values['startgate_dsc'];
			if ( $attrs[ 'ROUTEPLAN_DESTINATION_NAME' ] !== $values['endgate_name'] )
				$changes[ 'ROUTEPLAN_DESTINATION_NAME' ] = $values['endgate_name'];
			if ( $attrs[ 'ROUTEPLAN_DESTINATION_DESCRITION' ] !== $values['endgate_dsc'] )
				$changes[ 'ROUTEPLAN_DESTINATIONE_DESCRIPTION' ] = $values['endgate_dsc'];
			if ( count( $changes ) > 0 )
				$new_unit->set_attributes( $changes );
    	}
    	
		$GLOBALS[ "STEAM" ]->buffer_flush();
		
		if( ! isset( $unit ) )
			header( "Location: " . $course->get_url() . "units/" );
		else
			header( "Location: " . $unit->get_url() );
		exit;
	}
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}
else
{
	$content = new HTML_TEMPLATE_IT();
	$content->loadTemplateFile( PATH_TEMPLATES_UNITS_ROUTEPLANER . "units_routeplaner_new.template.html" );
	
	if (!empty($values)) {
	  if (!empty($values["name"])) $content->setVariable("VALUE_NAME", h($values["name"]));
	  if (!empty($values["short_dsc"])) $content->setVariable("VALUE_SHORT_DSC", h($values["short_dsc"]));
	  if (!empty($values["dsc"])) $content->setVariable("VALUE_DSC", h($values["dsc"]));
	  if (!empty($values["project_name"])) $content->setVariable("VALUE_PROJECT_NAME", h($values["project_name"]));
	  if (!empty($values["project_dsc"])) $content->setVariable("VALUE_PROJECT_DSC", h($values["project_dsc"]));
	  if (!empty($values["startgate_name"])) $content->setVariable("VALUE_STARTGATE_NAME", h($values["startgate_name"]));
	  if (!empty($values["startgate_dsc"])) $content->setVariable("VALUE_STARTGATE_DSC", h($values["startgate_dsc"]));
	  if (!empty($values["endgate_name"])) $content->setVariable("VALUE_ENDGATE_NAME", h($values["endgate_name"]));
	  if (!empty($values["endgate_dsc"])) $content->setVariable("VALUE_ENDGATE_DSC", h($values["endgate_dsc"]));
		switch($values["appearance"])
		{
		case "0": 
			$content->setVariable("AD_CHECKED", "checked=\"checked\"");
			$content->setVariable("AP_CHECKED", "");
			$content->setVariable("AI_CHECKED", "");
			break;
		case "1":
			$content->setVariable("AD_CHECKED", "");
			$content->setVariable("AP_CHECKED", "checked=\"checked\"");
			$content->setVariable("AI_CHECKED", "");
			break;
		case "2":
			$content->setVariable("AD_CHECKED", "");
			$content->setVariable("AP_CHECKED", "");
			$content->setVariable("AI_CHECKED", "checked=\"checked\"");
			break;
		}
	}
	else if ( isset( $unit ) ) {
		$content->setVariable( "VALUE_NAME", $unit->get_display_name() );
		$desc = $unit->get_attribute( OBJ_DESC );
		if ( !is_string( $desc ) ) $desc = "";
		$content->setVariable( "VALUE_SHORT_DSC", h($desc) );
		$long_desc = $unit->get_attribute( "OBJ_LONG_DESC" );
		if ( !is_string( $long_desc ) ) $long_desc = "";
		$content->setVariable( "VALUE_DSC", h($long_desc) );
		$project_name = $unit->get_attribute( "ROUTEPLAN_PROJECT_NAME" );
		if ( !is_string( $project_name ) ) $project_name = "";
		$content->setVariable( "VALUE_PROJECT_NAME", h($project_name) );
		$project_dsc = $unit->get_attribute( "ROUTEPLAN_PROJECT_DESCRIPTION" );
		if ( !is_string( $project_dsc ) ) $project_dsc = "";
		$content->setVariable( "VALUE_PROJECT_DSC", h($project_dsc) );
		$startgate_name = $unit->get_attribute( "ROUTEPLAN_START_NAME" );
		if ( !is_string( $startgate_name ) ) $startgate_name = "";
		$content->setVariable( "VALUE_STARTGATE_NAME", h($startgate_name) );
		$startgate_dsc = $unit->get_attribute( "ROUTEPLAN_START_DESCRIPTION" );
		if ( !is_string( $startgate_dsc ) ) $startgate_dsc = "";
		$content->setVariable( "VALUE_STARTGATE_DSC", h($startgate_dsc) );
		$endgate_name = $unit->get_attribute( "ROUTEPLAN_DESTINATION_NAME" );
		if ( !is_string( $endgate_name ) ) $endgate_name = "";
		$content->setVariable( "VALUE_ENDGATE_NAME", h($endgate_name) );
		$endgate_dsc = $unit->get_attribute( "ROUTEPLAN_DESTINATION_DESCRIPTION" );
		if ( !is_string( $endgate_dsc ) ) $endgate_dsc = "";
		$content->setVariable( "VALUE_ENDGATE_DSC", h($endgate_dsc) );
		
		$appearance = $unit->get_attribute("UNIT_ROUTEPLANER_APPEARANCE");
		switch($appearance)
		{
		case "0": 
			$content->setVariable("AD_CHECKED", "checked=\"checked\"");
			$content->setVariable("AP_CHECKED", "");
			$content->setVariable("AI_CHECKED", "");
			break;
		case "1":
			$content->setVariable("AD_CHECKED", "");
			$content->setVariable("AP_CHECKED", "checked=\"checked\"");
			$content->setVariable("AI_CHECKED", "");
			break;
		case "2":
			$content->setVariable("AD_CHECKED", "");
			$content->setVariable("AP_CHECKED", "");
			$content->setVariable("AI_CHECKED", "checked=\"checked\"");
			break;
		}
		
		$content->setVariable("LABEL_CREATE", gettext("Save changes"));
	} else
	{
		$content->setVariable( "LABEL_CREATE", gettext("Create unit") );
	}
	
	$content->setVariable("AD_CHECKED", "checked=\"checked\"");
	
	$content->setVariable( "UNIT_ICON", units_routeplaner::get_big_icon() );
	$content->setVariable( "CONFIRMATION_TEXT", gettext( "You are going to add a new unit for this course." ) );

	$content->setVariable( "CONFIRMATION_TEXT_LONG", gettext( "You are going to add a new routeplaner unit to this course. Please note that a routplaner unit will be processed using the open-sTeam server of the koaLA system. Therefore the routeplaner itself will be adressed using a link to an external system requiring username and password. The required username and password are the same as for koaLA." ) );
	$content->setVariable( "LABEL_NAME", gettext( "Name" ) );
	$content->setVariable( "LABEL_DSC", gettext( "Description of the unit" ) );
	$content->setVariable( "LABEL_SHORT_DSC", gettext( "Description" ) );
	$content->setVariable( "LABEL_DSC", gettext( "Long description" ) );
	$content->setVariable( "LABEL_DSC_SHOW_UP", gettext( "This description will show up on the units page." ) );

	$content->setVariable( "LABEL_LONG_DSC", gettext( "Long description" ) .":" );
	$content->setVariable( "LONG_DSC_SHOW_UP", gettext( "This description will show up on the units page." ) );

	$content->setVariable( "LABEL_APPEARANCE", gettext("Please choose how to access the content of the routeplaner") );
	
	$content->setVariable( "LABEL_APPEARANCE_DIRECT", gettext("The link to the routeplaner content will be displayed in the list of units directly"));
	$content->setVariable( "LABEL_APPEARANCE_PROXY", gettext("The long Description will be displayed along with a link to the routeplaner content"));
	$content->setVariable( "LABEL_APPEARANCE_INLINE", gettext("Inline using an IFrame") );

	$content->setVariable( "LABEL_GATES", gettext("Gates information") );
	$content->setVariable( "LABEL_STARTGATE_NAME", gettext("Name of the <b>start gate</b>") );
	$content->setVariable( "LABEL_ENDGATE_NAME", gettext("Name of the <b>end gate</b>") );
	$content->setVariable( "LABEL_STARTGATE_DSC", gettext("Description of the start gate") );
	$content->setVariable( "LABEL_ENDGATE_DSC", gettext("Description of the end gate") );
	$content->setVariable( "GATES_INFO_TEXT", gettext("Please specify information on start and end gate for the routeplan project here.") );
	
	$content->setVariable( "LABEL_PROJECT", gettext("Project information") );
	$content->setVariable( "PROJECT_INFO_TEXT", gettext("Please specify information on the project that you want to manage with the routeplan here.") );
	$content->setVariable( "LABEL_PROJECT_NAME", gettext("Name of the project") );
	$content->setVariable( "LABEL_PROJECT_DSC", gettext("Project description") );
	
	$content->setVariable( "UNIT", "units_routeplaner" );

	$backlink = $course->get_url() . "units/new";

	$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );

	$content->setVariable( "LABEL_BB_BOLD", gettext( "B" ) );
	$content->setVariable( "HINT_BB_BOLD", gettext( "boldface" ) );
	$content->setVariable( "LABEL_BB_ITALIC", gettext( "I" ) );
	$content->setVariable( "HINT_BB_ITALIC", gettext( "italic" ) );
	$content->setVariable( "LABEL_BB_UNDERLINE", gettext( "U" ) );
	$content->setVariable( "HINT_BB_UNDERLINE", gettext( "underline" ) );
	$content->setVariable( "LABEL_BB_STRIKETHROUGH", gettext( "S" ) );
	$content->setVariable( "HINT_BB_STRIKETHROUGH", gettext( "strikethrough" ) );
	$content->setVariable( "LABEL_BB_IMAGE", gettext( "IMG" ) );
	$content->setVariable( "HINT_BB_IMAGE", gettext( "image" ) );
	$content->setVariable( "LABEL_BB_URL", gettext( "URL" ) );
	$content->setVariable( "HINT_BB_URL", gettext( "web link" ) );
	$content->setVariable( "LABEL_BB_MAIL", gettext( "MAIL" ) );
	$content->setVariable( "HINT_BB_MAIL", gettext( "email link" ) );

	$unit_new_html = $content->get();
}
?>
