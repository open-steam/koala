<?php

require_once( "../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
$all_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );

if ( (! lms_steam::is_steam_admin( $user )) && ( ! lms_steam::is_semester_admin( $current_semester, $user ))  )
{
        include( "bad_link.php" );
        exit;
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
        $values = $_POST[ "values" ];
        $problems = "";
        $hints    = "";

        if ( empty( $values[ "semester" ] ) )
        {
                throw new Exception( "Semester is not given." );
        }

        if ( empty( $values[ "id" ] ) )
        {
                $problems .= gettext( "The course ID is missing." ) . " ";
                $hints    .= gettext( "The ID is necessary for unique identification, ordering and finding the course. Please fill this out." ) . " ";
        }

        if ( !empty( $values[ "access" ] ) && $values["access"] == PERMISSION_COURSE_PASSWORD && empty($values["password"]) ) {
                $problems .= gettext( "The course password is missing." ) . " ";
                $hints    .= gettext( "You chose to password protect your course. Please provide a password." ) . " ";
        }

        if ( empty( $problems ) && ! empty( $values[ "get_lsf_infos" ] ) )
        {
                // INFOS UEBER LSF HOLEN
                $lsf_client = new hislsf_soap();
                unset( $_SESSION[ "LSF_COURSE_INFO" ] );
                // TODO: SEMESTER DYNAMISCH SETZEN
                $result = $lsf_client->get_available_courses( SYNC_HISLSF_SEMESTER, $values[ "id" ] );
                if ( isset( $result->veranstaltung ) )
                {
                        if ( count( $result->veranstaltung ) == 1 )
                        {
                                header( "Location: " . PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name(). "/new/?lsf_course_id=" . $result->veranstaltung->Veranstaltungsschluessel );
                                exit;
                        }
                        else
                        {
                                header( "Location: " . PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name(). "/hislsf/" . $values[ "id" ]. "/" );
                                exit;
                        }
                }
                else
                {
                        $problems = "Keine Veranstaltungen im LSF unter dieser Nummer gefunden.";
                }
        }

        if ( empty( $problems ) )
        {

                if ( empty( $values[ "name" ] ) )
                {
                        $problems .= gettext( "The course name is missing." ) . " ";
                        $hints    .= gettext( "A name is necessary for identification." ) . " ";
                }
                if ( strpos($values[ 'id' ], '.' ) )
                {
                  $problems .= gettext("Please don't use the \".\"-char in the course ID.") . ' ';
                }
                if ( empty( $values[ "tutors" ] ) )
                {
                        $values[ "tutors" ] = "NN";
                }

                if ( empty( $problems ) )
                {
                        if ( !isset($values["hislsf"]) || ! $values[ "hislsf" ] )
                        {
                                $values[ "lsf_id" ] = "";
                        }
                        $max_members = -1;
                        if ($values["lsf_id"] === "") {
                          if ( !empty( $values[ "maxsize" ] ) && trim($values[ "maxsize" ]) != "" && preg_match('/[^-.0-9]/', trim($values[ "maxsize" ])) )
                          {
                            $problems .= gettext( "Invalid max number of participants." ) . " ";
                            $hints    .= gettext( "Please enter a valid number for the max number of participants."). " " . gettext("Please note that the input of a '0' or to leave the field blank means no limitation." ) . " ";
                          } else {
                            if ( !empty( $values[ "maxsize" ] ) && trim($values[ "maxsize" ]) != "" && trim($values[ "maxsize" ]) < 0 ) {
                              $problems .= gettext( "Invalid max number of participants." ) . " ";
                              $hints    .= gettext( "Please enter a number equal or greater than '0' for the max number of participants.") . " " . gettext("Please note that the input of a '0' or to leave the field blank means no limitation." ) . " ";
                            } else {
                              if (isset( $values[ "maxsize" ] )) {
                                if (trim($values[ "maxsize" ]) === "") $max_members = 0;
                                else $max_members = (int)trim($values["maxsize"]);
                              }
                            }
                          }
                        }
                        if (empty($problems)) {
                        	try {
                          		$new_course = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), $values[ "id" ], $current_semester, FALSE, $values[ "name" ] );
                        	    } catch (Exception $e) {
                       			$problems .= gettext( "The course ID already exists." ) . " ";
                				$hints    .= gettext( "The ID is necessary for unique identification, ordering and finding the course. This ID already exists." ) . " ";
                        	}
                          if (empty($problems)) {
                          $new_course->set_attributes( array(
                                                  "OBJ_TYPE"                     => "course",
                                                  "COURSE_PARTICIPANT_MNGMNT" => $obj_type,  // TODO: $obj_type seems not to be set...?
                                                  "COURSE_SEMESTER"              => $values[ "semester" ],
                                                  "COURSE_TUTORS"                => $values[ "tutors" ],
                                                  "COURSE_SHORT_DSC"             => $values[ "short_dsc" ],
                                                  "COURSE_LONG_DSC"              => $values[ "long_dsc" ],
                                                  "COURSE_HISLSF_ID" => $values[ "lsf_id" ]
                                                  ));
                          $learners = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), "learners", $new_course, FALSE, "Participants of course '" . $values[ "name" ]. "'" );
                          $learners->set_attribute( "OBJ_TYPE", "course_learners" );
                          $staff    = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), "staff", $new_course, FALSE, "Tutors of course '" . $values[ "name" ] . "'");
                          $staff->set_attribute( "OBJ_TYPE", "course_staff" );
                          $staff->add_member( $user );
                          $admins    = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), "admins", $new_course, FALSE, "Admins of course '" . $values[ "name" ] . "'");
                          $admins->set_attribute( "OBJ_TYPE", "course_admins" );
                          
                          // uncomment below if koala can handle admins vs tutors
                          //$admins->add_member( $user );

                          // RIGHTS MANAGEMENT =======================================
                          $course_calendar = $new_course->get_calendar();
                          $learners_workroom = $learners->get_workroom();
                          $course_workroom = $new_course->get_workroom();

                          $staff->set_sanction_all( $staff );
                          $staff->sanction_meta( SANCTION_ALL, $staff);
                          $learners->set_sanction_all( $staff );
                          $learners->sanction_meta( SANCTION_ALL, $staff );
                          $new_course->set_sanction_all( $staff );
                          $new_course->sanction_meta( SANCTION_ALL, $staff );

                          $admins->set_sanction_all( $admins );
                          $admins->sanction_meta( SANCTION_ALL, $admins);
                          $staff->set_sanction_all( $admins );
                          $staff->sanction_meta( SANCTION_ALL, $admins);
                          $learners->set_sanction_all( $admins );
                          $learners->sanction_meta( SANCTION_ALL, $admins );
                          $new_course->set_sanction_all( $admins );
                          $new_course->sanction_meta( SANCTION_ALL, $admins );

                          $course_calendar->set_acquire( FALSE );
                          $course_calendar->set_sanction_all( $staff );
                          $course_calendar->sanction_meta(SANCTION_ALL, $staff);
                          $course_calendar->set_sanction_all( $admins );
                          $course_calendar->sanction_meta(SANCTION_ALL, $admins);
                          $course_calendar->set_read_access( $learners, TRUE );
                          $course_calendar->set_write_access( $new_course, FALSE );
                          $course_calendar->set_insert_access( $new_course, FALSE );
                          $course_calendar->set_insert_access( $all_users, FALSE );
                          // Course workroom
                          $course_workroom->set_sanction($new_course, SANCTION_READ | SANCTION_EXECUTE | SANCTION_ANNOTATE);
                          $course_workroom->set_sanction_all( $staff );
                          $course_workroom->set_sanction_all( $admins );
                          $course_workroom->sanction_meta(SANCTION_ALL, $staff);
                          $course_workroom->sanction_meta(SANCTION_ALL, $admins);
                          // Learners workroom
                          $learners_workroom->set_read_access( $all_users, TRUE );
                          $learners_workroom->set_sanction($learners, SANCTION_READ | SANCTION_EXECUTE | SANCTION_ANNOTATE);
                          $learners_workroom->set_sanction_all( $staff );
                          $learners_workroom->set_sanction_all( $admins );
                          $learners_workroom->sanction_meta(SANCTION_ALL, $staff);
                          $learners_workroom->sanction_meta(SANCTION_ALL, $admins);

                          $koala_course = new koala_group_course($new_course);
                          if ( !isset($values[ "hislsf" ]) || !$values[ "hislsf"]) {
                            $access = $values["access"];
                            $koala_course->set_access($access, $learners, $staff, $admins, KOALA_GROUP_ACCESS );
                            if (isset($values["password"]) && $access == PERMISSION_COURSE_PASSWORD) $koala_course->get_group_learners()->set_password( $values["password"]);
                            else $koala_course->get_group_learners()->set_password("");
                          } else {
                            $koala_course->set_access(PERMISSION_COURSE_HISLSF, $learners, $staff, $admins, KOALA_GROUP_ACCESS);
                          }
                          if ($max_members > -1) $learners->set_attribute(GROUP_MAXSIZE, $max_members);
                          // RIGHTS MANAGEMENT =======================================

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
										$extension->disable_for( $koala_course );
									else
										$extension->enable_for( $koala_course );
								}
							}
						}

                          $cache = get_cache_function( "ORGANIZATION" );
                          $cache->drop( "lms_steam::semester_get_courses", $current_semester->get_id() );
                          header( "Location: " . PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name(). "/" . $new_course->get_name() . "/" );
                          exit;
                        }
                        }
                }
        }
        if ( ! empty( $problems ) )
        {
                $portal->set_problem_description( $problems, $hints );
        }
}
if ( ! empty( $_GET[ "lsf_course_id" ] ) )
{
        $lsf_client = new hislsf_soap();
        $course_infos = $lsf_client->get_course_information( SYNC_HISLSF_SEMESTER, $_GET[ "lsf_course_id" ] );
        if ( empty($course_infos) && empty($problems) ) {
          $portal->set_problem_description(gettext("Error getting course data from HIS/LSF.") );
        } else {
          if ( empty( $course_infos[ "course_dsc" ] ) )
          {
                  $course_infos[ "course_dsc" ] = "keine Beschreibung vorhanden.";
          }
          else
          {
                  $course_infos[ "course_dsc" ] = unhtmlentities( $course_infos[ "course_dsc" ]);
          }
          $values = array(
                          "lsf_id"=> $course_infos[ "course_lsf_id" ],
                          "id"    => $course_infos[ "course_id" ],
                          "name"  => $course_infos[ "course_name" ],
                          "tutors"=> $course_infos[ "course_tutors" ],
                          "short_dsc" => $course_infos[ "course_type" ],
                          "long_dsc" => $course_infos[ "course_dsc" ]
                         );
        }
        $_SESSION[ "LSF_COURSE_INFO" ] = "";
}
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "courses_create.template.html" );
$content->setVariable( "FORM_ACTION", PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name() . "/new/" );
$content->setVariable( "VALUE_SEMESTER", h($current_semester->get_name()) );
//$content->setVariable( "INFO_TEXT", gettext( "Creating a course means..." ) );

