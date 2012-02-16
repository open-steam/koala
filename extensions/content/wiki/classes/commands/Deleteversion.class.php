<?php
namespace Wiki\Commands;
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );
require_once( PATH_LIB . "comments_handling.inc.php" );
require_once( PATH_LIB . "wiki_handling.inc.php" );

class Deleteversion extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject = $this->execute($frameResponseObject);
		return $frameResponseObject;
	}
	public function execute (\FrameResponseObject $frameResponseObject) {
		//CODE FOR ALL COMMANDS OF THIS PAKAGE END
		$user = \lms_steam::get_current_user();

		// Disable caching
		// TODO: Work on cache handling. An enabled cache leads to bugs
		// if used with the wiki.
		\CacheSettings::disable_caching();

		if ( ! $wiki_container = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $this->id ) )
		{
			include( "bad_link.php" );
			exit;
		}
		if ( ! $wiki_container instanceof \steam_container )
		{
			$wiki_doc = $wiki_container;
			$wiki_container = $wiki_doc->get_environment();
			if ( $wiki_doc->get_attribute( DOC_MIME_TYPE ) != "text/wiki" )
			{
				include( "bad_link.php" );
				exit;
			}
		}
		//CODE FOR ALL COMMANDS OF THIS PAKAGE END
		$version_doc = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $this->params[1] );

		$id = $this->params[1];

		if ( $id != null )
		{
			$doc = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $id );
			$parent_wiki = $doc->get_attribute("OBJ_VERSIONOF");
			$all_versions = $parent_wiki->get_attribute("DOC_VERSIONS");

			//user authorized ?
			$current_user = \lms_steam::get_current_user();
			$author = $doc->get_attribute("DOC_USER_MODIFIED");

			if ( $current_user->get_name() !== $author->get_attribute("OBJ_NAME") )
			{
				//TODO: Error Message
				header( "Location: " . PATH_URL . "wiki/viewentry/" . $parent_wiki->get_id() . "/versions/" );
				exit;
			}

			$keys = array_keys( $all_versions );
			sort( $keys );
			$new_array = array();
			$new_key = 1;

			foreach ( $keys as $key )
			{
				$version = $all_versions[ $key ];
				if ( !( $version instanceof \steam_document ) || $version->get_id() == $doc->get_id() ) continue;
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

			\lms_steam::delete( $doc );
				
			// clean wiki cache (not used by wiki)
			$cache = get_cache_function( $doc->get_id(), 600 );
			$cache->clean( "lms_wiki::get_items", $doc->get_id() );
			$_SESSION[ "confirmation" ] = gettext( "Wiki entry deleted sucessfully");
			header( "Location: " . PATH_URL . "wiki/versionoverview/" . $parent_wiki->get_id()  );
			die;
		}

	}
}
?>