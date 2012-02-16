<?php

include_once( "../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$portal->set_page_title( gettext( "Profile Privacy" ) );
$user = lms_steam::get_current_user();
$cache = get_cache_function( $user->get_name(), 86400 );
$user_privacy = $cache->call( "lms_steam::user_get_profile_privacy", $user->get_name(), TRUE );

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST")
{
	$binary_values = array();
	$binary_values["PRIVACY_STATUS"] =				state_to_binary( $_POST["status"] );
	$binary_values["PRIVACY_GENDER"] =				state_to_binary( $_POST["gender"] );
	$binary_values["PRIVACY_FACULTY"] =				state_to_binary( $_POST["faculty"] );
	$binary_values["PRIVACY_MAIN_FOCUS"] =			state_to_binary( $_POST["main_focus"] );
	$binary_values["PRIVACY_WANTS"] =				state_to_binary( $_POST["wants"] );
	$binary_values["PRIVACY_HAVES"] =				state_to_binary( $_POST["haves"] );
	$binary_values["PRIVACY_ORGANIZATIONS"] =		state_to_binary( $_POST["organizations"] );
	$binary_values["PRIVACY_HOMETOWN"] =			state_to_binary( $_POST["hometown"] );
	$binary_values["PRIVACY_OTHER_INTERESTS"] =		state_to_binary( $_POST["other_interests"] );
	$binary_values["PRIVACY_LANGUAGES"] =			state_to_binary( $_POST["languages"] );
	$binary_values["PRIVACY_CONTACTS"] =			state_to_binary( $_POST["contacts"] );
	$binary_values["PRIVACY_GROUPS"] =				state_to_binary( $_POST["groups"] );
	$binary_values["PRIVACY_EMAIL"] =				state_to_binary( $_POST["email"] );
	$binary_values["PRIVACY_ADDRESS"] =				state_to_binary( $_POST["address"] );
	$binary_values["PRIVACY_TELEPHONE"] =			state_to_binary( $_POST["telephone"] );
	$binary_values["PRIVACY_PHONE_MOBILE"] =		state_to_binary( $_POST["phone_mobile"] );
	$binary_values["PRIVACY_WEBSITE"] =				state_to_binary( $_POST["website"] );
	$binary_values["PRIVACY_ICQ_NUMBER"] =			state_to_binary( $_POST["icq_number"] );
	$binary_values["PRIVACY_MSN_IDENTIFICATION"] =	state_to_binary( $_POST["msn_identification"] );
	$binary_values["PRIVACY_AIM_ALIAS"] =			state_to_binary( $_POST["aim_alias"] );
	$binary_values["PRIVACY_YAHOO_ID"] =			state_to_binary( $_POST["yahoo_id"] );
	$binary_values["PRIVACY_SKYPE_NAME"] =			state_to_binary( $_POST["skype_name"] );

	$privacy_object = $user->get_attribute( "KOALA_PRIVACY" );

	if ( !( $privacy_object instanceof steam_object ) )
	{
		$privacy_object = steam_factory::create_object( $GLOBALS[ "STEAM" ]->get_id(), "privacy profile", CLASS_OBJECT );

		if ( !( $privacy_object instanceof steam_object ) )
			throw new exception("Error creating Privacy-Proxy-Object", E_USER_NO_PRIVACYPROFILE);

		$user->set_attribute( "KOALA_PRIVACY", $privacy_object );
		$privacy_object->set_acquire( $user );
	}

	$privacy_object->set_attributes( $binary_values );

	/*
	require_once( "Cache/Lite.php" );
	$cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
	$cache->clean( $user->get_name() );
	$cache->clean( $user->get_id() );
	*/

  	$cache = get_cache_function( lms_steam::get_current_user()->get_name() );
  	$cache->drop( "lms_portal::get_menu_html", lms_steam::get_current_user()->get_name(), TRUE );

	$cache = get_cache_function( $user->get_name() );
	$cache->drop( "lms_steam::user_get_profile_privacy", $user->get_name(), TRUE );

	$_SESSION[ "confirmation" ] = gettext( "Your profile data has been saved." );
	header( "Location: " . PATH_URL . "profile_privacy.php" );
}


function state_to_binary( $states )
{
	$deny_all = PROFILE_DENY_ALLUSERS + PROFILE_DENY_CONTACTS; // + PROFILE_DENY_COURSEMATES + PROFILE_DENY_GROUPMATES;

	if ( $states == null ) return $deny_all;
	if ( in_array( "allusers", $states ) ) return 0;

	$binary = PROFILE_DENY_ALLUSERS;
	$binary += ( in_array("contacts", $states) ) ? 0 : PROFILE_DENY_CONTACTS;
	// $binary += ( in_array("coursemates", $states) ) ? 0 : PROFILE_DENY_COURSEMATES;
	// $binary += ( in_array("groupmates", $states) ) ? 0 : PROFILE_DENY_GROUPMATES;

	return $binary;
}


function set_checkbox( $name, $integer_value, &$content )
{
	$allusers_checked = ( $integer_value & PROFILE_DENY_ALLUSERS ) ? "" : "checked=\"checked\"";
	$content->setVariable( $name . "_ALLUSERS_CHECKED", $allusers_checked );

	$disabled = ( $allusers_checked == "" ) ? "" : "disabled=\"disabled\"";

	$checked = ( $integer_value & PROFILE_DENY_CONTACTS ) ? "" : "checked=\"checked\"";
	$content->setVariable( $name . "_CONTACTS_CHECKED", $checked );
	$content->setVariable( $name . "_CONTACTS_DISABLED", $disabled );

	/*
	$checked = ( $integer_value & PROFILE_DENY_COURSEMATES ) ? "" : "checked=\"checked\"";
	$content->setVariable( $name . "_COURSEMATES_CHECKED", $checked );
	$content->setVariable( $name . "_COURSEMATES_DISABLED", $disabled );

	$checked = ( $integer_value & PROFILE_DENY_GROUPMATES ) ? "" : "checked=\"checked\"";
	$content->setVariable( $name . "_GROUPMATES_CHECKED", $checked );
	$content->setVariable( $name . "_GROUPMATES_DISABLED", $disabled );
	*/
}


$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "profile_privacy.template.html" );
$content->setVariable( "HEADER_CONTACTS_AND_GROUPS", gettext( "Contacts and Groups" ) );
$content->setVariable( "HEADER_CONTACT_DATA", gettext( "Contact Data" ) );
$content->setVariable( "INFO_TEXT", gettext( "Here you can set which persons can see what information on your profile page." ) );

