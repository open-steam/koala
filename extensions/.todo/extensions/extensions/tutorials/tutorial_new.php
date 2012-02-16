<?php
require_once( PATH_EXTENSIONS . 'tutorials/classes/koala_group_tutorial.class.php');
if(!isset($portal) || !is_object($portal))
{
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}


if ( ! $course->is_admin( $user ) )
{
	throw new Exception( 'No admin!', E_ACCESS );
}

if ( isset( $tutorial ) ) {
	$tutorial_no = (int)$tutorial->get_steam_object()->get_name();
}
else {
	// calculate group number for new tutorial:
	$tutorial_no = 1;
	$existing_tutorials = steam_factory::groupname_to_object( $GLOBALS[ 'STEAM' ]->get_id(), $course->get_groupname() . '.learners')->get_subgroups();
	
	$a_Keys = array_keys($existing_tutorials);
	
	foreach($a_Keys as $key)
	{
		if ($existing_tutorials[$key]->get_attribute('OBJ_TYPE') == '0') {unset($existing_tutorials[$key]);continue;}
		if ($existing_tutorials[$key]->get_attribute('OBJ_TYPE') != 'course_tutorial' && $existing_tutorials[$key]->get_attribute('OBJ_TYPE') != 'group_tutorial_koala') {unset($existing_tutorials[$key]);continue;}
	}
	
	$existing_tutorials = array_values($existing_tutorials);
	
	usort( $existing_tutorials, 'sort_objects_new' );
	
	foreach ($existing_tutorials as $tut)
	{
		if($tut->get_name() == $tutorial_no) $tutorial_no++;
	}
}

