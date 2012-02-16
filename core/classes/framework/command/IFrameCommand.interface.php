<?php
interface IFrameCommand extends ICommand {
	
	/*
	 * @return FrameResponseObject
	 */
	public function frameResponse(FrameResponseObject $frameResponseObject);
	
}
?>