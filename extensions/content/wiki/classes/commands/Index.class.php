<?php
namespace Wiki\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {

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
		$wiki_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$startpage = $wiki_container->get_attribute("WIKI_STARTPAGE");
		
		if ( !$startpage || $startpage === "glossary" )
		{
			header("Location: " . PATH_URL . "wiki/glossary/" . $wiki_container->get_id() . "/");
		}
		else
		{
			header("Location: " . PATH_URL . "wiki/entry/" . $wiki_container->get_id() . "/" . $startpage . ".wiki");
		}
		exit;
	}
}