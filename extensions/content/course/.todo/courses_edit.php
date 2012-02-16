<?php

require_once( "../etc/koala.conf.php" );
$user = lms_steam::get_current_user();
$all_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );

if (! $group->is_admin( $user ) )
{
	include( "bad_link.php" );
	exit;
}

$lsfid = $group->get_steam_group()->get_attribute("COURSE_HISLSF_ID");
$is_lsfcourse = (is_string($lsfid) && strlen($lsfid) > 0 && $lsfid > 0);
$paul_id = $group->get_attribute("COURSE_NUMBER");
$is_paul_course = koala_group_course::is_paul_course( $paul_id );

$accessmergel = FALSE;
if (is_object($group)) {
  $creator = $group->get_steam_group()->get_creator();
  if ($group->get_steam_group()->get_attribute(KOALA_GROUP_ACCESS) == PERMISSION_UNDEFINED && lms_steam::get_current_user()->get_id() != $creator->get_id() && !lms_steam::is_koala_admin( lms_steam::get_current_user() )) {
    $accessmergel = TRUE;
  }
}


if ( isset($_POST[ "course_save" ]) /*$_SERVER[ "REQUEST_METHOD" ] == "POST"*/ )
{
	$values = $_POST[ "values" ];
	$problems = "";
	$hints    = "";

	if ( empty( $values[ "OBJ_DESC" ] ) )
	{
		$problems .= gettext( "The course name is missing." ) . " ";
		$hints    .= gettext( "A name is necessary for identification." ) . " ";
	}

	if ( empty( $values[ "COURSE_TUTORS" ] ) )
	{
		$values[ "COURSE_TUTORS" ] = "NN";
	}

  $max_members = -1;
  $sizeproblems = FALSE;
	if ( !empty( $values[ "maxsize" ] ) && trim($values[ "maxsize" ]) != "" && preg_match('/[^-.0-9]/', trim($values[ "maxsize" ])) )
	{
		$problems .= gettext( "Invalid max number of participants." ) . " ";
		$hints    .= gettext( "Please enter a valid number for the max number of participants."). " " . gettext("Please note that the input of a '0' or to leave the field blank means no limitation." ) . " ";
    $sizeproblems = TRUE;
	} else {
    if ( !empty( $values[ "maxsize" ] ) && trim($values[ "maxsize" ]) != "" && trim($values[ "maxsize" ]) < 0 ) {
      $problems .= gettext( "Invalid max number of participants." ) . " ";
      $hints    .= gettext( "Please enter a number equal or greater than '0' for the max number of participants.") . " " . gettext("Please note that the input of a '0' or to leave the field blank means no limitation." ) . " ";
      $sizeproblems = TRUE;
    } else {
      if (isset( $values[ "maxsize" ] )) {
        if (trim($values[ "maxsize" ]) === "") $max_members = 0;
        else $max_members = (int)trim($values["maxsize"]);
      }
    }
  }

  if (!$sizeproblems && isset($max_members) && $max_members > 0 && $max_members < $group->count_members()) {
    $problems .= gettext( "Cannot set max number of participants." ) . " ";
    $hints    .= str_replace("%ACTUAL", $group->count_members(), str_replace("%CHOSEN", $max_members, gettext( "You choosed to limit your course's max number of participants of %CHOSEN but your course already has %ACTUAL participants. If you want to set the max number of participants below %ACTUAL you have to remove some participants first." ))) . " ";
  }

  if ( !empty( $values[ "access" ] ) && $values["access"] == PERMISSION_COURSE_PASSWORD && empty($values["password"]) ) {
          $problems .= gettext( "The course password is missing." ) . " ";
          $hints    .= gettext( "You chose to password protect your course. Please provide a password." ) . " ";
  }

	if ( empty( $problems ) )
	{
		$group->set_attributes( array_diff_key($values, array("password" => "", "maxsize" => "")) );

    $learners = $group->get_group_learners();

    if ( !$is_lsfcourse ) {
      $access = $values["access"];

      $waspassword = 0;
      $akt_access = $group->get_attribute(KOALA_GROUP_ACCESS);
      if ($akt_access == PERMISSION_COURSE_PASSWORD) $waspassword = 1;

      if (!$accessmergel) $group->set_access($access, $learners, $group->get_group_staff());

      if (isset($values) && $waspassword == 1 && isset($values["password"]) && $values["password"] == "******" && $values["access"] == PERMISSION_COURSE_PASSWORD){
        // Do nothing in case of valid password dummy
      } elseif ( $values["access"] != PERMISSION_COURSE_PASSWORD ) {
        $learners->set_password("");
      }else {
        $learners->set_password( isset($values["password"])?trim($values["password"]):"" );
      }
      if ($max_members > -1) $learners->set_attribute(GROUP_MAXSIZE, $max_members);
    }

	// extensions:
	if ( isset( $_POST["extensions_available"] ) && !empty( $_POST["extensions_available"] ) ) {
		$extensions_available = explode( "/", $_POST["extensions_available"] );
		if ( isset( $_POST["extensions_enabled"] ) )
			$extensions_enabled = $_POST["extensions_enabled"];
		else
			$extensions_enabled = array();
		if ( isset( $_POST["extensions_enabled_add"]))
			$extensions_enabled = array_merge($extensions_enabled, explode("/", $_POST["extensions_enabled_add"]));
		if ( is_array( $extensions_available ) ) {
			foreach ( $extensions_available as $extension_name ) {
				$extension = lms_steam::get_extensionmanager()->get_extension( $extension_name );
				if ( !is_object( $extension ) ) continue;
				if ( array_search( $extension_name, $extensions_enabled ) === FALSE )
					$extension->disable_for( $group );
				else
					$extension->enable_for( $group );
			}
		}
	}

		$cache = get_cache_function( "ORGANIZATION" );
		$cache->drop( "lms_steam::semester_get_courses", $current_semester->get_id() );
		$_SESSION["confirmation"] = gettext( "The changes have been saved." ) ;
    header( "Location: " . $_SERVER["REQUEST_URI"]);
		exit;
	}
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}
elseif( isset($_POST[ "get_paul_course_data" ]) )
{	//at this time only the course name and short description are updated/changed

	$paul_client = new paul_soap();

	$paul_course_id = h($group->get_attribute(OBJ_NAME));

	try
	{
		$paul_course_info = $paul_client->get_course_information( $paul_course_id );
	}
	catch( Exception $exception )
	{
		$problem = $exception->getMessage();
		error_log($problem);
		throw new Exception( "PAUL_SOAP exception: " . $problem );
	}

	//the same as in the following else-block
	$values = $group->get_attributes( array( "OBJ_NAME", "OBJ_DESC", "COURSE_TUTORS", "COURSE_SHORT_DSC", "COURSE_LONG_DSC" ) );
	$ms = $group->get_group_learners()->get_attribute(GROUP_MAXSIZE);
	if ($ms === 0) $values["maxsize"] = "";
	else $values["maxsize"] = $ms;

	$values[ "OBJ_DESC" ] = $paul_course_info[ "course_name_german" ];
	$values[ "COURSE_SHORT_DSC" ] = $paul_course_info[ "short_description" ];

	//$portal->set_confirmation("test");

	//print_r($paul_course_values);
}
else
{
	$values = $group->get_attributes( array( "OBJ_NAME", "OBJ_DESC", "COURSE_TUTORS", "COURSE_SHORT_DSC", "COURSE_LONG_DSC" ) );
	$ms = $group->get_group_learners()->get_attribute(GROUP_MAXSIZE);
	if ($ms === 0) $values["maxsize"] = "";
	else $values["maxsize"] = $ms;
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "courses_edit.template.html" );

