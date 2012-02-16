<?php
include_once( "../etc/koala.conf.php" );

if(!isset($portal) || !is_object($portal))
{
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}

$user = lms_steam::get_current_user();

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$problems = "";

	try
	{
		$new_content = $version_doc->get_content();
		$wiki_doc->set_content($new_content);
	}
	catch( Exception $ex )
	{
		$problems = $ex->get_message();
	}
	
	if( empty($problems) )
	{
		$_SESSION[ "confirmation" ] = str_replace( "%VERSION", $version_doc->get_version(), gettext( "Version %VERSION recovered." ) );
			header( "Location: " . PATH_URL . "wiki/" . $wiki_doc->get_id() . "/" );
	   		exit;
		
	}
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}
$backlink = PATH_URL . "wiki/" . $wiki_doc->get_id() . "/";

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "wiki_recover_version.template.html" );
$content->setVariable( "BACK_LINK", $backlink );
$content->setVariable( "INFO_TEXT", gettext( "A new version will be created from the one you are recovering. The actual version will not be lost. Is that what you want?" ) );
$content->setVariable( "LABEL_OK", gettext( "Yes, Recover version" ) );
$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );

$rootlink = lms_steam::get_link_to_root( $wiki_container );
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/"),
				array( "link" => PATH_URL . "wiki/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
				array( "link" => "", "name" => str_replace("%VERSION", $version_doc->get_version(), gettext( "Recover version %VERSION" ) ) )
				);

$portal->set_page_main( 
	$headline,
	$content->get()
);
$portal->show_html();
?>