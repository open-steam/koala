<?php
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_ALLOWED );
$portal->set_page_title( gettext( "No access" ) );
$portal->set_problem_description(
	gettext( "You are not allowed to view this page." )
);
$content = "";
if ( $lms_user = $_SESSION[ "LMS_USER" ] ) {
	$login = $lms_user->get_login();
	$content .= " <a href='" . PATH_URL . "user/$login/'>" . gettext( "Back to your desktop" ) . "</a>";
}
else
{
	$content .= " <a href='" . PATH_URL . "'>" . gettext( "Homepage" ) . "</a>";
	
}

// TODO: also send HTML-Header for "no access" ?
if ( !isset( $headline ) )
	$headline = array( array( gettext( 'No access' ) ) );
$portal->set_page_main( $headline, $content, "ThinCase" );
$portal->show_html();
$exit;
?>
