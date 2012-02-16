<?php

require_once( "../etc/koala.conf.php" );

if (!isset($create_new)) $create_new = FALSE;

$user = lms_steam::get_current_user();
$all_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );

$accessmergel = FALSE;
if (isset($group) && is_object($group)) {
  $creator = $group->get_steam_group()->get_creator();
  if ($group->get_steam_group()->get_attribute(KOALA_GROUP_ACCESS) != PERMISSION_GROUP_PRIVATE && lms_steam::get_current_user()->get_id() != $creator->get_id() && !lms_steam::is_koala_admin( lms_steam::get_current_user() )) {
    $accessmergel = TRUE;
  }
}

if ($create_new) {
  // CREATE
  if (isset($_POST) && isset($_POST["grouptype"]) && $_POST[ "grouptype" ] == "group_private" ) $is_public = FALSE;
  else $is_public = TRUE;
  $waspassword = FALSE;
  $backlink = PATH_URL . "groups_create.php";
  $extensions = lms_steam::get_extensionmanager()->get_extensions_by_class( 'koala_group' );
  $submit_text = gettext ("Create group");
} else {
  // EDIT
  $backlink = PATH_URL . "groups/" . $group->get_steam_group()->get_id() . "/";
  if ( ! $group->is_admin( $user ) ) {
    include( "bad_link.php" );
    exit;
  }
  $is_public = $group->is_public();
  $waspassword = $group->get_steam_group()->has_password();
  $extensions = $group->get_extensions( TRUE );
  $submit_text = gettext ("Save changes");
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && isset($_POST["save"]) ) {
	$values = $_POST[ "values" ];
	$problems = "";
	$hints    = "";



	if ( empty( $values[ "name" ] ) )
	{
		$problems = gettext( "You have to specify a name for this group." ) . " ";
		$hints    = gettext( "Choose a clear synonym which helps people to find your group by name." ) . " ";
	}
	else
	{
    if ( strpos($values[ 'name' ], '/' )) {
      $problems .= gettext("Please don't use the \"/\"-char in the groupname.") . ' ';
    }
    if ( strpos($values[ 'name' ], '.' )) {
      $problems .= gettext("Please don't use the \".\"-char in the groupname.") . ' ';
    }
    else {
      if ( $is_public && $values[ "category" ] == "0" ) {
        $problems .= gettext( "You have to choose a category." ) . " ";
        $hints    .= gettext( "Choose a category to help the users find your group. " ) . " ";
      }

      if ( empty( $problems ) ) {
        if ($create_new) {
          if ($is_public)  $pgroup_id = STEAM_PUBLIC_GROUP;
          else             $pgroup_id = STEAM_PRIVATE_GROUP;
        } else {
          $parent = $group->get_steam_group()->get_parent_group();
          if (is_object($parent)) $pgroup_id = $parent->get_id(); // a koala group
          else $pgroup_id = -1; // no koala group. its a steam only group
        }
        if ($pgroup_id != -1) {
          $parentgroup = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $pgroup_id );
          if (!is_object($parentgroup) || !($parentgroup instanceof steam_group)) {
            throw new Exception("Configuration Error: Invalid Public or Private Group Setting. False Group id=" . $pgroup_id, E_CONFIGURATION);
            exit;
          }
          $siblings = $parentgroup->get_subgroups();
          steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $siblings, array(OBJ_NAME));
          foreach( $siblings as $sibling ) {
            if ( strtolower($sibling->get_name()) == strtolower($values[ "name" ]) ) {
              if ($create_new || $sibling->get_id() != $group->get_steam_group()->get_id() ) {
                $problems .= gettext( "The groupname you've choosen is used for another group already." ) . " ";
                $hints .= gettext( "Please choose another groupname." ) . " ";
                break;
              }
            }
          }
        }
      }
    }
	}

	if ( empty( $values[ "short_dsc" ] ) )
	{
		$problems .= gettext( "The short description is missing." ) . " ";
		$hints    .= gettext( "Sometimes, keywords are sufficient to help people understand what your group is for." ) . " ";
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

  if (!$create_new && !$sizeproblems && isset($max_members) && $max_members > 0 && $max_members < $group->count_members()) {
    $problems .= gettext( "Cannot set max number of participants." ) . " ";
    $hints    .= str_replace("%ACTUAL", $group->count_members(), str_replace("%CHOSEN", $max_members, gettext( "You choosed to limit your group's max number of participants of %CHOSEN but your course already has %ACTUAL participants. If you want to set the max number of participants below %ACTUAL you have to remove some participants first." ))) . " ";
  }

  if ( !empty( $values[ "access" ] ) && $values["access"] == PERMISSION_GROUP_PUBLIC_PASSWORD && empty($values["password"]) ) {
          $problems .= gettext( "The group password is missing." ) . " ";
          $hints    .= gettext( "You chose to password protect your group. Please provide a password." ) . " ";
  }

	if ( empty( $problems ) ) {
    $access = $values["access"];

    $waspassword = 0;
    if ($create_new) {
      // CREATE
      $akt_access = PERMISSION_GROUP_UNDEFINED;

      $environment = ( ! empty( $values[ "category" ] ) ) ? steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $values[ "category"] ) : FALSE;
			$new_group = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), $values[ "name" ], $parentgroup, $environment, $values[ "short_dsc" ] );
      if (!is_object($new_group)) {
        throw new Exception("Error creating group with name=" . $values["name"] . " in parent=" . $parentgroup->get_name(), E_PARAMETER);
        exit;
      }
      $group = new koala_group_default($new_group);
      $group->add_member( lms_steam::get_current_user() );
    } else {
      // EDIT
      $akt_access = $group->get_attribute(KOALA_GROUP_ACCESS);
    }

    if ($is_public) {
      // PUBLIC
      if ($akt_access == PERMISSION_GROUP_PUBLIC_PASSWORD) $waspassword = 1;

      if (!$accessmergel) {
        $group->set_group_access($access);
      }

      if (isset($values) && $waspassword == 1 && isset($values["password"]) && $values["password"] == "******" && $values["access"] == PERMISSION_GROUP_PUBLIC_PASSWORD){
        // Do nothing in case of valid password dummy
      } elseif ( $values["access"] != PERMISSION_GROUP_PUBLIC_PASSWORD ) {
        $group->get_steam_group()->set_password("");
      } else {
        $group->get_steam_group()->set_password( isset($values["password"])?trim($values["password"]):"" );
      }
      if ($max_members > -1) $group->set_attribute(GROUP_MAXSIZE, $max_members);
    } else {
      // PRIVATE
      // Set Group access only, if there is no problem with the access rights
      // Set group access only for koala groups. Skip this for the steam only groups
      if (!$accessmergel && ($create_new || $group->get_steam_group()->get_parent_group()->get_id() == STEAM_PRIVATE_GROUP) ) {
        $group->set_group_access(PERMISSION_GROUP_PRIVATE);
      }
    }

	//echo "*part. '" . $values["privacy_deny_participants"] . "'<br/>"; //TODO

    $newvalues = array(
      OBJ_DESC => $values["short_dsc"],
      "OBJ_LONG_DSC" => $values["dsc"],
      "GROUP_PRIVACY" => $values["privacy_deny_documents"] | $values["privacy_deny_participants"],
    );

    $group->set_attributes( $newvalues );
    if ($group->get_attribute('OBJ_NAME')!=$values['name']){
       if (!$group->set_name($values['name'])){
          $problems .= gettext( "A group with this name already exists." ) . " ";
          $hints    .= gettext( "Please choose another name for your group." ) . " ";
       }
    }
    if ($max_members > -1) $group->set_attribute(GROUP_MAXSIZE, $max_members);

    // extensions:
    if ( isset( $_POST["extensions_available"] ) && !empty( $_POST["extensions_available"] ) ) {
      $extensions_available = explode( "/", $_POST["extensions_available"] );
      if ( isset( $_POST["extensions_enabled"] ) )
        $extensions_enabled = array_keys( $_POST["extensions_enabled"] );
      else
        $extensions_enabled = array();
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

    if ($create_new) {
      if ($is_public) {
        $_SESSION[ "confirmation" ] = str_replace("%CATEGORY", $environment->get_name(), str_replace("%NAME", $values["name"], gettext( "The public group '%NAME' has been created in '%CATEGORY'.") ) );
      } else {
        $_SESSION[ "confirmation" ] = str_replace("%NAME", $values["name"], gettext( "The private group '%NAME' has been created.") );
      }
      header( "Location: " . PATH_URL . "/groups/" . $group->get_steam_group()->get_id() . "/");
      exit;

    } else {
      $_SESSION[ "confirmation" ] = gettext( "The changes have been saved." );
      header( "Location: " . $_SERVER["REQUEST_URI"]);
      exit;
    }
	}
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}
else
{
  if (!$create_new) {
    // EDIT
    $current_values = $group->get_attributes( array( OBJ_NAME, OBJ_DESC, "OBJ_LONG_DSC", GROUP_MAXSIZE, "GROUP_PRIVACY" ) ); //TODO
    // Convert "0" into "" for values which are not set yet
    foreach($current_values as $key => $value) {
      if ($value == "0") $current_values[$key] = "";
    }
    $values = array(
      "name" => $current_values[OBJ_NAME],
      "short_dsc" => $current_values[OBJ_DESC],
      "dsc" => $current_values["OBJ_LONG_DSC"],
      "maxsize" => $current_values[GROUP_MAXSIZE],
      "privacy_deny_documents" => ($current_values["GROUP_PRIVACY"] & PERMISSION_GROUP_PRIVACY_DENY_DOCUMENTS) ,
      "privacy_deny_participants" => ($current_values["GROUP_PRIVACY"] & PERMISSION_GROUP_PRIVACY_DENY_PARTICIPANTS),
    );

    	//echo "**part. '" . $values["privacy_deny_participants"] . "'<br/>"; //TODO
    	//$values["privacy_deny_participants"] = $act_privacy_deny_participants;
    	//echo "***part. '" . $values["privacy_deny_participants"] . "'<br/>";

    $ms = $values["maxsize"];
    if ($ms === 0) $values["maxsize"] = "";
    else $values["maxsize"] = $ms;
    $grouptype = $group->get_attribute(OBJ_TYPE);
  } else {
    // CREATE
    $grouptype = "";
    if ($_SERVER[ "REQUEST_METHOD" ] == "POST"  && isset($_POST["save"])) $values = $_POST["values"];
  }
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "groups_edit.template.html" );

