<?php
namespace Weblog\Commands;
class Category extends \AbstractCommand implements \IFrameCommand {

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
		//$portal = \lms_portal::get_instance();
		//$portal->initialize( GUEST_NOT_ALLOWED );
		$user = \lms_steam::get_current_user();

		//$path = $request->getPath();
		$STEAM = $GLOBALS["STEAM"];

		$weblogId = $this->id;

		$weblog = \steam_factory::get_object( $STEAM->get_id(), $weblogId ) ;
		//if ( ! $weblog = steam_factory::get_object( $STEAM->get_id(), $_GET[ "id" ] ) )
		//{
		//	include( "bad_link.php" );
		//	exit;
		//}
		$category = $weblog;
		if ( ! $weblog instanceof \steam_calendar )
		{
			if ( $weblog instanceof \steam_container )
			{
				$category = $weblog;
				$categories = $category->get_environment();
				$weblog = new \steam_weblog( $GLOBALS[ "STEAM" ]->get_id(), $categories->get_environment()->get_id() );
			}
			elseif ( $weblog instanceof \steam_date )
			{
				$date = $weblog;
				$weblog = new \steam_weblog( $GLOBALS[ "STEAM" ]->get_id(), $date->get_environment()->get_id() );
			}
			else
			{
				include( "bad_link.php" );
				exit;
			}
		}
		else
		{

			$weblog = new \steam_weblog( $GLOBALS[ "STEAM" ]->get_id(), $weblogId );
			defined("OBJ_ID") or define( "OBJ_ID",	$weblogId );
			if ( ! $weblog->check_access_read( $user ) )
			{
				throw new \Exception( "No rights to view this.", E_USER_RIGHTS );
			}
		}
		$weblog_html_handler = new \lms_weblog( $weblog );

		$weblog_html_handler->set_menu( "category" );
		$weblog_html_handler->set_widget_categories();
		$weblog_html_handler->set_widget_archive( 5 );

		$link_objects = $category->get_inventory( CLASS_LINK );

		$no_entries = count( $link_objects );
		$end = $no_entries;
		$start = 0;

		$date_objects = array();
		for( $i = $start; $i < $end ; $i++ )
		{
			$date_objects[ ] = $link_objects[ $i ]->get_source_object();
		}

		usort( $date_objects, "sort_dates" );   //sort_dates defined in steam_calendar.class

		$weblog_html_handler->print_entries( $date_objects );
		//TODO: Login korrekt ersetzen
		$login = "";
	//	$portal->set_rss_feed( PATH_URL . "services/feeds/weblog_public.php?id=" . OBJ_ID , gettext( "Feed" ), str_replace( "%l", $login, gettext( "Subscribe to this forum's Newsfeed" ) ) );

		$rootlink = \lms_steam::get_link_to_root( $weblog );
		$headline = array(
		$rootlink[0],
		$rootlink[1],
		array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
		array( "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/"),
		array( "link" => "", "name" => h( $category->get_name() ) )
		);




		/*$portal->set_page_main(
		$headline,
		$weblog_html_handler->get_html()
		);
		return $portal->get_html();
	*/
		$frameResponseObject->setHeadline($headline);
		$widget = new \Widgets\RawHtml();
		$widget->setHtml($weblog_html_handler->get_html());
		$frameResponseObject->addWidget($widget);
		return $frameResponseObject;
	}
}

?>