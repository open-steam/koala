<?php
namespace Portal\Commands;
class SaveSorting extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $user;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
                /*
                $this->params = $requestObject->getParams();
                $this->id = $this->params["id"];
		$this->user = $GLOBALS["STEAM"]->get_current_steam_user();
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$object->move($this->user);
                */
                
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		/*
                $ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		window.location.reload();
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
                */
	}
}
?>