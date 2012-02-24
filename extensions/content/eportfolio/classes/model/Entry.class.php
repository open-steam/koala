<?php

namespace Portfolio\Model;

class Entry extends Portfolios {

    private $room;
    public $entryAttributes = array();

    public function __construct(\steam_room $room) {
        $this->room = $room;
        $this->entryAttributes["date"] = array(
            "attributeName" => PORTFOLIO_PREFIX . "ENTRY_DATE",
            "label" => "Datum",
            "description" => "",
            "widget" => "\Widgets\DatePicker",
            "widgetMethods" => array("setPlaceholder" => "z.B. 01.01.1995"
            ),
            "defaultValue" => ""
        );
        $this->entryAttributes["note"] = array(
            "attributeName" => PORTFOLIO_PREFIX . "ENTRY_NOTE",
            "label" => "Bemerkung",
            "description" => "",
            "widget" => "\Widgets\TextInput",
            "widgetMethods" => array("setPlaceholder" => "z.B. inhaltliche Schwerpunkte; besondere Leistungen"
            ),
            "defaultValue" => ""
        );
    }

    public function __call($name, $param) {
        if (is_callable(array($this->room, $name))) { // steam attribute call
            return call_user_func_array(array($this->room, $name), $param);
        } else {
            throw new \Exception("Method " . $name . " can be called.");
        }
    }

    public function getRoom() {
        return $this->room;
    }

    public function addArtefact($name, $content, $mimeType) {
        $room = $this->getArtefactRoom();
        if ($room->get_object_by_name($name) instanceof steam_document) {
            $room->get_object_by_name($name)->delete();
        }
        $artefact = steam_factory::create_document(
                        $GLOBALS["STEAM"]->get_id(), $name, $content, $mimeType, $room
        );
    }

    public function deleteArtefact($name) {
        $room = $this->getArtefactRoom();
        if ($room->get_object_by_name($name) instanceof steam_document) {
            $room->get_object_by_name($name)->delete();
        }
    }

    public function getArtefact($name = null) {
        $room = $this->getArtefactRoom();
        if (isset($name)) {
            return $room->get_object_by_name($name);
        } else {
            $artefactArray = $this->getArtefactArray();
            return $artefactArray[0];
        }
    }

    public function getArtefactArray() {
        $room = $this->getArtefactRoom();
        $array = $room->get_inventory_filtered(
                array(array('+', 'class', CLASS_DOCUMENT)), array()
        );
        return $array;
    }

    /*
     * Competences
     */

    public function addCompetenceString($competenceString, $rating = "1") { //TODO: Rating
        $competence = CompetenceRaster::getCompetenceById($competenceString);
        $this->addCompetence($competence, $rating);
    }

    public function removeCompetenceString($competenceString) {
        $competence = CompetenceRaster::getCompetenceById($competenceString);
        $this->removeCompetence($competence);
    }

