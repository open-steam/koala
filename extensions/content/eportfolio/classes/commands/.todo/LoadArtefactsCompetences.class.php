<?php
namespace Portfolio\Commands;
class LoadArtefactsCompetences extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $portfolioModel;
	private $artefacts;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		if (empty($this->params[0])){
			$this->artefacts = \Artefacts::getAllArtefacts();
		} else {
			$portfolio = \PortfolioModel::getById($this->params[0]);
			$this->artefacts = $portfolio->getArtefacts();
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
		return array("", "", "Name", "Kompetenzen");
	}

	public function getHeadLineWidths() {
		return array(20, 20, 315, 150);
	}

	public function getHeadLineAligns() {
		return array("left", "left", "left", "right");
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
			if (isset($url) && $url != "") {
				return "<a href=\"".$url."\" title=\"$desc\"> " . $name ."</a>";
			} else {
				return $name;
			}
		} else if ($cell == 3) {
			$competencesArray = $contentItem->getCompetences();
			$competences = "";
			foreach ($competencesArray as $competence){
				$competences .= "<a href=\"/portfolio/ViewCompetence/" . $competence->getJobAffiliation() . "/" .$competence->getActivityAffiliation() . "/" .$competence->getFacetAffiliation() . "/" . "\">" . $competence->short . " </a>";
			}
			return $competences;

		} else if ($cell == 4) {
			$popupMenu = new \Widgets\PopupMenu();
			$popupMenu->setData($contentItem);
			return $popupMenu;
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