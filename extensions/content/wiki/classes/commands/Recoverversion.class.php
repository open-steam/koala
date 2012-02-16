<?php
namespace Wiki\Commands;
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );
require_once( PATH_LIB . "comments_handling.inc.php" );
require_once( PATH_LIB . "wiki_handling.inc.php" );

class Recoverversion extends \AbstractCommand implements \IFrameCommand {

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
		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
		{
			$problems = "";

			try
			{
				$new_content = $version_doc->get_content();
				$wiki_doc->set_content($new_content);
			}
			catch( Exception $ex )
			{
				$problems = $ex->get_message();
			}

			if( empty($problems) )
			{
				$_SESSION[ "confirmation" ] = str_replace( "%VERSION", $version_doc->get_version(), gettext( "Version %VERSION recovered." ) );
				header( "Location: " . PATH_URL . "wiki/viewentry/" . $wiki_doc->get_id() . "/" );
				exit;

			}
			else
			{
				$frameResponseObject->setProblemDescription($problems);
				
				//$portal->set_problem_description( $problems, $hints );
			}
		}
		$backlink = PATH_URL . "wiki/viewentry/" . $wiki_doc->get_id() . "/";

		$content = \Wiki::getInstance()->loadTemplate("wiki_recover_version.template.html");
		//$content = new HTML_TEMPLATE_IT();
		//$content->loadTemplateFile( PATH_TEMPLATES . "wiki_recover_version.template.html" );
		$content->setVariable( "BACK_LINK", $backlink );
		$content->setVariable( "INFO_TEXT", gettext( "A new version will be created from the one you are recovering. The actual version will not be lost. Is that what you want?" ) );
		$content->setVariable( "LABEL_OK", gettext( "Yes, Recover version" ) );
		$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );

		$rootlink = \lms_steam::get_link_to_root( $wiki_container );
		(WIKI_FULL_HEADLINE) ?
		$headline = array(
		$rootlink[0],
		$rootlink[1],
		array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
		array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/"),
		array( "link" => PATH_URL . "wiki/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
		array( "link" => "", "name" => str_replace("%VERSION", $version_doc->get_version(), gettext( "Recover version %VERSION" ) ) )
		):
		$headline = array(
		array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/"),
		array( "link" => PATH_URL . "wiki/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
		array( "link" => "", "name" => str_replace("%VERSION", $version_doc->get_version(), gettext( "Recover version %VERSION" ) ) )
		);
		
		$frameResponseObject->setHeadline($headline);
		$widget=new \Widgets\RawHtml();
		$widget->setHtml($content->get());
		$frameResponseObject->addWidget($widget);
		return $frameResponseObject;
		
		/*$portal->set_page_main(
		$headline,
		$content->get()
		);
		$portal->show_html();
		*/
	}
}
?>