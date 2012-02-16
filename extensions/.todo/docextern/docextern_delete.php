<?php

$user    = lms_steam::get_current_user();

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && $docextern->check_access_write( $user ) )
{
	$values = $_POST[ "values" ];
	if ( $values[ "delete" ] )
	{
    lms_steam::delete( $docextern );
		header( "Location: " . $values[ "return_to" ] );
		exit;
	}
	
}
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "docextern_delete.template.html" );
if ( $docextern->check_access_write( $user ) )
{
	$content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "Are you sure you want to delete this URL?" ) );
	$content->setVariable( "DELETE_BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
	$content->setCurrentBlock( "BLOCK_DELETE" );
	$content->setVariable( "FORM_ACTION", $_SERVER[ "REQUEST_URI" ] );
	$content->setVariable( "LABEL_DELETE_IT", gettext( "yes, delete it" ) );
	$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
	$content->setVariable( "LABEL_RETURN", gettext( "back" ) );
	$content->parse( "BLOCK_DELETE" );
}
else
{
	$content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "You have no rights to delete this URL!" ) );
}
$content->setVariable( "TEXT_COMMENT", h($docextern->get_attribute( "OBJ_DESC" )) );
$creator = $docextern->get_creator();
$creator_data = $creator->get_attributes( array( "USER_FULLNAME", "USER_FIRSTNAME", "OBJ_ICON" ) );
$content->setVariable( "LABEL_FROM_AND_AGO", str_replace( "%N", "<a href=\"" . PATH_URL . "/user/" . $creator->get_name() . "/\">" . h($creator_data[ "USER_FIRSTNAME" ]) . " " . h($creator_data[ "USER_FULLNAME" ]) . "</a>", gettext( "by %N" ) ) . "," . how_long_ago( $docextern->get_attribute( "OBJ_CREATION_TIME" ) )  );
$content->setVariable( "ICON_SRC", PATH_URL . "get_document.php?id=" . $creator_data[ "OBJ_ICON" ]->get_id() );

$portal->set_page_main(
	"Delete an URL",
	$content->get(),
	""
);
$portal->show_html();

?>