$content->setVariable( "LABEL_EDIT_COURSE_DESCRIPTION", gettext( "Course Preferences" ) );
$course_name = $group->get_attribute(OBJ_NAME);
$course_number = $group->get_attribute("COURSE_NUMBER");
$course_name = koala_group_course::convert_course_id( $course_name, $course_number );
if (koala_group_course::is_paul_course( $course_number) && lms_steam::is_koala_admin($user)) {
  $content->setVariable( "VALUE_PAUL_ID", "  (" . gettext( "PAUL-ID: " . h($group->get_attribute(OBJ_NAME)) ) . ")" );
  $content->setVariable( "ACTION_PAUL_COURSE_DATA", "<input type=\"submit\"  name=\"get_paul_course_data\" id=\"get_paul_course_data\" title=\"" . gettext("Get the course name and short description from PAUL") . "\" value=\"" . gettext( "Get PAUL course data" ) . "\">" );
}
$content->setVariable( "LABEL_COURSE_ID", gettext( "Course ID" ) );
$content->setVariable( "VALUE_COURSE_ID", h( $course_name ) );



$content->setVariable( "LABEL_COURSE_NAME", gettext( "Name" ) );
$content->setVariable( "VALUE_COURSE_NAME", h($values[ "OBJ_DESC" ]) );
$content->setVariable( "LABEL_COURSE_SHORT_INFORMATION", gettext( "Short Info" ) );
$content->setVariable( "VALUE_SHORT_DSC", h($values[ "COURSE_SHORT_DSC" ]) );
$content->setVariable( "SHORT_DSC_SHOW_UP", gettext( "This value will show up in the semester's courses list beside id, name and staff members." ) );

