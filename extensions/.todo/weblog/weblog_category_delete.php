<?php

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && $category->check_access_write( $user ) )
{
	$values = $_POST[ "values" ];
	if ( $values[ "delete" ] )
	{
		require_once( "Cache/Lite.php" );
		$cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
		$cache->clean( $weblog->get_id() );

		$link_objects = $category->get_inventory( CLASS_LINK );
		foreach( $link_objects as $link_object )
		{
			$date_object = $link_object->get_source_object();
			$link_object->delete();
			if ( $values[ "delete_all_dates" ] )
			{
				$date_object->delete();
			}
			else
			{
				$date_object->set_attribute( "DATE_CATEGORY", "0" );
			}
		}
		$category->delete();
	}
	header( "Location: " . PATH_URL . "weblog/" . $weblog->get_id() . "/" );
	exit;
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "weblog_category_delete.template.html" );
$content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "Are you sure you want to delete this category?" ) );

$content->setVariable( "NAME_CATEGORY", h($category->get_name()) );
$content->setVariable( "TEXT_CATEGORY", get_formatted_output( $category->get_attribute( "OBJ_DESC" ) ) );

$content->setVariable( "LABEL_DELETE_ALL_DATES", str_replace( "%NO", count( $category->get_inventory( CLASS_LINK ) ), gettext( "Should all %NO entries in this category be deleted, too?" ) ) );
$content->setVariable( "LABEL_DELETE_IT", gettext( "yes, delete it" ) );
$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
$content->setVariable( "LABEL_RETURN", gettext( "back" ) );

$rootlink = lms_steam::get_link_to_root( $weblog );
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/"),
				array( "link" => PATH_URL . "weblog/" . $category->get_id() . "/", "name" => h( $category->get_name() ) ),
				array( "link" => "", "name" => str_replace( "%NAME", h($category->get_name()), gettext( "Delete '%NAME'?" )) )
			);


$portal->set_page_main(
	$headline,
	$content->get(),
	""
);
$portal->show_html();

?>
