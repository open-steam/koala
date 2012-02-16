<?php
class IdResponseObject extends GenericObject implements IResponseObject{
	
	protected $widgets;
	
	public function addWidget($widget) {
		if (!$this->widgets) {
			$this->widgets = array();
		}
		$this->widgets[] = $widget;
	}

}
?>