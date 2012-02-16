<?php
interface IIdCommand extends ICommand {
	
	/*
	 * @return FrameResponseObject
	 */
	public function idResponse(IdResponseObject $idResponseObject);
	
}
?>