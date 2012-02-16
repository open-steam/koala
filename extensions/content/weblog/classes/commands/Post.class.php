<?php
namespace Weblog\Commands;
class Post extends \AbstractCommand implements \IFrameCommand {

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
		//$portal->set_confirmation();

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
			define( "OBJ_ID",	$weblogId );
			if ( ! $weblog->check_access_read( $user ) )
			{
				throw new \Exception( "No rights to view this.", E_USER_RIGHTS );
			}
		}

		$content = \Weblog::getInstance()->loadTemplate("weblog_post.template.html");
		//$content = new HTML_TEMPLATE_IT();
		//$content->loadTemplateFile( PATH_TEMPLATES . "weblog_post.template.html" );
		$headline = gettext( "Post a new entry" );
		
		if ( $_SERVER[ "REQUEST_METHOD" ] == "GET" )
		{
			$content->setVariable( "VALUE_BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
		}
		else
		{
			$values = $_POST[ "values" ];
			if ( get_magic_quotes_gpc() ) {
				if ( !empty( $values['title'] ) ) $values['title'] = stripslashes( $values['title'] );
				if ( !empty( $values['body'] ) ) $values['body'] = stripslashes( $values['body'] );
			}
			if ( ! empty( $values[ "save" ] ) )
			{
				$problem = "";
				$hint    = "";
				if ( empty( $values[ "title" ] ) )
				{
					$problem .= gettext( "The title is missing." ) . "&nbsp;";
					$hint    .= gettext( "Please add the missing values." );
				}
				if ( empty( $values[ "body" ] ) )
				{
					$problem .= gettext( "There is no message for your readers." ) . "&nbsp;" ;
					$hint    .= gettext( "Please write your post into the text area." );
				}
				if ( ! empty( $values[ "category" ] ) && $values[ "category" ] != 0 )
				{
					$category = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $values[ "category" ] );
					if ( ! $category instanceof \steam_container )
					{
						throw new \Exception( "Not a valid category: " . $values[ "category" ] );
					}
				}
				else
				{
					$category = "";
				}
				if ( ! $timestamp = strtotime( $values[ "date" ] . ":00" ) )
				{
					$problem .= gettext( "I cannot parse the date and time." );
					$hint .= gettext( "Please verify your date and time format" ) . ": YYYY-MM-DD HH:MM";
				}
				if ( strpos($values[ "title" ], "/" )) {
					if (!isset($problem)) $problem = "";
					$problem .= gettext("Please don't use the \"/\"-char in the subject of your post.");
				}
				if ( empty( $problem ) )
				{
					$new_entry = $weblog->create_entry( $values[ "title" ], $values[ "body" ], $category, array(), $timestamp );

					if ( $values[ "podcast" ] != 0  )
					{
						$new_entry->set_attribute(
					"DATE_PODCAST",
						$values[ "podcast" ]
						);
					}
					// Handle Related Cache-Data
					require_once( "Cache/Lite.php" );
					$cache = new \Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
					$cache->clean( OBJ_ID );
					// clean weblogcache
					$bcache = get_cache_function( $_GET[ "id" ], 600 );
					$bcache->drop( "lms_weblog::get_items", $_GET[ "id" ] );
					// clean rsscache of the weblog
					$feedlink = PATH_URL . "services/feeds/weblog_public.php?id=" . $_GET["id"];
					$rcache = get_cache_function( "rss", 600 );
					$rcache->drop( "lms_rss::get_items", $feedlink );

					header( "Location: " . PATH_URL . "weblog/index/" . $weblog->get_id() .  "/" );
					exit;
				}
				else
				{
					// TODO:THERE IS A PROBLEM
					//$portal->set_problem_description( $problem, $hint );
					$content->setVariable( "TEXT_COMMENT", h($values[ "body" ]) );
					$content->setVariable( "TITLE_COMMENT", h($values[ "title" ]) );
				}
			}
			else
			{
				// PREVIEW
				$content->setCurrentBlock( "BLOCK_PREVIEW" );
				$content->setVariable( "LABEL_PREVIEW_EDIT", gettext( "Preview the edit" ) );
				$content->setVariable( "PREVIEW_EDIT", get_formatted_output( $values[ "body" ] ) );
				$content->parse( "BLOCK_PREVIEW" );
				$headline =  gettext( "Change it?" );
				$content->setVariable( "TEXT_COMMENT", h($values[ "body" ]) );
				$content->setVariable( "TITLE_COMMENT", h($values[ "title" ]) );
			}
		}

		$backlink = ( empty( $_POST["values"]["return_to"] ) ) ? $_SERVER[ "HTTP_REFERER" ] : $_POST[ "values" ][ "return_to" ];
		$content->setVariable( "VALUE_BACK_LINK", $backlink );

		$content->setVariable( "POST_NEW_ENTRY_TEXT", $headline );
		$content->setVariable( "INFO_TEXT", "some hints about blogging" );
		$content->setVariable( "LABEL_DATE", gettext( "Date" ) );
		$content->setVariable( "INFO_FORMAT", gettext( "Format: YYYY-MM-DD HH:MM" ) );

		$date = ( empty( $values[ "date" ] ) ) ? strftime( "%Y-%m-%d %H:%M" ) : $values[ "date" ];
		$content->setVariable( "DATE_COMMENT", h($date)  );
		$content->setVariable( "LABEL_SUBJECT", gettext( "Subject" ) );
		$content->setVariable( "LABEL_YOUR_POST", gettext( "Your Post") );
		$content->setVariable( "LABEL_CATEGORY", gettext( "Category" ) );
		$content->setVariable( "LINK_NEW_CATEGORY", PATH_URL . "weblog/categorycreate/" . $weblog->get_id()  );
		$content->setVariable( "LABEL_NEW_CATEGORY", gettext( "Want to add a new category?" ) );
		$content->setVariable( "CAT_NO_SELECTION", gettext( "nothing selected" ) );
		$categories = $weblog->get_categories();
		$selection  = ( empty( $values[ "category" ] ) ) ? ((isset($_GET[ "category" ]))?$_GET[ "category" ]:'') : $values[ "category" ];
		foreach( $categories as $category )
		{
			$content->setCurrentBlock( "BLOCK_SELECT_CAT" );
			$content->setVariable( "VALUE_CAT", $category->get_id() );
			$content->setVariable( "LABEL_CAT", h($category->get_name()) );
			if (  $category->get_id() == $selection )
			{
				$content->setVariable( "CAT_SELECTED", 'selected="selected"' );
			}
			$content->parse( "BLOCK_SELECT_CAT" );
		}
		/*
		 $content->setVariable( "LABEL_PODCAST", gettext( "Podcast") );
		 $content->setVariable( "PODCAST_NO_SELECTION", gettext( "nothing selected" ) );
		 $content->setVariable( "LINK_UPLOAD_MULTIMEDIA", PATH_URL . "weblog/" . $weblog->get_id() . "/podcast/" );
		 $content->setVariable( "LABEL_UPLOAD_MULTIMEDIA", gettext( "Want to upload an audio or video file?" ) );
		 $files_in_podspace = $weblog->get_podspace()->get_inventory( CLASS_DOCUMENT );
		 foreach( $files_in_podspace as $file )
		 {
		 $content->setCurrentBlock( "BLOCK_MULTIMEDIA" );
		 $content->setVariable( "VALUE_MULTIMEDIA", $file->get_id() );
		 if ( ( $file->get_id() == $_GET[ "podcast" ] ) || ( $file->get_id() == $values[ "podcast" ] ) )
		 {
		 $content->setVariable( "MULTIMEDIA_SELECTED", 'selected="selected"' );
		 }
		 $content->setVariable( "LABEL_MULTIMEDIA", h($file->get_name()) );
		 $content->parse( "BLOCK_MULTIMEDIA" );
		 }
		 */
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
		//TODO:RSS-FEED
		//$portal->set_rss_feed( PATH_URL . "services/feeds/weblog_public.php?id=" . $weblog->get_id(), gettext( "Feed" ), str_replace( "%l", (isset($login))?$login:'', gettext( "Subscribe to this forum's Newsfeed" ) ) );

		$rootlink = \lms_steam::get_link_to_root( $weblog );
		$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/"),
				array( "link" => "", "name" => gettext( "New Post"))
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