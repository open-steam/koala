<?php

namespace Explorer\Commands;

class Restore extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $env;
    private $object;
    private $trashbin;
    private $restoreFromTrashbin;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];

        $this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $this->restoreFromTrashbin = false;

        if (isset($this->params["restoreFromTrashbin"]) && $this->params["restoreFromTrashbin"] === 'true') {
            $this->env = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->object->get_attribute("OBJ_LAST_LOCATION_ID"));
            if ($this->env instanceof \steam_object && $this->env->check_access_write()) {
                $this->restoreFromTrashbin = true;
                $this->object->move($this->env);
            }
        } else {
            $this->env = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["env"]);
            //if the environment is a postbox object, resore the object in the subdirectory postbox_container
            $possiblePostboxContainer = $this->env->get_attribute("bid:postbox:container");
            if ($possiblePostboxContainer instanceof \steam_container) {
                $this->env = $possiblePostboxContainer;
            }

            $this->trashbin = \lms_steam::get_current_user()->get_attribute("USER_TRASHBIN");

            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            $object->move($this->env);
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        $jswrapper = new \Widgets\JSWrapper();
        if ($this->restoreFromTrashbin) {

            $trashbinModel = new \Explorer\Model\Trashbin($this->trashbin);
            $js = "jQuery('#{$this->id}').addClass('justRestoredFromTrashed').removeClass('listviewer-item-selected selected').find('input:checkbox').attr('disabled', 'disabled');
			   var checkbox = document.getElementById('{$this->id}_checkbox');
			   if (!checkbox) {
			   		location.reload();
			   } else {
			   	checkbox.checked = false;
			   	document.getElementById('{$this->id}').onclick = \"\";
                        	$('#popupmenu{$this->id}').hide();
			   }";
        } else {
            $trashbinModel = new \Explorer\Model\Trashbin($this->trashbin);
            $js = "jQuery('#{$this->id}').removeClass('justTrashed').removeClass('listviewer-item-selected');
                document.getElementById('{$this->id}_checkbox').disabled = false;
                document.getElementById('{$this->id}').onclick = document.getElementById('{$this->id}').onclick_restore;
                document.getElementById('{$this->id}').onclick_restore = \"\";
                document.getElementById('trashbinIconbarWrapper').innerHTML = '" . $trashbinModel->getIconbarHtml() . "';";
        }

        $jswrapper->setJs($js);
        $ajaxResponseObject->addWidget($jswrapper);
        return $ajaxResponseObject;
    }

}

?>