    public function addCompetence(Competence $competence, $rating = "1") {
        $this->checkCompetence();
        $competencesRoom = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "COMPETENCES");
        $competence = steam_factory::create_document(
                        $GLOBALS["STEAM"]->get_id(), $competence->short, "", "", $competencesRoom
        );
        $competence->set_attribute(PORTFOLIO_PREFIX . "RATING", $rating);
    }

    public function removeCompetence(Competence $competence) {
        $this->checkCompetence();
        $competenceObject = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "COMPETENCES")->get_object_by_name($competence->short);
        if ($competenceObject instanceof steam_document)
            $competenceObject->delete();
    }

    /**
     * returns an array of competence objects
     */
    public function getCompetences() {
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
            $competencesArray[] = CompetenceRaster::getCompetenceByIdRated($steamObject->get_name(), $rating);
        }
        //		die;
        return $competencesArray;
    }

    public function getCompetencesStrings() {
        $this->checkCompetence();
        $competences = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "COMPETENCES")->get_inventory();
        $competenceStrings = array();
        foreach ($competences as $steamObject) {
            $competenceStrings[] = $steamObject->get_name();
        }
        return $competences;
    }

    /**
     *
     * Check for existing or need to create competences folder
     */
    private function checkCompetence() {
        if (!($this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "COMPETENCES") instanceof steam_room))
            \steam_factory::create_room(
                    $GLOBALS["STEAM"]->get_id(), PORTFOLIO_PREFIX . "COMPETENCES", $this->getRoom(), "Kompetenzen"
            );
    }

    /**
     *
     * Getting the competence document from the entry
     * @param  Index of the Competence
     */
    public function getCompetenceDocument($index) {
        $this->checkCompetence();
        $competences = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "COMPETENCES")->get_inventory();
        foreach ($competences as $steamObject) {
            if ($steamObject->get_name() == $index)
                break;
        }
        return $steamObject;
    }

    /*
     * Activities
     */

    public function addActivity($name) {
        $this->checkActivity();
        $activitiesRoom = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "ACTIVITIES");
        $activity = steam_factory::create_document(
                        $GLOBALS["STEAM"]->get_id(), $name, "", "", $activitiesRoom
        );
    }

    public function removeActivity($name) {
        $this->checkActivity();
        $activityObject = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "ACTIVITIES")->get_object_by_name($name);
        if ($activityObject instanceof steam_document)
            $activityObject->delete();
    }

    public function getActivities() {
        $this->checkActivity();
        $activities = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "ACTIVITIES")->get_inventory();
        $activitiesArray = array();
        foreach ($activitys as $steamObject) {
            $activitiesArray[] = $steamObject->get_name();
        }
        return $activitiesArray;
    }

    private function checkActivity() {
        if (!($this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "ACTIVITIES") instanceof steam_room))
            ;
        \steam_factory::create_room(
                $GLOBALS["STEAM"]->get_id(), PORTFOLIO_PREFIX . "ACTIVITIES", $this->getRoom(), "Fertigkeiten"
        );
    }

    public function getActivityDocument($index) {
        $this->checkActivity();
        $activities = $this->getRoom()->get_object_by_name(PORTFOLIO_PREFIX . "ACTIVITIES")->get_inventory();
        foreach ($activities as $steamObject) {
            if ($steamObject->get_name() == $index)
                break;
        }
        return $steamObject;
    }

    public function getEntryClass() {
        $attribute = $this->getAttribute(PORTFOLIO_PREFIX . "ENTRYCLASS");
        return $attribute;
    }

    /**
     *
     * Returns the attribute value if it exists
     * Returns null if the attribute does not exist.
     * @param string $name
     */
    private function getAttribute($name) {
        $names = $this->getRoom()->get_attribute_names();
        if (in_array(PORTFOLIO_PREFIX . "ENTRYCLASS", $names)) {
            $attribute = $this->getRoom()->get_attribute(PORTFOLIO_PREFIX . "ENTRYCLASS");
        } else {
            $attribute = null;
        }
        return $attribute;
    }

    public function delete() {
        $this->room->delete();
    }

    public static function getEntryByRoom($room) {
        $entryClass = $room->get_attribute(PORTFOLIO_PREFIX . "ENTRYCLASS");
        switch ($entryClass) {
            case "CERTIFICATE":
                $newEntryObject = new EntryCertificate($room);
                break;
            case "SCHOOL":
                $newEntryObject = new EntrySchool($room);
                break;
            case "EMPLOYMENT":
                $newEntryObject = new EntryEmployment($room);
                break;
            case "EDUCATION":
                $newEntryObject = new EntryEducation($room);
                break;
            case "ACADEMIC":
                $newEntryObject = new EntryAcademic($room);
                break;
            case "OTHER":
                $newEntryObject = new EntryOther($room);
                break;
            default :
                $newEntryObject = new EntryCertificate($room);
                break;
        }
        return $newEntryObject;
    }

    public static function getViewWidget(Portfolios $portfolio) {
        $entries = $portfolio->getEntriesByClass(get_called_class());
        $box = new \Widgets\Box();
        if (Portfolios::isManager()) {
            $addButton = new \Widgets\RawHtml();
            $addButton->setHtml("<a class=\"\" title=\"Eintrag hinzufügen\" onclick=\"sendRequest('editDialog', {'env':'{$portfolio->getId()}','type':'" . static::$entryType . "'}, '', 'popup', null, null);return false;\" href=\"#\">+</a>");
            $box->addWidget($addButton);
            $box->setTitle("<div style=\"float:right\">" . $addButton->getHtml() . "</div>" . static::$entryTypeDescription);
        } else {
            $box->setTitle(static::$entryTypeDescription);
        }

        $box->setTitleLink(PATH_URL . "portfolio/");

        $html = "<div style=\"text-align: center; color: gray; font-size: 80%\">" . static::$entryTypeInfo . "</div><br>";

        $class = get_called_class();
        foreach ($entries as $entry) {
            if ($entry instanceof $class) {
                $html .= $entry->getViewHtml($portfolio) . "<br>";
            }
        }

        $box->setContent($html);
        $box->setContentMoreLink(PATH_URL . "portfolio/");
        return $box;
    }

    private function getViewHtml(Portfolios $portfolio) {
        $contentHtml = "";
        foreach ($this->entryAttributes as $entryAttribute) {
            $contentHtml .= $entryAttribute["label"] . ": <em>" . $this->getReadableData($entryAttribute) . "</em><br clear=all>";
        }
        if (static::$entryTypeHasCompetences) {
            $editCompetencesHtml = "<a href=\"#\" onclick=\"sendRequest('competencesDialog', {'id':'{$this->get_id()}'}, '', 'popup', null, null);return false;\"><img src=\"/explorer/asset/icons/menu/rename.png\"> Kompetenzen bearbeiten</a> |";
        } else {
            $editCompetencesHtml = "";
        }
        if (Portfolios::isManager()) {
            $html = <<<END
		<div style="border: 3px dotted lightblue; padding: 5px; background-color: #ffe">
			{$contentHtml}
			<div style="float: right; display: inline">
				<a href="#" onclick="sendRequest('editDialog', {'id':'{$this->get_id()}'}, '', 'popup', null, null);return false;"><img src="/explorer/asset/icons/menu/rename.png"> bearbeiten</a> |
				{$editCompetencesHtml}
                                <a href="#"><img src="/explorer/asset/icons/mimetype/generic.png"> Beleg prüfen ({$this->getArtefactCount()})</a> |
				<a href="#" onclick="sendRequest('commentDialog', {'id':'{$this->get_id()}'}, '', 'popup', null, null);return false;"><img src="/explorer/asset/icons/mimetype/text.png"> kommentieren ({$this->getCommentsCount()})</a>
			</div>
			<br clear=all>
		</div>
END
            ;
        } else if ($portfolio->isOwner()) {
            $html = <<<END
		<div style="border: 3px dotted lightblue; padding: 5px; background-color: #ffe">
			{$contentHtml}
			<div style="float: right; display: inline">
				<a href="#"><img src="/explorer/asset/icons/mimetype/generic.png"> Beleg anfügen ({$this->getArtefactCount()})</a> |
				<a href="#" onclick="sendRequest('commentDialog', {'id':'{$this->get_id()}'}, '', 'popup', null, null);return false;"><img src="/explorer/asset/icons/mimetype/text.png"> kommentieren ({$this->getCommentsCount()})</a>
			</div>
			<br clear=all>
		</div>
END
            ;
        } else if (Portfolios::isViewer()) {
            $html = <<<END
		<div style="border: 3px dotted lightblue; padding: 5px; background-color: #ffe">
			{$contentHtml}
			<div style="float: right; display: inline">
				<a href="#"><img src="/explorer/asset/icons/mimetype/generic.png"> Beleg einsehen ({$this->getArtefactCount()})</a> |
				<a href="#" onclick="sendRequest('commentDialog', {'id':'{$this->get_id()}'}, '', 'popup', null, null);return false;"><img src="/explorer/asset/icons/mimetype/text.png"> Kommentare anzeigen ({$this->getCommentsCount()})</a>
			</div>
			<br clear=all>
		</div>
END
            ;
        }
        return $html;
    }

    private function getReadableData($entryAttribute) {
        $value = "";
        $raw = $this->get_attribute($entryAttribute["attributeName"]);
        if ($entryAttribute["widget"] === "\Widgets\ComboBox" && isset($entryAttribute["widgetMethods"]["setOptions"])) {
            $options = $entryAttribute["widgetMethods"]["setOptions"];
            foreach ($options as $options) {
                if ($options["value"] === $raw) {
                    $value = $options["name"];
                    return $value;
                }
            }
        } else {
            if ($raw === 0) {
                $value = "";
            } else {
                $value = $raw;
            }
        }
        return $value;
    }

    public static function getEntryTypeDescription() {
        return static::entryTypeDescription;
    }

    public static function getEntryTypeInfo() {
        return static::$entryTypeInfo;
    }

    public static function getEntryTypeEditDescription() {
        return static::$entryTypeEditDescription;
    }

    public static function getEntryTypeEditInfo() {
        return static::$entryTypeEditInfo;
    }

    public static function getEntryType() {
        return static::$entryType;
    }

    public function getEntryAttributes() {
        return $this->entryAttributes;
    }
    
    public function getCommentsCount() {
        $threads = $this->get_annotations();
        if (isset($threads[0])) {
            return sizeof($threads[0]->get_annotations());
        } else {
            return "0";
        }
    }
    
    public function getArtefactCount() {
        return "0";
    }

}

?>