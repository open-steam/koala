<?php
interface IAjaxCommand extends ICommand {
	
	/*
	 * @return IAjaxResponseObject
	 */
	public function ajaxResponse(AjaxResponseObject $ajaxResponseObject);
	
}
?>