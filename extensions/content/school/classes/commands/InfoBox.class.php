<?php
namespace Bookmarks\Commands;
class Infobox extends \AbstractCommand implements \IFrameCommand {
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
		
	}
	
	public function execute ($request, $response) {
		$box = new \Widgets\Box();
		$box->setId(\BookmarksHome::getInstance()->getId());
		$box->setTitle("Meine Lesezeichen (noch nicht fertig)");
		$box->setTitleLink(PATH_URL . "bookmarks/");
		$underconstruction = new \Widgets\Underconstruction();
		$box->setContent($underconstruction->getHtml() . "<p><b><a href=\"#\" title=\"/Schulen/gt/Ratsgymnasium/Klasse 8b/\">Klasse 8b</a></b><br><small>[gestern zuletzt besucht]</small></p>
					      <p><b><a href=\"#\" title=\"/Schulen/gt/Ratsgymnasium/Klasse 8b/\" >Deutsch</a></b><br><small>[gestern zuletzt besucht]</small></p>
					  	  <p><b><a href=\"#\" title=\"/Schulen/gt/Ratsgymnasium/Klasse 8b/\" >Projektwoche</a></b><br><small>[am 20.10.2010 zuletzt besucht]</small></p>
					      <p><b><a href=\"#\" title=\"/Schulen/gt/Ratsgymnasium/Klasse 8b/\" >Klasse 8a</a></b><br><small>[am 15.10.2010 zuletzt besucht]</small></p>
						  <p><b><a href=\"#\" title=\"/Schulen/gt/Ratsgymnasium/Klasse 8b/\" >AG Fotografie</a></b><br><small>[am 13.10.2010 zuletzt besucht]</small></p>");
		$box->setContentMore("Alle meine Lesezeichen");
		$box->setContentMoreLink(PATH_URL . "bookmarks/");
		return $box->getHtml();
	}
}
?>