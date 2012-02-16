<?php

include_once( "../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$portal->set_page_title( gettext( "Edit Profile" ) );
$user = lms_steam::get_current_user();
$cache = get_cache_function( $user->get_name(), 86400 );
$user_profile = $cache->call( "lms_steam::user_get_profile", $user->get_name() );

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$values = $_POST[ "values" ];
	
	if ( !empty( $values[ "USER_PROFILE_WEBSITE_URI" ] ) && substr( $values[ "USER_PROFILE_WEBSITE_URI" ], 0, 7 ) != "http://" )
	{
		$values[ "USER_PROFILE_WEBSITE_URI" ] = "http://" . $values[ "USER_PROFILE_WEBSITE_URI" ];
	}
	
	$user->set_attributes( $values );
	$old_fac_id = $user_profile[ "USER_PROFILE_FACULTY" ];
	$new_fac_id = $values[ "USER_PROFILE_FACULTY" ];

	if ( $new_fac_id != $old_fac_id )
	{
		if ( $old_fac_id > 0 )
		{
			$old_faculty = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $old_fac_id, CLASS_GROUP );
			$old_faculty->remove_member( $user );
		}
		if ( $new_fac_id > 0 )
		{
			$new_faculty = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $new_fac_id, CLASS_GROUP );
			$new_faculty->add_member( $user );
		}
	}
/*
	require_once( "Cache/Lite.php" );
	$cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
	$cache->clean( $user->get_name() );
	$cache->clean( $user->get_id() );*/

  $lang_index = language_support::get_language_index();
  language_support::choose_language( $lang_index[ $values["USER_LANGUAGE"] ] );

  $cache = get_cache_function( lms_steam::get_current_user()->get_name() );
  $cache->drop( "lms_portal::get_menu_html", lms_steam::get_current_user()->get_name(), TRUE );

	$cache = get_cache_function( $user->get_name() );
	$cache->drop( "lms_steam::user_get_profile", $user->get_name() );

  $_SESSION[ "confirmation" ] = gettext( "Your profile data has been saved." );

	header( "Location: " . PATH_URL . "profile_edit.php" );
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "profile_edit.template.html" );
$content->setVariable( "LABEL_INFO", gettext( "Please complete your profile. None of the fields are mandatory. Some of the fields can not be changed due to central identity management at the IMT.<br/><b>Note: With the button <i>Profile Privacy</i> you can control which information can be seen by other users.</b>" ) );
$content->setVariable( "LABEL_PROFILE", gettext( "General Information" ) );
$content->setVariable( "LABEL_LOOKING", gettext( "Your buddy icon" ) );
$content->setVariable( "LABEL_MAIL_PREFS", gettext( "Your mail preferences" ) );
$content->setVariable( "LABEL_PROFILE_PRIVACY", gettext( "Profile Privacy" ) );
$content->setVariable( "LINK_BUDDY_ICON", PATH_URL . "profile_icon.php" );
$content->setVariable( "LINK_MAIL_PREFS", PATH_URL . "messages_prefs.php" );
$content->setVariable( "LINK_PROFILE_PRIVACY", PATH_URL . "profile_privacy.php" );
$content->setVariable( "LABEL_FIRST_NAME", gettext( "First name" ) );
$content->setVariable( "LABEL_LAST_NAME", gettext( "Last name" ) );
$content->setVariable( "LABEL_TITLE", gettext( "Academic title" ) );
$content->setVariable( "LABEL_DEGREE", gettext( "Academic degree" ) );
$content->setVariable( "LABEL_IF_AVAILABLE", gettext( "only if available" ) );
$content->setVariable( "LABEL_STATUS", gettext( "Status" ) );
$content->setVariable( "LABEL_GENDER", gettext( "Gender" ) );
$content->setVariable( "LABEL_FEMALE", gettext( "female" ) );
$content->setVariable( "LABEL_MALE", gettext( "male" ) );
$content->setVariable( "LABEL_NOT_SAY", gettext( "rather not say" ) );
$content->setVariable( "LABEL_FACULTY", gettext( "Origin" ) );
$content->setvariable( "LABEL_MAIN_FOCUS", gettext( "Main focus" ) );
$content->setVariable( "LABEL_HOMETOWN", gettext( "Hometown" ) );
$content->setVariable( "LABEL_WANTS", gettext( "Wants" ) );
$content->setVariable( "LABEL_HAVES", gettext( "Haves" ) );
$content->setVariable( "LABEL_OTHER_INTERESTS", gettext( "Other interests" ) );
$content->setVariable( "LABEL_ORGANIZATIONS", gettext( "Organizations" ) );
$content->setVariable( "LABEL_DESCRIBE_YOURSELF", gettext( "Describe yourself" ) );
$content->setVariable( "LABEL_CONTACT_DATA", gettext( "Contact Data") );
$content->setVariable( "LABEL_EMAIL", gettext( "E-mail" ) );
$content->setVariable( "LABEL_EMAIL_PREFERENCES", gettext( "Looking for your e-mail preferences?" ) );
$content->setVariable( "LINK_EMAIL_PREFERENCES", PATH_URL . "messages_prefs.php" );
$content->setVariable( "LABEL_TELEPHONE", gettext( "Phone" ) );
$content->setVariable( "LABEL_MOBILE", gettext( "Phone, mobile" ) );
$content->setVariable( "LABEL_ADDRESS", gettext( "Address" ) );
$content->setVariable( "LABEL_PHONE_MOBILE", gettext( "Phone, mobile" ) );