$content->setVariable( "CONFIRMATION_TEXT", str_replace( "%SEMESTER", h($current_semester->get_attribute( "OBJ_DESC")), gettext( "You are going to create a new course in <b>%SEMESTER</b>." ) ) . " " . gettext( "Please fill out the requested meta data at first." ) . " " . gettext( "At the bottom, you can determine the manner of participant management." ) . " " . gettext( "Also you can add further course admins later on." ) );
$content->setVariable( "LABEL_GENERAL_INFORMATION", gettext( "General Information" ) );
$content->setVariable( "LABEL_COURSE_ID", gettext( "Course ID" ) );
$content->setVariable( "VALUE_COURSE_ID", isset($values)?h($values[ "id" ]):'' );
$content->setVariable( "LABEL_COURSE_NAME", gettext( "Name" ) );
$content->setVariable( "VALUE_COURSE_NAME", isset($values)?h($values[ "name" ]):'' );
$content->setVariable( "LABEL_COURSE_SHORT_INFORMATION", gettext( "Short Info" ) );
$content->setVariable( "VALUE_SHORT_DSC", isset($values)?h($values[ "short_dsc" ]):'' );
$content->setVariable( "SHORT_DSC_SHOW_UP", gettext( "This value will show up in the semester's courses list beside id, name and staff members." ) );

