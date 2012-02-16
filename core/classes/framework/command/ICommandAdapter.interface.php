<?php
interface ICommandAdapter {
	
	public function getDefaultCommandName($urlNamespace);
	
	public function getCommand($commandName);
	
	public function isValidCommand($commandName);
	
	public function getCommands();
	
}
?>