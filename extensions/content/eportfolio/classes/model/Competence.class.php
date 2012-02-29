<?php
namespace Portfolio\Model;
class Competence {

	static $activities;
	static $niveaus;
	static $facets;
	static $jobs;
	static $competences;
	static $competencesQuantity;

	public $job;
	public $facet;
	public $activity;
	public $index;
	public $niveau;
	public $name;
	public $rating;
	public $description;
	public $short;

	public static function initReadCompetences(){
		$path = \Portfolio::getInstance()->getExtensionPath(). "classes/data/";
		$competences = array();
		if (is_dir($path . "competences/")) {
			if ($dh = opendir($path . "competences/")) {
				while (($file = readdir($dh)) !== false) {
					$rows = array();
					if (($handle = fopen($path."competences/" . $file, "r")) !== FALSE) {
						$affil = basename($file, ".csv");
						$job = substr($affil, 0, 2);
						$facet = substr($affil, 3, 1);
						$activity = substr($affil, 2, 1);
						while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
							if (empty($data[0]))
								continue;
							$name = (array_key_exists(1, $data)) ? $data[1] : "";
							$description = (array_key_exists(2, $data)) ? $data[2] : "";
							$index = (array_key_exists(0, $data)) ? $data[0] : "";
							$niveau = self::getNiveau($activity, $job)->niveau;
							if ($data[0] != "")
								$competences[$job . $activity . $facet . $data[0]] = new Competence(
										$name,
										$description,
										$index,
										$niveau,
										$job,
										$facet,
										$activity);
						}
						fclose($handle);
					}

				}
				closedir($dh);
			}
		}
		ksort($competences);
		self::$competences = $competences;
	}

	public static function getCompetences($job = null, $activity = null, $facet = null, $index = null){
		if (empty(self::$competences)){
			self::initReadCompetences();
			$competences_tmp = self::$competences;
		} else {
			$competences_tmp = self::$competences;
		}
		$filtered = array();
		foreach ($competences_tmp as $competence) {
			if ($job == $competence->job || $job == null)
				if ($activity == $competence->activity || $activity == null)
				if ($facet == $competence->facet || $facet == null)
				if ($index == $competence->index || $index == null)
				$filtered []= $competence;
		}
		return $filtered;
	}

	public static function getCompetence($job, $activity, $facet, $index){
		$competences_tmp = self::getCompetences($job, $activity, $facet, $index);
		return array_pop($competences_tmp);
	}

	public static function getCompetenceById($id){
		return self::getCompetence(substr($id, 0, 2), substr($id, 2, 1), substr($id, 3, 1), substr($id, 4, 3));
	}

	public static function getCompetenceByIdRated($id, $rating){
		$competence = self::getCompetenceById($id);
		if ($competence == NULL){
			return $competence;
		}
		$competence->setRating($rating);
		return $competence;
	}

	public static function getJobs() {
		$path = \Portfolio::getInstance()->getExtensionPath(). "classes/data/";
		if (!empty(self::$jobs)){
			return self::$jobs;
		}
		$jobs = array();
		$row = 0;
		if (($handle = fopen($path."jobs.csv", "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
				if ($row>=0)
					$jobs[] = new CompetenceJob($data[0], $data[1]);
				$row++;
			}
			fclose($handle);
		}
		self::$jobs = $jobs;
		return $jobs;
	}

	/*
	 * get a job object by the long name like Chemiemeister
	*/
	public static function getJobByName($name){
		$jobs = self::getJobs();
		foreach ($jobs as $job){
			if (strtolower($job->name) == strtolower($name))
				return $job;
		}
	}

	public static function getActivityFields() {
		if (!empty(self::$activities)){
			return self::$activities;
		}
		return self::initActivities();
	}

	public static function initActivities() {
		$path = \Portfolio::getInstance()->getExtensionPath(). "classes/data/";
		if (!empty(self::$activities)){
			return self::$activities;
		}
		$activities = array();
		$row = 0;
		if (($handle = fopen($path."taetigkeitsfelder.csv", "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
				if ($row>1)
					$activities[$data[2] . $data[3] . $data[4]] = new CompetenceActivity($data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
				$row++;
			}
			fclose($handle);
		}
		ksort($activities);
		self::$activities = $activities;
		return $activities;
	}

	public static function getActivityFieldsDistinct() {
		$activities = self::getActivityFields();
		$distinctActivities = array();
		$present = array();
		foreach ($activities as $activity) {
			if (in_array($activity->name, $present))
				continue;
			$distinctActivities [$activity->index]= $activity;
			$present []= $activity->name;
		}
		return $distinctActivities;
	}

	
	public static function getFacets() {
		$path = \Portfolio::getInstance()->getExtensionPath(). "classes/data/";
		if (!empty(self::$facets)){
			return self::$facets;
		}
		$rows = array();
		$row = 0;
		if (($handle = fopen($path."Kompetenzaspekte.csv", "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
				$rows[] = $data;
			}
			fclose($handle);
		}
		$facets = array();
		for ($i = 0; $i < count($rows[0]); $i++) {
			$facets[] = new CompetenceFacet($rows[4][$i], $rows[5][$i], $rows[6][$i]);
		}
		self::$facets = $facets;
		return $facets;
	}

	/**
	 * returns an array
	 * keys: competence string / (+index)
	 * values: how many artefacts with this competence exist
	 */
	public static function getCollectedCompetences(){
		$artefacts = \Artefacts::getAllArtefacts();
		$competencesArray = array();
		$tmp = array();
		foreach ($artefacts as $artefact) {
			$competences = $artefact->getCompetences();
			foreach ($competences as $competence) {
				$index = $competence->getJobAffiliation() . $competence->getActivityAffiliation() . $competence->getFacetAffiliation();
				$index2 = $competence->getJobAffiliation() . $competence->getActivityAffiliation() . $competence->getFacetAffiliation() . $competence->getIndex();
				if (isset($competencesArray [$index])){
					$competencesArray [$index] += 1;
				} else {
					$competencesArray [$index] = 1;
				}
				if (isset($competencesArray [$index2])){
					continue;
					//$competencesArray [$index2] += 1;
				} else {
					$competencesArray [$index2] = 1;
					$competencesArray [$index2 . "ID"] = $artefact->getID();
				}
				if (isset($competencesArray [$competence->getJobAffiliation() . $competence->getActivityAffiliation()])){
					$competencesArray [$competence->getJobAffiliation() . $competence->getActivityAffiliation()] += 1;
				} else {
					$competencesArray [$competence->getJobAffiliation() . $competence->getActivityAffiliation()] = 1;
				}
			}
		}
		return $competencesArray;
	}

	public static function initNiveaus(){
		$activities = self::getActivityFields();
		$niveausArray = array();
		$tmp = array();
		foreach ($activities as $activity) {
			$niveausArray [$activity->index . $activity->job]= new Niveau($activity->name, $activity->niveau,  $activity->job, $activity->niveauDescription, $activity);
		}
		self::$niveaus = $niveausArray;
	}

	public static function getNiveaus(){
		if (empty(self::$niveaus)){
			self::initNiveaus();
		}
		return self::$niveaus;
	}
	
	public static function getNiveau($activity, $job){
		if (empty(self::$niveaus)){
			self::initNiveaus();
		} 
		return self::$niveaus[$activity . $job];
	}
	
	public static function getCompetencesQuantity(){
		if (self::$competencesQuantity){
			return self::$competencesQuantity;
		}
		$allCompetences = self::getAllCompetences();
		$competencesQuantity = array();
		foreach ($allCompetences as $competence) {
			$job = $competence->getJobAffiliation();
			$activity = $competence->getActivityAffiliation();
			$facet = $competence->getFacetAffiliation();
			isset($competencesQuantity [$job]) ? $competencesQuantity [$job] += 1 : $competencesQuantity [$job] = 1;
			isset($competencesQuantity [$activity]) ? $competencesQuantity [$activity] += 1 : $competencesQuantity [$activity] = 1;
			isset($competencesQuantity [$facet]) ?	$competencesQuantity [$facet] += 1 : $competencesQuantity [$facet] = 1;
			isset($competencesQuantity [$job . $activity . $facet]) ? $competencesQuantity [$job . $activity . $facet] += 1 : $competencesQuantity [$job . $activity . $facet] = 1;
			isset($competencesQuantity [$job . $activity]) ? $competencesQuantity [$job . $activity] += 1 : $competencesQuantity [$job . $activity] = 1;
		}
		self::$competencesQuantity = $competencesQuantity;
		return $competencesQuantity;

	}

	/*
	 public static function getCollectedCompetencesRelative($job){
	$artefacts = \ArtefactModel::getAllArtefacts();
	$competencesArray = array();
	foreach ($artefacts as $artefact) {
	$competences = $artefact->getCompetences();
	foreach ($competences as $competence) {
	$index = $competence->getJobAffiliation() . $competence->getActivityAffiliation() . $competence->getFacetAffiliation();
	if (isset($competencesArray [$index])){
	$competencesArray [$index] += 1;
	} else {
	$competencesArray [$index] = 1;
	}
	}
	}
	self::getCompetence($job, $activity, $facet, $index)
	return $competencesArray;

	}
	*/

	public static function getAllCompetences() {
		$array = self::getCompetences();
		return $array;
	}

	public function __construct(
			$name,
			$description,
			$index = null,
			$niveau = null,
			$job = null,
			$facet = null,
			$activity = null) {
		$this->name = $name;
		$this->description = $description;
		$this->niveau = $niveau;
		$this->index = $index;
		$this->activity = $activity;
		$this->facet = $facet;
		$this->job = $job;
		$this->short = $job . $activity . $facet . $index;
	}

	public function getJobAffiliation(){
		return $this->job;
	}

	public function getFacetAffiliation(){
		return $this->facet;
	}

	public function getActivityAffiliation(){
		return $this->activity;
	}

	public function getJob(){
		return $this->job;
	}

	public function getFacet(){
		return $this->facet;
	}

	public function getActivity(){
		return $this->activity;
	}

	/**
	 * index must be formatted like ActivityJobIndex
	 * @param unknown_type $index
	 * @return Ambigous <\Portfolio\Model\CompetenceActivity>
	 */
	public static function getActivityObject($index){
		$activities = self::getActivityFields();
		return $activities[$index];
	}
	
	public function getIndex(){
		return $this->index;
	}

	public function getNiveauObject(){
		return $this->getNiveau($this->activity, $this->job);
	}
	
	public function getJobObject(){
		return $this->getJobByName($this->job);
	}
	
	public function getRating() {
		return $this->rating;
	}

	public function setRating($rating) {
		$this->rating = $rating;
	}
	
	public function getShortHtml(){
		return "<div style=\"font-size:80%\">({$this->short})</div>";
	}
		
}

?>