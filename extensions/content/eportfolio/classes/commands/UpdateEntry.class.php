<?php

namespace Portfolio\Commands;

class UpdateEntry extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $entry;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->id = $this->params[0] : "";
        } else if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        }
        if (!isset($this->id) || $this->id === "") {
            throw new \Exception("no valid id");
        } else {
            $room = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            if ($room instanceof \steam_room) {
                $this->entry = \Portfolio\Model\Entry::getEntryByRoom($room);
            }
        }
        $this->id = $this->entry->get_id();
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        if ($this->entry instanceof \Portfolio\Model\EntryEducation) {
            if ($this->entry->get_attribute(PORTFOLIO_PREFIX . "ENTRY_EDUCATION_STATE") === "1") {
                $job = $this->entry->get_attribute(PORTFOLIO_PREFIX . "ENTRY_EDUCATION_TYPE");
                $competences = \Portfolio\Model\Competence::getCompetences($job);
                foreach ($competences as $competence) {
                    $this->entry->addCompetence($competence);
                }
            }
        } else if ($this->entry instanceof \Portfolio\Model\EntryAcademic) {
            if ($this->entry->get_attribute(PORTFOLIO_PREFIX . "ENTRY_ACADEMIC_STATE") === "1") {
                $job = $this->entry->get_attribute(PORTFOLIO_PREFIX . "ENTRY_ACADEMIC_TYPE");
                $competences = \Portfolio\Model\Competence::getCompetences($job);
                foreach ($competences as $competence) {
                    $this->entry->addCompetence($competence);
                }
            }
        }



        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}

?>