$content->setVariable( "LABEL_ALLUSERS", gettext( "All Users" ) );
$content->setVariable( "LABEL_CONTACTS", gettext( "Contacts" ) );
$content->setVariable( "LABEL_COURSEMATES", gettext( "Course Mates" ) );
$content->setVariable( "LABEL_GROUPMATES", gettext( "Group Mates" ) );

$content->setVariable( "LABEL_STATUS", gettext( "Status" ) );
$content->setVariable( "LABEL_GENDER", gettext( "Gender" ) );
$content->setVariable( "LABEL_FACULTY", gettext( "Origin" ) );
$content->setVariable( "LABEL_MAIN_FOCUS", gettext( "Main focus" ) );
$content->setVariable( "LABEL_WANTS", gettext( "Wants" ) );
$content->setVariable( "LABEL_HAVES", gettext( "Haves" ) );
$content->setVariable( "LABEL_ORGANIZATIONS", gettext( "Organizations" ) );
$content->setVariable( "LABEL_HOMETOWN", gettext( "Hometown" ) );
$content->setVariable( "LABEL_OTHER_INTERESTS", gettext( "Other interests" ) );
$content->setVariable( "LABEL_LANGUAGES", gettext( "Language" ) );
//$content->setVariable( "LABEL_CONTACTS", gettext( "Contacts" ) ); -> siehe oben
$content->setVariable( "LABEL_GROUPS", gettext( "Groups" ) );
$content->setVariable( "LABEL_EMAIL", gettext( "E-mail" ) );
$content->setVariable( "LABEL_ADDRESS", gettext( "Address" ) );
$content->setVariable( "LABEL_TELEPHONE", gettext( "Phone" ) );
$content->setVariable( "LABEL_PHONE_MOBILE", gettext( "Phone, mobile" ) );
$content->setVariable( "LABEL_WEBSITE", gettext( "Website" ) );
$content->setVariable( "LABEL_ICQ_NUMBER", gettext( "ICQ number" ) );
$content->setVariable( "LABEL_MSN_IDENTIFICATION", gettext( "MSN identification" ) );
$content->setVariable( "LABEL_AIM_ALIAS", gettext( "AIM-alias" ) );
$content->setVariable( "LABEL_YAHOO_ID", gettext( "Yahoo-ID" ) );
$content->setVariable( "LABEL_SKYPE_NAME", gettext( "Skype name" ) );

