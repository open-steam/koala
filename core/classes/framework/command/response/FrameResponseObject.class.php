<?php
class FrameResponseObject extends GenericObject implements IResponseObject{
	
	protected $title, $headline, $breadcrumb, $widgets, $problemDescription, $problemSolution, $confirmText;
	
	public function addWidget($widget) {
		if (!$this->widgets) {
			$this->widgets = array();
		}
		if (is_array($widget)) {
			foreach ($widget as $w) {
				$this->widgets[] = $w;
			}
		} else {
			$this->widgets[] = $widget;
		}
	}

}
?>