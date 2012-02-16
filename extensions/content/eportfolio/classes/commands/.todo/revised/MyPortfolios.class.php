<?php
namespace Portfolio\Commands;
class MyPortfolios extends \AbstractCommand implements \IFrameCommand {

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$portfolios = \PortfolioModel::getMyPortfolios();
		
		$listViewer = new \Widgets\ListViewer();
		$listViewer->setHeadlineProvider(new HeadlineProviderMyPortfolios());
		$listViewer->setContentProvider(new ContentProviderMyPortfolios());
		$listViewer->setContent($portfolios);

		$frameResponseObject->addWidget($listViewer);
		return $frameResponseObject;
	}
}

class HeadlineProviderMyPortfolios implements \Widgets\IHeadlineProvider {
	public function getHeadlines() {
		return array("", "", "Name", "Anzahl Artefakte", "Änderungsdatum");
	}
	
	public function getHeadLineWidths() {
		return array(20, 20, 350, 150, 150);
	}
	
	public function getHeadLineAligns() {
		return array("left", "right", "left", "right", "right");
	}
}

class ContentProviderMyPortfolios implements \Widgets\IContentProvider {
	
	public function getId($contentItem) {
		return $contentItem->getId();
	}
	
	public function getCellData($cell, $contentItem) {
		if (!is_int($cell)) {
			throw new \Exception("cell must be an integer!!");
		}
		
		if ($cell == 1) {
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
			return $contentItem->count();
		} else if ($cell == 4) {
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