<?php
if (!defined("PATH_TEMPLATES_UNITS_HOMEWORK")) define( "PATH_TEMPLATES_UNITS_HOMEWORK", PATH_EXTENSIONS . "units_homework/templates/" );

if ( !isset( $portal ) ) {
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}
$user = lms_steam::get_current_user();

if ( isset( $course) )
	$owner = $course;

if ( ! $owner->is_admin( $user ) )
{
	throw new Exception( "No group admin!", E_ACCESS );
}

if ( isset( $unit ) && $unit->get_steam_object()->get_attribute( OBJ_TYPE ) !== 'container_homework_unit_koala' )
	$old_unit = TRUE;
else
	$old_unit = FALSE;

if ( ($_SERVER[ 'REQUEST_METHOD' ] == 'POST') && (isset($_POST['values'])) )
{	
	$values = $_POST[ 'values' ];
	
	$problems = '';
	$hints    = '';

	$values = $_POST[ 'values' ];
	if ( empty( $values[ 'name' ] ) )
	{
		$problems = gettext( 'The name of the unit is missing.' );
		$hints    = gettext( 'Please type in a name.' );
		$have_problems = TRUE;
	}

	if ( strpos($values[ 'name' ], '/' )) {
		$problems .= ' ' . gettext("Please don't use the \"/\"-char in the name of the unit.");
		$have_problems = TRUE;
	}

	/*if ( $values['access'] == PERMISSION_UNDEFINED ) {
		$problems .= ' ' . gettext( 'Invalid access rights.' );
		$hints .= ' ' . gettext( 'Please choose a valid access rights option.' );
		$have_problems = TRUE;
	}*/
	
	if ( ! empty( $problems ) )
	{
		$portal->set_problem_description( $problems, $hints );
	}
	else
	{
		if ( ! isset( $unit ) ) {
			$env = $owner->get_workroom();
			$new_unit = steam_factory::create_container( $GLOBALS[ "STEAM" ]->get_id(), $values[ "name" ], $env, $values[ "short_dsc" ] );
			$new_unit->set_attributes(array(
									'UNIT_TYPE' => "units_homework",
									'UNIT_DISPLAY_TYPE' => gettext("Homework"),
									'OBJ_TYPE' => "container_homework_unit_koala",
									'OBJ_LONG_DESC' => $values[ "dsc" ],
									));
			$new_unit->set_attribute("HOMEWORK_MAX_GROUPSIZE", $values["max_groupsize"]);
			$GLOBALS[ "STEAM" ]->buffer_flush();
			$koala_unit = new koala_container_homework( $new_unit, new units_homework( $owner->get_steam_object() ) );
		}
		else {
			$new_unit = $unit->get_steam_object();
			$koala_unit = $unit;
			$attrs = $new_unit->get_attributes( array( OBJ_NAME, OBJ_DESC, 'OBJ_LONG_DESC', OBJ_TYPE, 'UNIT_TYPE', 'UNIT_DISPLAY_TYPE' ) );
			if ( $attrs[OBJ_NAME] !== $values['name'] )
				$new_unit->set_name( $values['name'] );
			$changes = array();
			if ( $attrs['OBJ_TYPE'] !== 'container_homework_unit_koala' )
				$changes['OBJ_TYPE'] = 'container_homework_unit_koala';
			if ( $attrs['UNIT_TYPE'] !== 'units_homework' )
				$changes['UNIT_TYPE'] = 'units_homework';
			if ( $attrs['UNIT_DISPLAY_TYPE'] !== gettext('Homework') )
				$changes['UNIT_DISPLAY_TYPE'] = gettext('Homework');
			if ( $attrs[ OBJ_DESC ] !== $values['short_dsc'] )
				$changes[ OBJ_DESC ] = $values["short_dsc"];
			if ( $attrs[ 'OBJ_LONG_DESC' ] !== $values['dsc'] )
				$changes[ 'OBJ_LONG_DESC' ] = $values['dsc'];
			if ( count( $changes ) > 0 )
				$new_unit->set_attributes( $changes );
		}
         // clean cache-related data
         //clean_usericoncache($user);

		$group_members = $owner->get_members_group();
		$group_staff = $owner->get_staff_group();
		$group_admins = $owner->get_admins_group();
		$workroom = $owner->get_workroom();
		
		$new_unit->set_sanction($group_staff, SANCTION_ALL, true);
		$new_unit->set_sanction($group_admins, SANCTION_ALL, true);
		$new_unit->set_sanction($group_members, SANCTION_READ | SANCTION_INSERT, true);
		$new_unit->set_acquire(false, true);
		$GLOBALS[ 'STEAM' ]->buffer_flush();
		
		// clear the unitcache for this group (not yet implemented)
		//$cache = get_cache_function( lms_steam::get_current_user()->get_name() );
		//$cache->drop( "lms_steam::get_inventory_recursive", $workroom->get_id(), CLASS_CONTAINER, array( "OBJ_TYPE", "WIKI_LANGUAGE" ) );
		
		if ( ! isset( $unit ) )
			header( "Location: " . $owner->get_url() . "units/" );
		else
			header( "Location: " . $unit->get_url() );
		exit;
	}
}

