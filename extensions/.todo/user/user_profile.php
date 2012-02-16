<?php
include_once( PATH_LIB . "format_handling.inc.php" );

function display( $block, $label, $value, $is_buddy = TRUE )
{
	global $GENERAL_displayed, $CONTACTS_AND_GROUPS_displayed, $CONTACT_DATA_displayed;
	
	if ( empty( $value ) )	return;
	
	if ( $is_buddy && viewer_authorized( $label ) )
	{
		$c = $GLOBALS[ "content" ];

		$c->setCurrentBlock("BLOCK_" . $block);
		$c->setVariable("LABEL_" . $block, secure_gettext($label));
		$c->setVariable("VALUE_" . $block, $value );
		$c->parse("BLOCK_" . $block);
		
		${$block . '_displayed'} = true;
	}
}

function viewer_authorized( $label )
{
	$current_user = $GLOBALS[ "current user" ];

	$authorizations = $GLOBALS[ "authorizations" ];
	
	(isset($authorizations[ label_to_mapping($label) ])) ? $current_authorization = $authorizations[ label_to_mapping($label) ] : $current_authorization = "" ;
	
	if ( !( $current_authorization & PROFILE_DENY_ALLUSERS ) ) return true;

	$is_contact = in_array( $current_user->get_id(), $GLOBALS[ "contact_ids" ] );
	if ( $is_contact && !( $current_authorization & PROFILE_DENY_CONTACTS ) ) return true;

	return false;
}

function label_to_mapping( $label )
{
	switch ( $label )
	{
		case "Origin":			return "PRIVACY_FACULTY"; break;
		case "Language":		return "PRIVACY_LANGUAGES"; break;
		case "E-mail":			return "PRIVACY_EMAIL"; break;
		case "AIM-alias":		return "PRIVACY_AIM_ALIAS"; break;
		case "Yahoo-ID":		return "PRIVACY_YAHOO_ID"; break;
		case "Phone, mobile":	return "PRIVACY_PHONE_MOBILE"; break;

		default: return "PRIVACY_" .  strtoupper( str_replace( " ", "_", $label ) ); break;
	}
}

$current_user = lms_steam::get_current_user();
$cache = get_cache_function( $login, 3600 );
$portal->set_page_title( $login );
$user_profile = $cache->call( "lms_steam::user_get_profile", $login );
$html_handler_profile = new koala_html_profile( $user );
$html_handler_profile->set_context( "profile" );

$GENERAL_displayed = false;
$CONTACTS_AND_GROUPS_displayed = false;
$CONTACT_DATA_displayed = false;

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "profile_display.template.html" );

if ( !empty( $user_profile[ "USER_PROFILE_DSC" ] ) )
{
	$content->setVariable( "HTML_CODE_DESCRIPTION", "<p>" . get_formatted_output( $user_profile[ "USER_PROFILE_DSC" ]  ) . "</p>" );
}

if ( !empty( $user_profile[ "USER_PROFILE_WEBSITE_URI" ] ) )
{
	$website_name = h(( empty( $user_profile[ "USER_PROFILE_WEBSITE_NAME" ] ) ) ? $user_profile[ "USER_PROFILE_WEBSITE_URI" ] : $user_profile[ "USER_PROFILE_WEBSITE_NAME" ]);
	$content->setVariable( "HTML_CODE_PERSONAL_WEBSITE", "<br/><b>" . gettext( "Website" ) . ":</b> <a href=\"" . h($user_profile[ "USER_PROFILE_WEBSITE_URI" ]) . "\" target=\"_blank\">$website_name</a>"   );
}

//get Buddys from user and put them into the $globals-Array for authorization-query
$confirmed = ( $user->get_id() != $current_user->get_id() ) ? TRUE : FALSE;
$contacts = $cache->call( "lms_steam::user_get_buddies", $login, $confirmed );

$tmp = array();
foreach ($contacts as $contact)
{
	$tmp[] = $contact["OBJ_ID"];
}
$GLOBALS["contact_ids"] = $tmp;

//get Viewer-Authorization and put them into the $globals-Array for authorization-query
$user_privacy = $cache->call( "lms_steam::user_get_profile_privacy", $user->get_name() );
$GLOBALS["authorizations"] = $user_privacy;
$GLOBALS["current user"] = $current_user;

///////////////////////////////////////////////////
//////////////  GENERAL INFORMATION  //////////////
///////////////////////////////////////////////////