if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' )
{
	$values = $_POST[ 'values' ];
	
	$just_edit = FALSE;
	// ABFRAGEN

	$problems = '';
	$hints    = '';
  
	if ( !$values['dsc'] || !$values['tutor'] || (!$values['max_learners'] && $values['max_learners'] != 0) )
	{
		$problems .= gettext( 'A short description, a tutor and the maximum number of learners have to be provided for the tutorial.' );
		$hints .= gettext( 'Please provide the data that is not optional for the tutorial.' );
	}
	
	if ( (int)$values['membership'] == PERMISSION_TUTORIAL_PASSWORD && !$values['password'])
	{
		$problems .= gettext('You have selected to use a password protected tutorial but you did not enter a password for it.');
		$hints .= gettext('Please specify a password for the tutorial.');
	}
	
  $max_members = -1;
  $sizeproblems = FALSE;
	if ( !empty( $values[ "max_learners" ] ) && trim($values[ "max_learners" ]) != "" && preg_match('/[^-.0-9]/', trim($values[ "max_learners" ])) )
	{
		$problems .= gettext( "Invalid max number of participants." ) . " ";
		$hints    .= gettext( "Please enter a valid number for the max number of participants."). " " . gettext("Please note that the input of a '0' or to leave the field blank means no limitation." ) . " ";
    $sizeproblems = TRUE;
	} else {
    if ( !empty( $values[ "max_learners" ] ) && trim($values[ "max_learners" ]) != "" && trim($values[ "max_learners" ]) < 0 ) {
      $problems .= gettext( "Invalid max number of participants." ) . " ";
      $hints    .= gettext( "Please enter a number equal or greater than '0' for the max number of participants.") . " " . gettext("Please note that the input of a '0' or to leave the field blank means no limitation." ) . " ";
      $sizeproblems = TRUE;
    } else {
      if (isset( $values[ "max_learners" ] )) {
        if (trim($values[ "max_learners" ]) === "") $max_members = 0;
        else $max_members = (int)trim($values["max_learners"]);
      }
    }
  }
  
  if (is_object($tutorial)) {
    $tutorial_steam_group = $tutorial->get_steam_object();
    if (!$sizeproblems && isset($max_members) && $max_members > 0 && $max_members < $tutorial_steam_group->count_members()) {
      $problems .= gettext( "Cannot set max number of participants." ) . " ";
      $hints    .= str_replace("%ACTUAL", $tutorial_steam_group->count_members(), str_replace("%CHOSEN", $max_members, gettext( "You chose to limit your tutorial's max number of participants to %CHOSEN but your tutorial already has %ACTUAL participants. If you want to set the max number of participants below %ACTUAL you have to remove some participants first." ))) . " ";
    }
  }
  
	if ( empty( $problems ) )
	{
		if ( (int) $values['membership'] == PERMISSION_TUTORIAL_PRIVATE )
			$tutorial_private = 'TRUE';
		else
			$tutorial_private = 'FALSE';
		
		
		if ( ! isset( $tutorial ) ) {
			// create new tutorial:
			$tutorial_steam_group = steam_factory::groupname_to_object( $GLOBALS[ 'STEAM' ]->get_id(), $course->get_groupname() . '.learners')->create_subgroup('' . $tutorial_no , FALSE, $values['dsc']);
			$tutorial_steam_group->set_attributes( array(
									'TUTORIAL_PRIVATE' => $tutorial_private,
									'TUTORIAL_LONG_DESC' => $values['long_dsc'],
									'TUTORIAL_TUTOR' => $values['tutor'],
									'TUTORIAL_MAX_LEARNERS' => $values['max_learners'],
									'GROUP_MAXSIZE' => (int)$values['max_learners'],
									'OBJ_TYPE' => 'group_tutorial_koala'
								));
			$koala_tutorial = new koala_group_tutorial( $tutorial_steam_group );
		}
		else {
			// update existing tutorial:
			$just_edit = TRUE;
			$tutorial_steam_group = $tutorial->get_steam_object();
      $koala_tutorial = $tutorial;
      $attrs = $tutorial_steam_group->get_attributes( array( OBJ_DESC, 'TUTORIAL_PRIVATE', 'TUTORIAL_LONG_DESC', 'TUTORIAL_TUTOR', 'TUTORIAL_MAX_LEARNERS', 'GROUP_MAXSIZE' ) );
      $changes = array();
      if ( $attrs[ OBJ_DESC ] != $values['dsc'] )
        $changes[ OBJ_DESC ] = $values['dsc'];
      if ( $attrs[ 'TUTORIAL_PRIVATE' ] != $tutorial_private )
        $changes[ 'TUTORIAL_PRIVATE' ] = $tutorial_private;
      if ( $attrs[ 'TUTORIAL_LONG_DESC' ] != $values['long_dsc'] )
        $changes[ 'TUTORIAL_LONG_DESC' ] = $values['long_dsc'];
      if ( $attrs[ 'TUTORIAL_TUTOR' ] != $values['tutor'] )
        $changes[ 'TUTORIAL_TUTOR' ] = $values['tutor'];
      if ( (int)$attrs[ 'TUTORIAL_MAX_LEARNERS' ] != (int)$values['max_learners'] )
        $changes[ 'TUTORIAL_MAX_LEARNERS' ] = (int)$values['max_learners'];
      if ( ($max_members > -1) && (int)$attrs[ 'GROUP_MAXSIZE' ] != (int)$values['max_learners'] )
        $changes[ 'GROUP_MAXSIZE' ] = (int)$values['max_learners'];
      if ( count( $changes ) > 0 )
        $tutorial_steam_group->set_attributes( $changes );
		}
		
		// participant management:
		if ( (int) $values['membership'] == PERMISSION_TUTORIAL_PASSWORD && $values['password'] != "******")
				$tutorial_steam_group->set_password($values['password']);
		else 
			$tutorial_steam_group->set_password("");
			
		$koala_tutorial->set_access( $values['membership'] );

		// access rights (workroom):
		$koala_tutorial->set_workroom_access( $values['access'] );
		
		$GLOBALS[ 'STEAM' ]->buffer_flush();
		
		if($just_edit)
		{
			$_SESSION["confirmation"] = gettext( "The changes have been saved." );
    		header( "Location: " . $_SERVER["REQUEST_URI"]);
    		exit;
		}
		else
		{
			header( 'Location: ' . $backlink );
			exit;
		}
	}
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_EXTENSIONS . 'tutorials/templates/tutorial_new.template.html' );

if (!empty($values)) {
	if (!empty($values['dsc'])) $content->setVariable('VALUE_DSC', h($values['dsc']));
	if (!empty($values['long_dsc'])) $content->setVariable('VALUE_LONG_DSC', h($values['long_dsc']));
	if (!empty($values['tutor'])) $content->setVariable('VALUE_TUTORIAL_TUTOR', h($values['tutor']));
	if (!empty($values['max_learners'])) $content->setVariable('VALUE_TUTORIAL_MAX_LEARNERS', h($values['max_learners']));
}
else if ( isset( $tutorial ) ) {
	$desc = $tutorial->get_attribute( OBJ_DESC );
	if ( !is_string( $desc ) ) $desc = '';
	$content->setVariable( 'VALUE_DSC', h($desc) );
	$long_desc = $tutorial->get_attribute( 'TUTORIAL_LONG_DESC' );
	if ( !is_string( $long_desc ) ) $long_desc = '';
	$content->setVariable( 'VALUE_LONG_DSC', h($long_desc) );
	$tutor = $tutorial->get_attribute( 'TUTORIAL_TUTOR' );
	if ( !is_string( $tutor ) ) $tutor = '';
	$content->setVariable( 'VALUE_TUTORIAL_TUTOR', $tutor );
	$max_learners = $tutorial->get_attribute( 'GROUP_MAXSIZE' );
	if ( ! $max_learners ) $max_learners = $tutorial->get_attribute( 'TUTORIAL_MAX_LEARNERS' );
	if ( ! $max_learners ) $max_learners = '';
	$content->setVariable( 'VALUE_TUTORIAL_MAX_LEARNERS', $max_learners );
}