$content->setVariable( "LABEL_WEBSITE", gettext( "Website" ) );
$content->setVariable( "LABEL_ICQ_NUMBER", gettext( "ICQ number" ) );
$content->setVariable( "LABEL_MSN_IDENTIFICATION", gettext( "MSN identification" ) );
$content->setVariable( "LABEL_AIM_ALIAS", gettext( "AIM-alias" ) );
$content->setVariable( "LABEL_YAHOO_ID", gettext( "Yahoo-ID" ) );
$content->setVariable( "LABEL_SKYPE_NAME", gettext( "Skype name" ) );

$content->setVariable( "INFO_INCLUDE_HTTP", gettext( "Please include the 'http://'" ) );
$content->setVariable( "LABEL_WEBSITE_NAME", gettext( "Website name" ) );

$content->setVariable( "LABEL_SAVE_IT", gettext( "Save changes" )  );
$content->setVariable( "BACK_LINK", PATH_URL . "user/" . $user->get_name() . "/" );
$content->setVariable( "LABEL_GOTO_HOMEPAGE", "<a href=\"" . PATH_URL . "user/" . $user->get_name() . "/\">" . gettext( "back to your profile" ) . "</a>" );

$content->setVariable( "LABEL_BB_BOLD", gettext( "B" ) );
$content->setVariable( "HINT_BB_BOLD", gettext( "boldface" ) );
$content->setVariable( "LABEL_BB_ITALIC", gettext( "I" ) );
$content->setVariable( "HINT_BB_ITALIC", gettext( "italic" ) );
$content->setVariable( "LABEL_BB_UNDERLINE", gettext( "U" ) );
$content->setVariable( "HINT_BB_UNDERLINE", gettext( "underline" ) );
$content->setVariable( "LABEL_BB_STRIKETHROUGH", gettext( "S" ) );
$content->setVariable( "HINT_BB_STRIKETHROUGH", gettext( "strikethrough" ) );
$content->setVariable( "LABEL_BB_IMAGE", gettext( "IMG" ) );
$content->setVariable( "HINT_BB_IMAGE", gettext( "image" ) );
$content->setVariable( "LABEL_BB_URL", gettext( "URL" ) );
$content->setVariable( "HINT_BB_URL", gettext( "web link" ) );
$content->setVariable( "LABEL_BB_MAIL", gettext( "MAIL" ) );
$content->setVariable( "HINT_BB_MAIL", gettext( "email link" ) );


