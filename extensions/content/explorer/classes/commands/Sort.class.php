<?php

namespace Explorer\Commands;

class Sort extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $newIds;
    private $inv;
    private $cE;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        $this->newIds = $this->params["newIds"];
        $this->cE = $this->params["changedElement"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $parent = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $this->inv = $parent->get_inventory();

        $numberOfInvalidObjs = $this->countInvalidObjs();

        $newIdArray = array();
        $newIdArray = explode(",", $this->newIds);

        $oldIdArray = array();
        for ($i = $numberOfInvalidObjs; $i < count($this->inv); $i++) {
            $oldIdArray[$i - $numberOfInvalidObjs] = $this->inv[$i]->get_id();
        }
        $oldPosition = 0;
        while ($oldIdArray[$oldPosition] != $this->cE) {
            $oldPosition++;
        }
        $newPosition = 0;
        while ($newIdArray[$newPosition] != $this->cE) {
            $newPosition++;
        }
        $oldPosition +=$numberOfInvalidObjs;
        $newPosition +=$numberOfInvalidObjs;
        if ($oldPosition < $newPosition) {
            for ($i = $oldPosition; $i < $newPosition; $i++) {
                $parent->swap_inventory($i, $i + 1);
            }
        }
        if ($oldPosition > $newPosition) {
            echo 1 . "          ";
            for ($i = $oldPosition; $i > $newPosition; $i--) {
                $parent->swap_inventory($i, $i - 1);
            }
        }
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

    private function repairOrder($obj) {
        $counter = 0;
        foreach ($this->inv as $i => $item) {
            if ($item instanceof \steam_trashbin || $item instanceof \steam_user) {
                $helper = $this->inv[$i];
                $this->inv[$i] = $this->inv[$counter];
                $this->inv[$counter] = $helper;
                $obj->swap_inventory($counter, $i);
                $counter++;
            }
        }
    }

    private function countInvalidObjs() {
        $counter = 0;
        while ($this->inv[$counter] instanceof \steam_trashbin || $this->inv[$counter] instanceof \steam_user) {
            $counter++;
        }
        return $counter;
    }

}

?>