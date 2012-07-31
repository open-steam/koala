<?php
include_once PATH_LIB . 'sort_functions.inc.php';

class ExtensionMaster {
	
	private static $instance;
	
	private function __construct() {
		$extensions = $this->getAllExtensionIds();
		foreach($extensions as $extension) {
			$extension_instance = $extension::getInstance();
		}
	}
	
	public static function getInstance() {
		if (!is_object(self::$instance)) {
			self::$instance = new ExtensionMaster();
			//$application = Application::getInstance();
			//$mainmenu = MainMenu::getInstance();
			//$semester = Semester::getInstance();
			//$signin = SignIn::getInstance();
			//$error = Error::getInstance();
			//$notFound = NotFound::getInstance();
		}
		return self::$instance;
	}
	
	public function getExtensionForNamespace($nameSpace) {
		$result = array();
		$extensions = $this->getAllExtensionsCached();
		foreach($extensions as $extension) {
			$extensionNameSpaces = $extension->getUrlNamespaces();
			foreach($extensionNameSpaces as $extensionNameSpace) {
				if (strtolower($extensionNameSpace) == strtolower($nameSpace)) {
					$result[] = $extension;
				}	
			}
		}
		return $this->findHighestPriorityExtension($result);
	}

	public function handleRequest() {
		$path = $this->getRequestUriAsArray();
		// ajax extension will handle ajax Requests
		$ajax = Ajax::getInstance();
		if (Ajax::isAjaxRequest()) {
			$ajax->enableAjaxErrorHandling();
			$ajax->handleRequest($path);
		} else {
			$indexExtensions = $this->getExtensionByType("IIndexExtension");
			$indexExtensions[0]->handleRequest($path);
		}
	}
	
	private function getRequestUriAsArray() {
		$server_name = str_replace("https://", "", str_replace("http://", "", PATH_URL));
		$server_array =  explode("/", $server_name);
		
		$result = array();
		$requestUrl = urldecode($_SERVER['REQUEST_URI']);
                
                
                //bid 2 compatibility
                $bid2PathCompatibilityExt = $this->getExtensionForNamespace("bid2PathCompatibility");
                if($bid2PathCompatibilityExt){
                    $keyStrings = $bid2PathCompatibilityExt->getOldPaths();
		
                    foreach ($keyStrings as $needle){
                        if(strstr($requestUrl,$needle)){
                            //found
                            $result[0]="bid2PathCompatibility";
                            //$result[1]=$requestUrl;
                            return $result;
                        }
                    }
                }
                
                
                
                //decode request path string
                $path = explode("/", $requestUrl);
		
		for($i=0; $i < count($path); $i++) {
			if (isset($server_array[$i])) {
				$server_array_part = $server_array[$i];
			}
			if (isset($server_array[$i])) {
				if ($path[$i] != "" && $path[$i] != $server_array[$i]) {
					$result[] = $path[$i];
				}
			} else {
			if ($path[$i] != "") {
					$result[] = $path[$i];
				}
			}
		}
                
                
                
                return $result;
	}
	
	public function getExtensionForObjectId($objectId) {
		if ($objectId == null || $objectId == 0) {
			throw new Exception("Object Id is missing.");
		}
		$result = array();
		$extensions = $this->getExtensionByType("IObjectExtension");
		foreach ($extensions as $extension) {
			if ($extension->canHandleObjectId($objectId)) {
				$result[] = $extension;
			}
		}
		return $this->findHighestPriorityExtension($result);
	}
	
	private function findHighestPriorityExtension($extensions) {
                //a higher number has higher priority
		if (count($extensions) == 0) {
			return null;
		} else if (count($extensions) == 1) {
			return $extensions[0];
		} else {
			$returnCandiate;
			foreach ($extensions as $extension) {
				if (!isset($returnCandiate)) {
					$returnCandiate = $extension;
				} else {
					if ($returnCandiate->getPriority() < $extension->getPriority()) {
						$returnCandiate = $extension;
					}
				}
			}
			return $returnCandiate;
		}
	}
	
	public function getCommandByObjectId($objectId, $method = "view", $requestType = "id") {
		if ($objectId == "") {
			throw new Exception("Missing Parameter objectId.");
		}
		$extensions = $this->getExtensionByType("IObjectExtension");
		$idRequestObject = new IdRequestObject();
		$idRequestObject->setId($objectId);
		$idRequestObject->setMethod($method);
		$idRequestObject->setRequestType($requestType);
		//TODO: sort extensions by priority
		foreach ($extensions as $extension) {
			$command = $extension->getCommandByObjectId($idRequestObject, $method, $requestType);
			if (isset($command) && $command instanceof ICommand) return $command;
		}
		return false;	
		
	}
	
	public function callCommand($commandName, $namespaceName, $params) {
		$extension = ExtensionMaster::getInstance()->getExtensionForNamespace($namespaceName);
		$ajaxRequestObject = new AjaxRequestObject();
		$ajaxRequestObject->setNamespace($namespaceName);
		$ajaxRequestObject->setCommand($commandName);
		$ajaxRequestObject->setElementId("");
		$ajaxRequestObject->setRequestType("internal");
		$ajaxRequestObject->setParams($params); 
		$command = $extension->getCommand($ajaxRequestObject->getCommand());
		$command->validateData($ajaxRequestObject);
		$command->processData($ajaxRequestObject);
		return $command;
	}
	