function safe_string ( $text, $default_result = "" ) {
	return is_string($text) ? $text : $default_result;
}

// PROFILE VALUES
$content->setVariable( "VALUE_USER_FIRSTNAME", safe_string( $user_profile[ "USER_FIRSTNAME" ] ) );
$content->setVariable( "VALUE_USER_FULLNAME", safe_string( $user_profile[ "USER_FULLNAME" ] ) );
$content->setVariable( "VALUE_ACADEMIC_DEGREE", safe_string( $user_profile[ "USER_ACADEMIC_DEGREE" ] ) );

/*
 *  Assure translations for statuses are available via gettext
 */


gettext("student");gettext("staff member");gettext("guest");gettext("alumni");

$stati = array( "student", "staff member", "guest", "alumni" );
foreach( $stati as $status )
{
	$content->setCurrentBlock( "BLOCK_STATUS" );
	$content->setVariable( "VALUE_STATUS", $status );
	if ( $status === $user_profile[ "OBJ_DESC" ] )
	{
		$content->setVariable( "STATUS_SELECTED", 'selected="selected"' );
	}
	$content->setVariable( "VALUE_STATUS_TRANSLATED", secure_gettext( $status ) );
	$content->parse( "BLOCK_STATUS" );
}


$academicTitle = (String) $user_profile[ "USER_ACADEMIC_TITLE" ];
switch( $academicTitle )
{
	case "Dr.":
		$content->setVariable( "TITLE_DR_SELECTED", 'selected="selected"'  );
	break;
	case ( "PD Dr." ):
		$content->setVariable( "TITLE_PRIVDOZDR_SELECTED", 'selected="selected"'  );
	break;
	case ( "Prof." ):
		$content->setVariable( "TITLE_PROF_SELECTED", 'selected="selected"'  );
	break;
	case ( "Prof. Dr." ):
		$content->setVariable( "TITLE_PROFDR_SELECTED", 'selected="selected"'  );
	break;
	default:
		$content->setVariable( "TITLE_NULL_SELECTED", 'selected="selected"' );
	break;
}

$content->setVariable( "GENDER_" . safe_string( $user_profile[ "USER_PROFILE_GENDER" ], "X" ). "_CHECKED", 'checked="checked"' );

$cache     = get_cache_function( "ORGANIZATION", 86400 );
$faculties = $cache->call( "lms_steam::get_faculties_asc" );
$content->setVariable( "LABEL_MISCELLANEOUS", gettext( "miscellaneous" ) );
foreach( $faculties as $faculty )
{
	$content->setCurrentBlock( "BLOCK_FACULTY" );
	$content->setVariable( "FACULTY_ID", $faculty[ "OBJ_ID" ] );
	if ( $user_profile[ "USER_PROFILE_FACULTY" ] == $faculty[ "OBJ_ID" ] )
	{
		$content->setVariable( "FACULTY_SELECTED", 'selected="selected"' );
	}
	$content->setVariable( "FACULTY_NAME", $faculty[ "OBJ_NAME" ] );
	$content->parse( "BLOCK_FACULTY" );
}
$content->setVariable( "VALUE_FOCUS", safe_string( $user_profile[ "USER_PROFILE_FOCUS" ] ) );
$content->setVariable( "VALUE_HOMETOWN", safe_string( $user_profile[ "USER_PROFILE_HOMETOWN" ] ) );
$content->setVariable( "VALUE_WANTS", safe_string( $user_profile[ "USER_PROFILE_WANTS" ] ) );
$content->setVariable( "VALUE_HAVES", safe_string( $user_profile[ "USER_PROFILE_HAVES" ] ) );
$content->setVariable( "VALUE_OTHER_INTERESTS", safe_string( $user_profile[ "USER_PROFILE_OTHER_INTERESTS" ] ) );
$content->setVariable( "VALUE_ORGANIZATIONS", safe_string( $user_profile[ "USER_PROFILE_ORGANIZATIONS" ] ) );

