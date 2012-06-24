<?php
$id = $path[1];

if ( $id != null )
{
	$doc = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $id );
	$parent_wiki = $doc->get_attribute("OBJ_VERSIONOF");
	$all_versions = $parent_wiki->get_attribute("DOC_VERSIONS");
	
	//user authorized ?
	$current_user = lms_steam::get_current_user();
	$author = $doc->get_attribute("DOC_USER_MODIFIED");
	
	if ( $current_user->get_name() !== $author->get_attribute("OBJ_NAME") )
	{
		//TODO: Error Message
		header( "Location: " . PATH_URL . "wiki/" . $parent_wiki->get_id() . "/versions/" );
		exit;
	}
	
	$keys = array_keys( $all_versions );
	sort( $keys );
	$new_array = array();
	$new_key = 1;
	
	foreach ( $keys as $key )
	{
		$version = $all_versions[ $key ];
		if ( !( $version instanceof steam_document ) || $version->get_id() == $doc->get_id() ) continue;
		$version->set_attribute( "DOC_VERSION", $new_key );
		$new_array[ $new_key ] = $version;
		$new_key++;
	}
	
	if ( empty( $new_array ) )
	{
		$parent_wiki->set_attribute( "DOC_VERSIONS", 0 );
		$parent_wiki->set_attribute( "DOC_VERSION", 1 );
	}
	else
	{
		$parent_wiki->set_attribute( "DOC_VERSIONS", $new_array );
		$parent_wiki->set_attribute( "DOC_VERSION", count( $new_array ) + 1 );
	}
	
	lms_steam::delete( $doc );
			
	// clean wiki cache (not used by wiki)
	$cache = get_cache_function( $doc->get_id(), 600 );
	$cache->clean( "koala_wiki::get_items", $doc->get_id() );
	$_SESSION[ "confirmation" ] = gettext( "Wiki entry deleted sucessfully");
	header( "Location: " . PATH_URL . "wiki/" . $parent_wiki->get_id() . "/versions/" );
}
?>