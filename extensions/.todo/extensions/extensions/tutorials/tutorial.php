<?php
require_once( PATH_EXTENSIONS . "tutorials/classes/koala_group_tutorial.class.php");
include_once( PATH_LIB . "format_handling.inc.php" );

if(!isset($portal) || !is_object($portal))
		{
			$portal = lms_portal::get_instance();
			$portal->initialize( GUEST_NOT_ALLOWED );
		}

$current_user = $user = lms_steam::get_current_user();
$lms_tutorial = new koala_group_tutorial($tutorial);

if ( ! $tutorial->get_workroom()->check_access_read( $current_user ) ) {
	$headline = $lms_tutorial->get_link_path();
	include( 'no_access.php' );
	exit;
}

$html_handler_course = new koala_html_course( $course );
$html_handler_course->set_context( "tutorials", array( 'subcontext' => 'tutorial', 'tutorial' => $lms_tutorial ) );

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	if ( is_array( $_POST[ "remove" ] ) )
	{
		$id = key( $_POST[ "remove" ] );
		$member_to_kick = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $id );
		$tutorial->remove_member( $member_to_kick );
		$portal->set_confirmation( str_replace( "%NAME", h($member_to_kick->get_full_name()), gettext( "User %NAME successfully removed from tutorial members." ) ) );
		$cache = get_cache_function( $tutorial->get_id(), CACHE_LIFETIME_STATIC );
		$cache->drop( "lms_steam::group_get_members", $tutorial->get_id() );
	}
}


if($lms_tutorial->is_password_protected()) $participant_mgmnt = gettext("password protected tutorial");
else if ($lms_tutorial->is_moderated() && ! $lms_tutorial->is_password_protected()) $participant_mgmnt = gettext("moderated tutorial");
else if ($lms_tutorial->is_private() && ! $lms_tutorial->is_password_protected()) $participant_mgmnt = gettext("private tutorial");
else $participant_mgmnt = gettext("public tutorial");

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_EXTENSIONS . "tutorials/templates/tutorial.template.html" );
$content->setVariable( "VALUE_CONTAINER_DESC", h($tutorial->get_attribute( "OBJ_DESC" )) . " (" . $participant_mgmnt . ")" );
$content->setVariable( "VALUE_CONTAINER_LONG_DESC", get_formatted_output( $tutorial->get_attribute( "TUTORIAL_LONG_DESC" ) ) );
$content->setVariable( "VALUE_TUTOR" , h($tutorial->get_attribute( "TUTORIAL_TUTOR" )) );
if ($tutorial->get_attribute( "TUTORIAL_MAX_LEARNERS" ) != "" && $tutorial->get_attribute( "TUTORIAL_MAX_LEARNERS" ) != 0)
{
	$content->setVariable( "VALUE_MAX_LEARNERS" , $tutorial->get_attribute( "TUTORIAL_MAX_LEARNERS" ) );
	$content->setVariable( "VALUE_FREE" , "(" . ($tutorial->get_attribute( "TUTORIAL_MAX_LEARNERS" ) - $tutorial->count_members()) . " " . gettext("free slots") . ")");
}
else
	$content->setVariable( "VALUE_MAX_LEARNERS" , gettext("unlimited") );

$content->setVariable( "LABEL_MAX_LEARNERS" , gettext( "Max learners:") );
$content->setVariable( "LABEL_MATERIAL" , gettext("Learning Material") );
$content->setVariable( "LABEL_LEARNERS" , gettext("Learners") );

//TODO: workaround unit Group.pike -> get_admin() is fixed
//$is_admin = $tutorial->check_access( SANCTION_ALL, $user );
$is_admin = $lms_tutorial->is_admin($user);

// Pointlist
/*
<!-- BEGIN BLOCK_POINTLIST -->
<h3>{LABEL_POINTLIST_HEADER}</h3>
<p>
  {LABEL_POINTLIST_INFO}
  {LABEL_POINTLIST_DATA}
</p>
<!-- END BLOCK_POINTLIST -->
*/
$pointlist_object = $tutorial->get_attribute("UNIT_POINTLIST");
if (is_object($pointlist_object)) {
  include_once( PATH_EXTENSIONS  . "units_pointlist/classes/units_pointlist.extension.class.php");
  $proxy = $pointlist_object->get_attribute("UNIT_POINTLIST_PROXY");
  if (is_object($proxy)) {
    $content->setCurrentBlock("BLOCK_POINTLIST");
    $content->setVariable("LABEL_POINTLIST_HEADER", gettext("Pointlist") );
    $content->setVariable("LABEL_POINTLIST_INFO", str_replace("%LINK" , "<a href='" . $course->get_url() . "units/" . $pointlist_object->get_id() . "/'>" . $pointlist_object->get_name() . "</a>" , gettext("View the pointlist '%LINK'") ));
    $content->setVariable("LABEL_POINTLIST_DATA", $plc );
    $content->parse("BLOCK_POINTLIST");
  }
}

