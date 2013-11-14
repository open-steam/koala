<?php

namespace Portal\Commands;

class Update extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $cE;

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
        $column = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $portletsOld = $column->get_inventory();
        $portletsOldIds = array();
        $this->cE=$this->params["changedElement"];

        foreach ($portletsOld as $p) {
            $portletsOldIds[] = $p->get_id();
        }

        $portletsIdsNew = array();
        $portletsIdsNew = explode(",", $this->params["elements"]);
        unset($portletsIdsNew[count($portletsIdsNew) - 1]);


        $countPortOld = count($portletsOldIds);
        $countPortNew = count($portletsIdsNew);


        if ($countPortNew < $countPortOld) {
            return;// $ajaxResponseObject;
        }
        
        
        //case: move object between columns
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
            
            $portletsOld = $column->get_inventory();
            
            $portletsOldIds = array();

            foreach ($portletsOld as $p) {
                $portletsOldIds[] = $p->get_id();
            }
            $countPortOld = count($portletsOldIds);
        }
        
        
        //case: sort
        if ($countPortOld == $countPortNew) {
            
            $oldIdArray = $portletsOldIds;
            $newIdArray = $portletsIdsNew;
            $parent=$column;

            //find the old position
            $oldPosition = 0;
            foreach($oldIdArray as $key => $value) {
                if (intval($value) === intval($this->cE)) {
                    $oldPosition = $key;
                    break;
                }
            }
            
            //find the new position
            $newPosition = 0;
            foreach($newIdArray as $key => $value) {
                if (intval($value) === intval($this->cE)) {
                    $newPosition = $key;
                    break;
                }
            }
            
            //sort
            if ($oldPosition < $newPosition) {
                for ($i = $oldPosition; $i < $newPosition; $i++) {
                    $parent->swap_inventory($i, $i + 1);
                }
            }
            if ($oldPosition > $newPosition) {
                for ($i = $oldPosition; $i > $newPosition; $i--) {
                    $parent->swap_inventory($i, $i - 1);
                }
            }

        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}
?>

