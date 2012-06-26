<?php
namespace Wiki\Commands;
class Recover extends \AbstractCommand implements \IFrameCommand {

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

		$wiki_doc = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$version_doc = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[1]);
		$wiki_container = $wiki_doc->get_environment();
		
$user = \lms_steam::get_current_user();

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
			header( "Location: " . PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/" );
	   		exit;
		
	}
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}
$backlink = PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/";

$WikiExtension = \Wiki::getInstance();
$content = $WikiExtension->loadTemplate("wiki_recover_version.template.html" );
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
				array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"),
				array( "link" => PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
				array( "link" => "", "name" => str_replace("%VERSION", $version_doc->get_version(), gettext( "Recover version %VERSION" ) ) )
				):
$headline = array(
				array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"),
				array( "link" => PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
				array( "link" => "", "name" => str_replace("%VERSION", $version_doc->get_version(), gettext( "Recover version %VERSION" ) ) )
				);


		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		$frameResponseObject->setHeadline($headline);
		return $frameResponseObject;
	}
}