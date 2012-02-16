<?php
namespace Wiki\Commands;
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );
require_once( PATH_LIB . "comments_handling.inc.php" );
require_once( PATH_LIB . "wiki_handling.inc.php" );

class Deleteentry extends \AbstractCommand implements \IFrameCommand {

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
		if ( $wiki_doc != null) {
			if (@$_REQUEST["force_delete"]) {

				// is deleted entry wiki startpage ?
				$entryName = $wiki_doc->get_name();
				$startpage = $wiki_container->get_attribute("OBJ_WIKI_STARTPAGE") . ".wiki";

				if ( $entryName == $startpage )
				$wiki_container->set_attribute("OBJ_WIKI_STARTPAGE", "glossary");

				\lms_steam::delete($wiki_doc);
				// clean wiki cache (not used by wiki)
				$cache = get_cache_function( $wiki_container->get_id(), 600 );
				$cache->clean( "lms_wiki::get_items", $wiki_container->get_id() );
				$_SESSION[ "confirmation" ] = gettext( "Wiki entry deleted sucessfully");
				 
				// clean rsscache
				$rcache = get_cache_function( "rss", 600 );
				$feedlink = PATH_URL . "services/feeds/wiki_public.php?id=" . $wiki_container->get_id();
				$rcache->drop( "lms_rss::get_items", $feedlink );

				header( "Location: " . PATH_URL . "wiki/deleteentry/" . $wiki_container->get_id() . "/" );
			} else {
				$wiki_name = h( substr( $wiki_doc->get_name(), 0, -5 ) );
				$content=\Wiki::getInstance()->loadTemplate("wiki_delete.template.html");
				//$content = new HTML_TEMPLATE_IT();
				//$content->loadTemplateFile( PATH_TEMPLATES . "wiki_delete.template.html" );
				$content->setVariable( "LABEL_ARE_YOU_SURE", str_replace("%NAME", h($wiki_name), gettext( "Are you sure you want to delete the wiki page '%NAME' ?" )) );
				$content->setVariable( "LABEL_DELETE", gettext('Delete'));
				$content->setVariable( "LABEL_OR", gettext('or'));
				$content->setVariable( "LABEL_CANCEL", gettext('Cancel'));
				$content->setVariable( "FORM_ACTION", $_SERVER[ "REQUEST_URI" ] );
				$content->setVariable( "BACK_LINK", PATH_URL . "wiki/viewentry/" . $wiki_doc->get_id() . "/" );

				//Breadcrumbs
				$rootlink = \lms_steam::get_link_to_root( $wiki_container );
				(WIKI_FULL_HEADLINE) ?
				$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/index/" . $wiki_container->get_id() . "/"),
				array( "link" => PATH_URL . "wiki/viewentry/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
				array( "link" => "", "name" => gettext( "Delete" ) )
				):
				$headline = array(
				array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/index/" . $wiki_container->get_id() . "/"),
				array( "link" => PATH_URL . "wiki/viewentry/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
				array( "link" => "", "name" => gettext( "Delete" ) )
				);
				/*$portal->set_page_main($headline, $content->get(), "");
				$portal->show_html();*/
				$frameResponseObject->setHeadline($headline);
				$widget = new \Widgets\RawHtml();
				$widget->setHtml($content->get());
				$frameResponseObject->addWidget($widget);
				return $frameResponseObject;
			}
		}

	}
}
?>