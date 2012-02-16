<?php

include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

if (!isset($portal)) {
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
} else $portal->set_guest_allowed( GUEST_NOT_ALLOWED );

$user = lms_steam::get_current_user();
$admin_group = steam_factory::get_group( $GLOBALS["STEAM"]->get_id(), "admin" );
if ( !is_object($admin_group) || !$admin_group->is_member( $user ) ) {
	include( "no_access.php" );
	exit;
}

$extension_manager = lms_steam::get_extensionmanager();

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
        if ( isset($_POST[ "enable" ]) && is_array( $_POST[ "enable" ] ) )
        {
        		$extension_manager->enable_extension( key($_POST[ "enable" ])  );
        		header( "Location: " . PATH_URL . "extensions_index.php" );
                exit;
        }
        if ( isset($_POST[ "disable" ]) && is_array( $_POST[ "disable" ] ) )
        {
        		$extension_manager->disable_extension( key($_POST[ "disable" ])  );
        		header( "Location: " . PATH_URL . "extensions_index.php" );
                exit;
        }
}
else if ( empty( $_GET[ "extension" ] ) )
{
	$content = new HTML_TEMPLATE_IT();
	$content->loadTemplateFile( PATH_TEMPLATES . "extensions_index.template.html" );
	
	$content->setVariable("LABEL_HEADLINE_1", gettext("Installed koaLA Extensions"));
	
	foreach($extension_manager->get_installed_extensions() as $ex)
	{
		$content->setVariable("LABEL_NAME", gettext("Extension name"));
		$content->setVariable("LABEL_INFOS", gettext("Extension information"));
		$content->setVariable("LABEL_ACTION", gettext("Action"));
		$content->setVariable("LABEL_DESCRIPTION", gettext("Extension description"));
		$content->setVariable("LABEL_PATH", gettext("Extension path"));
		$content->setVariable("LABEL_EXTENDS", gettext("Extended object types"));
		$content->setVariable("LABEL_PROVIDES", gettext("Provided object types"));
		$content->setVariable("LABEL_REQUIRES", gettext("Required extensions"));
		
		$content->setCurrentBlock("BLOCK_EXTENSION");
		$content->setVariable("VALUE_NAME", $ex->get_name());
		$content->setVariable("VALUE_DESCRIPTION", $ex->get_description());
		$content->setVariable("VALUE_PATH", $ex->get_path());
		$content->setVariable("VALUE_PROVIDES", ($ex->get_obj_type() != "") ? $ex->get_obj_type() : "None");
		$content->setVariable("VALUE_REQUIRES", ($ex->get_requirements(TRUE) != "") ? $ex->get_requirements(TRUE) : "None (Base module)");
		if($ex->is_enabled())
			$content->setVariable("ACTION", "<input type=\"submit\" value=\"" . gettext( "Disable" ) . "\" name=\"disable[" . $ex->get_name() . "]" . "\" >");
		else
			$content->setVariable("ACTION", "<input type=\"submit\" value=\"" . gettext( "Enable" ) . "\" name=\"enable[" . $ex->get_name() . "]" . "\" >");
		$content->parse("BLOCK_EXTENSION");
	}
	$portal->set_page_main("", $content->get(), "");
	$portal->show_html();
	exit;
}

$extension_manager->handle_path( $_GET[ "path" ], FALSE, $portal );

// if no extension handles the path, then the link was invalid:
include( "bad_link.php" );

?>
