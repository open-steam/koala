<?php
namespace PortletSubscription\Subscriptions;

class RapidfeedbackSubscription extends AbstractSubscription {

    public function getUpdates() {
        $updates = array();
        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        if ($this->object->get_creator()->get_id() == $user->get_id()) {
            $surveys = $this->object->get_inventory();
            $count = 0;
            foreach ($surveys as $survey) {
                $result_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
                $results = $result_container->get_inventory();
                foreach ($results as $result) {
                    if ($result instanceof \steam_document && $result->get_attribute("RAPIDFEEDBACK_RELEASED") != 0) {
                        if ($result->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !(isset($this->filter[$result->get_id()]) && in_array($result->get_attribute("OBJ_CREATION_TIME"), $this->filter[$result->get_id()]))) {
                            $updates[] = array(
                                $result->get_attribute("OBJ_CREATION_TIME"),
                                $result->get_id(),
                                $this->getElementHtml(
                                        $result->get_id(), 
                                        $result->get_id() . "_" . $count, 
                                        $this->private, 
                                        $result->get_attribute("OBJ_CREATION_TIME"), 
                                        $this->depth == 0 ? "Neue Abgabe:" : "Neue Abgabe (in Fragebogen Ordner <a href=\"" . PATH_URL . "rapidfeedback/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):", 
                                        \PortletSubscription::getNameForSubscription($suevey),
                                        PATH_URL . "rapidfeedback/individualResults/" . $survey->get_id() . "/"
                                )
                            );
                        }
                    }
                }
                $count++;
            }
        }
        return $updates;
    }

}

?>
