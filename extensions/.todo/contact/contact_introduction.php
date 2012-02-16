<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "sort_functions.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

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
				$values = $_POST[ "values" ];
				$problems = "";
				$hints = "";

				if ( empty( $values[ "receiver" ] ) )
				{
								$problems .= gettext( "No receiver given." );
								$hints    .= gettext( "Please choose a second person." );
				}
				if ( empty( $values[ "body" ] ) )
				{
								$problems .= gettext( "No message given." );
								$hints    .= gettext( "Please write why you think that both contacts should get in common." );
				}

				if ( empty( $problems ) )
				{

								function recommend_contact( $receiver, $contact, $message )
								{
                  $user = lms_steam::get_current_user();
                  $subject = str_replace( "%NAME", $contact->get_attribute( "USER_FIRSTNAME" ) . " " . $contact->get_attribute( "USER_FULLNAME" ), gettext( "Recommendation of %NAME as contact" ) );
                  $message .= "\n\n--\n\n"
                          . gettext( "This message was created via the introduce contact function." )
                          . " "
                          . str_replace( array( "%NAME1", "%NAME2" ), array( $user->get_attribute( "USER_FIRSTNAME" ) . " "
                                                  . $user->get_attribute( "USER_FULLNAME" ), $contact->get_attribute( "USER_FIRSTNAME" ) . " " . $contact->get_attribute( "USER_FULLNAME" ) )
                                          ,gettext( "%NAME1 wants to recommend %NAME2 to you." ) ) . "\n\n"
                          . gettext( "Name") . ": " . $contact->get_attribute( "USER_FIRSTNAME" ) . " " . $contact->get_attribute( "USER_FULLNAME" ) . "\n"
                          . gettext( "Contact page" ) . ": " . PATH_URL . "user/" . $contact->get_name(). "/\n";
                  lms_steam::mail( $receiver, $user, $subject, $message);
								}

								$receiver = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $values[ "receiver" ], CLASS_USER  );
								if ( $values[ "type" ] == "introduction"  )
								{
									recommend_contact( $receiver, $contact, $values[ "body" ] );
									recommend_contact( $contact, $receiver, $values[ "body" ] );
								}
								else
								{
									recommend_contact( $receiver, $contact, $values[ "body" ] );
								}
								header( "Location: " . PATH_URL . "user/" . $contact->get_name() . "/" );
								exit;
				}
				else
				{
								$portal->set_problem_description( $problems, $hints );
				}

}

$contact_attributes = $contact->get_attributes( 
								array( "USER_FIRSTNAME", "USER_FULLNAME", "OBJ_ICON" ) 
								);


$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "contact_introduction.template.html" );
$content->setVariable( "VALUE_TYPE", $_GET[ "type" ] );

if ( $_GET[ "type" ] == "recommendation" )
{

				$portal->set_page_title( gettext( "Recommendation" ) );
				$content->setVariable( "HELP_TEXT", gettext( "This message goes only to the second person." ) . " " . gettext( "Please introduce your contact to this person and write why you think that both should get in contact." ) );
				$content->setVariable( "HINT_TEXT", "..." );

}
else
{	
				$portal->set_page_title( gettext( "Introduction" ) );
				$content->setVariable( "HELP_TEXT", gettext( "This message goes to both contacts, the first and the second person." ) . " " . gettext( "Please introduce your contacts to each other and write why you think that both should get in contact." ) );
				$content->setVariable( "HINT_TEXT", "..." );

}

$content->setVariable( "LABEL_FIRST_PERSON", gettext( "Person to introduce") );
$icon_link = ( $contact_attributes[ "OBJ_ICON" ] == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . $contact_attributes[ "OBJ_ICON" ]->get_id();
$content->setVariable( "USER_ICON_LINK", $icon_link );
$content->setVariable( "USER_NAME", $contact_attributes[ "USER_FIRSTNAME" ] . " " . $contact_attributes[ "USER_FULLNAME"] );
$content->setVariable( "LABEL_SECOND_PERSON", gettext( "Introduce to contact" ) );

$current_user = lms_steam::get_current_user();
$cache = get_cache_function( $current_user->get_name(), 86400 );
$buddies = $cache->call( "lms_steam::user_get_buddies", $current_user->get_name(), FALSE );
if (count($buddies) > 0) $ctext = gettext( "Select"); 
else                     $ctext = gettext( "No contacts available to introduce to" );
$content->setVariable( "LABEL_PLEASE_CHOOSE", $ctext);

foreach( $buddies as $buddy )
{
				if ( $buddy[ "OBJ_ID" ] == $contact->get_id() )
				{
								continue;
				}
				$content->setCurrentBlock( "BLOCK_BUDDY" );
				$content->setVariable( "CONTACT_ID", $buddy[ "OBJ_ID" ] );
				if ( is_array( $values ) && $values[ "receiver" ] == $buddy[ "OBJ_ID" ] )
				{
								$content->setVariable( "CONTACT_SELECTED", 'selected="selected"'  );
				}
				$content->setVariable( "CONTACT_NAME", $buddy[ "USER_FULLNAME" ] . ", " . $buddy[ "USER_FIRSTNAME" ] );
				$content->parse( "BLOCK_BUDDY" );
}
$content->setVariable( "LABEL_MESSAGE", gettext( "Message") );
if ( is_array( $values ) && ! empty( $values[ "body" ] )  )
{
				$content->setVariable( "VALUE_MESSAGE", h($values[ "body" ]) );
}
$content->setVariable( "LABEL_SEND", gettext( "Send") );
$content->setVariable( "BACK_LINK", PATH_URL . "user/" . $contact->get_name() . "/" );
$content->setVariable( "LABEL_RETURN", str_replace( "%NAME", $contact->get_attribute( "USER_FIRSTNAME" ) . " " . $contact->get_attribute( "USER_FULLNAME" ), gettext( "cancel and go back to %NAME's profile" ) ) );


$headline = ( $_GET[ "type" ] == "recommendation" ) ? gettext( "Recommend %NAME" ) : gettext( "Introduce %NAME" );
$portal->set_page_main(
								array( array( "link" => "JavaScript:history.back();", "name" => gettext( "Profile" )), array( "link" => "", "name" => str_replace( "%NAME", $contact_attributes[ "USER_FIRSTNAME" ] . " " . $contact_attributes[ "USER_FULLNAME" ], $headline ) ) ),
								$content->get(),
								""
								);
$portal->show_html();
?>
