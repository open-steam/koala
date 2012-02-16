<?php
require_once( "../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );
require_once( PATH_LIB . "url_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$portal->set_confirmation();

if ( !isset($weblog) || !is_object($weblog)) {
  if ( empty( $_GET[ "env" ] ) )
    throw new Exception( "Environment not set." ); 
  if ( empty( $_GET[ "group" ] ) )
    throw new Exception( "Group not set." );
  
  if ( ! $env = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "env" ] ) )
    throw new Exception( "Environment unknown." );
  if ( ! $grp = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "group" ] ) )
    throw new Exception( "Group unknown" );
}


$accessmergel = FALSE;
if (isset($weblog) && is_object($weblog)) {
  $creator = $weblog->get_creator();
  if ($weblog->get_attribute(KOALA_ACCESS) == PERMISSION_UNDEFINED && lms_steam::get_current_user()->get_id() != $creator->get_id() && !lms_steam::is_koala_admin( lms_steam::get_current_user() )) {
    $accessmergel = TRUE;
  }
}

$backlink = ( empty( $_POST["values"]["backlink"] ) ) ? $_SERVER[ "HTTP_REFERER" ] : $_POST[ "values" ][ "backlink" ];

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$values = $_POST[ "values" ];
	if ( get_magic_quotes_gpc() ) {
		if ( !empty( $values['name'] ) ) $values['name'] = stripslashes( $values['name'] );
		if ( !empty( $values['dsc'] ) ) $values['dsc'] = stripslashes( $values['dsc'] );
	}
	if ( empty( $values[ "name" ] ) )
	{
		$problems = gettext( "The name of the weblog is missing." );
		$hints    = gettext( "Please type in a name." );
	}

  if ( strpos($values[ "name" ], "/" )) {
   if (!isset($problems)) $problems = "";
   $problems .= gettext("Please don't use the \"/\"-char in the name of the weblog.");
  }

	if ( empty( $problems ) )
	{
    $group_members = $grp;
    $group_admins = 0;
    $group_staff = 0;

    // check if group is a course
    $grouptype = (string)$grp->get_attribute( "OBJ_TYPE" );
    if ( $grouptype == "course" ) {
      $group_staff = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $grp->get_groupname() . ".staff" );
      $group_admins = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $grp->get_groupname() . ".admins" );
      $group_members = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $grp->get_groupname() . ".learners" );
      $workroom = $group_members->get_workroom();
    } else {
      $workroom = $grp->get_workroom();
    }

    if ( !isset($weblog) || !is_object($weblog)) {
      $weblog_new = steam_weblog::create_steam_structure( $GLOBALS[ "STEAM" ], $values[ "name" ], $values[ "dsc" ], $env );
      $_SESSION[ "confirmation" ] = str_replace( "%NAME", h($values[ "name" ]), gettext( "New weblog '%NAME' created." ) );
    } else {
      $weblog->set_attribute(OBJ_NAME, $values[ "name" ]);
      $weblog->set_attribute(OBJ_DESC, $values[ "dsc" ]);
      $portal->set_confirmation( gettext( "The changes have been saved." ));
      $weblog_new = $weblog;
    }

    $koala_weblog = new lms_weblog( $weblog_new );
    $access = (int)$values[ "access" ];
    $access_descriptions = lms_weblog::get_access_descriptions( $grp );
    if (!$accessmergel) $koala_weblog->set_access( $access, $access_descriptions[$access]["members"] , $access_descriptions[$access]["steam"], $group_members, $group_staff, $group_admins );

		// HIER DER NEUE CODE::ENDE
		
		$GLOBALS[ "STEAM" ]->buffer_flush();	

    $cache = get_cache_function( lms_steam::get_current_user()->get_name(), 600 );
    $cache->drop( "lms_steam::get_inventory_recursive", $workroom->get_id(), CLASS_CALENDAR, array( "WEBLOG_LANGUAGE" ) );
    $cache->drop( "lms_steam::get_group_communication_objects", $workroom->get_id(), CLASS_MESSAGEBOARD | CLASS_CALENDAR | CLASS_CONTAINER | CLASS_ROOM );

    if ( !isset($weblog) || !is_object($weblog)) {
      header( "Location: " . $backlink );
      exit;
    }
	}
	else
	{
		$portal->set_problem_description( $problems, isset($hints)?$hints:"" );
	}
}

$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES );
$content->loadTemplateFile( "object_new.template.html" );