$content->setVariable( "LABEL_COURSE_TUTORS", gettext( "Staff members" ) );
$content->setVariable( "VALUE_TUTORS", h($values[ "COURSE_TUTORS" ]) );
$content->setVariable( "LABEL_LONG_DSC", gettext( "Long description" ) );
$content->setVariable( "LONG_DSC_SHOW_UP", gettext( "This is for your course page. Please add information about schedule and locations at least." ) );
$content->setVariable( "VALUE_LONG_DSC", h($values[ "COURSE_LONG_DSC" ]) );
$content->setVariable( "COURSE_SAVE", gettext( "Save changes" ));
$content->setVariable( "AENDERN", gettext("Are you sure about your changes?"));

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

if ( !$is_lsfcourse ) {
  $content->setCurrentBlock("BLOCK_MAXSIZE");
  $content->setVariable("LABEL_MAXSIZE", gettext("Max number of participants"));
  $content->setVariable("LABEL_MAXSIZE_DSC", gettext("To limit the max number of participants for your course enter a number greater than 0. Leave this field blank or enter a '0' for no limitation."));

  $content->setVariable("VALUE_MAXSIZE", h($values["maxsize"]));
  $content->parse("BLOCK_MAXSIZE");

  $content->setCurrentBlock("BLOCK_ACCESS");
  $content->setVariable( "PARTICIPANT_MANAGEMENT", gettext( "Participant Management" ) );
  if ($accessmergel) {
    $mailto = "mailto:'.SUPPORT_EMAIL.'?subject=KoaLA:%20Invalid%20Access%20Rights&body=" . rawurlencode( "\nLink: " . get_current_URL() . "\nCreator: " . $creator->get_identifier() . "\n" );
    $content->setCurrentBlock("BLOCK_ACCESSMERGEL");
    $content->setVariable("LABEL_ACCESSMERGEL", str_replace("%MAILTO", $mailto, gettext( "There is a problem with the access settings. Please <a href=\"%MAILTO\">contact the support team</a> to fix it by setting the access rights again." )));
    $content->parse("BLOCK_ACCESSMERGEL");
  }
  else {
    $waspassword = 0;
    $access = koala_group_course::get_access_descriptions( );
    if (isset($values) && isset($values["access"])) $akt_access = $values["access"];
    else {
      $akt_access = $group->get_attribute(KOALA_GROUP_ACCESS);
      if ($akt_access == PERMISSION_COURSE_PASSWORD) $waspassword = 1;
    }
    if (is_array($access)) {
      $content->setVariable("WASPASSWORD", $waspassword);
      foreach($access as $key => $array) {
        if ( $key != PERMISSION_UNDEFINED || $akt_access == PERMISSION_UNDEFINED )
        {
          if ( $key != PERMISSION_COURSE_PAUL_SYNC || $is_paul_course || $akt_access ==  PERMISSION_COURSE_PAUL_SYNC) {
            $content->setCurrentBlock("ACCESS");
            $content->setVariable("LABEL", $array["summary_short"] . ": " .$array["label"]);
            $content->setVariable("VALUE", $key);
            if ($key == $akt_access) {
              $content->setVariable("CHECK", "checked=\"checked\"");
            }
            if ($key == PERMISSION_COURSE_PASSWORD) {
              $content->setVariable("ONCHANGE", "onchange=\"document.getElementById('passworddiv').style.display='block'\"");
              $content->setCurrentBlock("ACCESS_PASSWORD");
              $content->setVariable("LABEL_PASSWORD", gettext("Password"));
              if (!empty($values["password"])) $content->setVariable("VALUE_PASSWORD", $values["password"]);
              else if ($waspassword == 1) $content->setVariable("VALUE_PASSWORD", "******" );
              if ($akt_access == PERMISSION_COURSE_PASSWORD) {
                $content->setVariable("PASSWORDDIV_DISPLAY", "block");
              } else {
                $content->setVariable("PASSWORDDIV_DISPLAY", "none");
              }
              $content->parse("ACCESS_PASSWORD");
            }
            else {
              $content->setVariable("ONCHANGE", "onchange=\"document.getElementById('passworddiv').style.display='none'\"");
            }
            $content->parse("ACCESS");
          }
        }
      }
    }
  }
  $content->parse("BLOCK_ACCESS");
} else {
  $content->setCurrentBlock("BLOCK_ACCESS");
  $content->setCurrentBlock("BLOCK_HISLSF");
  $content->setVariable( "PARTICIPANT_MANAGEMENT", gettext( "Participant Management" ) );

  $hislink = "<a href=\"https://lsf.uni-paderborn.de/qisserver/rds?state=wsearchv&search=2&veranstaltung.veranstid=" . trim( $lsfid ) . "\" target=\"_blank\">HIS-LSF</a>";

  $content->setVariable( "PARTICIPANT_MANAGEMENT_VALUE", str_replace("%LINK", $hislink, gettext( "The participant management for this course is handled by <b>%LINK</b>.")));
  $content->parse("BLOCK_HISLSF");
  $content->parse("BLOCK_ACCESS");
}

