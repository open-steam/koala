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

		$portal = \lms_portal::get_instance();
		$portal->initialize( GUEST_NOT_ALLOWED );

		// Disable caching
		// TODO: Work on cache handling. An enabled cache leads to bugs
		// if used with the wiki.
		\CacheSettings::disable_caching();

		$WikiExtension = \Wiki::getInstance();
		$wiki_doc = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[0]);
		$compare = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[1]);
		$to = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[2]);
		$wiki_container = $wiki_doc->get_environment();
		$wiki_html_handler = new \koala_wiki($wiki_container);

		if($wiki_container->get_attribute("UNIT_TYPE")){
		    $place = "units";
		}
		else{
		    $place = "communication";
		}

		if (!($wiki_container->check_access_read())) {
				$errorHtml = new \Widgets\RawHtml();
				$errorHtml->setHtml("Das Wiki kann nicht angezeigt werden, da Sie nicht über die erforderlichen Leserechte verfügen.");
				$frameResponseObject->addWidget($errorHtml);
				return $frameResponseObject;
		}

		$content = $WikiExtension->loadTemplate("wiki_version_compare.template.html" );
		$difftext = wiki_diff_html( $to, $compare );
		$content->setVariable( "DIFF_TEXT", $difftext );
		$wiki_html_handler->set_main_html( $content->get() );

		(WIKI_FULL_HEADLINE) ?
		$headline = array(
						$rootlink[0],
						$rootlink[1],
						array( "link" => $rootlink[1]["link"] . "{$place}", "name" => gettext("{$place}")),
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
