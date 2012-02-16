<?php
class Artefacts extends PortfolioExtensionModel{

	public static function getArtefactsContainer($user = null) {
		self::init();
		if ($user == null)
		$user = lms_steam::get_current_user();
		if (!array_key_exists(PORTFOLIO_PREFIX . "ArtefactsContainer", $_SESSION)){
			//$user = lms_steam::get_current_user();
			$_SESSION[ PORTFOLIO_PREFIX . "ArtefactsContainer" ] = $user->get_workroom()->get_object_by_name("portfolio")->get_object_by_name("artefacts");
		}
		return $_SESSION[ PORTFOLIO_PREFIX . "ArtefactsContainer" ];
	}

	public static function getAllArtefacts(){
		self::init();
		$all = self::getArtefactsContainer()->get_inventory_filtered(
		array(array( '+', 'class', CLASS_ROOM )));

		$allArtefacts = array();
		foreach ($all as $room) {
			$allArtefacts[] = Artefacts::getArtefactByRoom($room);
		}
		return $allArtefacts;
	}

	public static function getLatestArtefacts($count = 10){
		$all = self::getArtefactsContainer()->get_inventory_filtered(
		array(array( '+', 'class', CLASS_ROOM )),
		array(
		array( '>', 'attribute', 'OBJ_CREATION_TIME' ),
		array( '>', 'attribute', 'OBJ_LAST_CHANGED' )
		)
		);

		$allArtefacts = array();
		$i = 0;
		foreach ($all as $room) {
			$i++;
			if ($i > $count)
			break;
			$allArtefacts[] = Artefacts::getArtefactByRoom($room);
		}
		return $allArtefacts;
	}


	public static function getArtefactsByCompetence($job = null, $facet = null, $activity = null){
		$all = self::getAllArtefacts();
		$filtered = array();
		foreach ($all as $artefact) {
			$competences = $artefact->getCompetences();
			foreach ($competences as $competence){
				if ($job == null || $competence->getJob() == $job)
				if ($activity == null || $competence->getActivity() == $activity)
				if ($facet == null || $competence->getFacet() == $facet)
				$filtered [$artefact->getId()]= $artefact;
			}
		}
		return $filtered;
	}

	public function getArtefactByRoom($room){
		$artefactClass = $room->get_attribute(PORTFOLIO_PREFIX . "ARTEFACTCLASS");
		switch ($artefactClass) {
			case "CERTIFICATE":
				$newArtefactObject = new ArtefactCertificate($room);
				break;
			case "SCHOOL":
				$newArtefactObject = new ArtefactSchool($room);
				break;
			case "EMPLOYMENT":
				$newArtefactObject = new ArtefactEmployment($room);
				break;
			default :
				$newArtefactObject = new ArtefactCertificate($room);
				break;
		}
		return $newArtefactObject;
	}
}