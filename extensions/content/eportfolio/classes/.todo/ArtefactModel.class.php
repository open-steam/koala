<?php
abstract class ArtefactModel extends PortfolioExtensionModel{

	public static function createObject($name, $description = "", $content = "", $mimeType = "application/x-msdownload", $artefactClass, $user = null) {
		self::init();
		if ($user == null)
		$user = lms_steam::get_current_user();
		$newArtefact = steam_factory::create_room(
		$GLOBALS[ "STEAM" ]->get_id(),
		$name,
		Artefacts::getArtefactsContainer($user),
			"Artefact: " + $name
		);
		$newArtefact->set_attribute(PORTFOLIO_PREFIX . "TYPE", "ARTEFACT");
		$newArtefact->set_attribute(PORTFOLIO_PREFIX . "ARTEFACTCLASS", $artefactClass);
		$dataPresent = ($content == "") ? false : true;
		$newArtefact->set_attribute(PORTFOLIO_PREFIX . "DATAPRESENT", $dataPresent);
		$newArtefact->set_attribute(PORTFOLIO_PREFIX . "LINKED_LOCATIONS", array());
		$newArtefact->set_attribute("OBJ_TYPE", PORTFOLIO_PREFIX . "ARTEFACT");
		if ($dataPresent){
			$data = steam_factory::create_document(
			$GLOBALS[ "STEAM" ]->get_id(),
			"data",
			$content,
			$mimeType,
			$newArtefact
			);
		}

		$newArtefactObject = Artefacts::getArtefactByRoom($newArtefact);
		$newArtefactObject->checkActivity();
		$newArtefactObject->checkCompetence();

		//Create Forum
		$newArtefactObject->createForum();

		//create and assign groups
		//		$newArtefact->createGroups();

		return $newArtefactObject;
	}

	public function getContent(){
		return $this->getRoom()->get_object_by_name("data")->get_content();
	}

	public function addToPortfolio($portfolio){
		$this->createLinkObject($portfolio->getRoom());
	}

	public function createLinkObject($room){
		$newLink = steam_factory::create_link(
		$GLOBALS[ "STEAM" ]->get_id(),
		$this->getRoom()
		);
		$newLink->move($room);
		$this->addLinkedLocation($room);
		return true;
	}

	/*
	 * following three fuctions for dealing with backlinks
	 */
	public function addLinkedLocation($location){
		$locationsArray = $this->getLinkedLocations();
		$locationsArray[]= $location;
		$this->getRoom()->set_attribute(PORTFOLIO_PREFIX . "LINKED_LOCATIONS", $locationsArray);
	}

	public function removeLinkedLocation($room){
		$locationsArray = $this->getLinkedLocations();
		//		if (!in_array($room, $locationsArray){
		//			return;
		//		}
		$key = array_search($room, $locationsArray);
		if (!$key){
			return;
		}
		unset($locationsArray[$key]);
		//		array_search($room, $this->getLinkedLocations()) ? return : unset($locationsArray[$key]);
		$this->getRoom()->set_attribute(PORTFOLIO_PREFIX . "LINKED_LOCATIONS", $locationsArray);
	}

	private function getLinkedLocations(){
		return $this->getRoom()->get_attribute(PORTFOLIO_PREFIX . "LINKED_LOCATIONS");
	}

	public function getMimeType(){
		if ($this->getRoom()->get_object_by_name("data"))
		return $this->getRoom()->get_object_by_name("data")->get_attribute("DOC_MIME_TYPE");
		return "none";
	}

	//	public function getCompetenceIndex(){
	//		return $this->getRoom()->get_attribute(PORTFOLIO_PREFIX . "COMPETENCE_INDEX");;
	//	}
	//
	//	public function setCompetenceIndex($competenceIndex){
	//		$this->getRoom()->set_attribute(PORTFOLIO_PREFIX . "COMPETENCE_INDEX", $competenceIndex);
	//	}

	public function setData($content, $mimeType){
		if ($this->getRoom()->get_object_by_name("data"))
		$this->getRoom()->get_object_by_name("data")->delete();
		$data = steam_factory::create_document(
		$GLOBALS[ "STEAM" ]->get_id(),
			"data",
		$content,
		$mimeType,
		$this->getRoom()
		);
	}

	public function delete(){
		$objectAuthorizationsGroup = $this->getAuthorizeGroupParent();
		if ($objectAuthorizationsGroup != false) {
			$objectAuthorizationsGroup->delete();
		}
		$this->getRoom()->delete();
	}

	/*
	 * Competences
	 */
	public function addCompetenceString($competenceString, $rating = "10"){
		$competence = CompetenceRaster::getCompetenceById($competenceString);
		$this->addCompetence($competence, $rating);
	}

	public function removeCompetenceString($competenceString){
		$competence = CompetenceRaster::getCompetenceById($competenceString);
		$this->removeCompetence($competence);
	}