$content->setVariable( "LABEL_COURSE_TUTORS", gettext( "Staff members" ) );
$content->setVariable( "VALUE_TUTORS", isset($values)?h($values[ "tutors" ]):'' );
$content->setVariable( "LABEL_LONG_DSC", gettext( "Long description" ) );
$content->setVariable( "LONG_DSC_SHOW_UP", gettext( "This is for your course page. Please add information about schedule and locations at least." ) );
$content->setVariable( "VALUE_LONG_DSC", isset($values)?h($values[ "long_dsc" ]):'' );

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

$content->setVariable( "PARTICIPANT_MANAGEMENT", gettext( "Participant Management" ) );

if ( isset($values) && $values[ "lsf_id" ] > 1 )
{
        $content->setCurrentBlock( "HIS_LSF_PM" );
        $content->setVariable( "LSF_COURSE_ID", isset($values)?h($values[ "lsf_id" ]):'' );
        $content->setVariable( "LSF_COURSE", isset($values)?h($values[ "id" ]):'' . " " . isset($values)?h($values[ "name"]):'' . " (" . isset($values)?h($values[ "short_dsc" ]):'' . ")" );
        if ( $values[ "hislsf"] )
        {
                $content->setVariable( "HISLSF_CHECKED", "CHECKED" );
        }
        $content->setVariable( "LABEL_HISLSF", "Ja, es soll die Teilnehmerverwaltung des HIS LSF verwendet werden." );
        $content->setVariable( "HISLSF_INFO", "Wenn gesetzt, k&ouml;nnen sich Studenten f&uuml;r diesen Kurs nur &uuml;ber das HIS LSF anmelden." );
        $content->parse( "HIS_LSF_PM" );
}