if ($create_new) {
  // CREATE
  $content->setVariable("VALUE_GROUPTYPE", $_POST["grouptype"]);
  $infotext =  gettext( "You are going to create a new group. ");
} else {
  // EDIT
  $infotext =  gettext( "You are going to edit information for '<b>%NAME</b>'. ");
  if ($is_public) {
    $infotext .= "<br />" . gettext( "'%NAME' is a <b>public group</b>. Feel free to edit the groups description, choose extension modules for your group or change the participant management method.");
  } else {
    $infotext .= "<br />" . gettext( "'%NAME' is a <b>private group</b>. Feel free to edit the groups description or choose extension modules for your group. The participant management method for private courses is invite only. Only group moderators can add users to this group.");
  }
  $infotext = str_replace( "%NAME", h($values[ "name" ]), $infotext);
}

$content->setVariable( "INFO_TEXT", $infotext );

$content->setVariable( "LABEL_NAME", gettext( "Name" ) );

(isset($values[ "name" ])) ? $content->setVariable( "VALUE_NAME", h($values[ "name" ])) : "";


$content->setVariable( "LABEL_SHORT_DSC", gettext( "Short description" ) );

(isset($values[ "short_dsc" ])) ? $content->setVariable( "VALUE_SHORT_DSC", h($values[ "short_dsc" ]) ) : "";

