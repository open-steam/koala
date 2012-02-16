<?php
namespace PortletAppointment\Commands;
class DeleteTerm extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	private $termIndex;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $params["portletId"];
		$this->termIndex = $params["termIndex"];
		
		$appointmentObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$terms = $appointmentObject->get_attribute("bid:portlet:content");
		
		$i=0;
		$termsNew = array();
		foreach ($terms as $term) {
			$i++;
			if($i-1==$this->termIndex) continue;
			$termsNew[] = $term;
		}
		
		$appointmentObject->set_attribute("bid:portlet:content", $termsNew);
	}
	
	public function idResponse(\IdResponseObject $idResponseObject) {
		//no response
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		// no response
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		window.location.reload();
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
?>