<?php
namespace Portfolio\Commands;
class Portfoliosdebug extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $portfolioId;
	private $artefactId;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->portfolioId = $this->params[0]: "";
		isset($this->params[1]) ? $this->artefactId = $this->params[1]: "";
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$portfolios = \PortfolioModel::getMyPortfolios();
		
		$listViewer = new \Widgets\ListViewer();
		
		if (isset($this->portfolioId)){
			$portfolioObject = new \PortfolioModel(\steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $this->portfolioId ));
		}
		if (isset($this->artefactId))
			$artefactObject = new \ArtefactModel(\steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $this->artefactId ));
		$steamUser = $GLOBALS["STEAM"]->get_current_steam_user();		

//		/** check the rights of the log-in user */
//		$threadObject_allowed_read = $threadObject->check_access_read($steamUser);
//		$threadObject_write = $threadObject->check_access_write($steamUser);
//		$threadObject_annotate = $threadObject->check_access_annotate($steamUser);

		$listViewer->setHeadlineProvider(new HeadlineProviderDebug());
		$listViewer->setContentProvider(new ContentProviderDebug());
		
		if (isset($portfolioObject)){
			$listViewer->setContent($artefacts);
		} else {
			$listViewer->setContent($portfolios);
		}		
		
		$frameResponseObject->addWidget($listViewer);
		return $frameResponseObject;
	}
}

class HeadlineProviderDebug implements \Widgets\IHeadlineProvider {
	public function getHeadlines() {
		return array("", "", "Name", "Änderungsdatum", "Größe");
//		return array("name", "description", "date", "size");
	}
	
	public function getHeadLineWidths() {
//		return array(250, 350, 50, 50);
		return array(20, 20, 315, 150, 80);
	}
	
	public function getHeadLineAligns() {
		return array("left", "right", "left", "right", "right");
	}
}

class ContentProviderDebug implements \Widgets\IContentProvider {
	
	public function getId($contentItem) {
		return $contentItem->getId();
	}
	
	public function getCellData($cell, $contentItem) {
		if (!is_int($cell)) {
			throw new \Exception("cell must be an integer!!");
		}
		
		if ($cell == 0) {
			return "<input style=\"margin-top:-4px\" type=\"checkbox\" onclick=\"if(this.checked) { jQuery('#{$contentItem->getId()}').css({'background-color':'#eee', 'boarder':'1px solid #eee'})} else {jQuery('#{$contentItem->getId()}').css({'background-color':'transparent', 'boarder':'1px solid white'})}\"></input>";
		} else if ($cell == 1) {
			//TODO
			return "<img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($contentItem->getRoom())."\"></img>";
		} else if ($cell == 2) {
			$url = \ExtensionMaster::getInstance()->getUrlForObjectId($contentItem->getId(), "view");
			$desc = $contentItem->getDescription();
			if ($desc !== 0 && $desc !== "") {
				$name = $desc;
			} else {
				$name = str_replace("%20", " ", $contentItem->getName());
			}
			if (isset($url) && $url != "") {
				return "<a href=\"".$url."\" title=\"$desc\"> " . $name ."</a>";
			} else {
				return $name;
			}
		}  else if ($cell == 3) {
			if ($contentItem instanceof \steam_document) {
				return getReadableSize($contentItem->get_content_size());
			} else if ($contentItem instanceof \steam_container) {
				try {
					$html = "<div style=\"color: #ccc\">" . count($contentItem->get_inventory()) . " Objekte</div>";
				} catch (\steam_exception $e) {
					$html = "keine Berechtigung";
				}
				return $html;
			}
		} else if ($cell == 4) {
			return "0";
		}
	}
		
	public function getNoContentText() {
		return "no artefacts available!";
	}
	
	public function getOnClickHandler($contentItem) {
		return "";
	}
}
?>