// extensions:
$extensions = lms_steam::get_extensionmanager()->get_extensions_by_class( 'koala_group_course' );
if ( count( $extensions ) > 0 ) {
	$content->setCurrentBlock( "BLOCK_EXTENSIONS" );
	$content->setVariable( "LABEL_EXTENSIONS", gettext( "Extensions" ) );
	$extension_list = array();
	foreach ( $extensions as $extension ) {
		if( $extension->get_requirements() === array() )
		{
			$extension_name = $extension->get_name();
			$content->setCurrentBlock( "BLOCK_EXTENSION" );
			$content->setVariable( "EXTENSION_ID", $extension_name );
			$content->setVariable( "EXTENSION_NAME", $extension->get_display_name() );
			$content->setVariable( "EXTENSION_DESC", $extension->get_display_description() );
			$extension_enabled = $extension->is_enabled( $group );
			if ( $extension_enabled )
				$content->setVariable( "EXTENSION_ENABLED", "checked='checked'" );
			$subextensions = lms_steam::get_extensionmanager()->get_dependent_extensions($extension);
			if( count( $subextensions ) > 0 )
			{
				$content->setCurrentBlock( "BLOCK_SUBEXTENSIONS" );
				$content->setVariable( "LABEL_SUBEXTENSIONS", str_replace( "%EXTENSION", h($extension->get_display_name()), gettext( "The following sub-extensions are available for %EXTENSION" ) ));
				foreach($subextensions as $subextension)
				{
					$subextension_name = $subextension->get_name();
					$content->setCurrentBlock( "BLOCK_SUBEXTENSION" );
					$content->setVariable( "PARENT_EXTENSION_ID", $extension_name );
					$content->setVariable( "SUBEXTENSION_ID", $subextension->get_name() );
					$content->setVariable( "SUBEXTENSION_NAME", $subextension->get_display_name() );
					$content->setVariable( "SUBEXTENSION_DESC", $subextension->get_display_description() );
					$checkbox_attributes = '';
					if ( $subextension->is_enabled_for( $group ) )
						$checkbox_attributes .= "checked='checked'";
					if ( ! $extension_enabled || $subextension_name === "units_docpool")
						$checkbox_attributes .= " disabled='disabled'";
					$content->setVariable( "SUBEXTENSION_ENABLED", $checkbox_attributes );
					$content->parse( "BLOCK_SUBEXTENSION" );
					$extension_list[] = $subextension_name;
				}
				$content->parse( "BLOCK_SUBEXTENSIONS" );
			}
			$content->parse( "BLOCK_EXTENSION" );
			$extension_list[] = $extension_name;
		}
	}
	$content->setVariable( "VALUE_EXTENSIONS", implode( "/", $extension_list ) );
	$content->parse( "BLOCK_EXTENSIONS" );
}


$backlink = PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name(). "/" . h($group->get_name()) . "/";

$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );

$portal->set_page_main(
	array( array( "link" => $backlink, "name" => h($values["OBJ_DESC"]) ), array( "linK" => "", "name" => gettext( "Course Preferences" ) ) ),
	$content->get()
);
$portal->show_html();

?>

