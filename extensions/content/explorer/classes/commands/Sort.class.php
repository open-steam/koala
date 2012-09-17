<?php

namespace Explorer\Commands;

class Sort extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $oldIds;
    private $newIds;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        $this->newIds = $this->params["newIds"];
        $this->oldIds = $this->params["oldIds"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $parent = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $inv = $parent->get_inventory();
        $newIdArray = array();
        $newIdArray = explode(",", $this->newIds);

        $oldIdArray = array();
        $oldIdArray = explode(",", $this->oldIds);

        $boolHelper = true;
        $counter = 0;
        $startValue = 0;
        for ($i = 0; $i < count($oldIdArray); $i++) {
            if ($oldIdArray[$i] != $newIdArray[$i]) {
                if ($boolHelper) {
                    $boolHelper = false;
                    $startValue = $i;
                }
                $counter++;
            }
        }
        $length = $startValue + $counter - 1;
        if ($length > $startValue) {
            $parent->swap_inventory($startValue, ($length));
        }

        
        for ($j = $startValue + 1; $j < ($length); $j++) {
            $parent->swap_inventory($j, $j + 1);
        }
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}

?>