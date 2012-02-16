<?php

namespace Weblog\Commands;
class Categorycreate extends \AbstractCommand implements \IFrameCommand {

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
//		$portal = \lms_portal::get_instance();
//		$portal->initialize( GUEST_NOT_ALLOWED );
		$user = \lms_steam::get_current_user();
		
//		$path = $request->getPath();
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
			if (!defined("OBJ_ID")) define( "OBJ_ID", $weblogId );
		
			//define( "OBJ_ID",	$weblogId );
			if ( ! $weblog->check_access_read( $user ) )
			{
				throw new \Exception( "No rights to view this.", E_USER_RIGHTS );
			}
		}
		$content = \Weblog::getInstance()->loadTemplate("weblog_category_create.template.html");
		//$content = new HTML_TEMPLATE_IT();
		//$content->loadTemplateFile( PATH_TEMPLATES . "weblog_category_create.template.html" );
		$headline = gettext( "Create a new weblog category" );
		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
		{
			$values = $_POST[ "values" ];
			if ( ! empty( $values[ "preview_comment" ] ) )
			{
				$content->setCurrentBlock( "BLOCK_PREVIEW_COMMENT" );
				$content->setVariable( "TEXT_DSC", get_formatted_output( $values[ "desc" ] )  );
				$content->setVariable( "LABEL_PREVIEW_YOUR_COMMENT", gettext( "Preview your description" ) );
				$template->parse( "BLOCK_PREVIEW_COMMENT" );
				$headline = gettext( "Change it?" );
			}
			if ( ! empty( $values[ "save" ] ) && ! empty( $values[ "desc" ] ) && ! empty( $values[ "title" ] ) )
			{
				
				$category = $weblog->create_category( $values[ "title" ], $values[ "desc" ] );
				header( "Location: " . PATH_URL . "weblog/index/" . $category->get_id() . "/" );
				exit;
			}
			else
			{
				// PREVIEW
				$content->setCurrentBlock( "BLOCK_PREVIEW" );
				$content->setVariable( "LABEL_PREVIEW_EDIT", gettext( "Preview the description" ) );
				$content->setVariable( "PREVIEW_EDIT", get_formatted_output( $values[ "desc" ] ) );
				$content->parse( "BLOCK_PREVIEW" );
				$headline = gettext( "Change it?" );
				$content->setVariable( "TEXT_DSC", h($values[ "desc" ]) );
				$content->setVariable( "TITLE_COMMENT", h($values[ "title" ]) );
			}
		}

		$backlink = ( empty( $_POST["values"]["return_to"] ) ) ? $_SERVER[ "HTTP_REFERER" ] : $_POST[ "values" ][ "return_to" ];
		$content->setVariable( "BACK_LINK", $backlink );

		$content->setVariable( "POST_NEW_TOPIC_TEXT", $headline );
		$content->setVariable( "GREETING", str_replace( "%n", $_SESSION["LMS_USER"]->get_forename(), gettext( "Hi %n!" ) ) );
		$content->setVariable( "HELP_TEXT", gettext( "By categories, you can find your entries easier than by date." ) );
		$content->setVariable( "HINT_TEXT", gettext( "Structuring your weblog entries by means of categories makes it much more simple for your readers to find what they are looking for!" ) );
		$content->setVariable( "LABEL_TITLE", gettext( "Title" ) );
		$content->setVariable( "LABEL_DESC", gettext( "Description") );

		$content->setVariable( "LABEL_BB_BOLD", gettext( "B" ) );
		$content->setVariable( "HINT_BB_BOLD", gettext( "boldface" ) );
		$content->setVariable( "LABEL_BB_ITALIC", gettext( "I" ) );
		$content->setVariable( "HINT_BB_ITALIC", gettext( "italic" ) );
		$content->setVariable( "LABEL_BB_UNDERLINE", gettext( "U" ) );
		$content->setVariable( "HINT_BB_UNDERLINE", gettext( "underline" ) );
		$content->setVariable( "LABEL_BB_STRIKETHROUGH", gettext( "S" ) );
		$content->setVariable( "HINT_BB_STRIKETHROUGH", gettext( "strikethrough" ) );
		$content->setVariable( "LABEL_BB_IMAGE", gettext( "IMG" ) );
		$content->setVariable( "HINT_BB_IMAGE", gettext( "image" ) );
		$content->setVariable( "LABEL_BB_URL", gettext( "URL" ) );
		$content->setVariable( "HINT_BB_URL", gettext( "web link" ) );
		$content->setVariable( "LABEL_BB_MAIL", gettext( "MAIL" ) );
		$content->setVariable( "HINT_BB_MAIL", gettext( "email link" ) );

		$content->setVariable( "LABEL_PREVIEW", gettext( "Preview" ) );
		$content->setVariable( "LABEL_SAVE_CHANGES", gettext( "Save changes" ) );
		$content->setVariable( "LABEL_RETURN", gettext( "back" ) );

		$rootlink = \lms_steam::get_link_to_root( $weblog );
		$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/index/" . $weblog->get_id() . "/"),
				array( "link" => "", "name" => gettext( "Create new category" ) )
			);
		

		/*$portal->set_page_main(
		$headline,
		$content->get()
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