//////////////////////////////////////////////////////////////////
// Documents
$attributes = array(DOC_LAST_MODIFIED, DOC_USER_MODIFIED);
$docs = $tutorial->get_workroom()->get_inventory(0, $attributes);
if ( count($docs) > 0 )
{
	$content->setCurrentBlock( "BLOCK_INVENTORY" );
	$content->setVariable( "LABEL_DOCNAME_DESCRIPTION", gettext( "Name/Description" ) );
	$content->setVariable( "LABEL_SIZE", gettext( "File size" ) );
	$content->setVariable( "LABEL_MODIFIED", gettext( "Last modified" ) );
	$content->setVariable( "LABEL_INFO", gettext( "Info" )  );
	$content->setVariable( "LABEL_ACTIONS", gettext( "Actions" )  );

  $creator_tnr = array();
  foreach ( $docs as $doc ) {
    if ( substr($doc->get_name(), 0, 1) == '.' ) {
      continue;
    }
    if ((CLASS_DOCUMENT & $doc->get_type()) == CLASS_DOCUMENT)
    {
      $last_modified = $doc->get_attribute( DOC_LAST_MODIFIED );
      if ( $last_modified == 0 ) $last_modified = $doc->get_attribute( OBJ_CREATION_TIME );
      if ( $last_modified != 0 ) {
        $autor = $doc->get_attribute(DOC_USER_MODIFIED);
        if (!is_object($autor)) $creator_tnr[$doc->get_id()] = $doc->get_creator(TRUE);
      }
    }
  }
  $creator_result = $GLOBALS["STEAM"]->buffer_flush();

  $data_tnr = array();
  foreach ( $docs as $doc ) {
    if ( substr($doc->get_name(), 0, 1) == '.' ) {
      continue;
    }
    if ((CLASS_DOCUMENT & $doc->get_type()) == CLASS_DOCUMENT)
    {
      $data_tnr[$doc->get_id()]= array();
      $last_modified = $doc->get_attribute( DOC_LAST_MODIFIED );
      if ( $last_modified == 0 ) $last_modified = $doc->get_attribute( OBJ_CREATION_TIME );
      if ( $last_modified != 0 ) {
        $autor = $doc->get_attribute(DOC_USER_MODIFIED);
        if (!is_object($autor)) $autor = $creator_result[$creator_tnr[$doc->get_id()]];
        $data_tnr[$doc->get_id()]["authorname"] = $autor->get_name(TRUE);
      }
      $data_tnr[$doc->get_id()]["readers"] = lms_steam::get_readers($doc, TRUE);
      $data_tnr[$doc->get_id()]["annotations"] = $doc->get_annotations(FALSE, TRUE);
    }
  }
  $data_result = $GLOBALS["STEAM"]->buffer_flush();
  
	foreach( $docs as $doc )
	{
    // Ignore hidden files starting with '.'
    if ( substr($doc->get_name(), 0, 1) == '.' ) {
      continue;
    }
    if ((CLASS_DOCUMENT & $doc->get_type()) == CLASS_DOCUMENT)
    {
      $content->setCurrentBlock( "BLOCK_ITEM" );
      $content->setVariable( "LINK_ITEM", PATH_URL . "doc/" . $doc->get_id() . "/" );
      $content->setVariable( "LINK_DOWNLOAD", PATH_URL . "get_document.php?id=" . $doc->get_id() );
      $content->setVariable( "LABEL_DOWNLOAD", gettext( "download" ) );
      $size = ( $doc instanceof steam_document ) ? $doc->get_attribute("DOC_SIZE") : 0;
      $content->setVariable( "SIZE_ITEM", get_formatted_filesize( $size ) );
      $last_modified = $doc->get_attribute( "DOC_LAST_MODIFIED" );
      if ( $last_modified == 0 ) $last_modified = $doc->get_attribute( "OBJ_CREATION_TIME" );
      if ( $last_modified != 0 ) {
        $autorname = $data_result[$data_tnr[$doc->get_id()]["authorname"]];
        $autorstring = "<a href=\"" . PATH_URL . "user/" . $autorname .  "/\">" . $autorname . "</a>";
        $modifiedstring = $autorstring . ",<br />" . "<small>" . strftime( "%x", $last_modified) . strftime(", %R", $last_modified ) . "</small>";
        $content->setVariable( "MODIFIED_ITEM", $modifiedstring );
      }
      $content->setVariable( "VALUE_VIEWS", str_replace( "%NO_VIEWS", count( $data_result[$data_tnr[$doc->get_id()]["readers"]] ), gettext( "%NO_VIEWS views" ) ) );
      $content->setVariable( "VALUE_COMMENTS", str_replace( "%NO_COMMENTS", count( $data_result[$data_tnr[$doc->get_id()]["annotations"]] ), gettext( "%NO_COMMENTS comments" ) ) );
      $content->setVariable( "LINK_COMMENTS", PATH_URL . "doc/" . $doc->get_id() . "/" );
      $content->setVariable( "NAME_ITEM", format_length( h( $doc->get_name() ), 70 ) );
      $content->setVariable( "OBJ_DESC", h($doc->get_attribute( "OBJ_DESC" )) );
      $content->parse( "BLOCK_ITEM" );
      }
      }
	$content->parse( "BLOCK_INVENTORY" );
  }
  else
  {
    $content->setVariable( "LABEL_NO_DOCUMENTS_FOUND", gettext( "No documents found." ) );
  }


