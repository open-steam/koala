<?php
if (!defined("PATH_TEMPLATES_UNITS_PYRAMIDDISCUSSION")) define( "PATH_TEMPLATES_UNITS_PYRAMIDDISCUSSION", PATH_EXTENSIONS . "units_pyramiddiscussion/templates/" );

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

if ( !is_object($GLOBALS[ "STEAM" ]->get_module("package:pyramiddiscussion")) ) {
    $problems = "The required package 'package:pyramiddiscussion' was not found on the sTeam server. Please install the required package to be able to use the pyramiddiscussion.";
  } else {
    $unit_xsl = steam_factory::path_to_object($GLOBALS[ "STEAM" ]->get_id(), "/styles/pyramiddiscussion/pyramiddiscussion.xsl");
    if ( !is_object($unit_xsl) ) {
      $problems = "The required file '/styles/pyramiddiscussion/pyramiddiscussion.xsl' was not found on the sTeam server. Please make sure that the pyramiddiscussion package was installed properly in order to be able to use the pyramiddiscussion.";
    }
    $content_xsl = steam_factory::path_to_object($GLOBALS[ "STEAM" ]->get_id(), "/stylesheets/content.xsl");
    if ( !is_object($content_xsl) ) {
      $problems = "The required file '/stylesheets/content.xsl' was not found on the sTeam server. Please make sure that the webinterface package was installed properly in order to be able to use the pyramiddiscussion.";
    }
  }
  if (!$values["name"] || !$values["dsc"])
  	$problems .= gettext("One of the required fields is missing.");
  	$hints .= gettext("Please provide a name and a description for the unit.");

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
    								'UNIT_TYPE' => "units_pyramiddiscussion",
    								'OBJ_TYPE' => "container_pyramiddiscussion_unit_koala",
									'UNIT_DISPLAY_TYPE' => gettext("Pyramiddiscussion"),
									'OBJ_LONG_DESC'=> $values[ "long_dsc" ],
    								));
    								
    		$new_unit->set_attribute("xsl:content", array( $all_users->get_id() => $unit_xsl, $staff->get_id() => $content_xsl, "_OBJECT_KEYS" => "TRUE" ));
 
    		$new_unit->set_attribute( "UNIT_PYRAMIDDISCUSSION_APPEARANCE", $values[ "appearance" ] );
    		
    		$koala_unit = new koala_container_pyramiddiscussion( $new_unit, new units_pyramiddiscussion( $course->get_steam_object() ) );
    		
    		$akt_unit->initialize_pyramiddiscussion($values, $new_unit, $learners);
    		$new_unit->set_attribute('OBJ_TYPE', "container_pyramiddiscussion_unit_kola");
    	} else
    	{
    		$new_unit = $unit->get_steam_object();
    		$koala_unit = $unit;
 			$attrs = $new_unit->get_attributes( array( OBJ_NAME, OBJ_DESC, 'OBJ_LONG_DESC', OBJ_TYPE,
 													   	'UNIT_TYPE', 'UNIT_DISPLAY_TYPE', 'UNIT_PYRAMIDDISCUSSION_APPEARANCE',
 														'PYRAMIDDISCUSSION_MAX'
 			 									) );
			if ( $attrs[OBJ_NAME] !== $values['name'] )
				$new_unit->set_name( $values['name'] );
			$changes = array();
			if ( $attrs['OBJ_TYPE'] !== 'container_pyramiddiscussion_unit_koala' )
				$changes['OBJ_TYPE'] = 'container_pyramiddiscussion_unit_koala';
			if ( $attrs['UNIT_TYPE'] !== 'units_pyramiddiscussion' )
				$changes['UNIT_TYPE'] = 'units_pyramiddiscussion';
			if ( $attrs['UNIT_DISPLAY_TYPE'] !== gettext('Pyramiddiscussion') )
				$changes['UNIT_DISPLAY_TYPE'] = gettext('Pyramiddiscussion');
			if ( $attrs[ OBJ_DESC ] !== $values['dsc'] )
				$changes[ OBJ_DESC ] = $values["dsc"];
			if ( $attrs[ 'OBJ_LONG_DESC' ] !== $values['long_dsc'] )
				$changes[ 'OBJ_LONG_DESC' ] = $values['long_dsc'];
			if ( $attrs[ 'UNIT_PYRAMIDDISCUSSION_APPEARANCE' ] !== $values['appearance'] )
				$changes[ 'UNIT_PYRAMIDDISCUSSION_APPEARANCE' ] = $values['appearance'];
			//if ( $attrs[ 'PYRAMIDDISCUSSION_MAX' ] !== $values['participants'] )
				//$changes[ 'PYRAMIDDISCUSSION_MAX' ] = $values['participants'];
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
	$content->loadTemplateFile( PATH_TEMPLATES_UNITS_PYRAMIDDISCUSSION . "units_pyramiddiscussion_new.template.html" );
	
	if (!empty($values)) {
	  if (!empty($values["name"])) $content->setVariable("VALUE_NAME", h($values["name"]));
	  if (!empty($values["short_dsc"])) $content->setVariable("VALUE_SHORT_DSC", h($values["short_dsc"]));
	  if (!empty($values["dsc"])) $content->setVariable("VALUE_DSC", h($values["dsc"]));
		switch($values["participants"])
		{
		case "2": 
			$content->setVariable("2_SELECTED", "selected=\"selected\"");
			$content->setVariable("4_SELECTED", "");
			$content->setVariable("8_SELECTED", "");
			$content->setVariable("16_SELECTED", "");
			break;
		case "4":
			$content->setVariable("2_SELECTED", "");
			$content->setVariable("4_SELECTED", "selected=\"selected\"");
			$content->setVariable("8_SELECTED", "");
			$content->setVariable("16_SELECTED", "");
			break;
		case "8":
			$content->setVariable("2_SELECTED", "");
			$content->setVariable("4_SELECTED", "");
			$content->setVariable("8_SELECTED", "selected=\"selected\"");
			$content->setVariable("16_SELECTED", "");
			break;
		case "16":
			$content->setVariable("2_SELECTED", "");
			$content->setVariable("4_SELECTED", "");
			$content->setVariable("8_SELECTED", "");
			$content->setVariable("16_SELECTED", "selected=\"selected\"");
			break;
		}
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
		
		$participants = $unit->get_attribute("PYRAMIDDISCUSSION_MAX");
		switch($participants)
		{
		case "2": 
			$content->setVariable("2_SELECTED", "selected=\"selected\"");
			$content->setVariable("4_SELECTED", "");
			$content->setVariable("8_SELECTED", "");
			$content->setVariable("16_SELECTED", "");
			break;
		case "4":
			$content->setVariable("2_SELECTED", "");
			$content->setVariable("4_SELECTED", "selected=\"selected\"");
			$content->setVariable("8_SELECTED", "");
			$content->setVariable("16_SELECTED", "");
			break;
		case "8":
			$content->setVariable("2_SELECTED", "");
			$content->setVariable("4_SELECTED", "");
			$content->setVariable("8_SELECTED", "selected=\"selected\"");
			$content->setVariable("16_SELECTED", "");
			break;
		case "16":
			$content->setVariable("2_SELECTED", "");
			$content->setVariable("4_SELECTED", "");
			$content->setVariable("8_SELECTED", "");
			$content->setVariable("16_SELECTED", "selected=\"selected\"");
			break;
		}
		$appearance = $unit->get_attribute("UNIT_PYRAMIDDISCUSSION_APPEARANCE");
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
	
	$content->setVariable( "UNIT_ICON", units_pyramiddiscussion::get_big_icon() );
	$content->setVariable( "CONFIRMATION_TEXT", gettext( "You are going to add a new unit for this course." ) );

	$content->setVariable( "CONFIRMATION_TEXT_LONG", gettext( "You are going to add a new pyramiddiscussion unit to this course. Please note that a pyramiddiscussion unit will be processed using the open-sTeam server of the koaLA system. Therefore the pyramiddiscussion itself will be adressed using a link to an external system requiring username and password. The required username and password are the same as for koaLA." ) );
	$content->setVariable( "LABEL_NAME", gettext( "Name" ) );
	$content->setVariable( "LABEL_DSC", gettext( "Description of the unit" ) );
	$content->setVariable( "LABEL_SHORT_DSC", gettext( "Description" ) );
	$content->setVariable( "LABEL_DSC", gettext( "Long description" ) );
	$content->setVariable( "LABEL_DSC_SHOW_UP", gettext( "This description will show up on the units page." ) );

	$content->setVariable( "LABEL_LONG_DSC", gettext( "Long description" ) .":" );
	$content->setVariable( "LONG_DSC_SHOW_UP", gettext( "This description will show up on the units page." ) );
	
	$content->setVariable( "INFO_DSC_SUBJECT", gettext( "The unit description will also be the subject of the pyramid discussion." ) );

	$content->setVariable( "LABEL_NUM_PARTICIPANTS", gettext( "Number of participants" ) );
	
	$content->setVariable( "LABEL_APPEARANCE", gettext("Please choose how to access the content of the pyramiddiscussion") );
	
	$content->setVariable( "LABEL_APPEARANCE_DIRECT", gettext("The link to the pyramiddiscussion content will be displayed in the list of units directly"));
	$content->setVariable( "LABEL_APPEARANCE_PROXY", gettext("The long Description will be displayed along with a link to the pyramiddiscussion content"));
	$content->setVariable( "LABEL_APPEARANCE_INLINE", gettext("Inline using an IFrame") );
	
	$content->setVariable( "UNIT", "units_pyramiddiscussion" );

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