$content->setVariable( "LABEL_LONG_DSC", gettext( "Long description" ) );
(isset($values[ "dsc" ])) ? $content->setVariable( "VALUE_LONG_DSC", h($values[ "dsc" ])) : "";
$content->setVariable( "GROUP_SAVE", $submit_text);

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

  //hier Voreinstellung?!
  //$values[ "privacy_deny_participants" ] = PERMISSION_GROUP_PRIVACY_DENY_PARTICIPANTS; //TODO
  //$values[ "privacy_deny_documents" ] = PERMISSION_GROUP_PRIVACY_DENY_DOCUMENTS;
  //$privacy_deny_participants_default = PERMISSION_GROUP_PRIVACY_DENY_PARTICIPANTS;
  //$privacy_deny_documents_default = PERMISSION_GROUP_PRIVACY_DENY_DOCUMENTS;


if ( ($create_new && $is_public) || ($grouptype !== "group_moderated" && $grouptype !== "group_private") ) {
  // Add group maxsize field
  $m = new HTML_TEMPLATE_IT();
  $m->loadTemplateFile( PATH_TEMPLATES . "groups_maxsize_widget.template.html" );
  $m->setCurrentBlock("BLOCK_MAXSIZE");
  $m->setVariable("LABEL_MAXSIZE", gettext("Max number of participants"));
  $m->setVariable("LABEL_MAXSIZE_DSC", gettext("To limit the max number of participants for your course enter a number greater than 0. Leave this field blank or enter a '0' for no limitation."));
  (isset($values["maxsize"])) ? $m->setVariable("VALUE_MAXSIZE", h($values["maxsize"])) : "";
  $mhtml = $m->get();
}