// Status
if (PROFILE_STATUS) {
$user_profile_desc = ( empty( $user_profile[ "OBJ_DESC" ] ) ) ? "student" : $user_profile[ "OBJ_DESC" ];
$status = secure_gettext( $user_profile_desc );
display( "GENERAL", "Status", $status );
}

if (PROFILE_EMAIL) {
	$user_email = (empty($user_profile["USER_EMAIL"]))? "keine E-Mail-Adresse gesetzt" : $user_profile["USER_EMAIL"];
	display("GENERAL", "E-Mail-Adresse", h($user_email));
}
	
// Gender
if (PROFILE_GENDER) {
switch( is_string( $user_profile[ "USER_PROFILE_GENDER" ] ) ? $user_profile[ "USER_PROFILE_GENDER" ] : "X" )
{
	case( "F" ):
		$gender = gettext( "female" );
		break;

	case( "M" ):
		$gender = gettext( "male" );
		break;

	default:
		$gender = gettext( "rather not say" );
		break;
}
display( "GENERAL", "Gender", $gender );
}

// Origin - Faculty
if (PROFILE_GENERAL) {
$faculty = lms_steam::get_faculty_name( $user_profile[ "USER_PROFILE_FACULTY" ] );
display( "GENERAL", "Origin", $faculty );

display( "GENERAL", "Wants", h($user_profile[ "USER_PROFILE_WANTS" ]));
display( "GENERAL", "Haves", h($user_profile[ "USER_PROFILE_HAVES" ]));
display( "GENERAL", "Organizations", h($user_profile[ "USER_PROFILE_ORGANIZATIONS" ]));
display( "GENERAL", "Hometown", h($user_profile[ "USER_PROFILE_HOMETOWN" ]));
display( "GENERAL", "Main focus", h($user_profile[ "USER_PROFILE_FOCUS" ]));
display( "GENERAL", "Other interests", h($user_profile[ "USER_PROFILE_OTHER_INTERESTS" ]));
}

// LANGUAGE
if (PROFILE_LANGUAGE) {
$languages = array(
	"english" => array("name" => gettext("English"), "icon" => "flag_gb.gif", "lang_key" => "en_US"),
    "german"  => array("name" => gettext("German"), "icon" => "flag_de.gif", "lang_key" => "de_DE") );

$ulang = $user_profile["USER_LANGUAGE"];

if ( !is_string($ulang) || $ulang === "0" ) $ulang = LANGUAGE_DEFAULT_STEAM;
if ( !array_key_exists( $ulang, $languages ) ) $ulang = LANGUAGE_DEFAULT_STEAM;

$language_string = "";

foreach( $languages as $key => $language)
{
    if ( $ulang == $key )
    {
		$language_string .= "<img class=\"flag\" src=\"" . PATH_STYLE . "/images/" . $language["icon"] . "\" title=\"" . $language["name"] . "\" />";
    }
}

display( "GENERAL", "Language", $language_string );
}

if ( $GENERAL_displayed )$content->setVariable( "HEADER_GENERAL_INFORMATION", gettext( "General Information" ) );

///////////////////////////////////////////////////
///////////////  CONTACTS & GROUPS  ///////////////
///////////////////////////////////////////////////

// CONTACTS
if (YOUR_CONTACTS) {
$html_code_contacts = "";
$max_contacts = $counter =  25;

if ( count( $contacts ) > 0 )
{
	foreach ( $contacts as $id => $contact )
	{
		if ( $counter > 0 )
		{
			$title = ( ! empty( $contact[ "USER_ACADEMIC_TITLE" ] ) ) ? $contact[ "USER_ACADEMIC_TITLE" ] . " " : "";
			$html_code_contacts .= "<a href=\"" . PATH_URL . "user/" . $contact[ "OBJ_NAME" ] . "/\">" . $title . $contact[ "USER_FIRSTNAME" ]. " " . $contact[ "USER_FULLNAME" ] . "</a>";
			$html_code_contacts .= ($id == count($contacts) - 1 || $counter == 1) ? "" : ", ";
			$counter--;
		}
		else
		{
			$html_code_contacts .= " <a href=\"" . PATH_URL . "user/$login/contacts/\">(". gettext( "more" ) . "...)</a>";
			break;
		}
	}
}
else
{
	$html_code_contacts = gettext( "No contacts yet." );
}

display("CONTACTS_AND_GROUPS", "Contacts", $html_code_contacts);
}

