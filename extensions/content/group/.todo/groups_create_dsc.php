<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "format_handling.inc.php" );
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$create_new = TRUE;
include("groups_edit.php");
/*
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

function parse_public_categories( $template, $selected = 0 )
{
	$template->setVariable( "CATEGORY_LABEL", gettext( "Categories" ) );
	$template->setVariable( "LABEL_PLEASE_CHOOSE", gettext( "Please choose") );
	$public = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_PUBLIC_GROUP, CLASS_GROUP );
	$categories = $public->get_workroom()->get_inventory( CLASS_CONTAINER | CLASS_ROOM );
	foreach( $categories as $category )
	{
		$template->setCurrentBlock( "BLOCK_CATEGORY" );
		$template->setVariable( "CAT_ID", $category->get_id() );
		$template->setVariable( "CAT_NAME", h($category->get_name()) );
		if ( $category->get_id() == $selected )
		$template->setVariable( "CAT_SELECTED", 'selected="selected"' );
		$template->parse( "BLOCK_CATEGORY" );
	}
}

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$group_id = ( ! empty( $_POST[ "parent" ] ) ) ? $_POST[ "parent" ] : STEAM_PUBLIC_GROUP;

$group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $group_id );
if (!($group instanceof steam_group)) {
  $portal->set_problem_description("Configuration Error: False Public or Private Group Setting.", "False Group is: " . $group->get_name());
  exit;
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "GET" || empty( $_POST[ "grouptype" ] ) )
{
	header( "Location: " . PATH_URL . "groups_create.php" );
	exit;
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "groups_create_dsc.template.html" );

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && isset( $_POST[ "creation_step" ]) && $_POST[ "creation_step" ] == 2 )
{
	$values = $_POST[ "values" ];
	$problem = "";
	$hint    = "";

	// Problem checks

	if ( empty( $values[ "name" ] ) )
	{
		$problem = gettext( "You have to specify a name for this group." ) . " ";
		$hint    = gettext( "Choose a clear synonym which helps people to find your group by name." ) . " ";
	}
	else
	{
    if ( strpos($values[ 'name' ], '/' )) {
      $problem .= gettext("Please don't use the \"/\"-char in the groupname.") . ' ';
    }
    if ( strpos($values[ 'name' ], '.' )) {
      $problem .= gettext("Please don't use the \".\"-char in the groupname.") . ' ';
    }
    else {
      $siblings = $group->get_subgroups();
      foreach( $siblings as $sibling )
      {
        if ( $sibling->get_name() == $values[ "name" ] )
        {
          $problem .= gettext( "There groupname you've choosen is used for another group already." ) . " ";
          $hint .= gettext( "Please choose another groupname." ) . " ";
          break;
        }
      }
    }
	}

	if ( empty( $values[ "short_dsc" ] ) )
	{
		$problem .= gettext( "The short description is missing." ) . " ";
		$hint    .= gettext( "Sometimes, keywords are sufficient to help people understand what your group is for." ) . " ";
	}

  if ( $_POST[ "grouptype" ] !== "group_private" && $values[ "category" ] == "0" ) {
    $problem .= gettext( "You have to choose a category." ) . " ";
    $hint    .= gettext( "Choose a category to help the users find your group. " ) . " ";
  }

  if ($_POST["grouptype"] === "group_moderated" || $_POST["grouptype"] === "group_private" ) $max_members = 0;
  else $max_members = -1;
  $sizeproblems = FALSE;
	if ( !empty( $values[ "maxsize" ] ) && trim($values[ "maxsize" ]) != "" && preg_match('/[^-.0-9]/', trim($values[ "maxsize" ])) )
	{
		$problem .= gettext( "Invalid max number of participants." ) . " ";
		$hint    .= gettext( "Please enter a valid number for the max number of participants."). " " . gettext("Please note that the input of a '0' or to leave the field blank means no limitation." ) . " ";
    $sizeproblems = TRUE;
	} else {
    if ( !empty( $values[ "maxsize" ] ) && trim($values[ "maxsize" ]) != "" && trim($values[ "maxsize" ]) < 0 ) {
      $problem .= gettext( "Invalid max number of participants." ) . " ";
      $hint    .= gettext( "Please enter a number equal or greater than '0' for the max number of participants.") . " " . gettext("Please note that the input of a '0' or to leave the field blank means no limitation." ) . " ";
      $sizeproblems = TRUE;
    } else {
      if (isset( $values[ "maxsize" ] )) {
        if (trim($values[ "maxsize" ]) === "") $max_members = 0;
        else $max_members = (int)trim($values["maxsize"]);
      }
    }
  }

  if (!$sizeproblems && isset($max_members) && $max_members != 0 && $max_members < $group->count_members()) {
    $problem .= gettext( "Cannot set max number of participants." ) . " ";
    $hint    .= str_replace("%ACTUAL", $group->count_members(), str_replace("%CHOSEN", $max_members, gettext( "You choosed to limit your group's max number of participants of %CHOSEN but your course already has %ACTUAL participants. If you want to set the max number of participants below %ACTUAL you have to remove some participants first." ))) . " ";
  }

	if ( empty( $problem ) )
	{
		switch( $_POST[ "grouptype" ] )
		{
			case( "group_moderated" ):
				$environment = ( ! empty( $values[ "category" ] ) ) ? steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $values[ "category"] ) : FALSE;
				$new_group = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), $values[ "name" ], $group, $environment, $values[ "short_dsc" ] );
				$new_group->add_member( lms_steam::get_current_user() );
				$new_group->set_attribute( "OBJ_TYPE", "group_moderated" );
				$new_group->set_attribute( "OBJ_LONG_DSC", $values[ "long_dsc" ] );
				// todo: not tested
				break;

			case( "group_private" );
        $new_group = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), $values[ "name" ], $group, FALSE, $values[ "short_dsc"] );
        $new_group->add_member( lms_steam::get_current_user() );
        $new_group->set_attribute( "OBJ_TYPE", "group_private" );
        $new_group->set_attribute( "OBJ_LONG_DSC", $values[ "long_dsc" ] );
			break;

			default:
          $environment = ( ! empty( $values[ "category" ] ) ) ? steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $values[ "category"] ) : FALSE;
          $new_group = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), $values[ "name" ], $group, $environment, $values[ "short_dsc" ] );
          $new_group->add_member( lms_steam::get_current_user() );
          $new_group->set_attribute( "OBJ_LONG_DSC", $values[ "long_dsc" ] );
          $all_user = steam_factory::groupname_to_object( $GLOBALS["STEAM"]->get_id(), STEAM_ALL_USER );
          $world_user = steam_factory::groupname_to_object( $GLOBALS["STEAM"]->get_id(), "Everyone" );
          $workroom = $new_group->get_workroom();
          if ( ! empty( $values[ "password" ] ) )
          {
            $new_group->set_password( trim( $values[ "password" ] ) );
            $new_group->set_acquire( FALSE );
            $new_group->set_attribute( "OBJ_TYPE", "group_password" );
            $new_group->set_insert_access( $all_user, FALSE );
            $workroom->set_insert_access( $all_user, FALSE );
            $workroom->set_read_access( $all_user );
          }
          else
          {
            $new_group->set_attribute( "OBJ_TYPE", "group_public" );
            $new_group->set_insert_access( $all_user );
            $new_group->set_insert_access( $world_user );
            $new_group->set_read_access( $world_user );
            $workroom->set_read_access( $all_user );
          }
          break;
		}
	
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
					$extension->disable_for( $new_group );
				else
					$extension->enable_for( $new_group );
			}
		}
	}
	
    if ($max_members > -1) $new_group->set_attribute(GROUP_MAXSIZE, $max_members);
    $_SESSION[ "confirmation" ] = str_replace( "%NAME", $values[ "name" ], gettext( "New group '%NAME' created." ) );
		require_once( "Cache/Lite.php" );
		$cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
		$cache->clean( $group->get_id() );
		header( "Location: " . PATH_URL . "groups/" . $new_group->get_id() . "/" );
		exit;
	}
	else
	{
		$portal->set_problem_description( $problem, $hint );
		$content->setVariable( "VALUE_NAME", h($values[ "name" ]) );
		if (isset($values["password"])) $content->setVariable( "VALUE_PASSWORD", h($values[ "password" ]) );
		$content->setVariable( "VALUE_SHORT_DSC", h($values[ "short_dsc" ]) );
		$content->setVariable( "VALUE_LONG_DSC", h($values[ "long_dsc" ]) );
	}

}

$cat = (isset($values) && isset($values["category"]))?$values["category"]:"0";

switch ( $_POST[ "grouptype" ] )
{

//	case "group_moderated":
//		$group_type = gettext( "public (invitation only) group" );
//		$content->setCurrentBlock( "BLOCK_PUBLIC" );
//		parse_public_categories( $content, $cat );
//		$content->parse( "BLOCK_PUBLIC" );
//		break;

	case "group_private":
		$group_type = gettext( "private group" );
		break;

	default:
		$group_type = gettext( "public group" );
		$content->setCurrentBlock( "BLOCK_PUBLIC" );
		parse_public_categories( $content, $cat);
		$content->setCurrentBlock( "BLOCK_PASSWORD" );
		$content->setVariable( "PASSWORD_LABEL", gettext( "Password" ) );
		$content->setVariable( "PASSWORD_INFO_TEXT", gettext( "If no password is set, each user can instantly join this group." ) . " " . gettext( "Otherwise, he must know the password." ) );
		$content->parse( "BLOCK_PASSWORD" );
		$content->parse( "BLOCK_PUBLIC" );
    
    // Add group maxsize field
    $m = new HTML_TEMPLATE_IT();
    $m->loadTemplateFile( PATH_TEMPLATES . "groups_maxsize_widget_old.template.html" );
    $m->setCurrentBlock("BLOCK_MAXSIZE");
    $m->setVariable("LABEL_MAXSIZE", gettext("Max number of participants"));
    $m->setVariable("LABEL_MAXSIZE_DSC", gettext("To limit the max number of participants for your course enter a number greater than 0. Leave this field blank or enter a '0' for no limitation."));
    if (isset($values)) $m->setVariable("VALUE_MAXSIZE", h($values["maxsize"]));
    $mhtml = $m->get();
    $content->setVariable("HTML_WIDGET_FOOTER", $mhtml);
		break;
}
$content->setVariable( "VALUE_GROUPTYPE", h($_POST[ "grouptype" ]) );
$content->setVariable( "VALUE_PARENT_GROUP", $group->get_id() );
$content->setVariable( "INFO_TEXT", gettext( "Give your group a name and description." ) );
$content->setVariable( "NAME_LABEL", gettext( "What are you going to call it? (required)" ) );
$content->setVariable( "SHORT_DSC_LABEL", gettext( "Give an one-line explanation of what is the group about? (required)" ) );
$content->setVariable( "SHORT_DSC_SHOW_UP", gettext( "This description will show up in group search results." ) );
$content->setVariable( "LONG_DSC_LABEL", gettext( "What's the group about? (optional)" ) );
$content->setVariable( "LONG_DSC_SHOW_UP", gettext( "This description will come up on your group's page." ) );
$content->setVariable( "LABEL_NEXT", gettext( "Create group") );

// extensions:
$extensions = lms_steam::get_extensionmanager()->get_extensions_by_class( 'koala_group_default' );
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
		$content->parse( "BLOCK_EXTENSION" );
		$extension_list[] = $extension_name;
	}
	$content->setVariable( "VALUE_EXTENSIONS", implode( "/", $extension_list ) );
	$content->parse( "BLOCK_EXTENSIONS" );
}

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

$portal->set_page_main(
array(
array( "link" => PATH_URL . "groups_create.php",
"name" => gettext( "Create a new group" )),
array( "link" => "",
"name" => secure_gettext($group_type) )
),
$content->get()
);
$portal->show_html();
*/
?>
