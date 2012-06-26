<?php
namespace Wiki\Commands;
class Compare extends \AbstractCommand implements \IFrameCommand {

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
		require_once( PATH_LIB . "wiki_handling.inc.php" );
		
		$wiki_doc = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[0]);
		$compare = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[1]);
		$to = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[2]);
		$wiki_container = $wiki_doc->get_environment();
		$wiki_html_handler = new \koala_wiki($wiki_container);


//$wiki_html_handler->set_admin_menu( "versions", $wiki_doc );

$WikiExtension = \Wiki::getInstance();
$content = $WikiExtension->loadTemplate("wiki_version_compare.template.html" );

$difftext = wiki_diff_html( $to, $compare );
$content->setVariable( "DIFF_TEXT", $difftext );
$content->setVariable( "BACK_LINK", PATH_URL . "wiki/versions/" . $wiki_doc->get_id());
$content->setVariable( "BACK_LABEL", gettext("back") );

$wiki_html_handler->set_main_html( $content->get() );

$rootlink = \lms_steam::get_link_to_root( $wiki_container );
(WIKI_FULL_HEADLINE) ?
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "{$place}/", "name" => gettext("{$place}")),
				array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"),
				array( "link" => PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
				array( "link" => PATH_URL . "wiki/versions/" . $wiki_doc->get_id() . "/", "name" => gettext("Version management")),
				array( "link" => "", "name" => gettext("Version compare") . " (" . $compare->get_version() . " " . gettext("to") . " " .$to->get_version() . ")")
				):
$headline = array(
				array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"),
				array( "link" => PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
				array( "link" => PATH_URL . "wiki/versions/" . $wiki_doc->get_id() . "/", "name" => gettext("Version management")),
				array( "link" => "", "name" => gettext("Version compare") . " (" . $compare->get_version() . " " . gettext("to") . " " .$to->get_version() . ")")
				);

		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($wiki_html_handler->get_html());
		$frameResponseObject->addWidget($rawHtml);
		$frameResponseObject->setHeadline($headline);
		return $frameResponseObject;
	}
}
?>