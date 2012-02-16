<?php
class AjaxResponseObject extends GenericObject implements IResponseObject{
	
	protected $status, $elementID, $widgets, $data = array();
	
	public function addWidget($widget) {
		if (!$this->widgets) {
			$this->widgets = array();
		}
		$this->widgets[] = $widget;
	}

}
?>