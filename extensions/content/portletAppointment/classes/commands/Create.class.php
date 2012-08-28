<?php
namespace PortletAppointment\Commands;
class Create extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $rawHtmlWidget;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();               
		$name = $params["title"];
		$column = $params["parent"];
                $order = $params["undefined"];               
		$version = "3.0";	

		//check diffrent types of parameter
		if(is_string($column)){
			$columnObject =  \steam_factory::get_object($GLOBALS["STEAM"]->get_id(),$column);
		}else{
			$columnObject = $column;
		}
		
		//create
		$appointment = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $name, $columnObject);
    	$appointment->set_attributes(array(
            OBJ_DESC => $name,
            OBJ_TYPE => "container_portlet_bid",
            "bid:portlet:version" => $version,
            "bid:portlet" => "appointment",
            "bid:portlet:app:app_order" => $order
    	));
		
		
	}
	
	public function idResponse(\IdResponseObject $idResponseObject) {
		
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
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