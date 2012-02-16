<?php

if (!defined("PATH_TEMPLATES_UNITS_DOCPOOL")) define( "PATH_TEMPLATES_UNITS_DOCPOOL", PATH_EXTENSIONS. "units_docpool/templates/" );

if ( !isset( $portal ) ) {
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}

if ( !isset( $html_handler_course ) ) {
	$html_handler_course = new koala_html_course( $course );
	$html_handler_course->set_context( "units", array( "subcontext" => "unit" ) );
}
$content = new HTML_TEMPLATE_IT();
//$content->loadTemplateFile( PATH_TEMPLATES_UNITS_DOCPOOL . "units_docpool.template.html" );
$content->loadTemplateFile( PATH_TEMPLATES . "list_inventory.template.html" );
$content->setVariable( "VALUE_CONTAINER_DESC", h( $unit->get_attribute( "OBJ_DESC" ) ) );
$content->setVariable( "VALUE_CONTAINER_LONG_DESC", get_formatted_output( $unit->get_attribute( "OBJ_LONG_DESC" ) ) );

$docs = $unit->get_inventory();
$item_ids = array();
if ( count( $docs > 0 ) )
{
	$content->setCurrentBlock( "BLOCK_INVENTORY" );
	$content->setVariable( "LABEL_DOCNAME_DESCRIPTION", gettext( "Name/Description" ) );
	$content->setVariable( "LABEL_SIZE", gettext( "File size" ) );
	$content->setVariable( "LABEL_MODIFIED", gettext( "Last modified" ) );
	$content->setVariable( "LABEL_ACTIONS", gettext( "Actions" )  );

	foreach( $docs as $doc )
	{
	   // Ignore hidden files starting with '.'
	   if ( substr($doc->get_name(), 0, 1) == '.' ) {
	      continue;
	   }
	   
		$content->setCurrentBlock( "BLOCK_ITEM" );
		$size = ( $doc instanceof steam_document ) ? $doc->get_content_size() : 0;
		$content->setVariable( "LINK_ITEM", PATH_URL . "doc/" . $doc->get_id() . "/" );
		$content->setVariable( "LINK_DOWNLOAD", PATH_URL . "get_document.php?id=" . $doc->get_id() );
		$content->setVariable( "LABEL_DOWNLOAD", gettext( "download" ) );
		$content->setVariable( "SIZE_ITEM", get_formatted_filesize( $size ) );
		$last_modified = $item->get_attribute( "DOC_LAST_MODIFIED" );
		if ( $last_modified == 0 ) $last_modified = $item->get_attribute( "OBJ_CREATION_TIME" );
		if ( $last_modified != 0 ) {
      $autor = $item->get_attribute("DOC_USER_MODIFIED");
      if (!is_object($autor)) $autor = $item->get_creator();
      $autorname = $autor->get_name();
      $autorstring = "<a href=\"" . PATH_URL . "user/" . $autorname .  "/\">" . $autorname . "</a>";
      $modifiedstring = $autorstring . ",<br />" . "<small>" . strftime( "%x", $last_modified) . strftime(", %R", $last_modified ) . "</small>";
			$content->setVariable( "MODIFIED_ITEM", $modifiedstring );
    }
		$content->setVariable( "VALUE_VIEWS", str_replace( "%NO_VIEWS", count( lms_steam::get_readers( $doc ) ), gettext( "%NO_VIEWS views" ) ) );
		$content->setVariable( "VALUE_COMMENTS", str_replace( "%NO_COMMENTS", count( $doc->get_annotations() ), gettext( "%NO_COMMENTS comments" ) ) );
		$content->setVariable( "LINK_COMMENTS", PATH_URL . "doc/" . $doc->get_id() . "/" );
		$content->setVariable( "NAME_ITEM", $doc->get_name() );
		$desc = ($doc->get_attribute( "OBJ_DESC" ) === "") ? gettext("No description available") : $doc->get_attribute( "OBJ_DESC" );
		$content->setVariable( "DESC_ITEM", $desc );
		$content->parse( "BLOCK_ITEM" );
		$item_ids[] = $doc->get_id();
	}
	$content->parse( "BLOCK_INVENTORY" );
}
else
{
	$content->setVariable( "LABEL_NO_DOCUMENTS_FOUND", gettext( "Sorry, no documents found." ) );
}

if ( $unit->check_access_write( lms_steam::get_current_user() ) ) {
	$content->setCurrentBlock( 'BLOCK_STAFF' );

	$infotext = '';
	if ( is_array( $item_ids ) && count( $item_ids ) > 0 ) {
		$content->setCurrentBlock( 'BLOCK_DRAG_DROP' );
		$portal->add_javascript_code( 'units_docpool', 'containerStart=0; containerEnd=' . count( $item_ids ) . '; itemIds=Array(' . implode(',', $item_ids) . ');' );
		$content->setVariable( 'CONTAINER_ID', $unit->get_id() );
		$content->setVariable( 'KOALA_VERSION', KOALA_VERSION );
		$content->setVariable( 'PATH_JAVASCRIPT', PATH_JAVASCRIPT );
		$infotext = gettext( 'Folders and documents can be sorted by dragging and dropping them.' ) . '<br/>';
		$content->parse( 'BLOCK_DRAG_DROP' );
	}
	
	$webdav_url = $unit->get_webdav_url();
	if ( !empty( $webdav_url ) )
		$infotext .= gettext( 'This folder is available as a web folder' ) . ': ' . $webdav_url;
	$content->setVariable( 'INFO_TEXT', $infotext );
	$content->parse( 'BLOCK_STAFF' );
}

?>
