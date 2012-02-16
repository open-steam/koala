<?php
require_once( "../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

if ( isset( $koala_container ) )
	$container = $koala_container->get_steam_object();
else if ( !isset( $koala_container ) && isset( $container ) && ($container instanceof steam_container) )
	$koala_container = koala_object::get_koala_object( $container );

if (!isset($koala_container) || !is_object($koala_container)) {
	if ( (!isset( $environment ) || !is_object( $environment )) && empty( $_GET[ "env" ] ) )
		throw new Exception( "Environment not set." );
	if ( !(isset( $environment ) || !is_object( $environment )) && ! is_object( $environment = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "env" ] ) ) )
		throw new Exception( "Environment unknown." );
	if ( $environment instanceof steam_user ) {
		$environment_koala_container = new koala_container_clipboard( $environment );
		$group = $environment;
	}
	else {
		$environment_koala_container = koala_object::get_koala_object( $environment );
		$group = $environment_koala_container->get_koala_owner();
	}
}
else {
	$group = $koala_container->get_koala_owner();
}

$backlink = ( empty( $_POST["values"]["backlink"] ) ) ? $_SERVER[ "HTTP_REFERER" ] : $_POST[ "values" ][ "backlink" ];

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" ) {
	$values = $_POST[ "values" ];
	if ( empty( $values[ "name" ] ) ) {
		$problems = gettext( "You didn't specify a name for the new folder." );
		$hints = gettext( "Please type in a name." );
	}

	if ( strpos( $values[ "name" ], "/" ) ) {
		if (!isset( $problems ) ) $problems = "";
		$problems .= gettext( "Please don't use a \"/\"-character in the the folder name." );
	}
	
	if ( empty( $problems ) ) {
		$group_members = $group;
		$group_admins = FALSE;
		$group_staff = FALSE;
		
		// check if group is a course
		$grouptype = (string)$group->get_attribute( "OBJ_TYPE" );
		if ( $grouptype == "course" ) {
			$group_staff = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $group->get_groupname() . ".staff" );
			$group_admins = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $group->get_groupname() . ".admins" );
			$group_members = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $group->get_groupname() . ".learners" );
			$workroom = $group_members->get_workroom();
		}
		
		// create new container:
		if ( !isset( $koala_container ) || !is_object( $koala_container ) ) {
			$new_container = koala_container::create_container( $values[ "name" ], $environment, $values[ "short_dsc" ] );
			if ( isset( $values["dsc"] ) && !empty( $values["dsc"] ) )
				$new_container->set_attribute( "OBJ_LONG_DESC", $values["dsc"] );
			if ( is_object( $new_container ) ) {
				$koala_container = new koala_container( $new_container );
				$_SESSION[ "confirmation" ] = str_replace( array( "%NAME", "%ENVIRONMENT" ), array( h( $new_container->get_name() ), h( $environment_koala_container->get_display_name() ) ), gettext( "New container '%NAME' has been created in '%ENVIRONMENT'." ) );
			}
			else
				$problems .= str_replace( array( "%NAME", "%ENVIRONMENT" ), array( h( $new_container->get_name(), h( $environment_koala_container->get_display_name() ) ) ), gettext( "Could not create new container '%NAME' in '%ENVIRONMENT'." ) );
		}
		// edit existing container:
		else {
			$changed = FALSE;
			if ( $container->get_name() !== $values["name"] )
				$container->set_name( $values[ "name" ] );
			if ( $container->get_attribute( OBJ_DESC ) !== $values["short_dsc"] )
				$container->set_attribute( OBJ_DESC, $values["short_dsc"] );
			if ( $container->get_attribute( "OBJ_LONG_DESC" ) !== $values["dsc"] )
				$container->set_attribute( "OBJ_LONG_DESC", $values["dsc"] );
			$portal->set_confirmation( gettext( "The changes have been saved." ) );
			$new_container = $container;
		}
		/* koaLA doesn't offer permission settings, containers always inherit the permissions from the environment
		if ( is_object( $new_container ) ) {
			$access = (int)$values[ "access" ];
			$access_descriptions = koala_container::get_access_descriptions( $group );
			if ( $access > PERMISSION_UNDEFINED ) {
				if ( ($group instanceof steam_user) && $access == PERMISSION_PRIVATE )
					$group_admins = $group;
				$koala_container->set_access( $access, $access_descriptions[$access]["members"], $access_descriptions[$access]["steam"], $group_members, $group_staff, $group_admins );
			}
			else if ( $access == PERMISSION_UNDEFINED ) {
				$koala_container->set_access_inherit( $group_members, $group_staff, $group_admins );
			}
		}
		*/

		$GLOBALS[ "STEAM" ]->buffer_flush();
		//$cache = get_cache_function( lms_steam::get_current_user()->get_name(), 600 );
		//$cache->drop( "lms_steam::get_inventory_recursive", $workroom->get_id(), CLASS_CONTAINER, array( "CONTAINER_LANGUAGE" ) );
		
		if ( !isset( $container ) || !is_object( $container ) ) {
			header( "Location: " . $backlink );
			exit;
		}
	}
	else
		$portal->set_problem_description( $problems, isset( $hints ) ? $hints : "" );
}

