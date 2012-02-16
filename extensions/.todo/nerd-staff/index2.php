<?php
include_once( "../../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );








$portal->set_page_main(
	"Hallo Christian",
	"Hallo Welt",
	""
);

$portal->show_html();
?>