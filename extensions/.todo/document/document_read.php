<?php

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "document_read.template.html" );

$user = lms_steam::get_current_user();
$rss_feeds = $user->get_attribute( "USER_RSS_FEEDS" );
$is_watching = FALSE;
if (is_array($rss_feeds)) {
  foreach(array_keys($rss_feeds) as $item) {
    if ($item == $document->get_id()) {
      $is_watching=TRUE;
    }
  }
}
if ($is_watching) {
  $block_watching = "<a class=\"button\" disable=\"true\" >" . gettext("You are watching the comments") . "</a>";
} else {
  $block_watching = "<a class=\"button\" href=\"?action=bookmark_rss\" >" . gettext("Watch comments") . "</a>";
}
$content->setVariable( "BLOCK_WATCHING", $block_watching );


$content->setVariable( "LABEL_TITLE", gettext( "Title" ) );
$content->setVariable( "LABEL_DESCRIPTION", gettext( "Description" ) );
$content->setVariable( "LABEL_CREATED", gettext( "Created by" ) );

if ($document instanceof steam_document) {
  $content->setCurrentBlock("BLOCK_DOCUMENT_OPTIONS");
  $content->setVariable( "LABEL_DOWNLOAD", gettext( "Download" ) );
  $content->setVariable( "LINK_DOWNLOAD", PATH_URL . "get_document.php?id=" . $document->get_id() );
  if ( $document->check_access_write( $user ) )
  {
    $content->setCurrentBlock( "BLOCK_WRITE_ACCESS" );
    $content->setVariable( "LINK_EDIT", PATH_URL . "doc/" . $document->get_id() . "/edit/" );
    $content->setVariable( "LABEL_EDIT", gettext( "Edit" ) );
    $content->setVariable( "LINK_DELETE", PATH_URL . "doc/" . $document->get_id() . "/delete/" );
    $content->setVariable( "LABEL_DELETE", gettext( "Delete" ) );
    $content->parse( "BLOCK_WRITE_ACCESS" );
  }
  $content->parse("BLOCK_DOCUMENT_OPTIONS");
}
if ( isset($_GET[ "action" ]) && $_GET[ "action" ] == "bookmark_rss" )
{
				lms_steam::user_add_rssfeed( $document->get_id(), PATH_URL . "services/feeds/document_public.php?id=" . $document->get_id(), "document", lms_steam::get_link_to_root( $document ) );
				$portal->set_confirmation( str_replace( "%NAME", h($document->get_name()), gettext( "You are keeping an eye on '%NAME' from now on." ) ) );
}

$content->setVariable( "VALUE_OBJ_NAME", h($document->get_name()) );
$content->setVariable( "VALUE_OBJ_DSC", h($document->get_attribute( "OBJ_DESC" )) );
$author = $document->get_creator();
$author_data = $author->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME", "OBJ_ICON" ) );
$author_data[ "OBJ_ICON" ] = $author_data[ "OBJ_ICON" ]->get_id();
$content->setVariable( "LINK_AUTHOR", PATH_URL . "user/" . h($author->get_name()) . "/" );
$content->setVariable( "AUTHOR_ICON", PATH_URL . "get_document.php?id=" . $author_data[ "OBJ_ICON" ] . "?type=usericon&width=30&height=40" );
$content->setVariable( "VALUE_CREATION_TIME", str_replace( "%DATE", strftime( "%x", $document->get_attribute( "OBJ_CREATION_TIME") ), gettext( "on %DATE" ) ) );
$content->setVariable( "VALUE_AUTHOR_NAME", h($author_data[ "USER_FIRSTNAME" ]) . " " . h($author_data[ "USER_FULLNAME" ]) );
$content->setVariable( "HTML_ANNOTATIONS", $comments_html );