if ( !isset( $tutorial ) ) {
	$content->setVariable( 'INFO_TEXT', gettext( 'You are going to add a new tutorial for this course.' ) );
	$content->setVariable( 'LABEL_CREATE_TUTORIAL', gettext( 'Create tutorial' ) );
}
else
	$content->setVariable( 'LABEL_CREATE_TUTORIAL', gettext( 'Save changes' ) );

$content->setVariable( 'LABEL_NR', gettext( 'Tutorial No' ));
$content->setVariable( 'LABEL_DSC', gettext( 'Description' ) );

$content->setVariable( 'LABEL_LONG_DSC', gettext( 'Long description' ) );
$content->setVariable( 'LABEL_DSC_EXTRA', gettext( 'Day, time and room of the tutorial go here.' ) );
$content->setVariable( 'LABEL_TUTOR', gettext( 'Tutor' ) );
$content->setVariable( 'LABEL_MAX_LEARNERS', gettext( 'Maximum number of learners' ) );
$content->setVariable("LABEL_MAX_LEARNERS_DSC", gettext("To limit the max number of participants for your tutorial enter a number greater than 0. Leave this field blank or enter a '0' for no limitation."));
$content->setVariable( 'VALUE_TUTORIAL_NUMBER', ($tutorial_no) );
$content->setVariable( 'BACKLINK', "<a href=\"$backlink\">" . gettext( 'back' ) . '</a>' );
$content->setVariable( 'LONG_DSC_SHOW_UP', gettext( 'This description will show up on the units page.' ) );

$content->setVariable( 'LABEL_BB_BOLD', gettext( 'B' ) );
$content->setVariable( 'HINT_BB_BOLD', gettext( 'boldface' ) );
$content->setVariable( 'LABEL_BB_ITALIC', gettext( 'I' ) );
$content->setVariable( 'HINT_BB_ITALIC', gettext( 'italic' ) );
$content->setVariable( 'LABEL_BB_UNDERLINE', gettext( 'U' ) );
$content->setVariable( 'HINT_BB_UNDERLINE', gettext( 'underline' ) );
$content->setVariable( 'LABEL_BB_STRIKETHROUGH', gettext( 'S' ) );
$content->setVariable( 'HINT_BB_STRIKETHROUGH', gettext( 'strikethrough' ) );
$content->setVariable( 'LABEL_BB_IMAGE', gettext( 'IMG' ) );
$content->setVariable( 'HINT_BB_IMAGE', gettext( 'image' ) );
$content->setVariable( 'LABEL_BB_URL', gettext( 'URL' ) );
$content->setVariable( 'HINT_BB_URL', gettext( 'web link' ) );
$content->setVariable( 'LABEL_BB_MAIL', gettext( 'MAIL' ) );
$content->setVariable( 'HINT_BB_MAIL', gettext( 'email link' ) );

