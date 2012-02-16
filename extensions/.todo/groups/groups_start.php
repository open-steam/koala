<?php

$html_handler_group = new koala_html_group( $group );
$html_handler_group->set_context( "start" );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "group_start.template.html" );
$content->setVariable( "LABEL_DESCRIPTION", gettext( "Description" ) );

$desc = $group->get_attribute( "OBJ_DESC" );

if ( empty( $desc ) )
{
	$content->setVariable( "OBJ_DESC", gettext( "No description available." ) );
}
else
{
	$content->setVariable( "OBJ_DESC", get_formatted_output( $desc ) );
}
$about = $group->get_attribute( "OBJ_LONG_DSC" );
if ( ! empty( $about ) )
{
	$content->setCurrentBlock( "BLOCK_ABOUT" );
	$content->setVariable( "VALUE_ABOUT", get_formatted_output( $about ) );
	$content->parse( "BLOCK_ABOUT" );
}
$content->setVariable( "LABEL_ADMINS", gettext( "Moderated by" ) );

if ($group->get_maxsize() > 0) {
  $content->setCurrentBlock("BLOCK_GROUPSIZE");
  $content->setVariable("LABEL_MAXSIZE_HEADER", gettext("The number of participants of this group is limited."));
  $content->setVariable("LABEL_MAXSIZE_DESCRIPTION", str_replace("%MAX", $group->get_maxsize(), str_replace("%ACTUAL", $group->count_members() ,  gettext("The actual participant count is %ACTUAL of %MAX."))));
  $content->parse("BLOCK_GROUPSIZE");
}

$admins = $group->get_admins();

if ( count( $admins ) > 0  )
{
foreach( $admins as $admin )
{
	$content->setCurrentBlock( "BLOCK_ADMIN" );
	$admin_attributes = $admin->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME", "OBJ_ICON", "OBJ_DESC", "OBJ_NAME" ) );
	if ( $admin instanceof steam_user )
	{
		$content->setVariable( "ADMIN_NAME", h($admin_attributes[ "USER_FIRSTNAME" ]) . " " . h($admin_attributes[ "USER_FULLNAME" ]) );
		$content->setVariable( "ADMIN_LINK", PATH_URL . "user/" . h($admin->get_name()) . "/" );
	}
	else
	{
		$content->setVariable( "ADMIN_NAME", h($admin_attributes[ "OBJ_NAME" ] ));
		$content->setVariable( "ADMIN_LINK", PATH_URL . "groups/" . $admin->get_id() . "/" );
	}
	$content->setVariable( "ADMIN_ICON", PATH_URL . "cached/get_document.php?id=" . $admin_attributes[ "OBJ_ICON" ]->get_id() . "&type=usericon&width=40&height=47" );
	
	$admin_desc = ( empty( $admin_attributes[ "OBJ_DESC" ] ) ) ? "student" :$admin_attributes[ "OBJ_DESC" ];
	$content->setVariable( "ADMIN_DESC", secure_gettext($admin_desc) );
	$content->parse( "BLOCK_ADMIN" );
}
}
else
{
	$content->setVariable( "LABEL_UNMODERATED", gettext( "Group is unmoderated." ) );
}

$html_handler_group->set_html_left( $content->get() );


$portal->set_page_main( $html_handler_group->get_headline(), $html_handler_group->get_html() , "" );
$portal->show_html();
?>