$content->setVariable( "BACKLINK", "<a class=\"button\" href=\"$backlink\">" . gettext( "back" ) . "</a>" );

// extensions:
if ( count( $extensions ) > 0 ) {
	$content->setCurrentBlock( "BLOCK_EXTENSIONS" );
	$content->setVariable( "LABEL_EXTENSIONS", gettext( "Extensions" ) );
	$extension_list = array();
	foreach ( $extensions as $extension ) {
		$extension_name = $extension->get_name();
		$content->setCurrentBlock( "BLOCK_EXTENSION" );
		$content->setVariable( "EXTENSION_ID", $extension_name );
		$content->setVariable( "EXTENSION_NAME", $extension->get_display_name() );
		$content->setVariable( "EXTENSION_DESC", $extension->get_display_description() );
		if ( $extension->is_enabled( $group ) )
			$content->setVariable( "EXTENSION_ENABLED", "checked='checked'" );
		$content->parse( "BLOCK_EXTENSION" );
		$extension_list[] = $extension_name;
	}
	$content->setVariable( "VALUE_EXTENSIONS", implode( "/", $extension_list ) );
	$content->parse( "BLOCK_EXTENSIONS" );
}

if ($is_public) {
  // PUBLIC GROUP
  if ($create_new) {

  	$privacy_deny_participants_default = PERMISSION_GROUP_PRIVACY_DENY_PARTICIPANTS; //TODO //Voreinstellung?!
  	$privacy_deny_documents_default = PERMISSION_GROUP_PRIVACY_DENY_DOCUMENTS;
  	$values[ "privacy_deny_participants" ] = $privacy_deny_participants_default;
  	$values[ "privacy_deny_documents" ] = $privacy_deny_documents_default;

    $content->setCurrentBlock( "BLOCK_CATEGORIES" );
   	$content->setVariable( "LABEL_CATEGORIES", gettext( "Categories" ) );
    $content->setVariable( "LABEL_PLEASE_CHOOSE", gettext( "Please choose") );
    $public = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_PUBLIC_GROUP, CLASS_GROUP );
    $categories = $public->get_workroom()->get_inventory( CLASS_CONTAINER | CLASS_ROOM );
    foreach( $categories as $category )
    {
      $content->setCurrentBlock( "BLOCK_CATEGORY_DISPLAY" );
      $content->setVariable( "CAT_ID", $category->get_id() );
      $content->setVariable( "CAT_NAME", h($category->get_name()) );
      if ( $category->get_id() == $values["category"] || (empty($values["category"]) && isset($_GET["parent"]) && $_GET["parent"] == $category->get_id()) )
      $content->setVariable( "CAT_SELECTED", 'selected="selected"' );
      $content->parse( "BLOCK_CATEGORY_DISPLAY" );
    }
    $content->parse( "BLOCK_CATEGORIES" );
  } else {
    $content->setCurrentBlock("BLOCK_CATEGORY");
    $category = $group->get_steam_group()->get_environment();
    if (is_object($category)) {
      $catname = $category->get_name();
    } else {
      $catname = gettext("Miscellaneous");
    }
    $content->setVariable("LABEL_CATEGORY", gettext("Category"));
    $content->setVariable("VALUE_CATEGORY", $catname . "<br/><small>(" . gettext("The category cannot be changed") . ")</small>");
    $content->parse("BLOCK_CATEGORY");
  }
  // MAXSIZE
  $content->setCurrentBlock("BLOCK_MAXSIZE");
  $content->setVariable("LABEL_MAXSIZE", gettext("Max number of participants"));
  $content->setVariable("LABEL_MAXSIZE_DSC", gettext("To limit the max number of participants for your course enter a number greater than 0. Leave this field blank or enter a '0' for no limitation."));
  $content->setVariable("VALUE_MAXSIZE", h($values["maxsize"]));
  $content->parse("BLOCK_MAXSIZE");
  // PARTICIPANT MANAGEMENT
  $content->setCurrentBlock("BLOCK_ACCESS");
  $content->setVariable( "PARTICIPANT_MANAGEMENT", gettext( "Participant Management" ) );
  if ($accessmergel) {
    $mailto = "mailto:'.SUPPORT_EMAIL.'?subject=KoaLA:%20Invalid%20Access%20Rights&body=" . rawurlencode( "\nLink: " . get_current_URL() . "\nCreator: " . $creator->get_identifier() . "\n" );
    $content->setCurrentBlock("BLOCK_ACCESSMERGEL");
    $content->setVariable("LABEL_ACCESSMERGEL", str_replace("%MAILTO", $mailto, gettext( "There is a problem with the participant management settings. Please <a href=\"%MAILTO\">contact the support team</a> to fix it by setting the access rights again." )));
    $content->parse("BLOCK_ACCESSMERGEL");
  }
  else {
    $waspassword = 0;
    $access = koala_group_default::get_group_access_descriptions( );
    if (isset($values) && isset($values["access"])) $akt_access = $values["access"];
    else {
      if ($create_new) {
        // CREATE
        $akt_access = PERMISSION_GROUP_PUBLIC_FREEENTRY;
      } else {
        // EDIT
        $akt_access = $group->get_attribute(KOALA_GROUP_ACCESS);
        if ($akt_access == PERMISSION_GROUP_PUBLIC_PASSWORD) $waspassword = 1;
      }
    }
    if (is_array($access)) {
      $content->setVariable("WASPASSWORD", $waspassword);
      foreach($access as $key => $array) {
        if ( ($key != PERMISSION_GROUP_UNDEFINED || $akt_access == PERMISSION_GROUP_UNDEFINED) && $key != PERMISSION_GROUP_PRIVATE )
        {
          $content->setCurrentBlock("ACCESS");
          $content->setVariable("LABEL", $array["summary_short"] . ": " .$array["label"]);
          $content->setVariable("VALUE", $key);
          if ($key == $akt_access) {
            $content->setVariable("CHECK", "checked=\"checked\"");
          }
          if ($key == PERMISSION_GROUP_PUBLIC_PASSWORD) {
            $content->setVariable("ONCHANGE", "onchange=\"document.getElementById('passworddiv').style.display='block'\"");
            $content->setCurrentBlock("ACCESS_PASSWORD");
            $content->setVariable("LABEL_PASSWORD", gettext("Password"));
            if (!empty($values["password"])) $content->setVariable("VALUE_PASSWORD", $values["password"]);
            else if ($waspassword == 1) $content->setVariable("VALUE_PASSWORD", "******" );
            if ($akt_access == PERMISSION_GROUP_PUBLIC_PASSWORD) {
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
  // PARTICIPANT AND DOCUMENT PRIVACY //TODO
  //$values[ "privacy_deny_participants" ] = PERMISSION_GROUP_PRIVACY_DENY_PARTICIPANTS; //voreingestellt//TODO
  //$values[ "privacy_deny_documents" ] = PERMISSION_GROUP_PRIVACY_DENY_DOCUMENTS;
  /*
  $privacy_deny_participants_default = PERMISSION_GROUP_PRIVACY_DENY_PARTICIPANTS;
  $privacy_deny_documents_default = PERMISSION_GROUP_PRIVACY_DENY_DOCUMENTS;
  $values[ "privacy_deny_participants" ] = $privacy_deny_participants_default;
  */
  $content->setCurrentBlock("BLOCK_PRIVACY");
  $content->setVariable("LABEL_PRIVACY", gettext("Privacy"));
  $content->setVariable("LABEL_PRIVACY_DSC", gettext("Set the privacy of participants and documents."));
  $content->setVariable("LABEL_PRIVACY_DENY_PARTICIPANTS", gettext("Hide participants"));
  $content->setVariable("LABEL_PRIVACY_DENY_DOCUMENTS", gettext("Hide documents"));
  $content->setVariable("VALUE_PRIVACY_DENY_PARTICIPANTS", PERMISSION_GROUP_PRIVACY_DENY_PARTICIPANTS);
  $content->setVariable("VALUE_PRIVACY_DENY_DOCUMENTS", PERMISSION_GROUP_PRIVACY_DENY_DOCUMENTS);

  //echo "****part. '" . $values["privacy_deny_participants"] . "'<br/>"; //todo

  if (isset($values) && isset($values["privacy_deny_participants"])) $privacy_deny_participants = $values["privacy_deny_participants"];
  if (isset($values) && isset($values["privacy_deny_documents"])) $privacy_deny_documents = $values["privacy_deny_documents"];
  //if (isset($values) && isset($values["privacy_deny_participants"])) $privacy_deny_participants = PERMISSION_GROUP_PRIVACY_DENY_PARTICIPANTS;
  //if ((isset($values) && $key == (int)$values[ "privacy_deny_participants" ]) || (empty($values) && $key == $privacy_deny_participants_default)) {

  if ((isset($values) && $values[ "privacy_deny_participants" ] == PERMISSION_GROUP_PRIVACY_DENY_PARTICIPANTS)) {
    $content->setVariable("CHECK1", "checked=\"checked\"");
  }
  //if ((isset($values) && $key == (int)$values[ "privacy_deny_documents" ]) || (empty($values) && $key == $privacy_deny_documents_default)) {
  if ((isset($values) && $values[ "privacy_deny_documents" ] == PERMISSION_GROUP_PRIVACY_DENY_DOCUMENTS)) {
    $content->setVariable("CHECK2", "checked=\"checked\"");
  }
  $content->parse("BLOCK_PRIVACY");

  //echo "*****part. '" . $values["privacy_deny_participants"] . "'"; //TODO

} else {
  // PRIVATE GROUP
  if ($create_new || $group->get_steam_group()->get_parent_group()->get_id() == STEAM_PRIVATE_GROUP) {
    // Display the participant management access block only for koala groups
    // The participant management select box wont be displayed for steam only groups
    $content->setCurrentBlock("BLOCK_ACCESS");
    $content->setVariable( "PARTICIPANT_MANAGEMENT", gettext( "Participant Management" ));
    if (!$create_new) $akt_access = $group->get_attribute(KOALA_GROUP_ACCESS);
    else $akt_access = PERMISSION_GROUP_PRIVATE;
    $access = koala_group_default::get_group_access_descriptions();
    $content->setCurrentBlock("ACCESS");
    if ($akt_access != PERMISSION_GROUP_PRIVATE) {
      if ($accessmergel) {
        $mailto = "mailto:'.SUPPORT_EMAIL.'?subject=KoaLA:%20Invalid%20Access%20Rights&body=" . rawurlencode( "\nLink: " . get_current_URL() . "\nCreator: " . $creator->get_identifier() . "\n" );
        $content->setVariable("ACCESS_TEXT", str_replace("%MAILTO", $mailto, gettext( "There is a problem with the access settings. Please <a href=\"%MAILTO\">contact the support team</a> to fix it by saving the properties one time." )));
      } else {
        $content->setVariable("ACCESS_TEXT", "A Problem with the access rights was detected. Please save the permissions one time to fix this issue." );
      }
    } else {
      $content->setVariable("ACCESS_TEXT", $access[PERMISSION_GROUP_PRIVATE]["label"]);
    }
    $content->parse("ACCESS");
    $content->parse("BLOCK_ACCESS");
  }
}

if ($create_new) {
  // CREATE
  if ($is_public) $headertext = gettext("Create public group");
  else            $headertext = gettext("Create private group");
  $headernavi = array( array( "link" => PATH_URL . "user/" .  $user->get_name() . "/groups/", "name" => gettext("Your groups") ), array( "link" => $backlink, "name" => gettext("Create Group") ), array( "link" => "", "name" => $headertext ) );
} else {
  // EDIT
  $headernavi = array( array( "link" => $backlink, "name" => h($group->get_name()) ), array( "link" => "", "name" => gettext( "Preferences" ) ) );
}

$portal->set_page_main(
	$headernavi,
	$content->get()
);
$portal->show_html();

?>