	public function getUrlForObjectId($objectId, $method = "view") {
		$command = $this->getCommandByObjectId($objectId, $method, $requestType = "frame");
		if ($command instanceof ICommand) {
			$extensionUrlNamespaces = $command->getExtension()->getUrlNamespaces();
			return PATH_URL . $extensionUrlNamespaces[0] . "/" . $command->getCommandName() . "/" . $objectId . "/";
		}
	}
	
	public function getWidgetsByObjectId($objectId, $method = "view", $params = array()) {
		$idRequestObject = new IdRequestObject();
		$idRequestObject->setId($objectId);
		$idRequestObject->setMethod($method);
		$idRequestObject->setParams($params);
		$command = $this->getCommandByObjectId($objectId, $method);
		if ($command instanceof IIdCommand) {
			if ($command->validateData($idRequestObject)) {
				$command->processData($idRequestObject);
				$idResponseObject = $command->idResponse(new IdResponseObject());
				if ($idResponseObject == null) {
					throw new Exception("idResponseObject is null for command " . get_class($command));
				}
				return $idResponseObject->getWidgets();
			} else {
				throw new Exception("Command validation error for $objectId.");
			}
		}
		return "";	
	}
	
	public function getExtensionById($id) {
		$result = array();
		$extensions = $this->getAllExtensionIds();
		foreach($extensions as $extension) {
			if ($extension == $id) {
				return call_user_func($extension .'::getInstance');
			}
		}
		return null;
	}
	
	public function getAllExtensionsCached() {
		$cache_status = CacheSettings::caching_enabled();
		CacheSettings::enable_caching();
		$cache = get_cache_function("extensionmaster", 3600 );
		$extensions = $cache->call(array($this, "getAllExtensions"));
		if (!$cache_status) {
			CacheSettings::disable_caching();
		}
		usort($extensions, "sortExtensions");
		return $extensions;
	}
	
	public function getAllExtensions() {
		$result = array();
		$extensions = $this->getAllExtensionIds();
		foreach($extensions as $extension) {
			$extension_instance = call_user_func($extension .'::getInstance');
			if ($extension_instance && $extension_instance instanceof IExtension) {
				$result[] = $extension_instance;
			}
		}
		return $result;
	}
	
	private function getAllExtensionIds() {
		$result = array();
		$paths = $this->getExtensionPaths();
		$cache_status = CacheSettings::caching_enabled();
		CacheSettings::enable_caching();
		$cache = get_cache_function("extensionmaster", 3600 );
		foreach($paths as $path) {
			$result += $cache->call(array($this, "searchForExtensions"), $path);
			//$result += $this->searchForExtensions($path);
		}
		if (!$cache_status) {
			CacheSettings::disable_caching();
		}
		
		if (defined("EXTENSIONS_WHITELIST") && EXTENSIONS_WHITELIST != "") {
			$whitelist = explode(",", EXTENSIONS_WHITELIST);
			$whitelist = array_trim($whitelist);
			$result = array_intersect($whitelist, $result);
		}
		if (BLACKLISTED_EXTENSIONS != "") {
			$parts = explode(",", BLACKLISTED_EXTENSIONS);
			$parts = array_trim($parts);
		} else {
			$parts = array();
		}
		$result  = array_diff($result, array_intersect($parts, $result));
		return $result;
	}
	
	public function getExtensionByType($ExtensionType) {
		$result = array();
		$extensions = $this->getAllExtensionsCached();
		foreach($extensions as $extension) {
			if ($extension instanceof $ExtensionType) {
				$result[] = $extension;
			}
		}
		return $result;
	}
	
	private function getExtensionPaths() {
		$result = array();
		$result[] = PATH_PLATFORMS . "PLATFORM_ID/extensions/";
		$result[] = PATH_PLATFORMS_DEFAULT . "extensions/";
		$result[] = PATH_EXTENSIONS;
		return $result;
	}
	
	public function searchForExtensions($path) {
		$result = array();
		if (is_dir($path)) {
		    if ($dh = opendir($path)) {
		        while (($file = readdir($dh)) !== false) {
					if( $file == "." || $file == ".." || $file == "CVS" || $file == ".settings" || $file == ".todo" ) continue;
					if (is_dir($path . "/" . $file)) {
						 $result = array_merge($result, $this->searchForExtensions($path . "/" . $file));
					} else if (strEndsWith($file, ".extension.php", false)) {
						$result[] = str_replace(".extension.php", "", $file);
					}
		        }
		        closedir($dh);
		    }
		}
		return $result;
	}
	
	public function send404Error($message = "no error description") {
		logging::write_log( LOG_404, date("d.m.Y H:i", time()) . " " . "HTTP-" . $_SERVER[ 'REQUEST_METHOD' ]. ': ' . $_SERVER[ 'REQUEST_URI' ]." ".$message);
		header("Location: " . URL_404);
		die;
	}
	
}
?>