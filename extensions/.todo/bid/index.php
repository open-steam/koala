<?php
include_once( "../../etc/koala.conf.php" );
require_once("./extension/journal/classes/journal.extension.class.php");

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );


$html="";

if ($_REQUEST["context"] == "") {
	header("location:" . PATH_SERVER . "/bid/home/");
	exit;
} else if ($_REQUEST["context"] == "home") {
	include(PATH_PUBLIC . "/bid/ui/home.php");
	$html = $content->get();
} else if ($_REQUEST["context"] == "journal") {
	$journal_extension = new journal_extension();
	try {
		$html = $journal_extension->handle_object(isset($_REQUEST["path"])?$_REQUEST["path"]:"");
	} catch (Exception $e) {
		$portal->set_problem_description("Fehler","Geht nix");
	}	
} else {
	header("location:" . PATH_SERVER . "/bid/home/");
	exit;
}






$portal->set_page_main(
	"",
	$html,
	""
);

$portal->show_html();
?>
