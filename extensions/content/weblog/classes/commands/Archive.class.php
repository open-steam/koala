<?php
namespace Weblog\Commands;
class Archive extends \AbstractCommand implements \IFrameCommand {

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
		$timestamp = $this->params[1];
		//$portal = \lms_portal::get_instance();
		//$portal->initialize( GUEST_NOT_ALLOWED );
		$user = \lms_steam::get_current_user();

		//$path = $request->getPath();
		$STEAM = $GLOBALS["STEAM"];

		$weblogId = $this->params[0];

		$weblog = \steam_factory::get_object( $STEAM->get_id(), $weblogId ) ;
		//if ( ! $weblog = steam_factory::get_object( $STEAM->get_id(), $_GET[ "id" ] ) )
		//{
		//	include( "bad_link.php" );
		//	exit;
		//}

		if ( ! $weblog instanceof \steam_calendar )
		{
			if ( $weblog instanceof \steam_container )
			{
				$category = $weblog;
				$categories = $category->get_environment();
				$weblog = new \steam_weblog( $GLOBALS[ "STEAM" ], $categories->get_environment()->get_id() );
			}
			elseif ( $weblog instanceof \steam_date )
			{
				$date = $weblog;
				$weblog = new \steam_weblog( $GLOBALS[ "STEAM" ], $date->get_environment()->get_id() );
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
			define( "OBJ_ID",	$weblogId );
			if ( ! $weblog->check_access_read( $user ) )
			{
				throw new \Exception( "No rights to view this.", E_USER_RIGHTS );
			}
		}
		$weblog_html_handler = new \lms_weblog( $weblog );

		$weblog_html_handler->set_widget_categories();
		$weblog_html_handler->set_widget_archive( );

		$first_of_month = $timestamp;
		$last_of_month = strtotime( "-1 day", strtotime( "+1 month", $first_of_month ) );

		$date_objects = $weblog->get_date_objects( (int) $first_of_month, (int) $last_of_month );
		// $date_objects = $weblog->get_date_objects( );
		$no_entries = count( $date_objects );

		//TODO: PAGINATOR REINKNALLEN
		$pageIterator = \lms_portal::get_paginator(5, $no_entries, gettext( "%TOTAL entries in this archive." ) );
		//$content->setVariable("PAGEITERATOR", $pageIterator["html"]);
		$start = $pageIterator["startIndex"];
		//$start = $portal->set_paginator( 5, $no_entries, gettext( "%TOTAL entries in this archive." ) );
		$end = ( $start + 5 > $no_entries ) ? $no_entries : $start + 5;

		$weblog_html_handler->print_entries( $date_objects, TRUE);

		//$portal->set_rss_feed( PATH_URL . "services/feeds/weblog_public.php?id=" . OBJ_ID , gettext( "Feed" ), str_replace( "%l", (isset($login))?$login:'', gettext( "Subscribe to this forum's Newsfeed" ) ) );

		$rootlink = \lms_steam::get_link_to_root( $weblog );
		$headline = array(
		$rootlink[0],
		$rootlink[1],
		array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
		array( "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/"),
		array( "link" => "", "name" => strftime( "%B %G", $timestamp ) )
		);

		//$portal->set_page_main(
		//	$headline,
		//	$weblog_html_handler->get_html()
		//);
		$frameResponseObject->setHeadline($headline);
		$widget = new \Widgets\RawHtml();
		$widget->setHtml($weblog_html_handler->get_html());
		$frameResponseObject->addWidget($widget);
		return $frameResponseObject;

	}
}

?>