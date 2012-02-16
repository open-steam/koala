<?php
namespace Weblog\Commands;
class Entrydelete extends \AbstractCommand implements \IFrameCommand {

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
			define( "OBJ_ID",	$weblogId );
			if ( ! $weblog->check_access_read( $user ) )
			{
				throw new \Exception( "No rights to view this.", E_USER_RIGHTS );
			}
		}
		if(!isset($date))
		throw new \Exception("variable date is not set.");
		//$date = $weblog;
		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && $date->check_access_write( $user ) )
		{
			$values = $_POST[ "values" ];
			if ( $values[ "delete" ] )
			{
				require_once( "Cache/Lite.php" );
				$cache = new \Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
				$cache->clean( $weblog->get_id() );
				$cache->clean( $date->get_id() );

				$trashbin = $GLOBALS["STEAM"]->get_current_steam_user();
				if (is_object($trashbin)) {
					$date->move($trashbin);
				}
				else {
					$date->delete();
				}

			}
			header( "Location: " . $values[ "return_to" ] );
			exit;
		}

		$content = \Weblog::getInstance()->loadTemplate("weblog_entry_delete.template.html");
		//$content = new HTML_TEMPLATE_IT();
		//$content->loadTemplateFile( PATH_TEMPLATES . "weblog_entry_delete.template.html" );
		$content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "Are you sure you want to delete this entry?" ) );

		$content->setVariable( "TEXT_COMMENT", get_formatted_output( $date->get_attribute( "DATE_DESCRIPTION" ) ) );
		$creator = $date->get_creator();
		$creator_data = $creator->get_attributes( array( "USER_FULLNAME", "USER_FIRSTNAME", "OBJ_ICON" ) );
		$content->setVariable( "LABEL_FROM_AND_AGO", str_replace( "%N", "<a href=\"" . PATH_URL . "/user/index/" . $creator->get_name() . "/\">" . h($creator_data[ "USER_FIRSTNAME" ]) . " " . h($creator_data[ "USER_FULLNAME" ]) . "</a>", gettext( "by %N" ) ) . "," . how_long_ago( $date->get_attribute( "OBJ_CREATION_TIME" ) )  );

		$content->setVariable( "LABEL_DELETE_IT", gettext( "yes, delete it" ) );
		$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
		$content->setVariable( "LABEL_RETURN", gettext( "back" ) );

		$content->setVariable( "ICON_SRC", PATH_URL . "get_document.php?id=" . $creator_data[ "OBJ_ICON" ]->get_id() );

		$rootlink = \lms_steam::get_link_to_root( $weblog );
		$headline = array(
		$rootlink[0],
		$rootlink[1],
		array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
		array( "link" => "", "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/index/" . $weblog->get_id() . "/"),
		array( "name" => 	str_replace( "%NAME", h($date->get_attribute( "DATE_TITLE" )), gettext( "Delete '%NAME'?" )))
		);


		/*$portal->set_page_main(
		 $headline,
		 $content->get(),
		 ""
		 );
		 return $portal->get_html();*/
		$frameResponseObject->setHeadline($headline);
		$widget = new \Widgets\RawHtml();
		$widget->setHtml($content->get());
		$frameResponseObject->addWidget($widget);
		return $frameResponseObject;

	}
}

?>