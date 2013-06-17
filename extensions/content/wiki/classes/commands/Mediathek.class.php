<?php
namespace Wiki\Commands;
class Mediathek extends \AbstractCommand implements \IFrameCommand {

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
		$portal = \lms_portal::get_instance();
		$portal->initialize( GUEST_NOT_ALLOWED );
		
		// Disable caching
		// TODO: Work on cache handling. An enabled cache leads to bugs
		// if used with the wiki.
		\CacheSettings::disable_caching();
		
		$WikiExtension = \Wiki::getInstance();
		$WikiExtension->addCSS();
		$wiki_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$wiki_html_handler = new \koala_wiki($wiki_container);
		$wiki_html_handler->set_admin_menu( "mediathek", $wiki_container );
		
		$content = $WikiExtension->loadTemplate( "wiki_mediathek.template.html" );
		
		// get images
		$inventory = $wiki_container->get_inventory();
		if ( !is_array( $inventory ) ) $inventory = array();
		
		$question = "Dieses Bild wirklich löschen?";
		$note = gettext("Achtung: Alle Wiki Einträge, die dieses Bild enthalten, müssen manuell aktualisiert werden.");
		if ( sizeof( $inventory ) > 0 )
		{
			\steam_factory::load_attributes( $GLOBALS["STEAM"]->get_id(), $inventory , array( OBJ_NAME, OBJ_DESC, DOC_MIME_TYPE ) );
			$images = array();
		
			foreach( $inventory as $object )
			{
		    	$mime = strtolower($object->get_attribute(DOC_MIME_TYPE));
		    	if ( $mime === "image/jpg" || $mime === "image/jpeg" || $mime === "image/gif" || $mime === "image/png" ) $images[] = $object;
			}
			
			foreach( $images as $image )
			{
				$actions = '<a href="#" onclick="sendRequest(\'Properties\', {\'id\':' . $image->get_id() . '}, \'\', \'popup\', null, null, \'explorer\')">' . "Eigenschaften" . '</a><br>';
				$actions .= '<a href="#" onclick="return confirmDeletion(\'' . $question . '\',\'' . $note . '\',' . $image->get_id() . ');">' . "Bild löschen" . '</a>';
				
				$imageData = imagecreatefromstring( $image->get_content() );
		
				$width = $newWidth = imagesx( $imageData );
				$height = $newHeight = imagesy( $imageData );
		
				if ( $width > 160 )
				{
					$newHeight = (int) ( $height * 160 / $width );
					$newWidth = 160;
				}
				
				if ( $newHeight > 80 )
				{
					$newWidth = (int) ( $newWidth * 80 / $newHeight );
					$newHeight = 80;
				}
				
				$content->setCurrentBlock("BLOCK_IMAGE");
				    $content->setVariable("IMAGE_NAME", $image->get_name());
		    	    $content->setVariable("IMAGE_ID", $image->get_id());
					$content->setVariable("IMAGE_DESCRIPTION", $image->get_attribute('OBJ_DESC'));
				    $content->setVariable("IMAGE_LINK", PATH_URL . "download/image/" . $image->get_id() . "/" . $newWidth . "/" . $newHeight);
				    $content->setVariable("PREVIEW_LINK", "javascript:showBox(" . $image->get_id() . "," . $width . "," . $height . ");");
				    $content->setVariable("IMAGE_ACTIONS", $actions);	    
				$content->parse("BLOCK_IMAGE");
			}
		}
		$content->setVariable( "LABEL_CLOSE", gettext( "close" ) );
                if (isset($_SERVER["HTTP_REFERER"])) {
                    $content->setVariable( "BACK_LINK", $_SERVER["HTTP_REFERER"]);
                } else {
                    $content->setVariable( "BACK_LINK", PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/" );
                }
                $content->setVariable( "BACK_LABEL", gettext( "back" ) );
		
		$wiki_html_handler->set_main_html( $content->get() );
		
		// breadcrumbs
		//$rootlink = \lms_steam::get_link_to_root( $wiki_container );
		(WIKI_FULL_HEADLINE) ? 
		$headline = array(
						$rootlink[0],
						$rootlink[1],
						array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
						array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"),
						array( "link" => "", "name" => gettext("Mediathek") )
					):
		$headline = array(
						array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"), 
						array( "link" => "", "name" => gettext("Mediathek") )
					);
			
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($wiki_html_handler->get_html());
		$frameResponseObject->addWidget($rawHtml);
		$frameResponseObject->setHeadline($headline);
		return $frameResponseObject;
	}
}
?>