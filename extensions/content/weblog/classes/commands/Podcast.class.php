<?php
namespace Weblog\Commands;
class Podcast extends \AbstractCommand implements \IFrameCommand {

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

	//	$path = $request->getPath();
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
			define( "OBJ_ID",	$weblogId );
			if ( ! $weblog->check_access_read( $user ) )
			{
				throw new \Exception( "No rights to view this.", E_USER_RIGHTS );
			}
		}
		$podspace = $weblog->get_podspace();

		if ( ! $podspace->check_access_write( $user ) )
		{
			throw new \Exception( $user->get_name() . " has no write acces on podspace " . $podscace->get_id(), E_USER_RIGHTS );
		}

		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
		{
			$values = $_POST[ "values" ];

			$problem = "";
			$hint = "";

			if ( ! empty( $values[ "file_id" ] ) )
			{
				// ALTER AN EXISTING DOCUMENT

			}
			else
			{
				// UPLOAD A NEW DOCUMENT
				if ( ! empty( $values[ "desc" ] ) )
				{
					$problem .= gettext( "Please describe the file." ) . " ";
					$hint    .= "";
				}
				$new_file = $podspace->upload( "FILE_TO_UPLOAD" );
			}
		}


		$content = \Weblog::getInstance()->loadTemplate("weblog_podcast.template.html");
		//$content = new HTML_TEMPLATE_IT();
		//$content->loadTemplatefile( PATH_TEMPLATES . "weblog_podcast.template.html" );

		$content->setVariable( "GREETING", str_replace( "%n", $_SESSION["LMS_USER"]->get_forename(), gettext( "Hi %n!" ) ) );

		if ( empty( $_GET[ "file" ] ) )
		{
			$content->touchBlock( "BLOCK_UPLOAD" );
		}

		$help_text = "<b>". gettext( "What is podcasting?" ) . "</b> "
		. gettext( "Podcasting is the method of distributing multimedia files, such as audio programs, over the Internet using syndication feeds, for playback on mobile decices and personal computers." );
		$help_text .= "<br/><br/>" . gettext( "The podcast is also available as a webfolder:" ) . " <a href=\"https://" . STEAM_SERVER . $podspace->get_path() . "\">WebDAV Mountpoint</a>";
		$content->setVariable( "HELP_TEXT", $help_text );
		$content->setVariable( "YOUR_PODSCPACE_TEXT", gettext( "Your Podspace" ) );
		$content->setVariable( "UPLOAD_NEW_FILE_TEXT", gettext( "Upload a multimedia file" ) );
		$content->setVariable( "PODSPACE", $podspace->get_id() );
		$content->setVariable( "LABEL_FILE", gettext( "File" ) );
		$content->setVariable( "LABEL_DESCRIPTION", gettext( "Description" ) );
		$content->setVariable( "LABEL_KEYWORDS", gettext( "Keywords") );
		$content->setVariable( "LABEL_SAVE_CHANGES", gettext( "Save changes") );
		$content->setVariable( "BACK_LINK", PATH_URL . "weblog/" . $weblog->get_id() . "/podspace/" );
		$content->setVariable( "YOUR_PODSPACE_TEXT", gettext( "Your Podspace" ) );

		$files_in_podspace = $podspace->get_inventory( CLASS_DOCUMENT );
		if ( count( $files_in_podspace ) == 0 )
		{
			$content->setCurrentBlock( "BLOCK_EMPTY_PODSPACE" );
			$content->setVariable( "NO_FILES_LABEL", gettext( "no files found" ) );
			$content->parse( "BLOCK_EMPTY_PODSPACE" );
		}

		foreach( $files_in_podspace as $file )
		{
			if ( ! $file instanceof \steam_document )
			{
				continue;
			}
			$content->setCurrentBlock( "BLOCK_FILE" );
			$content->setVariable( "FILE_NAME", h($file->get_name()) );
			$content->setVariable( "FILE_SIZE", get_formatted_filesize( $file->get_content_size()) );
			$content->setVariable( "FILE_DESC", h($file->get_attribute( "OBJ_DESC" )) );
			$content->setVariable( "LABEL_EDIT", gettext( "edit" ) );
			$content->setVariable( "LABEL_OR",   gettext( "or" ) );
			$content->setVariable( "LABEL_DELETE", gettext( "delete" ) );
			$content->parse( "BLOCK_FILE" );
		}

		$rootlink = \lms_steam::get_link_to_root( $weblog );
		$headline = array(
		$rootlink[0],
		$rootlink[1],
		array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
		array( "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/"),
		array( "link" => "", "name" => gettext( "Podcasting" ) )
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