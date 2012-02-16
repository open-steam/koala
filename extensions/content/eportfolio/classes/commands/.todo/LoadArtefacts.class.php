<?php
namespace Portfolio\Commands;
class LoadArtefacts extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $portfolioModel;
	private $artefacts;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		if (
		$this->params["job"] == null &&
		$this->params["activity"] == null &&
		$this->params["facet"] == null &&
		$this->params["index"] == null){
			$this->artefacts = \Artefacts::getAllArtefacts();
		} elseif ($this->params["portfolioId"] != null) {
			$portfolio = \PortfolioModel::getById($this->params["portfolioId"]);
			$this->artefacts = $portfolio->getArtefacts();
		}else{
			$this->artefacts = \Artefacts::getArtefactsByCompetence($this->params["job"], $this->params["facet"], $this->params["activity"], $this->params["index"]);
		}
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$listViewer = new \Widgets\ListViewer();
		$listViewer->setHeadlineProvider(new HeadlineProvider());
		$listViewer->setContentProvider(new ContentProvider());
		$listViewer->setColorProvider(new ColorProvider());
		$listViewer->setContentFilter(new ContentFilter());
		$listViewer->setContent($this->artefacts);

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($listViewer);
		return $ajaxResponseObject;
	}
}

class HeadlineProvider implements \Widgets\IHeadlineProvider {
	public function getHeadlines() {
		return array("", "", "Name", "Änderungsdatum", "Kompetenzen", "Typ", "");
	}

	public function getHeadLineWidths() {
		return array(20, 20, 315, 150, 183, 100, 100);
	}

	public function getHeadLineAligns() {
		return array("left", "left", "left", "right", "center", "center", "right");
	}
}

class ContentProvider implements \Widgets\IContentProvider {

	public function getId($contentItem) {
		return $contentItem->get_id();
	}

	public function getCellData($cell, $contentItem) {
		if (!is_int($cell)) {
			throw new \Exception("cell must be an integer!!");
		}

		if ($cell == 0) {
			return "<input style=\"margin-top:-4px\" type=\"checkbox\" onclick=\"event.stopPropagation(); if(this.checked) { jQuery('#{$contentItem->get_id()}').removeClass('listviewer-item-unhover').addClass('listviewer-item-selected') } else { jQuery('#{$contentItem->get_id()}').removeClass('listviewer-item-selected').addClass('listviewer-item-unhover') }\"></input>";
		} else if ($cell == 1) {
			return "<img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($contentItem)."\"></img>";
		} else if ($cell == 2) {
			$url = \ExtensionMaster::getInstance()->getUrlForObjectId($contentItem->get_id(), "view");
			$desc = $contentItem->get_attribute("OBJ_DESC");
			if ($desc !== 0 && $desc !== "") {
				$name = $desc;
			} else {
				$name = str_replace("%20", " ", $contentItem->get_name());
			}
			if ($name == "Trashbin") {
				$name = "Papierkorb";
			}
			if (isset($url) && $url != "") {
				return "<a href=\"".$url."\" title=\"$desc\"> " . $name ."</a>";
			} else {
				return $name;
			}
		} else if ($cell == 3) {
			return getReadableDate($contentItem->get_attribute("OBJ_LAST_CHANGED"));
		} else if ($cell == 4) {
			$competencesArray = $contentItem->getCompetences();
			$competences = "";
			foreach ($competencesArray as $competence){
				$competences .= "<a href=\"/portfolio/ViewCompetence/?job=" . $competence->getJobAffiliation() . "&activity=" .$competence->getActivityAffiliation() . "&facet=" .$competence->getFacetAffiliation() . "\">" . $competence->short . " </a>";
			}
			return $competences;
		} else if ($cell == 6) {
			$popupMenu = new \Widgets\PopupMenu();
			$popupMenu->setData($contentItem);
			$popupMenu->setElementId("listviewer-overlay");
			return $popupMenu;
		} else if ($cell == 5) {
			return $contentItem->getArtefactClass();
		}
	}

	public function getNoContentText() {
		return "Dieser Ordner enthält keine Objekte.";
	}

	public function getOnClickHandler($contentItem) {
		return "";
	}
}

class ColorProvider implements \Widgets\IColorProvider {

	public function getColor($contentItem) {
		$color = $contentItem->get_attribute("OBJ_COLOR_LABEL");
		return ($color === 0) ? "" : $color;
	}

}

class ContentFilter implements \Widgets\IContentFilter {

	public function filterObject($object) {
		if ($object instanceof \steam_user) {
			return true;
		} else {
			return false;
		}
	}

}
?>