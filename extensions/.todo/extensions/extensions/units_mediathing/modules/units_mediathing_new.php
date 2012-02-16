<?php
if (!defined("PATH_TEMPLATES_UNITS_MEDIATHING")) define( "PATH_TEMPLATES_UNITS_MEDIATHING", PATH_EXTENSIONS . "units_mediathing/templates/" );

if ( !isset( $portal ) ) {
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}
$user = lms_steam::get_current_user();

if ( ! $course->is_admin( $user ) )
{
	throw new Exception( "No course admin!", E_ACCESS );
}

// 2. Aufruf nach Eingabe der Werte
if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && isset($_POST["values"]))
{
	$values = $_POST[ "values" ];
	
	// ABFRAGEN

	$problems = "";
	$hints    = "";

 // Prüfen auf Parameter, die angegeben werden müssen
  if (!$values["name"]) {
  	$problems .= gettext("One of the required fields is missing.");
  	$hints .= gettext("Please provide a name for the unit.");
  }

	if ( empty( $problems ) )
	{
		$all_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
		$staff     = $course->steam_group_staff;
		$learners  = $course->steam_group_learners;
		$name	   = $values["name"];
		
    	if (! isset($unit) ) 
    	{
    		//Erstelle unit
			$env = $course->get_workroom();
			
			$new_unit_vilm = steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $name, $env, "Mediathing Lektion");
		
			$new_unit_vilm->set_attributes(array(
    								"UNIT_TYPE" => "units_mediathing",
    								"OBJ_TYPE" => "mediathing_unit_koala",
									"UNIT_DISPLAY_TYPE" => gettext("units_mediathing")
			));
			
			$file = PATH_EXTENSIONS . "units_mediathing/images/icon48.gif";
			$fh = fopen($file, 'r');
			$data = fread($fh, filesize($file));
			fclose($fh);
			$unit_icon = steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "Mediathing Icon", $data, "image/gif");
			$unit_icon->set_sanction($learners, SANCTION_READ);
			$new_unit_vilm->set_attribute(OBJ_ICON, $unit_icon);
			$new_unit_vilm->set_sanction($learners, SANCTION_WRITE | SANCTION_READ | SANCTION_EXECUTE | SANCTION_INSERT | SANCTION_ANNOTATE);
			// weitere Vorbereitungen z.B. Verzeichnisstuktur ... sollte hier erstellt werden
    	} else
    	{
			$new_unit = $unit->get_steam_object();
    		$koala_unit = $unit;
 			$attrs = $new_unit->get_attributes( array( OBJ_NAME ) );
			if ( $attrs[OBJ_NAME] !== $values['name'] )
				$new_unit->set_name( $values['name'] );
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
	//erster Aufruf hier
	$content = new HTML_TEMPLATE_IT();
	$content->loadTemplateFile( PATH_TEMPLATES_UNITS_MEDIATHING . "units_mediathing_new.template.html" );
	
	if (!empty($values)) {
	  if (!empty($values["name"])) $content->setVariable("VALUE_NAME", h($values["name"]));
	}
	else if ( isset( $unit ) ) {
		$content->setVariable( "VALUE_NAME", $unit->get_attribute(OBJ_NAME) );
		$content->setVariable("LABEL_CREATE", gettext("Save changes"));
	} else
	{
		$content->setVariable( "LABEL_CREATE", gettext("Create unit") );
	}
	
	$content->setVariable( "CONFIRMATION_TEXT", gettext( "You are going to add a new unit for this course." ) );

	$content->setVariable( "CONFIRMATION_TEXT_LONG", gettext( "You are going to add a new mediathing unit to this course." ) );
	$content->setVariable( "LABEL_NAME", gettext( "Name" ) );
	
	
	$content->setVariable( "UNIT", "units_mediathing" );

	if ( isset( $unit ) )
		$backlink = $course->get_url() . 'units/' . $unit->get_id() . '/';
	else
		$backlink = $course->get_url() . 'units/new';

	$content->setVariable( "BACKLINK", " <a class=\"button\" href=\"$backlink\">" . gettext( "back" ) . "</a>" );

	$unit_new_html = $content->get();
}
?>