$content->setVariable( "LABEL_SAVE_IT", gettext( "Save changes" )  );
$content->setVariable( "BACK_LINK", "<a href=\"" . PATH_URL . "user/" . $user->get_name() . "/\">" . gettext( "back to your user profile" ) . "</a>" );

set_checkbox("STATUS", $user_privacy[ "PRIVACY_STATUS" ], $content);
set_checkbox("GENDER", $user_privacy[ "PRIVACY_GENDER" ], $content);
set_checkbox("FACULTY", $user_privacy[ "PRIVACY_FACULTY" ], $content);
set_checkbox("MAIN_FOCUS", $user_privacy[ "PRIVACY_MAIN_FOCUS" ], $content);
set_checkbox("WANTS", $user_privacy[ "PRIVACY_WANTS" ], $content);
set_checkbox("HAVES", $user_privacy[ "PRIVACY_HAVES" ], $content);
set_checkbox("ORGANIZATIONS", $user_privacy[ "PRIVACY_ORGANIZATIONS" ], $content);
set_checkbox("HOMETOWN", $user_privacy[ "PRIVACY_HOMETOWN" ], $content);
set_checkbox("OTHER_INTERESTS", $user_privacy[ "PRIVACY_OTHER_INTERESTS" ], $content);
set_checkbox("LANGUAGES", $user_privacy[ "PRIVACY_LANGUAGES" ], $content);
set_checkbox("CONTACTS", $user_privacy[ "PRIVACY_CONTACTS" ], $content);
set_checkbox("GROUPS", $user_privacy[ "PRIVACY_GROUPS" ], $content);
set_checkbox("EMAIL", $user_privacy[ "PRIVACY_EMAIL" ], $content);
set_checkbox("ADDRESS", $user_privacy[ "PRIVACY_ADDRESS" ], $content);
set_checkbox("TELEPHONE", $user_privacy[ "PRIVACY_TELEPHONE" ], $content);
set_checkbox("PHONE_MOBILE", $user_privacy[ "PRIVACY_PHONE_MOBILE" ], $content);
set_checkbox("WEBSITE", $user_privacy[ "PRIVACY_WEBSITE" ], $content);
set_checkbox("ICQ_NUMBER", $user_privacy[ "PRIVACY_ICQ_NUMBER" ], $content);
set_checkbox("MSN_IDENTIFICATION", $user_privacy[ "PRIVACY_MSN_IDENTIFICATION" ], $content);
set_checkbox("AIM_ALIAS", $user_privacy[ "PRIVACY_AIM_ALIAS" ], $content);
set_checkbox("YAHOO_ID", $user_privacy[ "PRIVACY_YAHOO_ID" ], $content);
set_checkbox("SKYPE_NAME", $user_privacy[ "PRIVACY_SKYPE_NAME" ], $content);

$portal->set_page_main(
	array(
		array( "link" => PATH_URL . "user/" . $user->get_name() . "/",
			"name" => $user->get_attribute( "USER_FIRSTNAME" ) . " " . $user->get_attribute( "USER_FULLNAME" )
		),
		array( "link" => PATH_URL . "user/" . $user->get_name() . "/",
			"name" => gettext( "Profile" )
		),
		array( "link" => "",
			"name" => gettext( "Privacy" )
		)
	),
	$content->get(),
	""
);

$portal->show_html();
?>