$content = new HTML_TEMPLATE_IT();

$content->loadTemplateFile( PATH_TEMPLATES_UNITS_HOMEWORK . "units_homework_new.template.html" );

if (!empty($values)) {
  if (!empty($values["name"])) $content->setVariable("VALUE_NAME", h($values["name"]));
  if (!empty($values["short_dsc"])) $content->setVariable("VALUE_SHORT_DSC", h($values["short_dsc"]));
  if (!empty($values["dsc"])) $content->setVariable("VALUE_DSC", h($values["dsc"]));
}
else if ( isset( $unit ) ) {
	$content->setVariable( "VALUE_NAME", $unit->get_display_name() );
	$desc = $unit->get_attribute( OBJ_DESC );
	if ( !is_string( $desc ) ) $desc = "";
	$content->setVariable( "VALUE_SHORT_DSC", h($desc) );
	$long_desc = $unit->get_attribute( "OBJ_LONG_DESC" );
	if ( !is_string( $long_desc ) ) $long_desc = "";
	$content->setVariable( "VALUE_DSC", h($long_desc) );
}

if ( isset($values['access']) )
	$access_default = (int)$values['access'];
else if ( ! isset( $unit ) )
	$access_default = PERMISSION_PUBLIC;
else
	$access_default = $unit->get_access_scheme();

$access = units_homework::get_access_descriptions( $owner );
if ( is_array($access) ) {
  $content->setCurrentBlock("BLOCK_ACCESS");
  $content->setVariable( "LABEL_ACCESS", gettext( "Access rights" ) );
  $content->setVariable( "LABEL_ACCESS_INFO", gettext( "Here you can decide who may have what kind of access to the document pool and its material. You have the following options:") );
  foreach($access as $key => $array) {
    if ( ($key != PERMISSION_UNDEFINED) || ($access_default == PERMISSION_UNDEFINED) ) {
      $content->setCurrentBlock("ACCESS");
      $content->setVariable("LABEL", $array["summary_short"] . ": " .$array["label"]);
      $content->setVariable("VALUE", $key);
      if ( $key == $access_default ) {
        $content->setVariable("CHECK", "checked=\"checked\"");
      }
      $content->parse("ACCESS");
    }
  }
  $content->parse("BLOCK_ACCESS");
}
$content->setCurrentBlock("BLOCK_MAX_GROUPSIZE");
	$content->setVariable( "MAX_GROUPSIZE", gettext( "Participants" ) );
	$content->setVariable( "MAX_GROUPSIZE_INFO", gettext( "This is the maximum of participants for all homeworks in this unit." ) );
$content->parse("BLOCK_MAX_GROUPSIZE");



$content->setVariable( "LABEL_NAME", gettext( "Name" ) );
$content->setVariable( "LABEL_SHORT_DSC", gettext( "Description" ) );
$content->setVariable( "LABEL_DSC", gettext( "Long description" ) );
$content->setVariable( "VALUE_DSC", gettext( "Students can upload their solution and staffs can give their feedback and points" ) );
$content->setVariable( "LABEL_DSC_SHOW_UP", gettext( "This description will show up on the units page." ) );

if ( isset( $unit ) ) {
	$backlink = $unit->get_url();
	$content->setVariable("INFO_ICON", units_homework::get_big_icon() );
	if ( $old_unit ) {
		$content->setVariable( 'INFO_TEXT', gettext( 'This is an old unit that does not fully support the homework features.<br/><b>If you save any changes, it will be converted into a Homework.</b>' ) );
		$content->setVariable( 'LABEL_CREATE', gettext( 'Save changes and convert into homework' ) );
	}
	else
		$content->setVariable( "LABEL_CREATE", gettext( "Save changes" ) );
}
else {
	$backlink = $owner->get_url() . "units/new";
	$content->setVariable("INFO_ICON", units_homework::get_big_icon() );
	$content->setVariable( "INFO_TEXT", gettext( "You are going to add a new unit." ) );
	$content->setVariable( "LABEL_CREATE", gettext( "Create unit" ) );
}

$content->setVariable("UNIT", "units_homework");
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
?>
