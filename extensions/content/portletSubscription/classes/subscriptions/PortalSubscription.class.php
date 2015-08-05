<?php
namespace PortletSubscription\Subscriptions;

class PortalSubscription extends AbstractSubscription {
    
    public function getUpdates() {
        $updates = array();
        $columns = $this->object->get_inventory();
        $count = 0;
        foreach ($columns as $column) {
            if ($column instanceof \steam_container) {
                $portlets = $column->get_inventory();
                foreach ($portlets as $portlet) {
                    if ($portlet instanceof \steam_container) {
                        if ($portlet->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !(isset($this->filter[$portlet->get_id()]) && in_array($portlet->get_attribute("OBJ_CREATION_TIME"), $this->filter[$portlet->get_id()]))) {
                            $updates[] = array(
                                            $portlet->get_attribute("OBJ_CREATION_TIME"), 
                                            $portlet->get_id(),
                                            $this->getElementHtml(
                                                $portlet->get_id(), 
                                                $portlet->get_id() . "_" . $count,
                                                $this->private,
                                                $portlet->get_attribute("OBJ_CREATION_TIME"),
                                                $this->depth == 0 ? "Neues Portlet:" : "Neues Portlet (in Portal <a href=\"" . PATH_URL . "portal/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                                \PortletSubscription::getNameForSubscription($portlet),
                                                PATH_URL . "portal/Index/" . $this->object->get_id() . "/",
                                                " (Spalte " . $column->get_name() . ")"
                                            )
                                        );
                        } else if ($portlet->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && !(isset($this->filter[$portlet->get_id()]) && in_array($portlet->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$portlet->get_id()]))) {
                            $updates[] = array(
                                            $portlet->get_attribute("OBJ_LAST_CHANGED"), 
                                            $portlet->get_id(),
                                            $this->getElementHtml(
                                                $portlet->get_id(), 
                                                $portlet->get_id() . "_" . $count,
                                                $this->private,
                                                $portlet->get_attribute("OBJ_LAST_CHANGED"),
                                                $this->depth == 0 ? "Geändertes Portlet:" : "Geändertes Portlet (in Portal <a href=\"" . PATH_URL . "portal/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                                \PortletSubscription::getNameForSubscription($portlet),
                                                PATH_URL . "portal/Index/" . $this->object->get_id() . "/",
                                                " (Spalte " . $column->get_name() . ")"
                                            )
                                        );
                        }
                    }
                }
            }
            $count++;
        }
        return $updates;
    }
}
?>