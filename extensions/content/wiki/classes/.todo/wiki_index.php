<?php

include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();

// Disable caching
// TODO: Work on cache handling. An enabled cache leads to bugs
// if used with the wiki.
CacheSettings::disable_caching();

$path = url_parse_rewrite_path( $_GET[ "path" ] );
if ( ! $wiki_container = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "id" ] ) )
{
	include( "bad_link.php" );
	exit;
}
if ( ! $wiki_container instanceof steam_container )
{
	$wiki_doc = $wiki_container;
	$wiki_container = $wiki_doc->get_environment();
	if ( $wiki_doc->get_attribute( DOC_MIME_TYPE ) != "text/wiki" )
	{
		include( "bad_link.php" );
		exit;
	}
}
switch( TRUE )
{
	case ( $path[ 0 ] == "versions" && empty($path[ 1 ])):
		//enter wiki version management
		include( "wiki_versions.php" );
		exit;
	break;

	case ( $path[ 0 ] == "versions" && !empty($path[ 1 ]) && $path[ 2 ] === "compare" && !empty($path[ 3 ])):
	//compare two wiki entry versions
	$compare = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 1 ] );
	$to = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 3 ] );
	if(is_object($compare) && $compare instanceof steam_document && is_object($to) && $to instanceof steam_document)
		include( "wiki_version_compare.php" );
	else
		include( "bad_link.php" );
	exit;
	break;

	case ( $path[ 0 ] == "versions" && !empty($path[ 1 ]) && $path[ 2 ] === "recover"):
	//recover a previous version
	$version_doc = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 1 ] );
	if(is_object($version_doc) && $version_doc instanceof steam_document && $wiki_doc != NULL)
		include( "wiki_recover_version.php" );
	else
		include( "bad_link.php" );
	exit;
	break;
	
	case ( $path[ 0 ] == "versions" && !empty($path[ 1 ]) && $path[ 2 ] === "deleteVersion"):
	//delete selected version
	$version_doc = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 1 ] );
	if(is_object($version_doc) && $version_doc instanceof steam_document && $wiki_doc != NULL)
		include( "wiki_version_delete.php" );
	else
		include( "bad_link.php" );
	exit;
	break;

	case ( $path[ 0 ] == "versions" && !empty($path[ 1 ])):
		//enter previous version
		$version_doc = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 1 ] );
		if(is_object($version_doc) && $version_doc instanceof steam_document )
		{
			define("OBJ_ID", $wiki_doc->get_id());
			include( "wiki_entry.php" );
		}
		else
			include( "bad_link.php" );
	exit;
	break;

	case ( $path[ 0 ] == "delete" ):
    // Delete a wiki document
		if ( $wiki_doc != null) {
    	if (@$_REQUEST["force_delete"]) {
    		
        // is deleted entry wiki startpage ?
        $entryName = $wiki_doc->get_name();
        $startpage = $wiki_container->get_attribute("OBJ_WIKI_STARTPAGE") . ".wiki";
        
        if ( $entryName == $startpage )
        	$wiki_container->set_attribute("OBJ_WIKI_STARTPAGE", "glossary");
    		
        lms_steam::delete($wiki_doc);
        // clean wiki cache (not used by wiki)
        $cache = get_cache_function( $wiki_container->get_id(), 600 );
        $cache->clean( "lms_wiki::get_items", $wiki_container->get_id() );
        $_SESSION[ "confirmation" ] = gettext( "Wiki entry deleted sucessfully");
                	
        // clean rsscache
		$rcache = get_cache_function( "rss", 600 );
		$feedlink = PATH_URL . "services/feeds/wiki_public.php?id=" . $wiki_container->get_id();
		$rcache->drop( "lms_rss::get_items", $feedlink );
        
        header( "Location: " . PATH_URL . "wiki/" . $wiki_container->get_id() . "/" );
    	} else {
    		$wiki_name = h( substr( $wiki_doc->get_name(), 0, -5 ) );
    		$content = new HTML_TEMPLATE_IT();
    		$content->loadTemplateFile( PATH_TEMPLATES . "wiki_delete.template.html" );
    		$content->setVariable( "LABEL_ARE_YOU_SURE", str_replace("%NAME", h($wiki_name), gettext( "Are you sure you want to delete the wiki page '%NAME' ?" )) );
    		$content->setVariable( "LABEL_DELETE", gettext('Delete'));
    		$content->setVariable( "LABEL_OR", gettext('or'));
    		$content->setVariable( "LABEL_CANCEL", gettext('Cancel'));
    		$content->setVariable( "FORM_ACTION", $_SERVER[ "REQUEST_URI" ] );
    		$content->setVariable( "BACK_LINK", PATH_URL . "wiki/" . $wiki_doc->get_id() . "/" );
    		
    		//Breadcrumbs
    		$rootlink = lms_steam::get_link_to_root( $wiki_container );
    		(WIKI_FULL_HEADLINE) ?
			$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/"),
				array( "link" => PATH_URL . "wiki/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
				array( "link" => "", "name" => gettext( "Delete" ) )
				):
			$headline = array(
				array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/"),
				array( "link" => PATH_URL . "wiki/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
				array( "link" => "", "name" => gettext( "Delete" ) )
				);
    		$portal->set_page_main($headline, $content->get(), "");
			$portal->show_html();
    	}
    }

    exit;
    break;

	case ( ! ( stripos( $path[ 0 ], "deletecomment" ) === FALSE  ) && $wiki_doc ):
		$comment_id = substr( $path[ 0 ], 13 );
		define( "OBJ_ID", $wiki_container->get_id() );
		include( "comment_delete.php" );
		exit;
		break;

	case ( ! ( stripos( $path[ 0 ], "editcomment" ) === FALSE ) && $wiki_doc ):
		$comment_id = substr( $path[ 0 ], 11 );
		define( "OBJ_ID", $wiki_container->get_id() );
		include( "comment_edit.php" );
		exit;
		break;

	case ( stripos( $path[ 0 ], ".wiki" ) !== FALSE ):
		if ( $wiki_doc = $wiki_container->get_object_by_name( $path[ 0 ] ) )
		{
			define( "OBJ_ID", $wiki_doc->get_id() );
			include( "wiki_entry.php" );
			exit;
		}
		else
		{
			define( "OBJ_ID", $wiki_container->get_id() );
			$values[ "title" ] = str_replace( ".wiki", "", $path[ 0 ] );
			include( "wiki_post.php" );
			exit;
		}
		break;

	case ( $path[ 0 ] == "edit" && isset($wiki_doc) && $wiki_doc ):
		define( "OBJ_ID", $wiki_container->get_id() );
		include( "wiki_edit.php" );
		exit;
		break;

	case ( $path[ 0 ] == "edit" && (!isset($wiki_doc) || $wiki_doc == null )):
    define( "OBJ_ID", $wiki_container->get_id() );
    include( "wiki_edit_container.php" );
    exit;
    break;

	case ( isset($wiki_doc) && isset($wiki_doc) && $wiki_doc ):
		define( "OBJ_ID", $wiki_doc->get_id() );
		include( "wiki_entry.php" );
		exit;
		break;

	case ( $path[ 0 ] == "new" ):
		include( "wiki_post.php" );
		exit;
		break;
		
	case ( $path[ 0 ] == "mediathek" ):
		include( "wiki_mediathek.php" );
		exit;
		break;
		
		case ( $path[ 0 ] == "deletewiki" ):
		include( "wiki_delete.php" );
		exit;
		break;
		
	case ( $path[ 0 ] == "glossary" ):
		define( "OBJ_ID", $wiki_container->get_id() );
		include( "wiki_entries.php" );
		exit;
		break;
		
	default:
		$startpage = $wiki_container->get_attribute("OBJ_WIKI_STARTPAGE");
		
		if ( !$startpage || $startpage === "glossary" )
		{
			define( "OBJ_ID", $wiki_container->get_id() );
			include( "wiki_entries.php" );	
		}
		else
		{
			header("Location: " . PATH_URL . "wiki/" . $wiki_container->get_id() . "/" . $startpage . ".wiki");
		}
		
		exit;
}
?>