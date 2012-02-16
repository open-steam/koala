<?php
require_once( PATH_LIB . "wiki_handling.inc.php" );
//is it a previous version of an entry?
$is_prev_version = (isset($version_doc) && is_object($version_doc) && $version_doc instanceof steam_document) ? TRUE : FALSE;

$wiki_html_handler = new lms_wiki( $wiki_container );

if(!$is_prev_version)
{
	$wiki_html_handler->set_admin_menu( "entry", $wiki_doc );
	$attributes = $wiki_doc->get_attributes( array( "DOC_VERSION", "DOC_AUTHORS", "OBJ_LAST_CHANGED", "DOC_USER_MODIFIED", "DOC_TIMES_READ", "DOC_LAST_MODIFIED", "OBJ_WIKILINKS" ));
	//TODO: check if sourcecode can be deleted
	//$wiki_html_handler->set_widget_links( $wiki_doc );
	//$wiki_html_handler->set_widget_previous_versions( $wiki_doc );
}
else
{
	$wiki_html_handler->set_admin_menu( "version" , $version_doc );
 	$attributes = $version_doc->get_attributes( array( "DOC_VERSION", "DOC_AUTHORS", "OBJ_LAST_CHANGED", "DOC_USER_MODIFIED", "DOC_TIMES_READ", "DOC_LAST_MODIFIED", "OBJ_WIKILINKS" ));
}

$last_author  = $attributes[ "DOC_USER_MODIFIED" ]->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME" ) );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "wiki_entry.template.html" );
$content->setVariable( "LABEL_CLOSE", gettext( "close" ) );

if(!$is_prev_version) 
	$content->setVariable( "VALUE_ENTRY_TEXT", wiki_to_html_plain( $wiki_doc ) );
else 
	$content->setVariable( "VALUE_ENTRY_TEXT", wiki_to_html_plain( $wiki_doc, $version_doc ) );
	
$content->setVariable( "IMAGE_SRC", PATH_URL . "get_document.php?id=" . $attributes[ "DOC_USER_MODIFIED" ]->get_attribute( "OBJ_ICON" )->get_id() . "&type=usericon&width=60&height=70" );
$content->setVariable( "AUTHOR_LINK", PATH_URL . "user/" . $attributes[ "DOC_USER_MODIFIED" ]->get_name() . "/" );
$content->setVariable( "VALUE_POSTED_BY", h($last_author[ "USER_FIRSTNAME" ]) . " " . h($last_author[ "USER_FULLNAME" ]) );
$content->setVariable( "LABEL_BY", gettext("created by"));
$content->setVariable( "VALUE_VERSION", h($attributes["DOC_VERSION"]));
$content->setVariable( "VALUE_DATE_TIME", strftime( "%x %X", $attributes[ "DOC_LAST_MODIFIED" ] ) );

/*
if(!$is_prev_version)
{
	$content->setVariable( "POST_PERMALINK", PATH_URL . "wiki/" . $wiki_doc->get_id() . "/" );
	$content->setVariable( "POST_PERMALINK_LABEL", "(" . gettext( "permalink" ) . ")");
}
*/

if ( $wiki_doc->check_access_write( $user ) )
{
	$content->setCurrentBlock( "BLOCK_ACCESS" );
	$content->setVariable( "POST_LABEL_DELETE", gettext( "delete" ) );
	$content->setVariable( "POST_LABEL_EDIT", gettext( "edit" ) );
	$content->parse( "BLOCK_ACCESS" );
}

$versions = $wiki_doc->get_previous_versions();
$no_versions = ( is_array( $versions ) ) ? count( $versions ) : 0;
$content->setVariable("VERSION_MANAGEMENT", gettext( "Version Management" ) );

if ( $no_versions > 0 )
{
	$content->setVariable("NUMBER_VERSIONS", "<li>" . $no_versions . " " . gettext( "previous version(s) available" ) . "</li>" );
	$content->setVariable("LINK_VERSION_MANAGEMENT", "<li><a href=\"" . PATH_URL . "wiki/" . $wiki_doc->get_id() . "/versions/\">&raquo; " . gettext("enter version management") . "</a></li>");
}
else
{
	$content->setVariable("NUMBER_VERSIONS", "<li>" . gettext( "no previous versions available" ) . "</li>" );
}

$content->setVariable("LINKS", gettext( "Wiki Links" ) );
$links = $wiki_doc->get_attribute( "OBJ_WIKILINKS_CURRENT" );
$found_doc = false;
if (is_array($links)) {
	foreach($links as $doc) {
		if ($doc instanceof steam_document) {
			$found_doc = true;
			break;
		}
	}
}
		
if (!$found_doc)
{
	$content->setCurrentBlock( "BLOCK_LINKS" );
	$content->setVariable( "LINK", gettext("no links available"));
	$content->parse( "BLOCK_LINKS" );
}
else
{
	foreach( $links as $doc )
	{
		if ( $doc instanceof steam_document )
		{
			$name = str_replace( ".wiki", "", h( $doc->get_name() ) );
			$link = PATH_URL . "wiki/" . $wiki_doc->get_id() . "/" . $doc->get_identifier();			
			$content->setVariable( "LINK", '<li>&raquo; <a href="' . $link . '">' . $name . '</a></li>' );
			$content->parse( "BLOCK_LINKS" );
		}
	}
}

$wiki_html_handler->set_main_html( $content->get() );

$rootlink = lms_steam::get_link_to_root( $wiki_container );
(WIKI_FULL_HEADLINE) ? 
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/"),
			) :
$headline = array(array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/"));


if(!$is_prev_version)
{
	$headline[] = array( "link" => "", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) );
} else {
	$headline[] = array( "link" => PATH_URL . "wiki/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) );
	$headline[] = array( "link" => PATH_URL . "wiki/" . $wiki_doc->get_id() . "/versions/", "name" => gettext("Version management"));
	$headline[] = array( "link" => "", "name" => "Version" . " " . $version_doc->get_version() . " (" . gettext("Preview") . ")");
}

	$portal->set_page_main(
    $headline,
    $wiki_html_handler->get_html()
);
$portal->show_html();

?>