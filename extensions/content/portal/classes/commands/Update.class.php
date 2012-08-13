<?php

namespace Portal\Commands;

class Update extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

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
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        $column = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $portletsOld = $column->get_inventory();
        $portletsOldIds = array();

        foreach ($portletsOld as $p) {
            $portletsOldIds[] = $p->get_id();
        }

        $portletsIdsNew = array();
        $portletsIdsNew = explode(",", $this->params["elements"]);
        unset($portletsIdsNew[count($portletsIdsNew) - 1]);

        $countPortOld = count($portletsOldIds);
        $countPortNew = count($portletsIdsNew);

        //element leave the current column
        //kein löschen des aktuellen Objekts erforderlich... wird im folgenden Aufruf in andere Umwelt verschoben.
        //if($countPortOld > $countPortNew){
        //    $difference = array();
        //    $difference = array_diff($portletsOldIds, $portletsIdsNew);
        //    $movedElementObj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $difference[0]);
        //}
        if ($countPortOld < $countPortNew) {
            return $ajaxResponseObject;
        }
        if ($countPortOld < $countPortNew) {

            $difference = array();
            $difference = array_diff($portletsIdsNew, $portletsOldIds);
            if (count($difference) != 1) {
                $difference = array_diff($portletsOldIds, $portletsIdsNew);
            }

            foreach ($difference as $d) {
                $diffElement = $d;
            }
            $movedElementObj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $diffElement);
            $movedElementObj->move($column);

            //update current inventory
            $portletsOld = $column->get_inventory();
            $portletsOldIds = array();

            foreach ($portletsOld as $p) {
                $portletsOldIds[] = $p->get_id();
            }
            $countPortOld = count($portletsOldIds);
        }
        if ($countPortOld == $countPortNew) {
            $boolHelper = true;
            $counter = 0;
            $startValue = 0;
            for ($i = 0; $i < $countPortOld; $i++) {
                if ($portletsOldIds[$i] != $portletsIdsNew[$i]) {
                    if ($boolHelper) {
                        $boolHelper = false;
                        $startValue = $i;
                    }
                    $counter++;
                }
            }

            $column->swap_inventory($startValue, $startValue - 1 + $counter);
            for ($j = $startValue + 1; $j < ($startValue + $counter - 1); $j++) {
                $column->swap_inventory($j, $j + 1);
            }
        }

        return $ajaxResponseObject;
    }

}
?>

