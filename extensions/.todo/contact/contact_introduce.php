<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "sort_functions.inc.php" );

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$values = $_POST[ "values" ];
	if ( ! empty( $values[ "contact" ] ) && ! empty( $values[ "introduction" ] ) )
	{
		if ( $values[ "introduction" ] == "introduction" )
		{
			header( "Location: " . PATH_URL . "contact_introduction.php?id=" . $values[ "contact" ] . "&type=introduction" );
			exit;
		}
		else
		{
			header( "Location: " . PATH_URL . "contact_introduction.php?id=" . $values[ "contact" ] . "&type=recommendation" );
			exit;
		}
	}
}

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$portal->set_page_title( gettext( "Make an Introduction" ) );

$user = lms_steam::get_current_user();

$id = ( ! empty( $_GET[ "id" ] ) ) ? $_GET[ "id" ] : $_POST[ "id" ];
$contact = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $id );
if ( ! $contact instanceof steam_user )
{
	include( "bad_link.php" );
	exit;
}

$contact_attributes = $contact->get_attributes( 
	array( "USER_FIRSTNAME", "USER_FULLNAME" ) 
);


$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "contact_introduce.template.html" );

$content->setVariable( "LABEL_RECOMMEND", str_replace( "%NAME", $contact_attributes[ "USER_FIRSTNAME" ] . " " . $contact_attributes[ "USER_FULLNAME" ], gettext( "Recommend %NAME to another person." ) ) );
$content->setVariable( "INFO_RECOMMENDATION", str_replace( "%NAME", $contact_attributes[ "USER_FIRSTNAME" ] . " " . $contact_attributes[ "USER_FULLNAME" ], gettext( "If you wish to recommend %NAME to another person, use this option." ) ) . " " . gettext( "The introduced person will <u>not</u> be notified." ) . " " . gettext( "The recipient of the introduction can be contacted by e-mail or messaging." ) );

$content->setVariable( "LABEL_INTRODUCE", gettext( "Introduce contacts to one another." ) );
$content->setVariable( "INFO_INTRODUCTION", str_replace( "%NAME", $contact_attributes[ "USER_FIRSTNAME" ] . " " . $contact_attributes[ "USER_FULLNAME" ], gettext( "If you wish to make an introduction between %NAME and another person please use this option." ) ) . " " . gettext( "<u>Both</u> contacts will receive your message." ) );

$content->setVariable( "CONTACT_ID", $contact->get_id());
$content->setVariable( "LABEL_OK", gettext("Introduce") );
$content->setVariable( "BACKLINK_TEXT", "<a href=\"" . PATH_URL . "user/" . $contact->get_name() . "/\">" . str_replace( "%NAME", $contact_attributes[ "USER_FIRSTNAME" ] . " " . $contact_attributes[ "USER_FULLNAME" ], gettext( "cancel and go back to %NAME's profile." ) ). "</a>" );
$content->setVariable( "BACK_LINK", PATH_URL . "user/" . $contact_attributes[ "OBJ_NAME" ]. "/" );

$portal->set_page_main(
		array( array( "link" => "", "name" => gettext( "Make a match between two contacts" ) ) ),
		$content->get(),
		"ThinCase"
		);
$portal->show_html();
?>