////////////////////////////////////////////////
// Learners

$cache = get_cache_function( $tutorial->get_id(), CACHE_LIFETIME_STATIC );
$members = $cache->call( "lms_steam::group_get_members", $tutorial->get_id() );
$no_members = count( $members );
if ( $no_members > 0 )
{

	//$start = $portal->set_paginator( $content, 10, $no_members, "(" . str_replace( "%NAME", h($tutorial->get_name()), gettext( "%TOTAL members in %NAME" ) ) . ")" );
	//$end = ( $start + 10 > $no_members ) ? $no_members : $start + 10;

	$content->setVariable( "LABEL_CONTACTS", gettext( "Members" ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_members ), gettext( "%a-%z out of %s" ) ) . ")"  );

	$content->setCurrentBlock( "BLOCK_CONTACT_LIST" );
	$content->setVariable( "LABEL_NAME_POSITION", gettext( "Name, position" ) );
	$content->setVariable( "LABEL_SUBJECT_AREA", gettext( "Origin/Focus" ) );
	$content->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );
	if ( $is_admin )
	{
		$content->setVariable( "TH_MANAGE_CONTACT", gettext( "Action" ) );
	}
	$content->setVariable( "BEGIN_HTML_FORM", "<form method=\"POST\" action=\"\">" );
	$content->setVariable( "END_HTML_FORM", "</form>" );

	//for( $i = $start; $i < $end; $i++ )
	foreach($members as $member )
	{
		//$member = $members[ $i ];
		$content->setCurrentBlock( "BLOCK_CONTACT" );
		$content->setVariable( "CONTACT_LINK", PATH_URL . "user/" . h($member[ "OBJ_NAME" ]) . "/" );
		$icon_link = ( $member[ "OBJ_ICON" ] == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "get_document.php?id=" . h($member[ "OBJ_ICON" ]) . "&type=usericon&width=30&height=40";
		$content->setVariable( "CONTACT_IMAGE", $icon_link );
		$title = ( ! empty( $member[ "USER_ACADEMIC_TITLE" ] ) ) ? h($member[ "USER_ACADEMIC_TITLE" ]) . " " : "";
		$content->setVariable( "CONTACT_NAME", $title . h($member[ "USER_FIRSTNAME" ]) . " " . h($member[ "USER_FULLNAME" ]) );
		$content->setVariable( "LINK_SEND_MESSAGE", PATH_URL . "messages_write.php?to=" . h($member[ "OBJ_NAME" ]) );
		$content->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
		$content->setVariable( "LABEL_SEND", gettext( "Send" ) );
		$content->setVariable( "FACULTY_AND_FOCUS", h($member[ "USER_PROFILE_FACULTY" ]) );
		if ( $is_admin )
			$content->setVariable( "TD_MANAGE_CONTACT", "<td align=\"center\"><input type=\"submit\"  name=\"remove[" . h($member[ "OBJ_NAME" ]). "]\" value=\"" . gettext( "Remove" ) . "\"/></td>" );
		$member_desc = ( empty( $member[ "OBJ_DESC" ] ) ) ? "student" : $member[ "OBJ_DESC" ];
		$status = secure_gettext( $member_desc );
		$content->setVariable( "OBJ_DESC", h($status) );
		$content->parse( "BLOCK_CONTACT" );
	}

	$content->parse( "BLOCK_CONTACT_LIST" );
}

else
{
	$content->setVariable( "LABEL_NO_LEARNERS_FOUND", gettext( "No learners in this tutorial yet." ) );
}

$html_handler_course->set_html_left( $content->get());
$portal->set_page_main(
	$html_handler_course->get_headline(),//array( array( "link" => $backlink, "name" => str_replace( "%COURSE" , h($course->get_course_name()), gettext( "Tutorial Overview of '%COURSE'" ) ) ), array( "link" => "", "name" => str_replace( "%UNIT", h($tutorial->get_name()), gettext( "Tutorial '%UNIT'" ) ) ) ),
	$html_handler_course->get_html(),
	""
);
$portal->show_html();
?>
