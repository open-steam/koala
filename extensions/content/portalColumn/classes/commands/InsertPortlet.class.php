<?php
namespace PortalColumn\Commands;
class InsertPortlet extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $user;
	private $order;
	private $orderDependency; //object 
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$this->params = $requestObject->getParams();
		$this->id = $this->params["portletId"];
		$this->user = $GLOBALS["STEAM"]->get_current_steam_user()->get_name();
		
		
		//insert order
		$this->order=0;
		$this->orderDependency=0;
		
		if(isset($this->params["order"])){
			$this->order = $this->params["order"]; //first, last, above, below
		}
		if(isset($this->params["orderDependency"])){
			$this->orderDependency = $this->params["orderDependency"]; //object id relativ orderd to
		}
		
		
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$idRequestObject = new \IdRequestObject();
		$idRequestObject->setId($this->id);
		$columnObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		
		foreach($GLOBALS["STEAM"]->get_current_steam_user()->get_inventory() as $steamObject){
			$portletType = $steamObject->get_attribute("bid:portlet");
			$isPortlet = false;
			
			if( $portletType=="msg" || $portletType=="appointment" || $portletType=="termplan" || $portletType=="topic" || $portletType=="headline" || $portletType=="poll" || $portletType=="media" || $portletType=="rss"){
                            $isPortlet=true;
                        }   
                        
			if($isPortlet){
				$steamObject->move($columnObject);
                        }
		}
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