<?php

include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user   = lms_steam::get_current_user();

$path = url_parse_rewrite_path( $_GET[ "path" ] );

$document = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "id" ] );

while ( $document instanceof steam_link )
{
	$document = $document->get_source_object();
}

define( "OBJ_ID", $document->get_id() );
$env      = $document->get_environment();

if ( !($document instanceof steam_document) && ! ($document instanceof steam_docextern) && ! ($document instanceof steam_container) )
{
	include( "bad_link.php" );
	exit;
}

switch( TRUE )
{
	case ( ! ( stripos( $path[ 0 ], "editcomment" ) === FALSE ) ):
		$comment_id = substr( $path[ 0 ], 11 );
		include( "comment_edit.php" );
	break;
	case ( ! ( stripos( $path[ 0 ], "deletecomment" ) === FALSE ) ):
		$comment_id = substr( $path[ 0 ], 13 );
		include( "comment_delete.php" );
	break;
	case ( ($document instanceof steam_document) && (! ( stripos( $path[ 0 ], "edit" ) === FALSE )) ):
		$comment_id = substr( $path[ 0 ], 11 );
		include( "document_edit.php" );
	break;
	case ( ($document instanceof steam_document) && (! ( stripos( $path[ 0 ], "delete" ) === FALSE) ) ):
		include( "document_delete.php" );
	break;
	case ( ($document instanceof steam_docextern) && (! ( stripos( $path[ 0 ], "edit" ) === FALSE )) ):
		$comment_id = substr( $path[ 0 ], 11 );
    $docextern = $document;
		include( "docextern_edit.php" );
	break;
	case ( ($document instanceof steam_docextern) && (! ( stripos( $path[ 0 ], "delete" ) === FALSE) ) ):
    $docextern = $document;
		include( "docextern_delete.php" );
	break;
	case ( ($document instanceof steam_container) && (! ( stripos( $path[ 0 ], "edit" ) === FALSE )) ):
		$comment_id = substr( $path[ 0 ], 11 );
    $container = $document;
		include( "container_edit.php" );
	break;
	case ( ($document instanceof steam_container) && (! ( stripos( $path[ 0 ], "delete" ) === FALSE) ) ):
    $container = $document;
		include( "container_delete.php" );
	break;
	default:
		include_once( PATH_LIB . "comments_handling.inc.php" );
		$comments_html = get_comment_html( $document, PATH_URL . "doc/" . $document->get_id() );
		include( "document_read.php" );
	break;
}
?>
