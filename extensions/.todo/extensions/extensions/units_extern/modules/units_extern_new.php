<?php
if (!defined("PATH_TEMPLATES_UNITS_EXTERN")) define( "PATH_TEMPLATES_UNITS_EXTERN", PATH_EXTENSIONS . "units_extern/templates/" );

if ( !isset( $portal ) ) {
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}
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


  if (!$values["name"] || !$values["url"])
  	$problems .= gettext("One of the required fields is missing.");
  	$hints .= gettext("Please provide a name for the unit and an URL for the extern content.");

	if ( empty( $problems ) )
	{
		$all_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
		$staff     = $course->steam_group_staff;
		$learners  = $course->steam_group_learners;
		
    	if ( ! isset($unit) )
    	{
			$env = $course->get_workroom();
			$url = $values["url"];
		
			$new_unit = steam_factory::create_docextern( $GLOBALS[ "STEAM" ]->get_id(), $values[ "name" ], $url, $env, $values[ "dsc" ] );

    		$new_unit->set_attribute( "UNIT_EXTERN_APPEARANCE", $values[ "appearance" ]);
    		$new_unit->set_attribute( "UNIT_EXTERN_URL_DESCRIPTION", $values["url_dsc"]);
    		$new_unit->set_attributes(array(
    								"OBJ_LONG_DESC" => $values[ "long_dsc" ],
    								"UNIT_TYPE" => "units_extern",
    								"OBJ_TYPE" => "docextern_unit_koala",
									"UNIT_DISPLAY_TYPE" => gettext("Extern Ressource")
    								));
    		$koala_unit = new koala_object_docextern( $new_unit, new units_extern( $course->get_steam_object() ) );
    	} else
    	{
    		$new_unit = $unit->get_steam_object();
    		$koala_unit = $unit;
 			$attrs = $new_unit->get_attributes( array( OBJ_NAME, OBJ_DESC, 'OBJ_LONG_DESC', OBJ_TYPE,
 													   	'UNIT_TYPE', 'UNIT_DISPLAY_TYPE', 'UNIT_EXTERN_APPEARANCE',
 														'DOC_EXTERN_URL', 'UNIT_EXTERN_URL_DESCRIPTION'
 			 									) );
			if ( $attrs[OBJ_NAME] !== $values['name'] )
				$new_unit->set_name( $values['name'] );
			$changes = array();
			if ( $attrs['OBJ_TYPE'] !== 'docextern_unit_koala' )
				$changes['OBJ_TYPE'] = 'docextern_unit_koala';
			if ( $attrs['UNIT_TYPE'] !== 'units_extern' )
				$changes['UNIT_TYPE'] = 'units_extern';
			if ( $attrs['UNIT_DISPLAY_TYPE'] !== gettext('Extern Ressource') )
				$changes['UNIT_DISPLAY_TYPE'] = gettext('Extern Ressource');
			if ( $attrs[ OBJ_DESC ] !== $values['dsc'] )
				$changes[ OBJ_DESC ] = $values['dsc'];
			if ( $attrs[ 'OBJ_LONG_DESC' ] !== $values['long_dsc'] )
				$changes[ 'OBJ_LONG_DESC' ] = $values['long_dsc'];
			if ( $attrs[ 'UNIT_EXTERN_APPEARANCE' ] !== $values['appearance'] )
				$changes[ 'UNIT_EXTERN_APPEARANCE' ] = $values['appearance'];
			if ( $attrs[ 'DOC_EXTERN_URL' ] !== $values['url'] )
				$changes[ 'DOC_EXTERN_URL' ] = $values['url'];
			if ( $attrs[ 'UNIT_EXTERN_URL_DESCRIPTION' ] !== $values['url_dsc'] )
				$changes[ 'UNIT_EXTERN_URL_DESCRIPTION' ] = $values['url_dsc'];
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
	$content->loadTemplateFile( PATH_TEMPLATES_UNITS_EXTERN . "units_extern_new.template.html" );
	
	if (!empty($values)) {
	  if (!empty($values["name"])) $content->setVariable("VALUE_NAME", h($values["name"]));
	  if (!empty($values["short_dsc"])) $content->setVariable("VALUE_SHORT_DSC", h($values["dsc"]));
	  if (!empty($values["dsc"])) $content->setVariable("VALUE_DSC", h($values["long_dsc"]));
	  if (!empty($values["url"])) $content->setVariable("VALUE_URL", h($values["url"]));
	  if (!empty($values["url_dsc"])) $content->setVariable("VALUE_URL_DSC", h($values["url_dsc"]));
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
		$url = $unit->get_attribute( "DOC_EXTERN_URL" );
		if ( !is_string( $url ) ) $url = "";
		$content->setVariable( "VALUE_URL", h($url) );
		$url_dsc = $unit->get_attribute( "UNIT_EXTERN_URL_DESCRIPTION" );
		if ( !is_string( $url_dsc ) ) $url_dsc = "";
		$content->setVariable( "VALUE_URL_DSC", h($url_dsc) );
		
		$appearance = $unit->get_attribute("UNIT_EXTERN_APPEARANCE");
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
    $content->setVariable("AP_CHECKED", "checked=\"checked\"");
	}
	
	$content->setVariable( "CONFIRMATION_TEXT", gettext( "You are going to add a new unit for this course." ) );

	$content->setVariable( "CONFIRMATION_TEXT_LONG", gettext( "You are going to add a new extern unit to this course." ) );
	$content->setVariable( "LABEL_NAME", gettext( "Name" ) );
	$content->setVariable( "LABEL_DSC", gettext( "Description of the unit" ) );
	$content->setVariable( "LABEL_SHORT_DSC", gettext( "Description" ) );
	$content->setVariable( "LABEL_DSC", gettext( "Long description" ) );
	$content->setVariable( "LABEL_DSC_SHOW_UP", gettext( "This description will show up on the units page." ) );

	$content->setVariable( "LABEL_LONG_DSC", gettext( "Long description" ) .":" );
	$content->setVariable( "LONG_DSC_SHOW_UP", gettext( "This description will show up on the units page." ) );
	
	$content->setVariable( "LABEL_EXTERN_URL", gettext("External ressource") );
	$content->setVariable( "URL_INFO_TEXT", gettext("Please specify the URL of the external content you want to provide in this unit below. You can give an optional description for the URL.") );
	$content->setVariable( "LABEL_URL", gettext("URL") );
	$content->setVariable( "LABEL_URL_DSC", gettext("Description for the URL") );
	
	$content->setVariable( "LABEL_APPEARANCE", gettext("Please choose how to access the extern content") );
	
	$content->setVariable( "LABEL_APPEARANCE_DIRECT", gettext("The link to the external content will be displayed in the list of units directly"));
	$content->setVariable( "LABEL_APPEARANCE_PROXY", gettext("The long Description will be displayed along with a link to the external content"));
	$content->setVariable( "LABEL_APPEARANCE_INLINE", gettext("Inline using an IFrame") );
	
	$content->setVariable( "UNIT", "units_extern" );

	if ( isset( $unit ) )
		$backlink = $course->get_url() . 'units/' . $unit->get_id() . '/';
	else
		$backlink = $course->get_url() . 'units/new';

	$content->setVariable( "BACKLINK", " <a href=\"$backlink\">" . gettext( "back" ) . "</a>" );

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
