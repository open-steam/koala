<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "format_handling.inc.php" );
if (!defined("PATH_TEMPLATES_UNITS_HOMEWORK")) define( "PATH_TEMPLATES_UNITS_HOMEWORK", PATH_EXTENSIONS. "units_homework/templates/" );
/*
 * The following variables *must* be set before including this file:
 *   $koala_container : a koala_container object that represents the container
 *     to be displayed here (you can use the koala_container functions to
 *     limit the inventory items to display here)
 *   $html_handler : a valid html_handler class, e.g. koala_html_user,
 *      koala_html_group, koala_html_course, ...
 *   $portal : a valid lms_portal instance
 * 
 * The following variables *may* be set before including this file:
 *   $container_icons : if set to FALSE, then no icons will be displayed for
 *      the inventory objects, otherwise the icons from the open-sTeam backend
 *      will be displayed.
 *   $path_offset : an offset in the link path from which to display the
 *     container's path. Default is 1, so that the user's or group's documents
 *     folder will not be displayed in the path.
 */

if ( !isset( $koala_container ) || !($koala_container instanceof koala_container) )
	throw new Exception( "No koala_container provided." );

if ( !isset( $portal ) || !is_object( $portal ) )
	throw new Exception( "No portal provided." );

if ( !isset( $html_handler ) || !is_object( $html_handler ) )
	throw new Exception( "No valid html_handler provided." );

$container = $koala_container->get_steam_object();

// check read permission:
if ( !$container->check_access_read( lms_steam::get_current_user() ) ) {
	$portal->set_problem_description( gettext( "You are not permitted to view this folder.'" ) );
	$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html(), "");
	$portal->show_html();
	exit;
}

if ( !isset( $container_icons ) ) $container_icons = TRUE;
if ( !isset( $path_offset ) ) $path_offset = 1;

$portal->set_environment( $container );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES_UNITS_HOMEWORK . "units_homework_list_inventory.template.html" );

if ( isset( $_REQUEST['nrshow'] ) ) $nr_show = (int)$_REQUEST['nrshow'];
else $nr_show = 20;
$start = $portal->get_paginator_start( $nr_show );

if ( isset( $_REQUEST['sort'] ) )
	$sort = $_REQUEST['sort'];
else $sort = FALSE;

$pagination_info = $koala_container->get_inventory_paginated( $start, $nr_show, $sort );
$inventory = $pagination_info['objects'];

if ( !isset( $link_path ) )
	$link_path = $koala_container->get_link_path();
$base_url = $link_path[0]["link"];
$link_path_index = array_search( $path_offset, array_keys( $link_path ) );
if ( $link_path_index !== FALSE ) {
	$link_path = array_slice( $link_path, $link_path_index );
	$content->setVariable( "CONTAINER_PATH", $koala_container->get_link_path_html( $link_path ) );
}

$desc = $container->get_attribute( "OBJ_DESC" );
// don't show description for user clipboards (which would be the user description) or workrooms:
if ( is_string( $desc ) && (($container instanceof steam_user) || (is_object( $creator = $container->get_creator() ) && $creator->get_name() . "s workroom" == $desc)) )
	$desc = FALSE;
if ( is_string( $desc ) )
	$content->setVariable( "VALUE_CONTAINER_DESC", h( $desc ) );
$long_desc = $container->get_attribute( "OBJ_LONG_DESC" );
if ( is_string( $long_desc ) )
	$content->setVariable( "VALUE_CONTAINER_LONG_DESC", get_formatted_output( $long_desc ) );

$can_write = $container->check_access_write( $user );

// don't show clipboard when viewing clipboard contents as a folder:
if ( ! $container->get_root_environment() instanceof steam_user ) {
	//clipboard:
	$koala_user = new koala_html_user( $user );
	$clipboard_menu = $koala_user->get_clipboard_menu( $koala_container );
	$content->setCurrentBlock( "BLOCK_CLIPBOARD" );
	$content->setVariable( "CLIPBOARD_HTML", $clipboard_menu->get_html() );
	$content->parse( "BLOCK_CLIPBOARD" );
}

