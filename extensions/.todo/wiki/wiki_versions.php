<?php
require_once( PATH_LIB . "wiki_handling.inc.php" );

$wiki_html_handler = new lms_wiki( $wiki_container );
$wiki_html_handler->set_admin_menu( "versions", $wiki_doc );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "wiki_versions.template.html" );

$prev_versions = $wiki_doc->get_previous_versions();

if ( ! is_array( $prev_versions ) )
{
	$prev_versions = array();
}

array_unshift($prev_versions, $wiki_doc);

$no_versions = count( $prev_versions );

$content->setCurrentBlock( "BLOCK_VERSION_LIST" );

if(isset($_GET["markedfordiff"]) && !empty($_GET["markedfordiff"]))
{
	$uri_params = "?markedfordiff=" . $_GET["markedfordiff"];
	$start = $portal->set_paginator( $content, 10, $no_versions, "(" . gettext("%TOTAL versions in list") . ")", $uri_params );
}
else
$start = $portal->set_paginator( $content, 10, $no_versions, "(" . gettext("%TOTAL versions in list") . ")" );
$end   = ( $start + 10 > $no_versions ) ? $no_versions : $start + 10;

$content->setVariable("LABEL_VERSIONS", gettext("Available Versions for the entry") . " " . h($wiki_doc->get_identifier()) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_versions ), gettext( "%a-%z out of %s" ) ) . ")");

$content->setVariable( "LABEL_VERSION_NUMBER", gettext("Version number"));
$content->setVariable( "LABEL_SIZE", gettext("Size"));
$content->setVariable( "LABEL_DATE", gettext("Modification date"));
$content->setVariable( "LABEL_CREATOR", gettext("Modified by"));
$content->setVariable( "LABEL_ACTION", gettext("Action"));

// Use buffer for document attributes
$attributes_tnr = array();
for( $i = $start; $i < $end; $i++ )
{
	$attributes_tnr[$prev_versions[$i]->get_id()] = $prev_versions[$i]->get_attributes( array( DOC_USER_MODIFIED, DOC_LAST_MODIFIED, DOC_VERSION, DOC_SIZE ), TRUE);      
}
$attributes_result = $GLOBALS["STEAM"]->buffer_flush();
    
// use buffer for author attributes
$author_tnr = array();
for( $i = $start; $i < $end; $i++ )
{
	$author_tnr[$prev_versions[$i]->get_id()] = $attributes_result[$attributes_tnr[$prev_versions[$i]->get_id()]][DOC_USER_MODIFIED]->get_attributes( array(USER_FIRSTNAME, USER_FULLNAME, OBJ_NAME) , TRUE);      
}
$author_result = $GLOBALS["STEAM"]->buffer_flush();    


for( $i = $start; $i < $end; $i++ )
{
	$doc = $prev_versions[$i];
	
	$attributes = $attributes_result[$attributes_tnr[$doc->get_id()]];
	$last_author  = $author_result[$author_tnr[$doc->get_id()]];
	$content->setCurrentBlock( "BLOCK_VERSION" );

	if ( $doc instanceof steam_document )
	{
		$content->setVariable( "VALUE_SIZE", get_formatted_filesize( $doc->get_content_size() ) );
		$content->setVariable( "VALUE_CREATOR_LINK", PATH_URL . "user/" . $author_result[$author_tnr[$doc->get_id()]][OBJ_NAME] . "/" );
		$content->setVariable( "VALUE_CREATOR", h($last_author[ USER_FIRSTNAME ]) . " " . h($last_author[ USER_FULLNAME ]) );
		$content->setVariable( "VALUE_DATE", strftime( "%x %X", $attributes[ "DOC_LAST_MODIFIED" ] ) );
		
    if ($doc->get_id() !== $wiki_doc->get_id()) {
      $content->setVariable( "VALUE_VERSION_LINK", PATH_URL . "wiki/" . $wiki_doc->get_id() . "/versions/" . $doc->get_id() . "/" );
      $content->setVariable( "VALUE_VERSION_NUMBER", "Version " . h($attributes_result[$attributes_tnr[$doc->get_id()]][DOC_VERSION]) );
      $content->setCurrentBlock("BLOCK_RECOVER");
      $content->setVariable( "VALUE_ACTION_RECOVER", "&raquo; " . gettext("Recover this version"));
      $content->setVariable( "VALUE_RECOVER_LINK", PATH_URL . "wiki/" . $wiki_doc->get_id() . "/versions/" . $doc->get_id() . "/recover/");
      $content->parse("BLOCK_RECOVER");
    } else {
      $content->setVariable( "VALUE_VERSION_LINK", PATH_URL . "wiki/" . $wiki_doc->get_id() . "/" );
      $content->setVariable( "VALUE_VERSION_NUMBER", "Version " . h($attributes_result[$attributes_tnr[$doc->get_id()]][DOC_VERSION]) . " (" . gettext("current") . ")" );
    }
    
		if($_GET["markedfordiff"] && $_GET["markedfordiff"] == $doc->get_id())
		{
			$content->setVariable( "VALUE_ACTION_MARK", "&raquo; " . gettext("Currently marked for version compare"));
		}
		else
		{
			$content->setVariable( "VALUE_ACTION_MARK", "<a href=\"" . PATH_URL . "wiki/" . $wiki_doc->get_id() . "/versions/?markedfordiff=" . $doc->get_id() . "\">" . "&raquo; " . gettext("Mark for version compare") . "</a>");
		}
		if($attributes[DOC_VERSION] != 1)
		{
			$content->setVariable( "VALUE_ACTION_DIFF", "&raquo; " . gettext("Compare to previous version") . " " . ($attributes[ DOC_VERSION ] - 1) );
			$content->setVariable( "VALUE_DIFF_LINK", PATH_URL . "wiki/" . $wiki_doc->get_id() . "/versions/" . $doc->get_id() . "/compare/" .  $prev_versions[$i+1]->get_id());
		}
		if($_GET["markedfordiff"] &&  $_GET["markedfordiff"] != $doc->get_id())
		{
			$marked = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $_GET["markedfordiff"] );
			$content->setVariable( "VALUE_ACTION_MARKED_DIFF", "<a href=\"" . PATH_URL . "wiki/" . $wiki_doc->get_id() . "/versions/" . $doc->get_id() . "/compare/" . $_GET["markedfordiff"] . "\">" . "&raquo; " . gettext("Compare to marked version") . " " . $marked->get_version() . "</a>");
		}
	}
	$content->parse( "BLOCK_VERSION" );
}
$content->parse( "BLOCK_VERSION_LIST" );

$wiki_html_handler->set_main_html( $content->get() );

$rootlink = lms_steam::get_link_to_root( $wiki_container );
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/"),
				array( "link" => PATH_URL . "wiki/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
				array( "link" => "", "name" => gettext("Version management"))
				);

$portal->set_page_main(
    $headline,
    $wiki_html_handler->get_html()
);
$portal->show_html();

?>