$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES );
$content->loadTemplateFile( "object_new.template.html" );

if (isset($container) && is_object($container)) { 
	$content->setVariable( "INFO_TEXT", str_replace( "%NAME", h($container->get_name()), gettext( "You are going to edit the container '<b>%NAME</b>'." ) ) );
	$content->setVariable( "LABEL_CREATE", gettext( "Save changes" ) );
	$pagetitle = gettext( "Preferences" );
	if (empty($values)) {
		$values = array();
		$values["name"] = $container->get_name();
		$values["short_dsc"] = $container->get_attribute(OBJ_DESC);
		$values["dsc"] = $container->get_attribute( "OBJ_LONG_DESC" );
		$values["access"] = $container->get_attribute(KOALA_ACCESS);
	}
	$breadcrumbheader = gettext("Preferences");
}
else {
	$content->setVariable( "INFO_TEXT", str_replace( "%ENVIRONMENT", h( $environment_koala_container->get_display_name() ), gettext( "You are going to create a new container in '<b>%ENVIRONMENT</b>'." ) ) );
	$content->setVariable( "LABEL_CREATE", gettext( "Create container" ) );
	$pagetitle = gettext( "Create container" );
	$breadcrumbheader = gettext("Create container");
}

if (!empty($values)) {
	if (!empty($values["name"])) $content->setVariable("VALUE_NAME", h($values["name"]));
	if (!empty($values["short_dsc"])) $content->setVariable("VALUE_SHORT_DSC", h($values["short_dsc"]));
	if (!empty($values["dsc"])) $content->setVariable("VALUE_DSC", h($values["dsc"]));
}

$content->setVariable( "VALUE_BACKLINK", $backlink );
$content->setVariable( "LABEL_NAME", gettext( "Name" ) );
$content->setVariable( "LABEL_SHORT_DSC", gettext( "Short description" ) );
$content->setVariable( "LABEL_DSC", gettext( "Long description" ) );

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

/* koaLA doesn't offer permission settings, containers always inherit the permissions from the environment
$access = koala_container::get_access_descriptions( $group );
$access_default = PERMISSION_UNDEFINED;
if (is_array($access)) {
	$content->setCurrentBlock("BLOCK_ACCESS");
	$content->setVariable( "LABEL_ACCESS", gettext( "Access") );
	
	if ( isset( $koala_container ) && $koala_container->get_access_scheme() == PERMISSION_UNDEFINED && !$koala_container->get_steam_object()->get_acquire() ) {
		$content->setCurrentBlock( "ACCESS" );
		$content->setVariable( "VALUE", -1 );
		$values[ "access" ] = -1;
		$content->setVariable( "LABEL", gettext( "This folder's permissions are invalid. If you would like to fix this, choose a permission scheme below." ) );
		$content->setVariable( "CHECK", "checked=\"checked\"" );
		$content->parse( "ACCESS" );
	}
	
	foreach($access as $key => $array) {
		$content->setCurrentBlock("ACCESS");
		$content->setVariable("LABEL", $array["label"]);
		$content->setVariable("VALUE", $key);
		if ((isset($values) && $key == (int)$values[ "access" ]) || (empty($values) && $key == $access_default)) {
			$content->setVariable("CHECK", "checked=\"checked\"");
		}
		$content->parse("ACCESS");
	}
	$content->parse("BLOCK_ACCESS");
}
*/

if ( isset( $koala_container ) )
	$headline = $koala_container->get_link_path();
else if ( isset( $environment_koala_container ) )
	$headline = $environment_koala_container->get_link_path();
else
	$headline = array();
/*
$rootlink = lms_steam::get_link_to_root( $group );
$headline = array( $rootlink, array("link" => $rootlink["link"] . "communication/", "name" => gettext("Communication")));
if (isset($container) && is_object($container)) {
  $headline[] = array( "link" => PATH_URL . "forums/" . $container->get_id() . "/", "name" => $container->get_name() );
}
*/
$headline[] = array( "link" => "", "name" =>  $breadcrumbheader );

$portal->set_page_main( $headline, $content->get() );
$portal->set_page_title( $pagetitle );
$portal->show_html();

?>
