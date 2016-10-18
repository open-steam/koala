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
			for($i=0;$i<$index+1;$i++){
				$environment->swap_inventory(0,$i);
			}
		} else if ($this->direction == "bottom") {
			for($i=count($inventory)-1;$i>=$index;$i--){
				$environment->swap_inventory($index,$i);
			}
		} else {
			$environment->order_inventory_objects($this->params["indices"]);
		}
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
	 	$js = "var element = jQuery('#{$this->id}');
					if ('{$this->direction}' == 'up') {
					 element.insertBefore(element.prev());
					 if($('.listviewer').length != 0) resetListViewerHeadItem();
					} else if ('{$this->direction}' == 'down') {
					 element.insertAfter(element.next());
					 if($('.listviewer').length != 0) resetListViewerHeadItem();
					} else if ('{$this->direction}' == 'top') {
						 element.insertBefore(element.parent().children().first());
						 if($('.listviewer').length != 0) resetListViewerHeadItem();
					} else if ('{$this->direction}' == 'bottom') {
						 element.insertAfter(element.parent().children().last());
						 if($('.listviewer').length != 0) resetListViewerHeadItem();
					}";
		$jswrapper->setJs($js);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
?>
