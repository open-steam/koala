<?php

include_once( "../etc/koala.conf.php" );
if (!defined("PROFILE_VISITORS") || !PROFILE_VISITORS) {
	header("location:/");
	exit;
}
require_once( PATH_LIB . "format_handling.inc.php" );

function resolve_referer( $referer )
{
	// TODO
	return '';
	//return gettext( "available soon" );
}

require_once( "../etc/koala.conf.php" );
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$user = lms_steam::get_current_user();
$profile = new lms_networking_profile( $user );
$result = $profile->get_current_visitors();

// Filter out duplications and non-objects
$result_filtered = array();
$visitors_tmp = array();
$visitor = 0;
foreach ($result as $visitor_data) {
  $visitor = 0;
  if (isset($visitor_data["visitor"])) $visitor = $visitor_data["visitor"];
  if (is_object($visitor) && !isset($visitors_tmp[$visitor->get_id()])) {
    $visitors_tmp[$visitor->get_id()] = TRUE;
    $result_filtered[] = $visitor_data;
  }
}
$result = $result_filtered;

$html_people = new HTML_TEMPLATE_IT();
$html_people->loadTemplateFile( PATH_TEMPLATES . "list_users.template.html" );
$no_people = count( $result );
$start = $portal->set_paginator( $html_people, 10, $no_people, "(" . gettext( "%TOTAL visitors currently visited your profile" ). ")" );
$end = ( $start + 10 > $no_people ) ? $no_people : $start + 10;
$html_people->setVariable( "LABEL_CONTACTS", gettext( "Visitors" ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_people), gettext( "%a-%z out of %s" ) ) . ")" );
if ( $no_people > 0 )
{
				$html_people->setCurrentBlock( "BLOCK_CONTACT_LIST" );
				$html_people->setVariable( "LABEL_NAME_POSITION", gettext( "Name, position" ) );
				$html_people->setVariable( "LABEL_SUBJECT_AREA", gettext( "Origin/Focus" ) );
				$html_people->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );
				$html_people->setVariable( "TH_MANAGE_CONTACT", '' );
				for ( $i = $start; $i < $end; $i++ )
				{
								$visitor = $result[ $i ][ "visitor" ];
								$referer = $result[ $i ][ "referer" ];
                                
                if (!is_object($visitor)) continue; //Skip if the entry is not an object
                                
								$cache = get_cache_function( $visitor->get_name() );
								$attributes = $cache->call( "lms_steam::user_get_profile", $visitor->get_name() );
								
								$html_people->setCurrentBlock( "BLOCK_CONTACT" );
								$html_people->setVariable( "CONTACT_LINK", PATH_URL . "user/" . $visitor->get_name(). "/" );
								$icon_link = ( $attributes[ "OBJ_ICON" ] == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . $attributes[ "OBJ_ICON" ] . "&type=usericon&width=30&height=40";
								$html_people->setVariable( "CONTACT_IMAGE", $icon_link );
								$html_people->setVariable( "CONTACT_NAME", $attributes[ "USER_FIRSTNAME" ] . " " . $attributes[ "USER_FULLNAME" ] );
								$html_people->setVariable( "LINK_SEND_MESSAGE", PATH_URL . "messages_write.php?to=" . $visitor->get_name() );
								$html_people->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
								$html_people->setVariable( "LABEL_SEND", gettext( "Send" ) );
								$html_people->setVariable( "OBJ_DESC", h($attributes[ "OBJ_DESC" ]) );
								$fof = lms_steam::get_faculty_name( $attributes[ "USER_PROFILE_FACULTY" ] );
								$fof .= ( empty( $attributes[ "USER_PROFILE_FOCUS" ] ) ) ? "" : ", " . htmlentities($attributes[ "USER_PROFILE_FOCUS" ],ENT_QUOTES, "UTF-8");
								$html_people->setVariable( "FACULTY_AND_FOCUS", $fof );
								//$html_people->setVariable( "TD_MANAGE_CONTACT", "<td align=\"center\">".resolve_referer( $referer )."</td>" );
								$html_people->parse( "BLOCK_CONTACT" );
				}
				$html_people->parse( "BLOCK_CONTACT_LIST" );
}
else
{
				$html_people->setVariable( "LABEL_CONTACTS", gettext( "No visitors yet." ) );
}

$portal->set_page_main( array( array( "link" => PATH_URL . "user/" . $user->get_name() . "/", "name" => gettext( "Your Profile" ) ), array( "linK" => "", "name" => gettext( "Members who have recently visited your contact page" )) ), $html_people->get() , "" );
$portal->show_html();
?>
