<?php
namespace Explorer\Commands;
class LoadSanctions extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $object;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$sanctions_result = array();
		$sanctions = $this->object->get_sanction();
		foreach ($sanctions as $id => $sanction) {
			if ($sanction | SANCTION_READ) {
				$sanctions_result[] = "read_{$id}";
			}
			if ($sanction | SANCTION_WRITE) {
				$sanctions_result[] = "write_{$id}";
			}
			if ($sanction | SANCTION_SANCTION) {
				$sanctions_result[] = "sanction_{$id}";
			}
		}
		
		
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->setData(array("acquire"=>$this->object->get_acquire(), "sanctions"=>$sanctions_result));
		
		return $ajaxResponseObject;
	}
}
?>