if ( !is_array( $inventory ) || count( $inventory ) == 0 ) {
	$content->setCurrentBlock( "BLOCK_EMPTY_INVENTORY" );
	$content->setVariable( "LABEL_NO_DOCUMENTS_FOUND", gettext( "There are no documents available yet." ) . "<br /><br />");
	$content->parse( "BLOCK_EMPTY_INVENTORY" );
}
else {
	$content->setCurrentBlock( "BLOCK_INVENTORY" );
	$page_option = '';
	$content->setVariable( "LABEL_HOMEWORK", gettext( "Tasks" ));
	$content->setVariable( "LABEL_ENDDATE", gettext( "Enddate" ));
	$content->setVariable( "LABEL_MODIFIED", '<a href="' . $label_date_link . $page_option . '">' . gettext( "Last modified" ) . '</a>' );
	$content->setVariable( "LABEL_WORKINGPEOPLE", gettext( "Participants" )  );
	$content->setVariable( "LABEL_POINTS", gettext( "Points").("/ <br>").gettext( "Feedback" )  );
	$content->setVariable( "LABEL_UPLOAD", gettext( "submit" ));

	$paginator_text = gettext('%START - %END of %TOTAL');
	if ( $nr_show > 0 )
		$paginator_text .= ', <a href="?nrshow=0' . (is_string($sort) ? '&sort=' . $sort : '') . '">' . gettext( 'show all' ) . '</a>';
	else $nr_show = count( $inventory );
	$portal->set_paginator( $content, $nr_show, $pagination_info['total'], '(' . $paginator_text . ')', is_string($sort) ? '?sort=' . $sort : '' );
	
	$item_ids = array();
	$i = 0;
	foreach( $inventory as $item )
	{
		// Ignore hidden files starting with '.'
		if ( substr( $item->get_name(), 0, 1 ) == '.' ) continue;
		
		$content->setCurrentBlock( "BLOCK_ITEM" );
		$size = ( $item instanceof steam_document ) ? $item->get_content_size() : 0;
		if ( !($item instanceof steam_container) && !($item instanceof steam_room) )
			$content->setVariable( "LINK_ITEM", PATH_URL . "doc/" . $item->get_id() . "/" );
		else
			$content->setVariable( "LINK_ITEM", $base_url . $item->get_id() );
		// pre-fetch attributes:
		$attributes = array( OBJ_CREATION_TIME, DOC_LAST_MODIFIED, DOC_USER_MODIFIED, OBJ_DESC );
		if ( $container_icons ) $attributes[] = OBJ_ICON;
		$attributes = $item->get_attributes( $attributes );
		
		if ( $container_icons && is_object( $icon = $attributes[ "OBJ_ICON" ] ) )
			$content->setVariable( "ICON_ITEM", "<div class='objecticon'><img src='" . PATH_URL . "cached/get_document.php?id=" . $icon->get_id() . "&type=objecticon&width=32&height=32' /></div>" );
		if ( $item instanceof steam_document ) {
			$content->setCurrentBlock( "BLOCK_DOWNLOAD" );
			$content->setVariable( "LINK_DOWNLOAD", PATH_URL . "get_document.php?id=" . $item->get_id() );
			$content->setVariable( "LABEL_DOWNLOAD", gettext( "Download Task" ) );
			$content->setVariable( "ICONPATH_DOWNLOAD", PATH_STYLE . "images/download.png" );
			$content->parse( "BLOCK_DOWNLOAD" );
		}
		if ( $item->check_access_read( $user ) ) {
			$content->setCurrentBlock( "BLOCK_COPY" );
			$content->setVariable( "LINK_TAKE_COPY", PATH_URL . "clipboard/take-copy/" . $item->get_id() . "/from/" . $container->get_id() );
			$content->setVariable( "LABEL_TAKE_COPY", gettext( "pick up a copy" ) );
			$content->setVariable( "ICONPATH_TAKE_COPY", PATH_STYLE . "images/copy.png" );
			$content->parse( "BLOCK_COPY" );
		}
		if ( $can_write ) {
			$content->setCurrentBlock( "BLOCK_TAKE" );
			$content->setVariable( "LINK_TAKE_OBJECT", PATH_URL . "clipboard/take/" . $item->get_id() . "/from/" . $container->get_id() );
			$content->setVariable( "LABEL_TAKE_OBJECT", gettext( "pick up the object" ) );
			$content->setVariable( "ICONPATH_TAKE_OBJECT", PATH_STYLE . "images/cut.png" );
			$content->parse( "BLOCK_TAKE" );
		}
		//Abgeben Button
		$content->setVariable( "LABEL_UPLOAD", gettext( "submit" ));
		
		if ( !($item instanceof steam_container) && !($item instanceof steam_room) )
			$content->setVariable( "SIZE_ITEM", get_formatted_filesize( $size ) );
		//zuletzt bearbeitet f�ngt an
		$last_modified = $attributes[ DOC_LAST_MODIFIED ];
		if ( $last_modified == 0 ) $last_modified = $attributes[ OBJ_CREATION_TIME ];
		if ( $last_modified != 0 ) {
			$author = $attributes[ DOC_USER_MODIFIED ];
			if (!is_object($author)) $author = $item->get_creator();
			$authorname = $author->get_name();
			$authorstring = "<a href=\"" . PATH_URL . "user/" . $authorname .  "/\">" . $authorname . "</a>";
			$modifiedstring = $authorstring . ",<br />" . "<small>" . strftime( "%x", $last_modified) . strftime(", %R", $last_modified ) . "</small>";
			$content->setVariable( "MODIFIED_ITEM", $modifiedstring );
		}
		$content->setVariable( "VALUE_VIEWS", str_replace( "%NO_VIEWS", count( lms_steam::get_readers( $item ) ), gettext( "%NO_VIEWS views" ) ) );
		$content->setVariable( "VALUE_COMMENTS", str_replace( "%NO_COMMENTS", count( $item->get_annotations() ), gettext( "%NO_COMMENTS comments" ) ) );
		$content->setVariable( "HOMEWORK_COMMENTS", PATH_URL . "doc/" . $item->get_id() . "/" );//TODO Hier muss der Link zur Bewertung hin
		$content->setVariable( "NAME_ITEM", h( $item->get_name() ) );
		
		$item_desc = $attributes[ OBJ_DESC ];
		if (is_string($item_desc) && strlen($item_desc) > 0) {
			$content->setCurrentBlock("BLOCK_DESCRIPTION");
			$content->setVariable( "OBJ_DESC", h( $item_desc ) );
			$content->parse("BLOCK_DESCRIPTION");
			$content->setVariable("ITEM_STYLE", "style=\"margin-top: 3px;\"");
		} else {
			$content->setVariable("ITEM_STYLE", "style=\"margin-top: 8px;\"");
		}
		$content->setVariable( "BOXES", "boxes_" . $i);
		$content->parse( "BLOCK_ITEM" );
		$item_ids[] = (string)$item->get_id();
		$i++;
	}
	$content->parse( "BLOCK_INVENTORY" );
}

