<?php
if ( ! $container->check_access_write( $user ) )
	throw new Exception( $user->get_login() . ": no right to delete " . $container->get_id(), E_USER_RIGHTS );

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST"  ) {
	$values     = $_POST[ "values" ];
	$container_name   = $container->get_name();
	$environment = $container->get_environment();
	if ( is_object( $environment ) && $environment instanceof steam_container ) {
		$koala_environment = new koala_container( $environment );
		$upper_link = $koala_environment->get_url();
	}
	else {
		$upper_link = lms_steam::get_link_to_root( $container );
		$upper_link[ "link" ];
	}
	
	if ( lms_steam::delete( $container ) ) {
		$_SESSION[ "confirmation" ] = str_replace( "%NAME", h($container_name), gettext( "The folder '%NAME' has been deleted." ) );
		header( "Location: " . $upper_link );
		exit;
	}
	else
		throw new Exception( "Cannot delete container" );
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "container_delete.template.html" );
$content->setVariable( "FORM_ACTION", "" );
$content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "Are you sure?" ) );
$content->setVariable( "INFO_DELETE_CONTAINER", str_replace( "%CONTAINER_NAME", h($container->get_name()), gettext( "You are going to delete '%CONTAINER_NAME'." ) ) );

$content->setVariable( "LABEL_DELETE_IT", gettext( "Yes, delete this container" ) );
$content->setVariable( "DELETE_BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
$content->setVariable( "LABEL_RETURN", gettext( "back" ) );

$portal->set_page_main(
	array( lms_steam::get_link_to_root( $container->get_environment() ), array( "link" => "", "name" => h($container->get_name()) ) ),
	$content->get(),
	""
);
$portal->show_html();

?>
