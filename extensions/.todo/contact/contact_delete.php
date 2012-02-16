<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "sort_functions.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$portal->set_page_title( gettext( "Delete contact" ) );

$user = lms_steam::get_current_user();

$id = ( ! empty( $_GET[ "id" ] ) ) ? $_GET[ "id" ] : $_POST[ "id" ];
$contact = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $id );
if ( ! $contact instanceof steam_user )
{
	include( "bad_link.php" );
	exit;
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$buddies = $user->get_attribute( "USER_FAVOURITES" );
	$buddies_new = array();
	foreach ( $buddies as $buddy ) {
		if ( !is_object($buddy) || $buddy->get_id() == $id ) continue;
		$buddies_new[] = $buddy;
	}
	$user->set_attribute( "USER_FAVOURITES", $buddies_new );

	$confirmed_contacts = $user->get_attribute( "USER_CONTACTS_CONFIRMED" );
	$confirmed_contacts_new = array();
	foreach ( $confirmed_contacts as $key => $value ) {
		if ( $key == $id ) continue;
		$confirmed_contacts_new[$key] = $value;
	}
	if ( count($confirmed_contacts) != count($confirmed_contacts_new) ) {
    $confirmed_contacts_new["_OBJECT_KEYS"] = "TRUE";
		$user->set_attribute( 'USER_CONTACTS_CONFIRMED', $confirmed_contacts_new );
  }
	if ( count($buddies) != count($buddies_new) || count($confirmed_contacts) != count($confirmed_contacts_new) ) {
		require_once( 'Cache/Lite.php' );
		$cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
		$cache = get_cache_function( $user->get_name() );
		$cache->clean( $user->get_name() );
		$cache->clean( $user->get_id());
		$cache = get_cache_function( $contact->get_name() );
		$cache->clean( $contact->get_name() );
		$cache->clean( $contact->get_id());
	}
	header( "Location: " . $_POST[ "backlink" ]);
	exit;
}


$contact_attributes = $contact->get_attributes( 
	array( "USER_FIRSTNAME", "USER_FULLNAME" ) 
);


$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "contact_delete.template.html" );
$content->setVariable( "CONTACT_ID", $contact->get_id());
$content->setVariable( "LABEL_CLICK_ON_OK", str_replace( "%NAME", $contact_attributes[ "USER_FIRSTNAME" ] . " " . $contact_attributes[ "USER_FULLNAME" ], gettext( "Remove %NAME from your contact list?." ) ) );
$content->setVariable( "INFO_TEXT", str_replace( "%NAME", $contact_attributes[ "USER_FIRSTNAME" ] . " " . $contact_attributes[ "USER_FULLNAME" ], gettext( "This will remove %NAME from your contact list." ) ) );
$content->setVariable( "LABEL_OK", gettext( "Delete contact" ) );
$content->setVariable( "BACKLINK_TEXT", "<a href=\"" . $_SERVER[ "HTTP_REFERER" ] . "\">" . str_replace( "%NAME", $contact_attributes[ "USER_FIRSTNAME" ] . " " . $contact_attributes[ "USER_FULLNAME" ], gettext( "cancel and go back to %NAME's profile." ) ). "</a>" );
$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );

$portal->set_page_main(
		array( array( "link" => "", "name" => str_replace( "%NAME", $contact_attributes[ "USER_FIRSTNAME" ] . " " . $contact_attributes[ "USER_FULLNAME" ], gettext( "Remove %NAME?" ) ) ) ),
		$content->get(),
		"ThinCase"
		);
$portal->show_html();
?>
