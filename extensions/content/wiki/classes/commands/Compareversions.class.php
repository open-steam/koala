<?php
namespace Wiki\Commands;
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );
require_once( PATH_LIB . "comments_handling.inc.php" );
require_once( PATH_LIB . "wiki_handling.inc.php" );

class Compareversions extends \AbstractCommand implements \IFrameCommand {

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

		$compare = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $this->params[1] );
		$to = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $this->params[2] );
		$wiki_html_handler = new \lms_wiki( $wiki_container );
		//$wiki_html_handler->set_admin_menu( "versions", $wiki_doc );

		$content = \Wiki::getInstance()->loadTemplate("wiki_version_compare.template.html");
		//$content = new HTML_TEMPLATE_IT();
		//$content->loadTemplateFile( PATH_TEMPLATES . "wiki_version_compare.template.html" );


		$difftext = wiki_diff_html( $to, $compare );

		$content->setVariable( "DIFF_TEXT", $difftext);

		$wiki_html_handler->set_main_html( $content->get() );

		$rootlink = \lms_steam::get_link_to_root( $wiki_container );
		(WIKI_FULL_HEADLINE) ?
		$headline = array(
		$rootlink[0],
		$rootlink[1],
		array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
		array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/index/" . $wiki_container->get_id() . "/"),
		array( "link" => PATH_URL . "wiki/viewentry/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
		array( "link" => PATH_URL . "wiki/versionoverview/" . $wiki_doc->get_id() , "name" => gettext("Version management")),
		array( "link" => "", "name" => gettext("Version compare") . " (" . $compare->get_version() . " " . gettext("to") . " " .$to->get_version() . ")")
		):
		$headline = array(
		array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/index/" . $wiki_container->get_id() . "/"),
		array( "link" => PATH_URL . "wiki/viewentry/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
		array( "link" => PATH_URL . "wiki/versionoverview/" . $wiki_doc->get_id() . "/versions/", "name" => gettext("Version management")),
		array( "link" => "", "name" => gettext("Version compare") . " (" . $compare->get_version() . " " . gettext("to") . " " .$to->get_version() . ")")
		);

		/*$portal->set_page_main(
		$headline,
		$wiki_html_handler->get_html()
		);
		$portal->show_html();
		*/
		$frameResponseObject->setHeadline($headline);
		$widget=new \Widgets\RawHtml();
		$widget->setHtml($wiki_html_handler->get_html());
		$frameResponseObject->addWidget($widget);
		return $frameResponseObject;

	}
}
?>