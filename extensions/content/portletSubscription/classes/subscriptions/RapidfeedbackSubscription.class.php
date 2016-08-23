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
                    if ($result->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !(isset($this->filter[$result->get_id()]) && in_array($result->get_attribute("OBJ_CREATION_TIME"), $this->filter[$result->get_id()]))) {
                        $this->updates[] = array(
                            $result->get_attribute("OBJ_CREATION_TIME"),
                            $result->get_id(),
                            $this->getElementHtml(
                                    $result->get_id(), 
                                    $result->get_id() . "_" . $this->count, 
                                    $this->private, 
                                    $result->get_attribute("OBJ_CREATION_TIME"), 
                                    "Neue Abgabe (in Fragebogen Ordner <a href=\"" . PATH_URL . "rapidfeedback/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):", 
                                    \PortletSubscription::getNameForSubscription($survey), 
                                    PATH_URL . "rapidfeedback/individualResults/" . $survey->get_id() . "/"
                            )
                        );
                    } else if ($result->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && !(isset($this->filter[$result->get_id()]) && in_array($result->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$result->get_id()]))) {
                        $this->updates[] = array(
                            $result->get_attribute("OBJ_LAST_CHANGED"),
                            $result->get_id(),
                            $this->getElementHtml(
                                    $result->get_id(), 
                                    $result->get_id() . "_" . $this->count, 
                                    $this->private, 
                                    $result->get_attribute("OBJ_LAST_CHANGED"), 
                                    "Geänderte Abgabe (in Fragebogen Ordner <a href=\"" . PATH_URL . "rapidfeedback/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):", 
                                    \PortletSubscription::getNameForSubscription($survey), 
                                    PATH_URL . "rapidfeedback/individualResults/" . $survey->get_id() . "/"
                            )
                        );
                    }
                }
            }



            if ($survey instanceof \steam_container) {
                if ($survey->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !(isset($this->filter[$survey->get_id()]) && in_array($survey->get_attribute("OBJ_CREATION_TIME"), $this->filter[$survey->get_id()]))) {
                    $this->updates[] = array(
                        $survey->get_attribute("OBJ_CREATION_TIME"),
                        $survey->get_id(),
                        $this->getElementHtml(
                                $survey->get_id(), 
                                $survey->get_id() . "_" . $this->count, 
                                $this->private, 
                                $survey->get_attribute("OBJ_CREATION_TIME"), 
                                "Neuer Fragebogen in <a href=\"" . PATH_URL . "rapidfeedback/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>:", 
                                $survey->get_attribute("OBJ_DESC"), 
                                PATH_URL . "rapidfeedback/individualResults/" . $survey->get_id() . "/"
                        )
                    );
                } else if ($survey->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && !(isset($this->filter[$survey->get_id()]) && in_array($survey->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$survey->get_id()]))) {
                    $this->updates[] = array(
                        $survey->get_attribute("OBJ_LAST_CHANGED"),
                        $survey->get_id(),
                        $this->getElementHtml(
                                $survey->get_id(), 
                                $survey->get_id() . "_" . $this->count, 
                                $this->private, 
                                $survey->get_attribute("OBJ_LAST_CHANGED"), 
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