if ($document instanceof steam_document) {
  // DOCUMENT
  $content->setCurrentBlock("BLOCK_DOCUMENT");
  $content->setVariable( "LABEL_MIME_TYPE", gettext( "Mime Type" ) );
  $content->setVariable( "LABEL_FILESIZE", gettext( "Filesize" ) );
  $content->setVariable( "LABEL_DOWNLOADS", gettext( "Downloads" ) );
  $mime = $document->get_attribute( "DOC_MIME_TYPE" );
  if( $mime === "image/jpeg" || $mime === "image/png" || $mime === "image/gif" || $mime === "image/tiff" ) {
    $content->setCurrentBlock("BLOCK_PRVIEW");
    $content->setVariable( "LABEL_PREVIEW", gettext( "Preview" ));
    $content->setVariable("VALUE_PREVIEW", "<img src=" . PATH_URL . "get_document?id=" . $document->get_id() . "&width=180&height=180" . " />" );
  //	$content->setVariable("VALUE_PREVIEW", gettext("No preview available"));
    $content->parse("BLOCK_PRVIEW");
  }
  $content->setVariable( "VALUE_OBJ_MIMETYPE", h($document->get_attribute( "DOC_MIME_TYPE" )) );
  $changer = $document->get_attribute( DOC_USER_MODIFIED );
  if (is_object($changer)) {
    $content->setCurrentBlock("BLOCK_CHANGER");
    $content->setVariable( "LABEL_LAST_CHANGED", gettext( "Last changed by" ) );
    $content->setVariable( "VALUE_LAST_CHANGE", strftime( "%x - %X", $document->get_attribute( DOC_LAST_MODIFIED ) ) );
    $changer_data = $changer->get_attributes( array( USER_FIRSTNAME, USER_FULLNAME, OBJ_ICON ) );
    $content->setVariable( "CHANGER_ICON", PATH_URL . "get_document.php?id=" . $changer_data[ OBJ_ICON ]->get_id() . "?type=usericon&width=30&height=40" );
    $content->setVariable( "LINK_CHANGER", PATH_URL . "user/" . h($changer->get_name()) . "/" );
    $content->setVariable( "VALUE_CHANGER_NAME", h($changer_data[ USER_FIRSTNAME ]) . " " . h($changer_data[ USER_FULLNAME ]));
    $content->parse("BLOCK_CHANGER");
  }
  $readers = lms_steam::get_readers( $document );
  $content->setVariable( "VALUE_VIEWED",  str_replace( "%x", count( $readers ), gettext( "%x times" ) ) );
  $content->setVariable( "VALUE_SIZE", get_formatted_filesize( $document->get_content_size() ) );
} else if ($document instanceof steam_container) {
  // CONTAINER
  $content->setCurrentBlock("BLOCK_CONTAINER");
  $content->setVariable("LABEL_OBJECTCOUNT", gettext("Contents"));
  $content->setVariable("VALUE_OBJECTCOUNT", str_replace("%COUNT", $document->count_inventory(), gettext("%COUNT Objects")));
  $content->parse("BLOCK_CONTAINER");
} else {
  // DOCEXTERN
  $content->setCurrentBlock("BLOCK_DOCEXTERN");
  $content->setVariable("LABEL_URL", gettext("URL"));
  $content->setVariable("VALUE_URL", $document->get_attribute( DOC_EXTERN_URL ));
  $content->parse("BLOCK_DOCEXTERN");
}

$portal->set_rss_feed( PATH_URL . "services/feeds/document_public.php?id=" . $document->get_id(), gettext( "Feed" ), gettext( "Subscribe to this document's newsfeed" ) );

$parent = $document->get_environment();
$link_path = -1;
if (is_object($parent)) {
  $parent = koala_object::get_koala_object($parent);
  if (is_object($parent) && ($parent instanceof lms_wiki)) {
    $link_path = lms_steam::get_link_path( $document );
  }
}

if ($link_path === -1) $link_path =	koala_object::get_koala_object( $document )->get_link_path();

$portal->set_page_main(
  $link_path,
	//array( lms_steam::get_link_to_root( $env ), array( "link" => "", "name" => h($document->get_name()) ) ),
	$content->get(),
	""
);
$portal->show_html();

?>