if ( $can_write ) {
	$content->setCurrentBlock( 'BLOCK_STAFF' );

	$infotext = '';
	if ( is_array( $inventory ) && count( $inventory ) > 0 ) {
		$content->setCurrentBlock( 'BLOCK_DRAG_DROP' );
		$portal->add_javascript_code( 'units_homework_container_inventory', 'containerStart=0; containerEnd=' . count( $inventory ) . '; itemIds=Array(' . implode(',', $item_ids) . ');' );
		$content->setVariable( 'CONTAINER_ID', $container->get_id() );
		$content->setVariable( 'KOALA_VERSION', KOALA_VERSION );
		$content->setVariable( 'PATH_JAVASCRIPT', PATH_JAVASCRIPT );
		$infotext = gettext( 'Folders and documents can be sorted by dragging and dropping them.' ) . '<br/>';
		$content->parse( 'BLOCK_DRAG_DROP' );
	}
	
	$webdav_url = $koala_container->get_webdav_url();
	if ( !empty( $webdav_url ) )
		$infotext .= gettext( 'This folder is available as a web folder' ) . ': ' . $webdav_url;
	$content->setVariable( 'INFO_TEXT', $infotext );
	$content->parse( 'BLOCK_STAFF' );
}

$html_handler->set_html_left( $content->get() );
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html(), "");
$portal->show_html();

?>
