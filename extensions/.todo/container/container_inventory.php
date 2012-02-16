<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

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


//hide documents in public and private groups
$setHidden = false; //init
$group = $koala_container->get_koala_owner();
if (method_exists($group,'is_member')){
	$privacy_names_docs = (int) $group->get_attribute("GROUP_PRIVACY");
	$isGroupMember = $group->is_member(lms_steam::get_current_user());

	if ( ($privacy_names_docs==3 || $privacy_names_docs==2) && !($isGroupMember)){
		$setHidden = true;
	} else {
		$setHidden = false;
	}		
}


// check read permission:
if ( !$container->check_access_read( lms_steam::get_current_user() ) | $setHidden ) {
	$portal->set_problem_description( gettext( "You are not permitted to view this folder.'" ) );
	$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html(), "");
	$portal->show_html();
	exit;
}

if ( !isset( $container_icons ) ) $container_icons = TRUE;
if ( !isset( $path_offset ) ) $path_offset = 1;

$portal->set_environment( $container );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "list_inventory.template.html" );

if ( isset( $_REQUEST['nrshow'] ) ) $nr_show = (int)$_REQUEST['nrshow'];
else $nr_show = 10;
$start = $portal->get_paginator_start( $nr_show );

if ( isset( $_REQUEST['sort'] ) )
	$sort = $_REQUEST['sort'];
else $sort = FALSE;

if ( !isset( $link_path ) )
	$link_path = $koala_container->get_link_path();
$base_url = $link_path[0]["link"];
$link_path_index = array_search( $path_offset, array_keys( $link_path ) );
if ( $link_path_index !== FALSE ) {
	$link_path = array_slice( $link_path, $link_path_index );
	$content->setVariable( "CONTAINER_PATH", $koala_container->get_link_path_html( $link_path ) );
}

$main_tnr = array();
$main_tnr[OBJ_DESC] = $container->get_attribute( OBJ_DESC, TRUE );
$main_tnr["OBJ_LONG_DESC"] = $container->get_attribute( "OBJ_LONG_DESC", TRUE );

$main_tnr["creator"] = $container->get_creator(TRUE);
$main_tnr["can_write"] = $container->check_access_write( $user, TRUE );
$main_tnr["inventory"] = $koala_container->get_inventory_paginated( $start, $nr_show, $sort, TRUE );
$main_result = $GLOBALS["STEAM"]->buffer_flush();

$pagination_info = $main_result[$main_tnr["inventory"]];
$inventory = $pagination_info['objects'];

$desc = $main_result[$main_tnr[OBJ_DESC]];
// don't show description for user clipboards (which would be the user description) or workrooms:

if ( is_string( $desc ) && (($container instanceof steam_user) || (is_object( $creator =  $main_result[$main_tnr["creator"]]) && $creator->get_name() . "s workroom" == $desc)) )
	$desc = FALSE;
if ( is_string( $desc ) )
	$content->setVariable( "VALUE_CONTAINER_DESC", h( $desc ) );
$long_desc = $main_result[$main_tnr["OBJ_LONG_DESC"]];
if ( is_string( $long_desc ) )
	$content->setVariable( "VALUE_CONTAINER_LONG_DESC", get_formatted_output( $long_desc ) );

$can_write = $main_result[$main_tnr["can_write"]];

// don't show clipboard when viewing clipboard contents as a folder:
if ( ! $container->get_root_environment() instanceof steam_user ) {
	//clipboard:
	$koala_user = new koala_html_user( $user );
	$clipboard_menu = $koala_user->get_clipboard_menu( $koala_container );
	if(CLIPBOARD){
		$content->setCurrentBlock( "BLOCK_CLIPBOARD" );
		$content->setVariable( "CLIPBOARD_HTML", $clipboard_menu->get_html() );
		$content->parse( "BLOCK_CLIPBOARD" );
	}
}