$content->setVariable( "VALUE_USER_PROFILE_DSC", safe_string( $user_profile[ "USER_PROFILE_DSC" ] ) );
$content->setVariable( "VALUE_EMAIL", safe_string( $user_profile[ "USER_EMAIL" ] ) );
$content->setVariable( "VALUE_ADDRESS", safe_string( $user_profile[ "USER_PROFILE_ADDRESS" ] ) );
$content->setVariable( "VALUE_TELEPHONE", safe_string( $user_profile[ "USER_PROFILE_TELEPHONE" ] ) );
$content->setVariable( "VALUE_PHONE_MOBILE", safe_string( $user_profile[ "USER_PROFILE_PHONE_MOBILE" ] ) );
$content->setVariable( "VALUE_WEBSITE", safe_string( $user_profile[ "USER_PROFILE_WEBSITE_URI" ] ) );
$content->setVariable( "VALUE_WEBSITE_NAME", safe_string( $user_profile[ "USER_PROFILE_WEBSITE_NAME" ] ) );
$content->setVariable( "VALUE_IM_ICQ", safe_string( $user_profile[ "USER_PROFILE_IM_ICQ" ] ) );
$content->setVariable( "VALUE_IM_SKYPE", safe_string( $user_profile[ "USER_PROFILE_IM_SKYPE" ] ) );
$content->setVariable( "VALUE_IM_AIM", safe_string( $user_profile[ "USER_PROFILE_IM_AIM" ] ) );
$content->setVariable( "VALUE_IM_MSN", safe_string( $user_profile[ "USER_PROFILE_IM_MSN" ] ) );
$content->setVariable( "VALUE_IM_YAHOO", safe_string( $user_profile[ "USER_PROFILE_IM_YAHOO" ] ) );

// LANGUAGE
if ( TRUE ) { // && !empty($user_profile["USER_LANGUAGE"]) ) {
  $ulang = $user_profile["USER_LANGUAGE"];
  if (!is_string($ulang) || $ulang === "0") $ulang = LANGUAGE_DEFAULT_STEAM;
  $languages = array(
    "english" => array("name" => gettext("English"), "icon" => "flag_gb.gif", "lang_key" => "en_US"),
    "german"  => array("name" => gettext("German"), "icon" => "flag_de.gif", "lang_key" => "de_DE")
  );
  if (!array_key_exists($ulang, $languages)) {
    $ulang = LANGUAGE_DEFAULT_STEAM;
  }
  $content->setCurrentBlock("USER_LANGUAGE");
  $content->setVariable("LABEL_LANGUAGES", gettext("Language"));
  foreach( $languages as $key => $language) {
    $content->setCurrentBlock("LANGUAGE");
    $content->setVariable("LABEL_LANGUAGE_LABEL", "profile_language_" . $key);
    $content->setVariable("LANGUAGE_ICON", PATH_STYLE . "/images/" . $language["icon"]);
    $content->setVariable("LABEL_LANGUAGE", $language["name"]);
    $content->setVariable("LANGUAGE_VALUE", $key);
    if ( $ulang == $key ) {
      $content->setVariable("LANGUAGE_CHECKED", "checked=\"checked\"");
    }
    $content->parse("LANGUAGE");
  }
  $content->parse("USER_LANGUAGE");
}

$portal->set_page_main(
	array(
		array( "link" => PATH_URL . "user/" . $user->get_name() . "/",
			"name" => $user->get_attribute( "USER_FIRSTNAME" ) . " " . $user->get_attribute( "USER_FULLNAME" )
		),
		array( "link" => "",
			"name" => gettext( "Profile" )
		)
	),
	$content->get(),
	""
);
$portal->show_html();
?>