	public function addCompetence(Competence $competence, $rating = "10"){
		$this->checkCompetence();
		$competencesRoom = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "COMPETENCES");
		$competence = steam_factory::create_document(
		$GLOBALS[ "STEAM" ]->get_id(),
		$competence->short,
			"",
			"",
		$competencesRoom
		);
		$competence->set_attribute(PORTFOLIO_PREFIX . "RATING", $rating);
	}

	public function removeCompetence(Competence $competence){
		$this->checkCompetence();
		$competenceObject = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "COMPETENCES")->get_object_by_name($competence->short);
		if ($competenceObject instanceof steam_document)
		$competenceObject->delete();
	}

	/**
	 * returns an array of competence objects
	 */
	public function getCompetences(){
		$this->checkCompetence();
		$competences = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "COMPETENCES")->get_inventory();
		//		$competences = $this->getRoom()->get_attribute(PORTFOLIO_PREFIX . "COMPETENCES");
		//		$competenceStrings = array_keys($competences);
		$competencesArray = array();
		//		print "<pre>";
		foreach ($competences as $steamObject) {
			$rating = $steamObject->get_attribute(PORTFOLIO_PREFIX . "RATING");
			//		var_dump($steamObject);
			//		print $steamObject->get_name() . "<br>";
			$competencesArray[]=CompetenceRaster::getCompetenceByIdRated($steamObject->get_name(), $rating);
		}
		//		die;
		return $competencesArray;
	}


	public function getCompetencesStrings(){
		$this->checkCompetence();
		$competences = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "COMPETENCES")->get_inventory();
		$competenceStrings = array();
		foreach ($competences as $steamObject) {
			$competenceStrings[]= $steamObject->get_name();
		}
		return $competences;
	}

	private function checkCompetence(){
		if (!($this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "COMPETENCES") instanceof steam_room));
		\steam_factory::create_room(
		$GLOBALS[ "STEAM" ]->get_id(),
		PORTFOLIO_PREFIX . "COMPETENCES",
		$this->getRoom(),
			"Kompetenzen"
			);
	}

	public function getCompetenceDocument($index){
		$this->checkCompetence();
		$competences = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "COMPETENCES")->get_inventory();
		foreach ($competences as $steamObject) {
			if ($steamObject->get_name() == $index)
			break;

		}
		return $steamObject;
	}

	public function addCommentCompetence($comment, $competenceIndex){
		$this->addComment($this->getCompetenceDocument($competenceIndex));
	}

	/*
	 * Activities
	 */
	public function addActivity($name){
		$this->checkActivity();
		$activitiesRoom = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "ACTIVITIES");
		$activity = steam_factory::create_document(
		$GLOBALS[ "STEAM" ]->get_id(),
		$name,
			"",
			"",
		$activitiesRoom
		);
	}

	public function removeActivity($name){
		$this->checkActivity();
		$activityObject = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "ACTIVITIES")->get_object_by_name($name);
		if ($activityObject instanceof steam_document)
		$activityObject->delete();
	}

	public function getActivities(){
		$this->checkActivity();
		$activities = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "ACTIVITIES")->get_inventory();
		$activitiesArray = array();
		foreach ($activitys as $steamObject) {
			$activitiesArray[]= $steamObject->get_name();
		}
		return $activitiesArray;
	}

	private function checkActivity(){
		if (!($this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "ACTIVITIES") instanceof steam_room));
		\steam_factory::create_room(
		$GLOBALS[ "STEAM" ]->get_id(),
		PORTFOLIO_PREFIX . "ACTIVITIES",
		$this->getRoom(),
			"Fertigkeiten"
			);
	}

	public function getActivityDocument($index){
		$this->checkActivity();
		$activities = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "ACTIVITIES")->get_inventory();
		foreach ($activities as $steamObject) {
			if ($steamObject->get_name() == $index)
			break;

		}
		return $steamObject;
	}

	private function addComment($doc){
		$comment = steam_factory::create_document(
		$GLOBALS[ "STEAM" ]->get_id(),
		"COMMENT",
		$comment,
			""
			);
			$doc->add_annotation($comment);
	}

	public function setTimeAcquired($timestamp){
		$this->getRoom()->set_attribute(PORTFOLIO_PREFIX . "TIME_ACQUIRED", $timestamp);
	}

	public function getTimeAcquired(){
		$attribute = $this->getAttribute(PORTFOLIO_PREFIX . "TIME_ACQUIRED");
		return $attribute;
	}

	public function addCommentActivity($comment, $activityName){
		$this->addComment($this->getActivityDocument($activityName));
	}

	private function getComments($doc){
		$comments = array();
		$docArray = $doc->get_annotations();
		foreach ($docArray as $doc){
			$comments []= $doc->get_content();
		}
		return $comments;
	}

	public function getCompetenceComments($index){
		return getComments($this->getCompetenceDocument($index));
	}
	public function getActivityComments($index){
		return getComments($this->getActivityDocument($index));
	}

	public function getArtefactClass(){
		$attribute = $this->getAttribute(PORTFOLIO_PREFIX . "ARTEFACTCLASS");
		return $attribute;
	}

	/**
	 *
	 * Returns the attribute value if it exists
	 * Returns null if the attribute does not exist.
	 * @param string $name
	 */
	private function getAttribute($name){
		$names = $this->getRoom()->get_attribute_names();
		if (in_array(PORTFOLIO_PREFIX . "ARTEFACTCLASS", $names)){
			$attribute =  $this->getRoom()->get_attribute(PORTFOLIO_PREFIX . "ARTEFACTCLASS");
		} else {
			$attribute = null;
		}
		return $attribute;
	}
	
	public function getEntryDateRaw() {
		return $this->get_attribute("PORTFOLIO_ENTRY_DATE");
	}
	
	public function setEntryDateRaw($date) {
		return $this->set_attribute("PORTFOLIO_ENTRY_DATE", $date);
	}
	
	public function getEntryDate() {
		return ($this->getEntryDateRaw() !== 0) ? $this->getEntryDateRaw() : "";
	}
	
	public function getEntryNoteRaw() {
		return $this->get_attribute("PORTFOLIO_ENTRY_NOTE");
	}
	
	public function setEntryNoteRaw($note) {
		return $this->set_attribute("PORTFOLIO_ENTRY_NOTE", $note);
	}
	
	public function getEntryNote() {
		return ($this->getEntryNoteRaw() !== 0) ? $this->getEntryNoteRaw() : "";
	}
}
?>