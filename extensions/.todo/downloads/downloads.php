<?php

require_once( "../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_ALLOWED );
$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES );
$content->loadTemplateFile( "downloads.de_DE.html" );
$content->setVariable( "PATH_IMAGES", PATH_STYLE . "images/" );

$portal->set_page_title( gettext( "Downloads" ) );
$portal->set_page_main( gettext( "Downloads" ), $content->get() );
$portal->show_html();

?>