if ( !is_array( $inventory ) || count( $inventory ) == 0 ) {
	$content->setCurrentBlock( "BLOCK_EMPTY_INVENTORY" );
	$content->setVariable( "LABEL_NO_DOCUMENTS_FOUND", gettext( "There are no documents available yet." ) . "<br /><br />");
	$content->parse( "BLOCK_EMPTY_INVENTORY" );
}
else {

	// Prefetch needed data
  $attributes = array( OBJ_CREATION_TIME, DOC_LAST_MODIFIED, DOC_USER_MODIFIED, OBJ_DESC, DOC_EXTERN_URL, OBJ_ICON, OBJ_NAME );
  $data_tnr = array();
  $only_containers = TRUE;
	foreach( $inventory as $item ) {
     $data_tnr[ $item->get_id() ] = array();
     $data_tnr[ $item->get_id() ]["attributes"] = $item->get_attributes( $attributes, TRUE);
     if ($item instanceof steam_document ) {
       $data_tnr[$item->get_id()]["contentsize"] = $item->get_content_size( TRUE );
       $data_tnr[$item->get_id()]["readers"] = lms_steam::get_readers( $item, TRUE );
     }
     $data_tnr[$item->get_id()]["can_read"] = $item->check_access_read( $user, TRUE );
     $data_tnr[$item->get_id()]["annotations"] = $item->get_annotations(FALSE, TRUE);
     $data_tnr[$item->get_id()]["creator"] = $item->get_creator(TRUE);
     if ( !$item instanceof steam_container )
     	$only_containers = FALSE;
  }
  $data_result = $GLOBALS["STEAM"]->buffer_flush();

  $author_tnr = array();
	foreach( $inventory as $item ) {
     $author_tnr[ $item->get_id() ] = array();
     if (!is_object($data_result[$data_tnr[$item->get_id()]["attributes"]][DOC_USER_MODIFIED])) {
       $author_tnr[ $item->get_id() ]["authorname"] = $data_result[$data_tnr[$item->get_id()]["creator"]]->get_name(TRUE);
     } else {
       $author_tnr[ $item->get_id() ]["authorname"] = $data_result[$data_tnr[$item->get_id()]["attributes"]][DOC_USER_MODIFIED]->get_name(TRUE);
     }
  }
  $author_result = $GLOBALS["STEAM"]->buffer_flush();


	$content->setCurrentBlock( "BLOCK_INVENTORY" );
	$page_option = '';
	if ( isset( $_REQUEST['page'] ) ) $page_option = '&page=' . $_REQUEST['page'];
	if ( $sort === 'name' ) $label_name_link = '?sort=-name';
	else if ( $sort === '-name' ) $label_name_link = '?sort=';
	else $label_name_link = '?sort=name';
	switch ($sort)
	{
		case '-name':
			$n_sort_indicator = "&#9660;";
			break;
		case 'name':
			$n_sort_indicator = "&#9650;";
			break;
		default:
			$n_sort_indicator = "";
	}
	$content->setVariable( "LABEL_DOCNAME_DESCRIPTION", '<a href="' . $label_name_link . $page_option . '">' . gettext( "Name/Description" ) . "  " . $n_sort_indicator . '</a>' );
	if ( $sort === 'size' ) $label_size_link = '?sort=-size';
	else if ( $sort === '-size' ) $label_size_link = '?sort=';
	else $label_size_link = '?sort=size';
	switch ($sort)
	{
		case '-size':
			$s_sort_indicator = "&#9660;";
			break;
		case 'size':
			$s_sort_indicator = "&#9650;";
			break;
		default:
			$s_sort_indicator = "";
	}
	if (!$only_containers || !isset( $_REQUEST['nrshow'] ) )
		$content->setVariable( "LABEL_SIZE", '<a href="' . $label_size_link . $page_option . '">' . gettext( "File size" ) . "  " . $s_sort_indicator . '</a>' );
	else
		$content->setVariable( "LABEL_SIZE", gettext( "File size" ) );
	if ( $sort === 'date' ) $label_date_link = '?sort=-date';
	else if ( $sort === '-date' ) $label_date_link = '?sort=';
	else $label_date_link = '?sort=date';
	switch ($sort)
	{
		case '-date':
			$d_sort_indicator = "&#9660;";
			break;
		case 'date':
			$d_sort_indicator = "&#9650;";
			break;
		default:
			$d_sort_indicator = "";
	}
	$content->setVariable( "LABEL_MODIFIED", '<a href="' . $label_date_link . $page_option . '">' . gettext( "Last modified" ) . " " . $d_sort_indicator . '</a>' );
	$content->setVariable( "LABEL_INFO", gettext( "Info" )  );
	$content->setVariable( "LABEL_ACTIONS", gettext( "Actions" )  );

	$paginator_text = gettext('%START - %END of %TOTAL');
	if ( $nr_show > 0 )
		$paginator_text .= ', <a href="?nrshow=0' . (is_string($sort) ? '&sort=' . $sort : '') . '">' . gettext( 'show all' ) . '</a>';
	else $nr_show = count( $inventory );
	$portal->set_paginator( $content, $nr_show, $pagination_info['total'], '(' . $paginator_text . ')', is_string($sort) ? '?sort=' . $sort : '' );

	$item_ids = array();
	$i = 0;


	foreach( $inventory as $item ) {
		$attributes = $data_result[$data_tnr[$item->get_id()]["attributes"]];	
	
		// Ignore hidden files starting with '.'
		if ( substr( $attributes[OBJ_NAME], 0, 1 ) == '.' ) continue;

		if ($item instanceof steam_container)
		{
			$content->setVariable( "INVENTORY_SIZE", $item->count_inventory() . " " . gettext("object(s)") );
		}
 
		$content->setCurrentBlock( "BLOCK_ITEM" );
		$size = ( $item instanceof steam_document ) ? $data_result[$data_tnr[$item->get_id()]["contentsize"]] : 0;
	if ( ($item instanceof steam_container) || ($item instanceof steam_room) ) {
      $content->setVariable( "LINK_ITEM", $base_url . $item->get_id() );
      $edit_link = $base_url . $item->get_id() . "/" . "edit";
      $delete_link = $base_url . $item->get_id() . "/" . "delete";
    } else if ($item instanceof steam_docextern) {
      $content->setVariable( "LINK_ITEM", $attributes[DOC_EXTERN_URL] );
      $edit_link = PATH_URL . "doc/" . $item->get_id() . "/" . "edit";
      $delete_link = PATH_URL . "doc/" . $item->get_id() . "/" . "delete";
    } else {
      // Document
//      $content->setVariable( "LINK_ITEM", PATH_URL . "get_document.php?id=" . $item->get_id() );
      $content->setVariable( "LINK_ITEM", PATH_URL . "download/" . $item->get_id() . "/" . rawurlencode($attributes[OBJ_NAME]) );
      //$content->setVariable( "LINK_ITEM", PATH_URL . "doc/" . $item->get_id() . "/");
      $content->setVariable( "SIZE_ITEM", get_formatted_filesize( $size ) );
      $last_modified = $attributes[ DOC_LAST_MODIFIED ];
      $edit_link = PATH_URL . "doc/" . $item->get_id(). "/edit";
      $delete_link = PATH_URL . "doc/" . $item->get_id(). "/delete";
    }
		if ( !isset($last_modified) || $last_modified == 0 ) $last_modified = $attributes[OBJ_CREATION_TIME];
		if ( $last_modified != 0 ) {
      		$authorname = $author_result[$author_tnr[$item->get_id()]["authorname"]];
			$authorstring = "<a href=\"" . PATH_URL . "user/" . $authorname .  "/\">" . $authorname . "</a>";
			$modifiedstring = $authorstring . "<br />" . "<small>" . strftime( "%x", $last_modified) . strftime(", %R", $last_modified ) . "</small>";
			$content->setVariable( "MODIFIED_ITEM", $modifiedstring );
		}
		$content->setVariable( "VALUE_COMMENTS", str_replace( "%NO_COMMENTS", count(  $data_result[$data_tnr[$item->get_id()]["annotations"]] ), gettext( "%NO_COMMENTS comments" ) ) );
		$content->setVariable( "LINK_COMMENTS", PATH_URL . "doc/" . $item->get_id() . "/" );

		if ( $container_icons && is_object( $icon = $attributes[ "OBJ_ICON" ] ) )
			$content->setVariable( "ICON_ITEM", "<div class='objecticon'><img src='" . PATH_URL . "cached/get_document.php?id=" . $icon->get_id() . "&type=objecticon&width=32&height=32' /></div>" );
		if ( $item instanceof steam_document ) {
			$content->setCurrentBlock( "BLOCK_DOWNLOAD" );
			//$content->setVariable( "LINK_DOWNLOAD", PATH_URL . "get_document.php?id=" . $item->get_id() );
      $content->setVariable( "LINK_ITEM", PATH_URL . "download/" . $item->get_id() . "/" . rawurlencode($attributes[OBJ_NAME]) );
			$content->setVariable( "LABEL_DOWNLOAD", gettext( "download" ) );
			$content->setVariable( "ICONPATH_DOWNLOAD", PATH_STYLE . "images/download.png" );
			$content->parse( "BLOCK_DOWNLOAD" );
		}
		if ( $data_tnr[$item->get_id()]["can_read"] ) {
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
		if ( $can_write ) {
			$content->setCurrentBlock( "BLOCK_EDIT" );
			$content->setVariable( "LINK_EDIT_OBJECT", $edit_link);
			$content->setVariable( "LABEL_EDIT_OBJECT", gettext( "edit" ) );
			$content->setVariable( "ICONPATH_EDIT_OBJECT", PATH_STYLE . "images/edit.png" );
			$content->parse( "BLOCK_EDIT" );
			$content->setCurrentBlock( "BLOCK_DELETE" );
			$content->setVariable( "LINK_DELETE_OBJECT", $delete_link);
			$content->setVariable( "LABEL_DELETE_OBJECT", gettext( "delete" ) );
			$content->setVariable( "ICONPATH_DELETE_OBJECT", PATH_STYLE . "images/delete.png" );
			$content->parse( "BLOCK_DELETE" );
		}
		//$content->setVariable( "NAME_ITEM", h( $attributes[OBJ_NAME] ) );
		$content->setVariable( "NAME_ITEM", format_length( h( $attributes[OBJ_NAME]) , 25 ) );

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
		$portal->add_javascript_code( 'container_inventory', 'containerStart=0; containerEnd=' . count( $inventory ) . '; itemIds=Array(' . implode(',', $item_ids) . ');' );
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

if ($container->get_attribute("KOALA_CONTAINER_TYPE") ==="container_podcast_koala") {
  //$pclink = "pcast://" . SERVER . PATH_PREFIX . "services/feeds/podcast_container.php?id=" . $unit->get_id();
  $pclink = PATH_URL . "services/feeds/podcast_container.php?id=" . $unit->get_id();
  $content->setCurrentBlock( "BLOCK_PODCAST" );
  $content->setVariable( "LINK_PODCAST", $pclink );
  $content->setVariable( "LABEL_PODCAST", gettext("This container is available as Podcast") . ": ");
  $content->setVariable( "LABEL_LINK_PODCAST", gettext("Subscribe to the podcast"));
  $content->setVariable( "LABEL_HINT_PODCAST", gettext("(Please note that the appearance of the podcast depends on your application you use to subscribe to this podcast. Common types which may be handled by most podcast-viewers are: audio (.mp3) and PDF (.pdf))"));
  $content->setVariable( "LOGO_PODCAST", PATH_STYLE . "images/podcast_icon_orange.png" );
  $content->parse( "BLOCK_PODCAST" );
}

$html_handler->set_html_left( $content->get() );
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html(), "");
$portal->show_html();

?>