// participant management:
$content->setCurrentBlock( 'BLOCK_PARTICIPANT_MANAGEMENT' );
$content->setVariable( 'PARTICIPANT_MANAGEMENT', gettext( 'Participant Management' ) );
$content->setVariable( 'PM_INFO_TEXT', gettext( "Here you can determine the kind of participant management you want to practice for this tutorial. You have the following options:" ) );
if ( isset( $tutorial ) && $tutorial->get_attribute( KOALA_GROUP_ACCESS ) == PERMISSION_UNDEFINED && $tutorial->get_steam_object()->get_creator()->get_id() != lms_steam::get_current_user()->get_id() ) {
	// broken access settings, and user doesn't have the permissions to fix it:
	$mailto = 'mailto:'.SUPPORT_EMAIL.'?subject=KoaLA:%20Invalid%20participant%20management&body=' . rawurlencode( "\nLink: " . get_current_URL() . "\nCreator: " . $creator->get_identifier() . "\n" );
	$content->setCurrentBlock( 'BLOCK_PARTICIPANTMERGEL' );
	$content->setVariable( 'LABEL_PARTICIPANTMERGEL', str_replace( '%MAILTO', $mailto, gettext( "There is a problem with the access settings. Please <a href=\"%MAILTO\">contact the support team</a> to fix it by setting the access rights again." )) );
	$content->parse( 'BLOCK_PARTICIPANTMERGEL' );
}
else {
	if ( !empty( $values['membership'] ) ) $current_access = $values['membership'];
	else if ( isset( $tutorial ) ) $current_access = $tutorial->get_attribute( KOALA_GROUP_ACCESS );
	else $current_access = PERMISSION_TUTORIAL_PUBLIC;
	if ( $current_access == PERMISSION_TUTORIAL_PASSWORD) $waspassword = TRUE;
	else $waspassword = FALSE;
	$content->setVariable( 'WASPASSWORD', $waspassword ? '1' : '0' );
	$access = koala_group_tutorial::get_access_descriptions();
	foreach ( $access as $key => $array ) {
		if ( $key == PERMISSION_UNDEFINED && $current_access != PERMISSION_UNDEFINED )
			continue;
		$content->setCurrentBlock( 'PARTICIPANT' );
		$content->setVariable( 'LABEL', $array['summary_short'] . ': ' . $array[ 'label' ] );
		$content->setVariable( 'VALUE', $key );
		if ( $key == $current_access )
			$content->setVariable( 'CHECK', "checked=\"checked\"" );
		if ( $key == PERMISSION_TUTORIAL_PASSWORD ) {
			$content->setVariable( 'ONCHANGE', "onchange=\"document.getElementById('passworddiv').style.display='block'\"" );
			$content->setCurrentBlock( 'PARTICIPANT_PASSWORD' );
			$content->setVariable( 'LABEL_PASSWORD', gettext( 'Password' ) );
			if ( !empty($values['password']) )
				$content->setVariable( 'VALUE_PASSWORD', $values['password'] );
			else if ( $waspassword )
				$content->setVariable( 'VALUE_PASSWORD', '******' );
			if ( $current_access == PERMISSION_TUTORIAL_PASSWORD )
				$content->setVariable( 'PASSWORDDIV_DISPLAY', 'block' );
			else
				$content->setVariable( 'PASSWORDDIV_DISPLAY', 'none' );
			$content->parse( 'PARTICIPANT_PASSWORD' );
		}
		else
			$content->setVariable( 'ONCHANGE', "onchange=\"document.getElementById('passworddiv').style.display='none'\"" );
		$content->parse( 'PARTICIPANT' );
	}
}
$content->parse( 'BLOCK_PARTICIPANT_MANAGEMENT' );

// access rights:
$content->setCurrentBlock( 'BLOCK_ACCESS' );
$content->setVariable( 'LABEL_ACCESS', gettext( 'Access rights' ) );
$content->setVariable( 'ACCESS_INFO_TEXT', gettext( "Here you can decide who may have what kind of access to this tutorial and its material. You have the following options:" ) );
if ( isset( $tutorial ) && $tutorial->get_attribute( KOALA_ACCESS ) == PERMISSION_UNDEFINED && $tutorial->get_steam_object()->get_creator()->get_id() != lms_steam::get_current_user()->get_id() ) {
	// broken access settings, and user doesn't have the permissions to fix it:
	$mailto = 'mailto:'.SUPPORT_EMAIL.'?subject=KoaLA:%20Invalid%20participant%20management&body=' . rawurlencode( "\nLink: " . get_current_URL() . "\nCreator: " . $creator->get_identifier() . "\n" );
	$content->setCurrentBlock( 'BLOCK_ACCESSMERGEL' );
	$content->setVariable( 'LABEL_ACCESSMERGEL', str_replace( '%MAILTO', $mailto, gettext( "There is a problem with the access settings. Please <a href=\"%MAILTO\">contact the support team</a> to fix it by setting the access rights again." )) );
	$content->parse( 'BLOCK_ACCESSMERGEL' );
}
else {
	if ( !empty( $values['access'] ) ) $current_access = $values['access'];
	else if ( isset( $tutorial ) ) $current_access = $tutorial->get_attribute( KOALA_ACCESS );
	else $current_access = PERMISSION_TUTORIAL_MATERIALS_COURSE;
	$access = koala_group_tutorial::get_workroom_access_descriptions();
	foreach ( $access as $key => $array ) {
		if ( $key == PERMISSION_UNDEFINED && $current_access != PERMISSION_UNDEFINED )
			continue;
		$content->setCurrentBlock( 'ACCESS' );
		$content->setVariable( 'LABEL', $array['summary_short'] . ': ' . $array[ 'label' ] );
		$content->setVariable( 'VALUE', $key );
		if ( $key == $current_access )
			$content->setVariable( 'CHECK', "checked=\"checked\"" );
		$content->parse( 'ACCESS' );
	}
}
$content->parse( 'BLOCK_ACCESS' );

if ( isset( $tutorial ) ) {
	$link_path = $tutorial->get_link_path();
	$link_path[] = array( 'name' => gettext( 'Preferences' ), 'link' => $link_path[-1]['link'] . 'edit/' );
}
else {
	$link_path = $course->get_link_path();
	$link_path[] = array( 'name' => gettext( 'Create a new Tutorial' ) );
	//$portal->set_page_title( gettext( 'Create Tutorial' ) );
}
$portal->set_page_main( $link_path, $content->get(), '' );

$portal->show_html();
?>
