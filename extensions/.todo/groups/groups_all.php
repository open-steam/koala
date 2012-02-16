<?php

// Seems to be never used at all

$group_id = ( ! empty( $_GET[ "parent" ] ) ) ? $_GET[ "parent" ] : STEAM_PUBLIC_GROUP;

$group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $group_id, CLASS_GROUP);
$user  = lms_steam::get_current_user();
$cache = get_cache_function( $group_id, CACHE_LIFETIME_STATIC );
$subgroups = $cache->call( "lms_steam::group_get_subgroups", $group_id );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "groups_all.template.html" );
$content->setVariable( "LABEL_ALL_GROUPS", str_replace( "%NAME", h($group->get_name()), gettext( "Subgroups of %NAME" ) ) );
$content->setVariable( "LABEL_MANAGE_SUBSCRIPTIONS", gettext( "Manage subscriptions" ) );
$content->setVariable( "LINK_MANAGE_SUBSCRPTIONS", PATH_URL . "user/" . h($user->get_name()) . "/groups/" );
$content->setVariable( "LINK_CREATE_NEW_GROUP", PATH_URL . "groups_create.php?parent=" . h($group->get_id()) );
$content->setVariable( "LABEL_CREATE_NEW_GROUP", gettext( "Create new group" ) );
$content->setVariable( "LABEL_SEARCH", gettext( "Search" ) );
$content->setVariable( "LABEL_GROUPNAME", gettext( "Name" ) );
$content->setVariable( "LABEL_GROUP_DESC", gettext( "Description" ) );
$content->setVariable( "LABEL_GROUP_MEMBERS", gettext( "Members" ) );
$content->setVariable( "LABEL_GROUP_SUBGROUPS", gettext( "Subgroups" ) );

$no_subgroups = count( $subgroups );
if ( $no_subgroups > 0 )
{
	$start = $portal->set_paginator( 20, $no_subgroups, "(" . str_replace( "%NAME", h($group->get_name()), gettext( "%TOTAL groups in %NAME" ) ) . ")");
	$end = ( $start + 20 > $no_subgroups ) ? $no_subgroups : $start + 20;
	for ( $i = $start; $i < $end; $i++ )
	{
		$subgroup = $subgroups[ $i ];
		$content->setCurrentBlock( "BLOCK_GROUP" );
		$content->setVariable( "GROUP_LINK", PATH_URL . "groups/" . h($subgroup[ "OBJ_ID" ]) . "/" );
		$content->setVariable( "GROUP_NAME", h($subgroup[ "OBJ_NAME" ]) );
		$content->setVariable( "GROUP_DESC", h($subgroup[ "OBJ_DESC" ]) );
		$content->setVariable( "GROUP_MEMBERS_LINK", PATH_URL . "groups/" . h($subgroup[ "OBJ_ID" ]) . "/members/" );
		$content->setVariable( "GROUP_MEMBERS", h($subgroup[ "NO_MEMBERS" ]) );
		if ( $subgroup[ "NO_SUBGROUPS" ] == 0 )
		{
			$content->setVariable( "GROUP_SUBGROUPS", "-" );
		}
		else
		{
			$content->setVariable( "GROUP_SUBGROUPS", "<a href=\"" . PATH_URL . "groups/?parent=" . h($subgroup[ "OBJ_ID" ]) . "\">" . h($subgroup[ "NO_SUBGROUPS" ]) . "</a>" );
		}
		$content->parse( "BLOCK_GROUP" );
	}
}
$headline = ( ! empty( $_GET[ "parent" ] ) ) ? 
	array( array( "link" => PATH_URL . "groups/" . h($group->get_id()) . "/", "name" => h($group->get_name())),
		array( "link" => "", "name" => gettext( "Subgroups available" ) ) ) :
	gettext( "Groups available" );
$portal->set_page_main( $headline, $content->get(), "" );
$portal->show_html();
?>