if ( isset($weblog) && is_object($weblog)) { 
  $content->setVariable( "INFO_TEXT", str_replace( "%NAME", h($weblog->get_name()), gettext( "You are going to edit the weblog '<b>%NAME</b>'." ) ) );
  $content->setVariable( "LABEL_CREATE", gettext( "Save changes" ) );
  $pagetitle = gettext( "Preferences" );
  if (empty($values)) {
    $values = array();
    $values["name"] = $weblog->get_name();
    $values["dsc"] = $weblog->get_attribute(OBJ_DESC);
    $values["access"] = $weblog->get_attribute(KOALA_ACCESS);
  }
  $breadcrumbheader = gettext("Preferences");
}
else {
  $grpname = $grp->get_attribute(OBJ_NAME);
  if ($grp->get_attribute(OBJ_TYPE) == "course") {
    $grpname = $grp->get_attribute(OBJ_DESC);
  }
  $content->setVariable( "INFO_TEXT", str_replace( "%ENV", h($grpname), gettext( "You are going to create a new weblog in '<b>%ENV</b>'." ) ) );
  $content->setVariable( "LABEL_CREATE", gettext( "Create weblog" ) );
  $pagetitle = gettext( "Create weblog" );
  $breadcrumbheader = gettext("Add new weblog");
}

if (!empty($values)) {
  if (!empty($values["name"])) $content->setVariable("VALUE_NAME", h($values["name"]));
  if (!empty($values["dsc"])) $content->setVariable("VALUE_DSC", h($values["dsc"]));
}

$content->setVariable( "VALUE_BACKLINK", $backlink );
$content->setVariable( "LABEL_NAME", gettext( "Name" ) );
$content->setVariable( "LABEL_DSC", gettext( "Description" ) );
$content->setVariable( "LABEL_ACCESS", gettext( "Access") );

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

if ($accessmergel) {
  $mailto = "mailto:'.SUPPORT_EMAIL.'?subject=KoaLA:%20Invalid%20Access%20Rights&body=" . rawurlencode( "\nLink: " . get_current_URL() . "\nCreator: " . $creator->get_identifier() . "\n" );
  
  $content->setCurrentBlock("BLOCK_ACCESSMERGEL");
  $content->setVariable("LABEL_ACCESSMERGEL", str_replace("%MAILTO", $mailto, gettext( "There is a problem with the access settings. Please <a href=\"%MAILTO\">contact the support team</a> to fix it by setting the access rights again." )));
  $content->parse("BLOCK_ACCESSMERGEL");
}
else {
  $access = lms_weblog::get_access_descriptions( $grp );
  if ((string) $grp->get_attribute( "OBJ_TYPE" ) == "course") {
    $access_default = PERMISSION_PUBLIC;
  } else {
    $access_default = PERMISSION_PUBLIC_READONLY;
    if (is_object($weblog) && $creator->get_id() != lms_steam::get_current_user()->get_id() ) {
      $access[PERMISSION_PRIVATE_READONLY]["label"] = str_replace( "%NAME", $creator->get_name(), $access[PERMISSION_PRIVATE_READONLY]["label"] );
    } else {
      $access[PERMISSION_PRIVATE_READONLY]["label"] =  gettext("Only members can read and comment. Only you can post.");
    }
  }
  if (is_array($access)) {
    $content->setCurrentBlock("BLOCK_ACCESS");
    foreach($access as $key => $array) {
      if ( ($key != PERMISSION_UNDEFINED) || ((isset($values) && (int)$values[ "access" ] == PERMISSION_UNDEFINED ))) {
        $content->setCurrentBlock("ACCESS");
        $content->setVariable("LABEL", $array["summary_short"] . ": " .$array["label"]);
        $content->setVariable("VALUE", $key);
        if ((isset($values) && $key == (int)$values[ "access" ]) || (empty($values) && $key == $access_default)) {
          $content->setVariable("CHECK", "checked=\"checked\"");
        }
        $content->parse("ACCESS");
      }
    }
    $content->parse("BLOCK_ACCESS");
  }
}

$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );

$rootlink = lms_steam::get_link_to_root( $grp );
$headline = array( $rootlink[0], $rootlink[1], array("link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")));
if ( isset($weblog) && is_object($weblog)) {
  $headline[] = array( "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/", "name" => $weblog->get_name() );
}
$headline[] = array( "link" => "", "name" =>  $breadcrumbheader );

$portal->set_page_main( $headline, $content->get() );
$portal->set_page_title( $pagetitle );
$portal->show_html()
?>
