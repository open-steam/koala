<?php
require_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "groups_create.template.html" );

if(CREATE_PUBLIC_GROUP && CREATE_PRIVATE_GROUP){
	$content->setVariable( "INFO_TEXT", gettext( "There are 2 types of groups on koaLA: <b>public</b> and <b>private</b>."));
	$content->setVariable( "CHOOSE_TEXT", gettext( "Choose which sort of group you'd like to start" ) );
	$content->setVariable( "QUICK_SEARCH_TEXT_WIDTH", "200");
}else{
	$content->setVariable( "QUICK_SEARCH_TEXT_WIDTH", "500");
}

$content->setVariable( "QUICK_SEARCH_TEXT",
	str_replace( "%l", "<a href=\"" . PATH_URL. "groups/\">" . gettext( "quick search" ) . "</a>", gettext( "There are some groups on koaLA yet. Would you like to do a %l to make sure someone hasn't already started the group you're about to make?" ) )
);


if(CREATE_PUBLIC_GROUP){
	$content->setCurrentBlock( "BLOCK_CREATE_PUBLIC_GROUP" );
	$content->setVariable( "VALUE_PARENT_PUBLIC_GROUP", STEAM_PUBLIC_GROUP );
	$content->setVariable( "LABEL_PUBLIC", gettext( "Public" ) );
	$content->setVariable( "LABEL_CREATE_PUBLIC", gettext( "Create" ));
	$content->setVariable( "FORM_ACTION_PUBLIC", PATH_URL . "groups_create_dsc.php" );
	$content->parse( "BLOCK_CREATE_PUBLIC_GROUP" );
	
	$public_expl = array(
		gettext( "Public groups are <b>useful for discussion and documents of general subjects</b>" ),
		gettext( "Admins can choose to show or hide discussions and/or group pools from non-members." ),
		gettext( "You can choose the participant management you like for your public group: Everybody can join the group freely, to join your group participants have to enter a secret password or you have to accept their application becoming a memebr of your group. Anyway, you are free to add members independently from the choosen participant management method." )
	);
	$content->setCurrentBlock( "BLOCK_EXPLAIN_PUBLIC_ALL" );
	foreach( $public_expl as $e )
	{
		$content->setCurrentBlock( "BLOCK_EXPLAIN_PUBLIC" );
		$content->setVariable( "EXPLAIN_PUBLIC_TEXT", $e );
		$content->parse( "BLOCK_EXPLAIN_PUBLIC" );
	}
	$content->parse( "BLOCK_EXPLAIN_PUBLIC_ALL" );
	
}

if(CREATE_PRIVATE_GROUP){
	$content->setCurrentBlock( "BLOCK_CREATE_PRIVATE_GROUP" );
	$content->setVariable( "VALUE_PARENT_PRIVATE_GROUP", STEAM_PRIVATE_GROUP );
	$content->setVariable( "LABEL_PRIVATE", gettext( "Private" ) );
	$content->setVariable( "LABEL_CREATE_PRIVATE", gettext( "Create" ));
	$content->setVariable( "FORM_ACTION_PRIVATE", PATH_URL . "groups_create_dsc.php" );
	$content->parse( "BLOCK_CREATE_PRIVATE_GROUP" );
	
	$private_expl = array(
		gettext( "Private groups cannot be made public later." ),
		gettext( "Private groups are <b>useful for smaller learning or working groups or groups of friends.</b>" ),
		gettext( "Private groups are completely hidden from group searches, and don't display on people's profiles amongst groups they belong to." )
	);

	$content->setCurrentBlock( "BLOCK_EXPLAIN_PRIVATE_ALL" );
	foreach( $private_expl as $e )
	{
		$content->setCurrentBlock( "BLOCK_EXPLAIN_PRIVATE" );
		$content->setVariable( "EXPLAIN_PRIVATE_TEXT", $e );
		$content->parse( "BLOCK_EXPLAIN_PRIVATE" );
	}
	$content->parse( "BLOCK_EXPLAIN_PRIVATE_ALL" );
}

$portal->set_page_main( 
	array( array( "link" => PATH_URL . "user/" . lms_steam::get_current_user()->get_name() . "/groups/", "name" => gettext( "Your groups" ) ), array( "link" => "", "name" => gettext( "Create a new group" ) ) ),
	$content->get()
);

$portal->show_html();


?>
