<?php
interface IObjectExtension {
	
	public function getObjectReadableName();
	
	public function getObjectReadableDescription();
	
	public function getObjectIconUrl();
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment);
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject);

}
?>