if (YOUR_GROUPS) {
// GROUPS
$public = ( $user->get_id() != $current_user->get_id() ) ? TRUE : FALSE;
$groups = $cache->call( "lms_steam::user_get_groups", $login, $public );
$html_code_groups = "";
$max_groups = $counter = 25;

if ( count( $groups ) > 0 )
{
	usort( $groups, "sort_objects" );

	foreach ( $groups as $id => $group )
	{
		if ( $counter > 0 )
		{
			$html_code_groups .= "<a href=\"" . PATH_URL . "groups/" . $group[ "OBJ_ID" ] . "/\">" . h($group[ "OBJ_NAME" ]) . "</a>";
			$html_code_groups .= ($id == count($groups) - 1 || $counter == 1) ? "" : ", ";
			$counter--;
		}
		else
		{
			$html_code_groups .= " <a href=\"" . PATH_URL . "user/$login/groups/\">(" . gettext( "more" ). "...)</a>";
			break;
		}
	}
}
else
{
	$html_code_groups = gettext("No memberships yet.");
}

display( "CONTACTS_AND_GROUPS", "Groups", $html_code_groups );
}

if ( $CONTACTS_AND_GROUPS_displayed ) $content->setVariable( "HEADER_CONTACTS_AND_GROUPS", gettext( "Contacts and Groups" ) );

/////////////////////////////////////////////////////
///////////////  CONTACT INFORMATION  ///////////////
/////////////////////////////////////////////////////

if (PROFIL_CONTACT) {
$is_buddy = ( $user->is_buddy( $current_user ) || $user->get_id() == $current_user->get_id() ) ? TRUE : FALSE ;

display( "CONTACT_DATA", "E-mail", h($user_profile[ "USER_EMAIL" ]), $is_buddy );
display( "CONTACT_DATA", "Address", h($user_profile[ "USER_PROFILE_ADDRESS" ]), $is_buddy );
display( "CONTACT_DATA", "Telephone", h($user_profile[ "USER_PROFILE_TELEPHONE" ]), $is_buddy );
display( "CONTACT_DATA", "Phone, mobile", h($user_profile[ "USER_PROFILE_PHONE_MOBILE" ]), $is_buddy );


// Website
$website_name = $user_profile[ "USER_PROFILE_WEBSITE_NAME" ];
$website_uri = $user_profile[ "USER_PROFILE_WEBSITE_URI" ];
if ( empty( $website_name ) ) $website_name = $website_uri;
$website_link = ( empty( $website_name ) ) ? '' : '<a target="_blank" href="' . h($website_uri) . '">' . h($website_name) . '</a>';
display( "CONTACT_DATA", gettext("Website"), $website_link );

display( "CONTACT_DATA", "ICQ number", h($user_profile[ "USER_PROFILE_IM_ICQ" ]) );
display( "CONTACT_DATA", "MSN identification", h($user_profile[ "USER_PROFILE_IM_MSN" ]) );

// AIM
if ( !empty( $user_profile[ "USER_PROFILE_IM_AIM" ] ) )
{
	//$aim = "<span id=\"USER_PROFILE_IM_AIM\"><a href=\"{VALUE_AIM_LINK}\">{VALUE_AIM_ALIAS}</a></span>";
	$aim_alias = h($user_profile[ "USER_PROFILE_IM_AIM" ]);
	$aim = "<a href=\"aim:" . $aim_alias . "\">" . $aim_alias . "</a>";
	display( "CONTACT_DATA", "AIM-alias", $aim );
}

display( "CONTACT_DATA", "Yahoo-ID", h($user_profile[ "USER_PROFILE_IM_YAHOO" ]) );

// Skype
if ( !empty( $user_profile[ "USER_PROFILE_IM_SKYPE" ] ) )
{
	//$skype = "<span id=\"USER_PROFILE_IM_SKYPE\"><a href=\"{VALUE_SKYPE_LINK}\">{VALUE_SKYPE_NAME}</a></span>";
	$skype_alias = h($user_profile[ "USER_PROFILE_IM_SKYPE" ]);
	$skype = "<a href=\"skype:" . $skype_alias . "\">" . $skype_alias . "</a>";
	display( "CONTACT_DATA", "Skype name", $skype );
}
}

if ( $CONTACT_DATA_displayed ) $content->setVariable( "HEADER_CONTACT_DATA", gettext( "Contact Data" ) );


$content->setVariable( "PATH_JAVASCRIPT", PATH_JAVASCRIPT );
$content->setVariable( "KOALA_VERSION", KOALA_VERSION );
$content->setVariable( "USER_LOGIN", $login );

$html_handler_profile->set_html_left( $content->get() );
$portal->set_page_main( $html_handler_profile->get_headline(), $html_handler_profile->get_html(), "vcard" );
$portal->show_html();
?>