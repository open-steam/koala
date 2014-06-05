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
        $presentation = $parent->get_attribute("bid:presentation");
        $head = false;
        if ($presentation == "head") {
            $head = true;
        }
        $this->inv = $parent->get_inventory();
        $numberOfInvalidObjs = $this->countInvalidObjs();
        if($head){
            $numberOfInvalidObjs += 1;
        }

        $newIdArray = array();
        $newIdArray = explode(",", $this->newIds);
        foreach ($newIdArray as $index => $ele) {
            $newIdArray[$index] = intval($ele);
        }


        $oldIdArray = array();

        for ($i = $numberOfInvalidObjs; $i < count($this->inv); $i++) {
            $oldIdArray[$i - $numberOfInvalidObjs] = $this->inv[$i]->get_id();
        }


        $hiddenElements = array();
        $hiddenElements = array_diff($oldIdArray, $newIdArray);

        $exchangeArray = array();

        for ($i = 0; $i < $numberOfInvalidObjs; $i++) {
            $exchangeArray[$i] = $this->inv[$i]->get_id();
        }

        for ($i = $numberOfInvalidObjs; $i < (count($newIdArray) + $numberOfInvalidObjs); $i++) {
            $exchangeArray[$i] = $newIdArray[$i - $numberOfInvalidObjs];
        }

        $i = count($newIdArray) + $numberOfInvalidObjs;
        foreach ($hiddenElements as $he) {
            $exchangeArray[$i] = $he;
            $i++;
        }

        $parent->order_inventory_objects($exchangeArray);

        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
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