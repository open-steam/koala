<?php
namespace Portfolio\Commands;
class ListPortfolios extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $portfolioId;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->portfolioId = $this->params[0]: "";
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$steamUser = $GLOBALS["STEAM"]->get_current_steam_user();		
		
		if (isset($this->portfolioId)){
			$portfolioObject = new \PortfolioModel(\steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $this->portfolioId ));
			$artefacts = array();
			foreach ($portfolioObject->getArtefacts() as $artefact) {
				$artefacts []= $artefact;
			}
		}

		$listViewer = new \Widgets\ListViewer();
		$listViewer->setHeadlineProvider(new HeadlineProviderPortfolios());
		$listViewer->setContentProvider(new ContentProviderPortfolios());
		$listViewer->setContent($artefacts);
		
		$frameResponseObject->addWidget($listViewer);
		return $frameResponseObject;
	}
}

class HeadlineProviderPortfolios implements \Widgets\IHeadlineProvider {
	public function getHeadlines() {
		return array("", "", "Name", "Änderungsdatum");
	}
	
	public function getHeadLineWidths() {
		return array(20, 25, 415, 250);
	}
	
	public function getHeadLineAligns() {
		return array("left", "right", "left", "right");
	}
}

class ContentProviderPortfolios implements \Widgets\IContentProvider {
	
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
		} else if ($cell == 3) {
			return getReadableDate($contentItem->getModificationTime());
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