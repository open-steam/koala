<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
$admin_group = steam_factory::get_group( $GLOBALS["STEAM"]->get_id(), "admin" );
if ( !is_object($admin_group) || !$admin_group->is_member( $user ) ) {
	header("location:/");
	exit;
}

$portal_user = $portal->get_user();
$path = url_parse_rewrite_path( (isset($_GET["path"])?$_GET[ "path" ]:"") );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "admin_server.template.html" );


function show_value( $value )
{
	if( $value == "TRUE" ) return "Enabled";
	else return "Disabled";
}


// version information:
if ( $webinterface = $GLOBALS[ "STEAM" ]->get_module( "package:web" ) )
$wi_version = $GLOBALS["STEAM"]->predefined_command($webinterface, "get_version", array(), 0);
else $wi_version = "not installed";
$steam_version = $GLOBALS["STEAM"]->get_server_version();
$pike_version = $GLOBALS["STEAM"]->get_pike_version();
$koala_support = $GLOBALS["STEAM"]->get_module( "package:koala_support" );
if ( is_object($koala_support) ) $koala_support_version = $GLOBALS["STEAM"]->predefined_command( $koala_support, "get_version", array(), 0 );
else $koala_support_version = gettext( "not installed" );

$content->setVariable( "LABEL_VERSION_INFORMATION", gettext( "Version information" ) );

$loglevel = array(
  0 => "None",
  1 => "Error",
  2 => "Warning",
  3 => "Debug"
);

$connector = $GLOBALS["STEAM"];
$admin_pike = steam_factory::path_to_object($connector->get_id(), "/scripts/admin.pike");
$tnr = array();

$logmodule = $connector->get_module("log");
$xmlconvertermodule = $connector->get_module("Converter:XML");

$tnr_smtp = $connector->predefined_command(
																$logmodule,
																"get_log_level",
																array("smtp"),
																TRUE
																);
$tnr_http = $connector->predefined_command(
																$logmodule,
																"get_log_level",
																array("http"),
																TRUE
																);
$tnr_events = $connector->predefined_command(
																$logmodule,
																"get_log_level",
																array("events"),
																TRUE
																);
$tnr_security = $connector->predefined_command(
																$logmodule,
																"get_log_level",
																array("security"),
																TRUE
																);
$tnr_servertime = $connector->predefined_command(
																$xmlconvertermodule,
																"gettime",
																array( ),
																TRUE
																);
$result = $connector->buffer_flush();
$uptime = $result[$tnr_servertime] - $connector->get_last_reboot();
$content->setCurrentBlock("BLOCK_BLOCK");
$content->setVariable( "LABEL_BLOCK", "sTeam Server Backend");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "Server Name" );
$content->setVariable( "VALUE", STEAM_SERVER );
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "COAL Port" );
$content->setVariable( "VALUE", STEAM_PORT );
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "Server Version" );
$content->setVariable( "VALUE", $steam_version );
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "Webinterface Version" );
$content->setVariable( "VALUE", $wi_version );
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "Pike Version" );
$content->setVariable( "VALUE", $pike_version );
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "koala_support Paket Version" );
$content->setVariable( "VALUE", $koala_support_version );
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "Servertime" );
$content->setVariable( "VALUE",  date("d.m.y, H:i:s", $result[$tnr_servertime] ) );
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "Last Reboot" );
$content->setVariable( "VALUE", date("d.m.y, H:i:s", $connector->get_last_reboot()));
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "Uptime" );
$content->setVariable( "VALUE", number_format($uptime / ( 60 * 60 ), 0) . " Hours (" . number_format($uptime / ( 60 * 60 * 24 ), 0) . " Days)" );
$content->parse("ENTRY");

$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "http Loglevel" );
$content->setVariable( "VALUE", $loglevel[$result[$tnr_http]] );
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "smtp Loglevel" );
$content->setVariable( "VALUE", $loglevel[$result[$tnr_smtp]] );
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "events Loglevel" );
$content->setVariable( "VALUE", $loglevel[$result[$tnr_events]] );
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "security loglevel" );
$content->setVariable( "VALUE", $loglevel[$result[$tnr_security]] );
$content->parse("ENTRY");






$content->parse("BLOCK_BLOCK");

$content->setCurrentBlock("BLOCK_BLOCK");
$content->setVariable( "LABEL_BLOCK", PLATFORM_NAME. " PHP Frontend" );
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "Version" );
$content->setVariable( "VALUE", KOALA_VERSION );
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "Database Download" );
$content->setVariable( "VALUE", show_value(USE_DATABASE_DOWNLOAD) );
$content->parse("ENTRY");
$content->parse("BLOCK_BLOCK");

// PHP information:
$content->setVariable( "LABEL_PHP_INFORMATION", gettext( "PHP information" ) );
//TODO

$portal->set_page_main( "", $content->get(), "" );
$portal->show_html();

// manage public group categories:
// public group categories are containers in /home/PublicGroups (with a description)

?>
