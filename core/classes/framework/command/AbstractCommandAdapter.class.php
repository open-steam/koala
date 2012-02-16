<?php
abstract class AbstractCommandAdapter implements  ICommandAdapter {
	
	public function getCommandNamespace() {
		return $this->getId() . "\Commands";
	}
	
	public function getDefaultCommandName($urlNamespace) {
		return "Index";
	}
	
	public function getCommand($commandName) {
		$fullCommandName = $this->getCommandNamespace() . "\\" . $commandName;
		if (file_exists($this->getExtensionPath() . "classes/commands/" . ucfirst($commandName) . ".class.php")) {
			$command = new $fullCommandName;
			if (is_object($command) && $command instanceof ICommand) {
				return $command;
			}
		}
		return null;
	}
	
	
	public function isValidCommand($commandName) {
		$fullCommandName = $this->getCommandNamespace() . "\\" . $commandName;
		$command = new $fullCommandName;
		if (is_object($command) && $command instanceof ICommand) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getCommands() {
		$result = array();
		$path = $this->getExtensionPath() . "classes/commands";
		if (is_dir($path)) {
		    if ($dh = opendir($path)) {
		        while (($file = readdir($dh)) !== false) {
					if( $file=='.' || $file=='..' ) continue;
					if (is_dir($path . "/" . $file)) {
						continue;
					} else if (strEndsWith($file, ".class.php", false)) {
						$result[] = str_replace(".class.php", "", $file);
					}
		        }
		        closedir($dh);
		    }
		}
		return $result;
	}
}
?>