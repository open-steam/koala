<?php
namespace Pyramiddiscussion\Commands;

class InitializePyramid extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        if (isset($this->params["id"])) {
            $this->id = $this->params["id"];
            $this->initializePyramid();
        } 
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        if (!isset($this->id)) {
            $jswrapper = new \Widgets\JSWrapper();
            $ids = "{\"id\":\"" . $this->params["pyramid"] . "\"}";
            $elements = "\"\"";
            $js = "sendMultiRequest('InitializePyramid', jQuery.parseJSON('[$ids]'), jQuery.parseJSON('[$elements]'), 'updater', null, null, 'Pyramiddiscussion', 'Initialisiere Pyramide ...', 0, 1);";
            $jswrapper->setJs($js);
            $ajaxResponseObject->addWidget($jswrapper);
        } else {
           $jswrapper = new \Widgets\JSWrapper();
            $js = "window.location.reload();";
            $jswrapper->setJs($js);
            $ajaxResponseObject->addWidget($jswrapper);
        }
        return $ajaxResponseObject;
    }
    
    private function initializePyramid() {
        $pyramidRoom = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $maxcol = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_MAXCOL");
        $start = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_MAX");
        $basegroup = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_PRIVGROUP");
        $editor = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_EDITOR"); //$this->params["editor"]
        
        $groups = array();
        for ($count = 1; $count <= $maxcol; $count++) {
            for ($count2 = 1; $count2 <= $start / pow(2, $count - 1); $count2++) {
                $newGroup = \steam_factory::create_group($GLOBALS["STEAM"]->get_id(), "group_" . $count . "_" . $count2, $basegroup);
                $newGroup->set_insert_access($basegroup);
                $newGroup->set_attribute("GROUP_INVISIBLE", true);
                $groups[$count . $count2] = $newGroup;

                $newPosition = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "Position_" . $count . "_" . $count2, "", $editor, $pyramidRoom, "Position " . $count . "-" . $count2);
                $newPosition->set_attribute("PYRAMIDDISCUSSION_COLUMN", $count);
                $newPosition->set_attribute("PYRAMIDDISCUSSION_ROW", $count2);
                $newPosition->set_attribute("PYRAMIDDISCUSSION_POS_READ_STATES", array());
                $newPosition->set_attribute("PYRAMIDDISCUSSION_POS_TITLE", "");
                $newPosition->set_attribute("PYRAMIDDISCUSSION_RELGROUP", $newGroup);
            }
        }
        // generate group structure
        for ($count = 2; $count <= $maxcol; $count++) {
            for ($count2 = 1; $count2 <= $start / pow(2, $count - 1); $count2++) {
                $groups[$count . $count2]->add_member($groups[$count - 1 . ($count2 * 2 - 1)]);
                $groups[$count . $count2]->add_member($groups[$count - 1 . ($count2 * 2)]);
            }
        }
        foreach ($groups as $group) {
            $basegroup->add_member($group);
        }
        
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_INITIALIZED", 1);
    }
}
?>