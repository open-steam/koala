<?php
interface ICommand {
	
	public function validateData(IRequestObject $requestObject);
	
	public function processData(IRequestObject $requestObject);
	
	public function isGuestAllowed(IRequestObject $requestObject);
	
	public function serverAdminOnly(IRequestObject $requestObject);
	
	public function embedContent(IRequestObject $requestObject);
	
	public function workOffline(IRequestObject $requestObject);
	
}
?>