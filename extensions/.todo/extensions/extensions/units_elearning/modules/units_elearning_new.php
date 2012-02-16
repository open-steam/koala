<?php
//TODO: write a superclass for this
$unit_new_html = "";
//TODO: superclass should generate this path!!
if (!defined("PATH_TEMPLATES_UNITS_ELEARNING")) define( "PATH_TEMPLATES_UNITS_ELEARNING", PATH_EXTENSIONS . "units_elearning/templates/" );

if ( !isset( $portal ) ) {
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}
$user = lms_steam::get_current_user();

if ( ! $course->is_admin( $user ) ) //TODO: only server admin allowed here
{
	throw new Exception( "No course admin!", E_ACCESS );
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && isset($_POST["values"]))
{
	$values = $_POST[ "values" ];

	$problems = "";
	$hints    = "";
	
	//check for correct post values
	if (!$values["elearning_course_id"]) {
		$problems .= gettext("One of the required fields is missing.");
		$hints .= gettext("Please provide a existing id of an elearning couse.");
	}
	
	// if correct
	if (empty($problems)) {
		$all_users = steam_factory::groupname_to_object( $GLOBALS["STEAM"]->get_id(), STEAM_ALL_USER );
		$staff     = $course->steam_group_staff;
		$learners  = $course->steam_group_learners;

		// 1. create object on server
		if (!isset($unit)) {
			$env = $course->get_workroom();
			$id = $values["elearning_course_id"];
			
			$mediathek = elearning_mediathek::get_instance();
			$elearning_course = $mediathek->get_course_by_id($id);
			
			$name = $elearning_course->get_name();
			$new_unit_elearning_course = steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $elearning_course->get_name(), $env, $elearning_course->get_description());
		
			$new_unit_elearning_course->set_attributes(array(
    								"UNIT_TYPE" => "units_elearning",
    								"OBJ_TYPE" => "elearning_unit_koala",
									"UNIT_DISPLAY_TYPE" => gettext("units_elearning"),
									"ELEARNING_UNIT_ID" => $id
			));
			//$koala_unit = new koala_object_docextern( $new_unit, new units_extern( $course->get_steam_object() ) );
		} else
		{
			die;
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
	//first page call
	$content = new HTML_TEMPLATE_IT();
	//TODO: superclass should handle the Template Path!
	$content->loadTemplateFile( PATH_TEMPLATES_UNITS_ELEARNING . "units_elearning_new.template.html" );

	if (!empty($values)) {
		//if this happens set all values form $values back to form
		
		//nothing to do here
	}
	else if ( isset( $unit ) ) {
		//if unit allready exits, show values.
		
		//still nothing to do here
	} else
	{
		//normal case
	}

	//load elearning mediathek
	$mediathek = elearning_mediathek::get_instance();
	$courses = $mediathek->get_elearning_courses();
	foreach ($courses as $c) {
		$content->setCurrentBlock("BLOCK_ITEM");
		$content->setVariable("ELEARNING_COURSE_ICON_URL", $c->get_icon_url() . "&width=100&height=-1");
		$content->setVariable("LABEL_ELEARNING_COURSE_ID", $c->get_name());
		$content->setVariable("LABEL_ELEARNING_COURSE_COPYRIGHT", $c->get_copyright());
		$content->setVariable("LABEL_ELEARNING_COURSE_ADD",gettext("label_add_elearning_course"));
		$content->setVariable("ELEARNING_EXTENSION", "units_elearning");
		$content->setVariable("ELEARNING_COURSE_ID", $c->get_ID());
		$content->parseCurrentBlock();
	}

	if ( isset( $unit ) )
		$backlink = $course->get_url() . 'units/' . $unit->get_id() . '/';
	else
		$backlink = $course->get_url() . 'units/new';

	$content->setVariable( "BACKLINK", " <a href=\"$backlink\">" . gettext( "back" ) . "</a>" );

	$unit_new_html = $content->get();
} 
?>