<?php

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && $date->check_access_write( $user ) )
{
	$values = $_POST[ "values" ];
	if ( $values[ "delete" ] )
	{
		require_once( "Cache/Lite.php" );
		$cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
		$cache->clean( $weblog->get_id() );
		$cache->clean( $date->get_id() );
		
    $trashbin = $GLOBALS["STEAM"]->get_current_steam_user();
    if (is_object($trashbin)) {
      $date->move($trashbin);
    }
    else {
      $date->delete();
    }

	}
	header( "Location: " . $values[ "return_to" ] );
	exit;
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "weblog_entry_delete.template.html" );
$content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "Are you sure you want to delete this entry?" ) );

$content->setVariable( "TEXT_COMMENT", get_formatted_output( $date->get_attribute( "DATE_DESCRIPTION" ) ) );
$creator = $date->get_creator();
$creator_data = $creator->get_attributes( array( "USER_FULLNAME", "USER_FIRSTNAME", "OBJ_ICON" ) );
$content->setVariable( "LABEL_FROM_AND_AGO", str_replace( "%N", "<a href=\"" . PATH_URL . "/user/" . $creator->get_name() . "/\">" . h($creator_data[ "USER_FIRSTNAME" ]) . " " . h($creator_data[ "USER_FULLNAME" ]) . "</a>", gettext( "by %N" ) ) . "," . how_long_ago( $date->get_attribute( "OBJ_CREATION_TIME" ) )  );

$content->setVariable( "LABEL_DELETE_IT", gettext( "yes, delete it" ) );
$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
$content->setVariable( "LABEL_RETURN", gettext( "back" ) );

$content->setVariable( "ICON_SRC", PATH_URL . "get_document.php?id=" . $creator_data[ "OBJ_ICON" ]->get_id() );

$rootlink = lms_steam::get_link_to_root( $weblog );
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "link" => "", "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/"),
				array( "name" => 	str_replace( "%NAME", h($date->get_attribute( "DATE_TITLE" )), gettext( "Delete '%NAME'?" )))
			);


$portal->set_page_main(
	$headline,
	$content->get(),
	""
);
$portal->show_html();

?>
