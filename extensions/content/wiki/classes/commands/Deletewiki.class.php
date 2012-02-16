<?php
namespace Wiki\Commands;
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );
require_once( PATH_LIB . "comments_handling.inc.php" );
require_once( PATH_LIB . "wiki_handling.inc.php" );

class Deletewiki extends \AbstractCommand implements \IFrameCommand {

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
		//CODE FOR ALL COMMANDS OF THIS PACKAGE START
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
		//CODE FOR ALL COMMANDS OF THIS PACKAGE END

		$object = $wiki_container;
		$user    = \lms_steam::get_current_user();

		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && $object->check_access_write( $user ) )
		{
			$values = $_POST[ "values" ];
			if ( $values[ "delete" ] )
			{
				$_SESSION[ "confirmation" ] = str_replace("%NAME", h($object->get_name()), gettext( "The wiki '%NAME' has been deleted." ));
				$workroom = $object->get_environment();
				\lms_steam::delete( $object );

				$wid = $object->get_id();
				// Clean Cache for the deleted wiki
				require_once( "Cache/Lite.php" );
				$cache = new \Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
				$cache = get_cache_function(  $wid, 600 );
				$cache->drop( "lms_steam::get_items",  $wid );
				// Handle Related Cache-Data
				require_once( "Cache/Lite.php" );
				$cache = new \Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
				$cache->clean(  $wid );
				// clean wiki cache (not used by wiki)
				$fcache = get_cache_function( $object->get_id(), 600 );
				$fcache->drop( "lms_wiki::get_items", $object->get_id() );

				// Clean communication summary cache für the group/course
				if ( is_object( $workroom ) )
				{
					$cache = get_cache_function( \lms_steam::get_current_user()->get_name(), 600 );
					$cache->drop("lms_steam::get_inventory_recursive", $workroom->get_id(), CLASS_CONTAINER | CLASS_ROOM, array( "OBJ_TYPE", "WIKI_LANGUAGE" ) );
					$cache->drop( "lms_steam::get_group_communication_objects", $workroom->get_id(), CLASS_MESSAGEBOARD | CLASS_CALENDAR | CLASS_CONTAINER | CLASS_ROOM );

					// clean rsscache
					$rcache = get_cache_function( "rss", 600 );
					$feedlink = PATH_URL . "services/feeds/wiki_public.php?id=" . $wid;
					$rcache->drop( "lms_rss::get_items", $feedlink );
				}

				header( "Location: " . $values[ "return_to" ] );
				exit;
			}
		}
		$content = \Wiki::getInstance()->loadTemplate("object_delete.template.html");
		//$content = new HTML_TEMPLATE_IT();
		//$content->loadTemplateFile( PATH_TEMPLATES . "object_delete.template.html" );
		if ( $object->check_access_write( $user ) )
		{
			$content->setVariable( "LABEL_ARE_YOU_SURE", str_replace("%NAME", h($object->get_name()), gettext( "Are you sure you want to delete the wiki '%NAME' ?" )) );

			$rootlink = \lms_steam::get_link_to_root( $object );
			$content->setVariable( "DELETE_BACK_LINK", $rootlink[1]["link"] . "communication/");

			$content->setCurrentBlock( "BLOCK_DELETE" );
			$content->setVariable( "FORM_ACTION", $_SERVER[ "REQUEST_URI" ] );
			$content->setVariable( "LABEL_DELETE_IT", gettext( "yes, delete it" ) );
			$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
			$content->setVariable( "LABEL_RETURN", gettext( "back" ) );
			$content->parse( "BLOCK_DELETE" );
		}
		else
		{
			$content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "You have no rights to delete this wiki!" ) );
		}
		$content->setVariable( "TEXT_INFORMATION", gettext( "The Wiki and all its entries will be deleted." ) );
		$creator = $object->get_creator();
		$creator_data = $creator->get_attributes( array( "USER_FULLNAME", "USER_FIRSTNAME", "OBJ_ICON" ) );
		$content->setVariable( "LABEL_FROM_AND_AGO", str_replace( "%N", "<a href=\"" . PATH_URL . "/user/" . $creator->get_name() . "/\">" . h($creator_data[ "USER_FIRSTNAME" ]) . " " . h($creator_data[ "USER_FULLNAME" ]) . "</a>", gettext( "by %N" ) ) . "," . how_long_ago( $object->get_attribute( "OBJ_CREATION_TIME" ) )  );

		$icon = $creator_data[ "OBJ_ICON" ];
		if ( $icon instanceof \steam_object )
		{
			$icon_id = $icon->get_id();
		}
		else
		{
			$icon_id = 0;
		}

		$content->setVariable( "ICON_SRC", PATH_URL . "get_document.php?id=" . $icon_id );

		$rootlink = \lms_steam::get_link_to_root( $wiki_container );
		(WIKI_FULL_HEADLINE) ?
		$headline = array(
		$rootlink[0],
		$rootlink[1],
		array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
		array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/index/" . $wiki_container->get_id() . "/"),
		array( "name" => gettext("Delete wiki"), "link" => "" )
		):
		$headline = array(
		array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/index/" . $wiki_container->get_id() . "/"),
		array( "name" => gettext("Delete wiki"), "link" => "" ));
		
		$frameResponseObject->setHeadline($headline);
		$widget = new \Widgets\RawHtml();
		$widget->setHtml($content->get());
		$frameResponseObject->addWidget($widget);
		return $frameResponseObject;
		/*
		 $portal->set_page_main(
		 $headline,
		 $content->get(),
		 ""
		 );
		 $portal->show_html();*/
	}
}
?>