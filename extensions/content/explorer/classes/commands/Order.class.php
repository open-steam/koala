<?php
namespace Explorer\Commands;
class Order extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $direction;
	private $object;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->direction = $this->params["direction"];
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$environment = $this->object->get_environment();
		$inventory = $environment->get_inventory();
		foreach ($inventory as $key => $element) {
			if ($element->get_id() == $this->id) {
				$index = $key;
			}
		}
		if ($this->direction == "up") {
			$environment->swap_inventory($index, $index-1);
		} else if ($this->direction == "down") {
			$environment->swap_inventory($index, $index+1);
		} else if ($this->direction == "top") {
			//$environment->swap_inventory($index, 0);
			for($i=0;$i<$index;$i++){
				$environment->swap_inventory(0,$i); 
			}
		} else if ($this->direction == "bottom") {
			//$environment->swap_inventory($index, count($inventory)-1);
			for($i=count($inventory)-1;$i>=$index;$i--){
				$environment->swap_inventory($index,$i);
			}		
		}
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$js = "console.log('start');
			   if (jQuery('#explorerWrapper').length == 0) {
			   	location.reload();
			   } else {
			  	 var element = jQuery('#{$this->id}');
				   if ('{$this->direction}' == 'up') {
						element.insertBefore(element.prev());
				   } else if ('{$this->direction}' == 'down') {
						element.insertAfter(element.next());
				   } else if ('{$this->direction}' == 'top') {
				   		element.insertBefore(element.parent().children().first());
				   } else if ('{$this->direction}' == 'bottom') {
				   		element.insertAfter(element.parent().children().last());
				   }
			   }
		       console.log('DOnE');" ;
		$jswrapper->setJs($js);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
?>