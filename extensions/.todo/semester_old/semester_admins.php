<?php

require_once( "../etc/koala.conf.php" );
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$delete = $_POST[ "delete" ];
	if ( count( $delete ) == 1 )
	{
		$login = key( $delete );
		$admin = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $login );
		$admin_group->remove_member( $admin );
	}
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "semester_admins.template.html" );
$content->setVariable( "INFORMATION_ADMINS", str_replace( "%SEMESTER", h($current_semester->get_attribute( "OBJ_DESC" )), gettext( "These people are allowed to create courses for %SEMESTER." ) ) . " " . gettext( "They can appoint other users as staff members/moderators for their own courses." ) );

$content->setVariable( "LINK_ADD_ADMIN", PATH_URL . "group_add_member.php?group=" . $admin_group->get_id() );
$content->setVariable( "LABEL_ADD_ADMIN", gettext( "Add another admin" ) );
$content->setVariable( "LINK_MESSAGE", PATH_URL . "messages_write.php?group=" . $admin_group->get_id() );
$content->setVariable( "LABEL_MESSAGE_ADMINS", gettext( "Mail to admins" ) );

$admins = $admin_group->get_members(); 
$no_admins = count( $admins );

if ( $no_admins > 0 )
{
	$content->setVariable( "LABEL_ADMINS", gettext( "Course admins" ) );
	$content->setCurrentBlock( "BLOCK_CONTACT_LIST" );
	$content->setVariable( "LABEL_NAME_POSITION", gettext( "Name" ) . "/" . gettext( "Position" ) );
	$content->setVariable( "LABEL_SUBJECT_AREA", gettext( "Subject area" ) );
	$content->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );
	$content->setVariable( "LABEL_REMOVE_ADMIN", gettext( "Action" ) );
	
	foreach( $admins as $admin )
	{
		$adm_attributes = $admin->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME", "OBJ_DESC", "OBJ_ICON" ) );
		$content->setCurrentBlock( "BLOCK_CONTACT" );
		$content->setVariable( "CONTACT_NAME", h($adm_attributes[ "USER_FIRSTNAME" ])  . " " . h($adm_attributes[ "USER_FULLNAME" ]) );
		$icon_link = ( is_object( $adm_attributes[ "OBJ_ICON" ] ) ) ? PATH_URL . "get_document.php?id=" . $adm_attributes[ "OBJ_ICON" ]->get_id() . "&type=usericon&width=30&height=40" : PATH_STYLE . "images/anonymous.jpg";
		$content->setVariable( "CONTACT_IMAGE", $icon_link );
		$content->setVariable( "CONTACT_LINK", PATH_URL . "user/" . $admin->get_name() . "/" );
		$content->setVariable( "OBJ_DESC", h($adm_attributes[ "OBJ_DESC"]) );
		$content->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
		$content->setVariable( "LINK_SEND_MESSAGE", PATH_URL . "messages_write.php?to=" . $admin->get_name() );
		$content->setVariable( "LABEL_SEND", gettext( "Send" ) );
		$content->setVariable( "LABEL_REMOVE", gettext( "Remove" ) );
		$content->setVariable( "CONTACT_ID", $admin->get_name() );
		$content->parse( "BLOCK_CONTACT" );
	}
	
	$content->parse( "BLOCK_CONTACT_LIST" );
}
else
{
	$content->setVariable( "LABEL_ADMINS", gettext( "No admins found." ) );
}

$portal->set_page_title( h($current_semester->get_name()) . " Admins" );
$portal->set_page_main( 
	array(
		array( "link" => PATH_URL . SEMESTER_URL . "/" . h($current_semester->get_name()) . "/", "name" => h($current_semester->get_attribute( "OBJ_DESC" ))), array( "link" => "", "name" => gettext( "Admins" ) )
	),
	$content->get(),
	""
);
$portal->show_html( );
?>
