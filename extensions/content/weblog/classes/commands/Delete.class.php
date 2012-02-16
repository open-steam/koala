<?php
namespace Weblog\Commands;
class Delete extends \AbstractCommand implements \IFrameCommand {

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
		if (!defined("OBJ_ID")) define( "OBJ_ID", $weblog->get_id() );
		$user = \lms_steam::get_current_user();

		$STEAM = $GLOBALS["STEAM"];

		$weblogId = $this->id;


		$weblog = \steam_factory::get_object( $STEAM->get_id(), $weblogId ) ;
		if (!defined("OBJ_ID")) define( "OBJ_ID", $weblog->get_id() );
		/*if ( ! $weblog = \steam_factory::get_object( $STEAM->get_id(), $_GET[ "id" ] ) )
		{	
			include( "bad_link.php" );
			exit;
		}*/

		if ( ! $weblog instanceof \steam_calendar )
		{
			if ( $weblog instanceof \steam_container )
			{
				$category = $weblog;
				$categories = $category->get_environment();
				$weblog = new \steam_weblog( (String)$GLOBALS[ "STEAM" ]->get_id(), $categories->get_environment()->get_id() );
			}
			elseif ( $weblog instanceof \steam_date )
			{
				$date = $weblog;
				$weblog = new \steam_weblog( (String)$GLOBALS[ "STEAM" ]->get_id(), $date->get_environment()->get_id() );
			}
			else
			{
				
				include( "bad_link.php" );
				exit;
			}
		}
		else
		{

			$weblog = new \steam_weblog( (String)$GLOBALS[ "STEAM" ]->get_id(), $weblogId );
			//define( "OBJ_ID",	$weblogId );
			if ( ! $weblog->check_access_read( $user ) )
			{
				throw new \Exception( "No rights to view this.", E_USER_RIGHTS );
			}
		}
		$object = $weblog;
		$user    = \lms_steam::get_current_user();

		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && $object->check_access_write( $user ) )
		{
			$values = $_POST[ "values" ];
			if ( $values[ "delete" ] )
			{
				$_SESSION[ "confirmation" ] = str_replace("%NAME", h($object->get_name()), gettext( "The weblog '%NAME' has been deleted." ));
				$workroom = $object->get_environment();
				\lms_steam::delete( $object );
				// Clean Cache for the deleted Weblog
				require_once( "Cache/Lite.php" );
				$cache = new \Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
				$cache = get_cache_function( OBJ_ID, 600 );
				$cache->drop( "lms_steam::get_annotations", OBJ_ID );
				// Handle Related Cache-Data
				require_once( "Cache/Lite.php" );
				$cache = new \Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
				$cache->clean( OBJ_ID );
				// clean weblog cache (not used by weblog)
				// $fcache = get_cache_function( OBJ_ID, 600 );
				//$fcache->drop( "lms_forum::get_discussions",  OBJ_ID );
				// clean cache for Weblog RSS Feed for the Comments (not used by weblog)
				// $fcache->drop( "lms_steam::get_annotations", OBJ_ID );
				// clean rsscache
				$rcache = get_cache_function( "rss", 600 );
				$feedlink = PATH_URL . "services/feeds/weblog_public.php?id=" . OBJ_ID;
				$rcache->drop( "lms_rss::get_items", $feedlink );
				// Clean communication summary cache für the group/course
				if (is_object($workroom)) {
					$cache = get_cache_function( \lms_steam::get_current_user()->get_name(), 600 );
					$cache->drop( "lms_steam::get_inventory_recursive", $workroom->get_id(), CLASS_CALENDAR, array( "WEBLOG_LANGUAGE" ) );
					$cache->drop( "lms_steam::get_group_communication_objects", $workroom->get_id(), CLASS_MESSAGEBOARD | CLASS_CALENDAR | CLASS_CONTAINER | CLASS_ROOM );
				}

				header( "Location: " . $values[ "return_to" ] );
				exit;
			}

		}
		$content = \Weblog::getInstance()->loadTemplate("object_delete.template.html");
		//$content = new HTML_TEMPLATE_IT();
		//$content->loadTemplateFile( PATH_TEMPLATES . "object_delete.template.html" );
		if ( $object->check_access_write( $user ) )
		{
			$content->setVariable( "LABEL_ARE_YOU_SURE", str_replace("%NAME", h($object->get_name()), gettext( "Are you sure you want to delete the weblog '%NAME' ?" )) );

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
			$content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "You have no rights to delete this weblog!" ) );
		}
		$content->setVariable( "TEXT_INFORMATION", gettext("The weblog and all its entries will be deleted.") );
		$creator = $object->get_creator();
		$creator_data = $creator->get_attributes( array( "USER_FULLNAME", "USER_FIRSTNAME", "OBJ_ICON" ) );
		$content->setVariable( "LABEL_FROM_AND_AGO", str_replace( "%N", "<a href=\"" . PATH_URL . "/user/" . $creator->get_name() . "/\">" . h($creator_data[ "USER_FIRSTNAME" ]) . " " . h($creator_data[ "USER_FULLNAME" ]) . "</a>", gettext( "by %N" ) ) . "," . how_long_ago( $object->get_attribute( "OBJ_CREATION_TIME" ) )  );

		$icon = $creator_data[ "OBJ_ICON" ];
		if ( $icon instanceof steam_object )
		{
			$icon_id = $icon->get_id();
		}
		else
		{
			$icon_id = 0;
		}

		$content->setVariable( "ICON_SRC", PATH_URL . "get_document.php?id=" . $icon_id );

		$rootlink = \lms_steam::get_link_to_root( $object );
		$headline = array( $rootlink[0], $rootlink[1], array("link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication"))  ,  array( "link" => "", "name" =>  h($object->get_name()), "link" => PATH_URL . "weblog/" . $object->get_id() . "/"), array("name" => gettext("Delete weblog")) );
		$frameResponseObject->setHeadline($headline);
		$widget = new \Widgets\RawHtml();
		$widget->setHtml($content->get());
		$frameResponseObject->addWidget($widget);
		return $frameResponseObject;
		/*$portal->set_page_main(
		$headline,
		$content->get(),
		""
		);
		$portal->show_html();*/

	}
}
?>