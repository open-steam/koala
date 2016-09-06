<?php
namespace PortletSubscription\Subscriptions;

class RapidfeedbackSubscription extends AbstractSubscription {

    public function getUpdates() {
        
        $this->content = $this->object->get_inventory();
        
        // travers over all surveys in this container
        foreach ($this->content as $survey) {
            $result_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
            $results = $result_container->get_inventory();
            foreach ($results as $result) {
                if ($result instanceof \steam_document && $result->get_attribute("RAPIDFEEDBACK_RELEASED") != 0) {
                    $resultCreationTime = $result->get_attribute("OBJ_CREATION_TIME");
                    $resultLastChanged = $result->get_attribute("OBJ_LAST_CHANGED");
                    if ($resultCreationTime > $this->timestamp && !(isset($this->filter[$result->get_id()]) && in_array($resultCreationTime, $this->filter[$result->get_id()]))) {
                        $this->updates[] = array(
                            $resultCreationTime,
                            $result->get_id(),
                            $this->getElementHtml(
                                    $result->get_id(), 
                                    $result->get_id() . "_" . $this->count, 
                                    $this->private, 
                                    $resultCreationTime, 
                                    "Neue Abgabe (in Fragebogen Ordner <a href=\"" . PATH_URL . "rapidfeedback/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):", 
                                    \PortletSubscription::getNameForSubscription($result), 
                                    PATH_URL . "rapidfeedback/view/" . $survey->get_id() . "/1/". $result->get_id() ."/1/"
                                    //                       id of the survey object / page / result id / if isset: forms are view only
                            )
                        );
                    }
                    if ($resultLastChanged > $this->timestamp && $resultLastChanged > $resultCreationTime && !(isset($this->filter[$result->get_id()]) && in_array($resultLastChanged, $this->filter[$result->get_id()]))) {
                        $this->updates[] = array(
                            $resultLastChanged,
                            $result->get_id(),
                            $this->getElementHtml(
                                    $result->get_id(), 
                                    $result->get_id() . "_" . $this->count, 
                                    $this->private, 
                                    $resultLastChanged, 
                                    "Geänderte Abgabe (in Fragebogen Ordner <a href=\"" . PATH_URL . "rapidfeedback/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):", 
                                    \PortletSubscription::getNameForSubscription($result), 
                                    PATH_URL . "rapidfeedback/view/" . $survey->get_id() . "/1/". $result->get_id() ."/1/"
                            )
                        );
                    }
                }
            }



            if ($survey instanceof \steam_container) {
                $surveyCreationTime = $survey->get_attribute("OBJ_CREATION_TIME");
                $surveyLastChanged = $survey->get_attribute("OBJ_LAST_CHANGED");
                
                if ($surveyCreationTime > $this->timestamp && !(isset($this->filter[$survey->get_id()]) && in_array($surveyCreationTime, $this->filter[$survey->get_id()]))) {
                    $this->updates[] = array(
                        $surveyCreationTime,
                        $survey->get_id(),
                        $this->getElementHtml(
                                $survey->get_id(), 
                                $survey->get_id() . "_" . $this->count, 
                                $this->private, 
                                $surveyCreationTime, 
                                "Neuer Fragebogen in <a href=\"" . PATH_URL . "rapidfeedback/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>:", 
                                $survey->get_attribute("OBJ_DESC"), 
                                PATH_URL . "rapidfeedback/individualResults/" . $survey->get_id() . "/"
                        )
                    );
                }
                if ($surveyLastChanged > $this->timestamp && $surveyLastChanged > $surveyCreationTime && !(isset($this->filter[$survey->get_id()]) && in_array($surveyLastChanged, $this->filter[$survey->get_id()]))) {
                    $this->updates[] = array(
                        $surveyLastChanged,
                        $survey->get_id(),
                        $this->getElementHtml(
                                $survey->get_id(), 
                                $survey->get_id() . "_" . $this->count, 
                                $this->private, 
                                $surveyLastChanged, 
                                "Geänderter Fragebogen in <a href=\"" . PATH_URL . "rapidfeedback/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>:", 
                                $survey->get_attribute("OBJ_DESC"), 
                                PATH_URL . "rapidfeedback/individualResults/" . $survey->get_id() . "/"
                        )
                    );
                }
            }

            $this->count++;
        }
        
        //if the object change doesn't come from the modified content, the object itself was modified
        if ($this->object->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && $this->object->get_attribute("CONT_LAST_MODIFIED") != $this->object->get_attribute("OBJ_LAST_CHANGED") && !(isset($this->filter[$this->object->get_id()]) && in_array($this->object->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$this->object->get_id()]))) {
            
            $this->updates[] = array(
                            $this->object->get_attribute("OBJ_LAST_CHANGED"), 
                            $this->object->get_id(), 
                            $this->getElementHtml(
                                $this->object->get_id(), 
                                $this->object->get_id() . "_0",
                                $this->private,
                                $this->object->get_attribute("OBJ_LAST_CHANGED"),
                                "Die Fragebogeneigenschaften wurden geändert",
                                "",
                                \ExtensionMaster::getInstance()->getUrlForObjectId($this->object->get_id(), "view")
                            )
            );
        }

        return $this->updates;
    }

}

?>
