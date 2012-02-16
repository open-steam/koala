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
$content->loadTemplateFile( PATH_TEMPLATES . "admin_paul.template.html" );

$paulsync_folder = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), "/home/root/documents/paulsync");
if (!is_object($paulsync_folder)) {
  throw new Exception("Paul sync log folder /home/root/documents/paulsync not found", E_CONFIGURATION);
  exit;
}
steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), array($paulsync_folder), array("PAUL_SYNC_STARTTIME", "PAUL_SYNC_ENDTIME", "PAUL_SYNC_RUNNING"));

$content->setCurrentBlock("BLOCK_BLOCK");
$content->setVariable( "LABEL_BLOCK", "PAUL synchronisation");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "Sync Status" );
$content->setVariable( "VALUE", ($paulsync_folder->get_attribute("PAUL_SYNC_RUNNING")==="TRUE"?gettext("Running"):gettext("Not running")) );
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "Last sync start time" );
$content->setVariable( "VALUE", date("d.m.y, H:i:s",   $paulsync_folder->get_attribute("PAUL_SYNC_STARTTIME")) );
$content->parse("ENTRY");
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "Last sync end time" );
$content->setVariable( "VALUE", date("d.m.y, H:i:s",   $paulsync_folder->get_attribute("PAUL_SYNC_ENDTIME")) );
$content->parse("ENTRY");
/*
$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "View logs" );
$content->setVariable( "VALUE", "<a href='" . PATH_URL . "/user/root/documents/" . $paulsync_folder->get_id() . "'>" . gettext("View logfiles") . "</a>" );
$content->parse("ENTRY");
*/

$logdata = "";
if ( file_exists( LOG_PAULSYNC_LAST ) ) {
  $logdata = "";
  if ( file_exists( LOG_PAULSYNC_LAST) ) {
    $fp = fopen( LOG_PAULSYNC_LAST, "r");
    $logdata = fread($fp, filesize (LOG_PAULSYNC_LAST));
    fclose($fp);
  }
}

$content->setCurrentBlock("ENTRY");
$content->setVariable( "LABEL", "Last Log" );
$content->setVariable( "ADDINFO", "valign='top'" );
$content->setVariable( "VALUE", ($logdata==""?gettext("Not available"):str_replace("\n", "<br />", $logdata)) );
$content->parse("ENTRY");
$content->parse("BLOCK_BLOCK");


$portal->set_page_main( "", $content->get(), "" );
$portal->show_html();
?>