if (!isset($values) || !isset($values["hislsf"]) ) {
  $content->setCurrentBlock("BLOCK_MAXSIZE");
  $content->setVariable("LABEL_MAXSIZE", gettext("Max number of participants"));
  $content->setVariable("LABEL_MAXSIZE_DSC", gettext("To limit the max number of participants for your course enter a number greater than 0. Leave this field blank or enter a '0' for no limitation."));

  if ( isset( $values["maxsize"] ) )
	  $content->setVariable("VALUE_MAXSIZE", h($values["maxsize"]));
  $content->parse("BLOCK_MAXSIZE");

  $content->setCurrentBlock("BLOCK_ACCESS");
  $content->setVariable( "PARTICIPANT_MANAGEMENT", gettext( "Participant Management" ) );
}

$access = koala_group_course::get_access_descriptions( );
$access_default = PERMISSION_COURSE_PUBLIC;
if (is_array($access) && (!isset($values) || !isset($values["hislsf"])) ) {
  $content->setCurrentBlock("BLOCK_ACCESS");
  foreach($access as $key => $array) {
    if ( $key != PERMISSION_COURSE_PAUL_SYNC && ($key != PERMISSION_UNDEFINED) || ((isset($values) && (int)$values[ "access" ] == PERMISSION_UNDEFINED ))) {
      $content->setCurrentBlock("ACCESS");
      $content->setVariable("LABEL", $array["summary_short"] . ": " . $array["label"]);
      $content->setVariable("VALUE", $key);
      if ((isset($values) && $key == (int)$values[ "access" ]) || (empty($values) && $key == $access_default)) {
        $content->setVariable("CHECK", "checked=\"checked\"");
      }
      if ($key == PERMISSION_COURSE_PASSWORD) {
        $content->setVariable("ONCHANGE", "onchange=\"document.getElementById('passworddiv').style.display='block'\"");
        $content->setCurrentBlock("ACCESS_PASSWORD");
        $content->setVariable("LABEL_PASSWORD", gettext("Password"));
        if (!empty($values["password"])) $content->setVariable("VALUE_PASSWORD", $values["password"]);
        if ((isset($values["access"]) && $values["access"] == PERMISSION_COURSE_PASSWORD)) {
          $content->setVariable("PASSWORDDIV_DISPLAY", "block");
        } elseif (!isset($values["access"]) && $access_default == PERMISSION_COURSE_PASSWORD) {
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
					$content->setVariable( "SUBEXTENSION_ID", $subextension_name );
					$content->setVariable( "SUBEXTENSION_NAME", $subextension->get_display_name() );
					$content->setVariable( "SUBEXTENSION_DESC", $subextension->get_display_description() );
					$content->setVariable( "SUBEXTENSION_DISABLED", "disabled=\"disabled\"" );
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

$content->setVariable( "LABEL_CREATE_COURSE", gettext( "Create and finish" ) );
$content->setVariable( "LABEL_CREATE_ADD_ADMIN", gettext( "Create and add further admins" ) );

$portal->set_page_main(
                array( array( "link" => PATH_URL . SEMESTER_URL . "/" . $current_semester->get_name(). "/", "name" => h($current_semester->get_attribute( "OBJ_DESC" )) ), array( "link" => "", "name" => gettext( "Create new course" ) ) ),
                $content->get()

                );
$portal->show_html();

?>
