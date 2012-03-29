<?php
class Chronic extends AbstractExtension implements IMenuExtension {
	
	private static $currentObject;
        private $chronicLength = 10;
	
	public function getName() {
		return "Chronic";
	}
	
	public function getDesciption() {
		return "Extension for chronic handling.";
	}
	
	public function getVersion() {
		return "v1.0.1";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Marcel", "Jakoblew", "mjako@uni-paderborn.de");
		return $result;
	}
	
	public function getMenuEntries() {
                /*
		$chronic = $this->loadChronic();
		$length = count($chronic);
		$result = array(array("name" => "Chronik", "menu" => array(
													$this->getBackEntry(),
													$this->getParentEntry(), 
											 )));
		array_pop($chronic);
		$chronic = array_reverse($chronic);
		$chronicEntries = array();
		foreach ($chronic as $item) {
			$chronicEntries[] = $this->getChronicEntry($item);
		}
		if (count($chronicEntries) > 1) {
			$menuArray = $result[0]["menu"];
			$menuArray[] = array("name" => "SEPARATOR");
			foreach ($chronicEntries as $entry) {
				$menuArray[] = $entry;
			}
			$result[0]["menu"] = $menuArray;
		}
		return $result;
                */
            
            
                //-------------
                $chronic = $this->loadChronic();
                $length = count($chronic);
                $result = array(array("name" => "Chronik", "menu" => array($this->getBackEntry(),$this->getParentEntry())));
		
                if ($length > 1) {
                    $menuArray = $result[0]["menu"];
                    $menuArray[] = array("name" => "SEPARATOR");
                    $count = 0;
                    foreach ($chronic as $chronicItem){
                        $count++;
                        if($count<2) continue; //skip this and last element
                        $menuArray[] = array("name" => $this->getEntryName($chronicItem), "link" => $this->getEntryPath($chronicItem)); //todo
                    }
                    $result[0]["menu"] = $menuArray;
                }
                return $result;
	}
	
        
        
	public function setCurrentObject($steamObject) {
                if ($steamObject instanceof steam_object && $steamObject->check_access_read()){
                    $this->updateChronic("oid:".$steamObject->get_id());
                    $this->currentObject = $steamObject;
                }
        }
        
        public function setCurrentCommand($namespace, $command) {
                $this->updateChronic("cmd:".$namespace.":".$command);
        }
        
        public function setCurrentPath($path) {
                $this->updateChronic("pth:".$path);
        }
        
        public function setCurrentOther($other) {
                if($other=="profile") $this->updateChronic("oth:profile");
                if($other=="desktop") $this->updateChronic("oth:desktop");
                if($other=="bookmarks") $this->updateChronic("oth:bookmarks");
        }
        
        
        
        
        
	//get entry for back button
	private function getBackEntry() {
		
                $chronic = $this->loadChronic();
		$length = count($chronic);
		if ($length > 1) {
                    //$steam_object = $chronic[$length-2];
                    //return array("name" => "zurück", "link" => \ExtensionMaster::getInstance()->getUrlForObjectId($steam_object->get_id(), "view"));
                    
                    $backEntry = $chronic[1];
                    return array("name" => "zurück", "link" => $this->getEntryPath($backEntry));
		}
		return "";
                
 	}
	
        
        
        
        //get entry for up button
	private function getParentEntry() {
		$type = getObjectType(self::$currentObject);
		if (array_search($type, array("forum", "referenceFolder", "trashbin", "gallery", "portal", "room", "container")) !== false) {
			$steam_object = self::$currentObject->get_environment();
		        return array("name" => "nach oben ( <img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($steam_object)."\"></img> " . getCleanName($steam_object, 20) . " )", "link" => $this->getEntryPath("oid:".$steam_object->get_id()));
		}
		return "";
	}
       
        
        //add a new object to chronic
        private function updateChronic($entry){
            $chronic = $this->loadChronic();
            
            //put new element on pos 0
            $chronic = array_reverse($chronic);
            $chronic[] = $entry;
            $chronic = array_reverse($chronic);
            
            
            //dedupe
            $cleandChronic = array();
            $lastElement = "";
            foreach ($chronic as $chronicItem){
                if($chronicItem !== $lastElement){
                    $lastElement=$chronicItem;
                    $cleandChronic[] = $chronicItem;
                }else{
                    
                }
            }
            $chronic = $cleandChronic;
            
            
            //throw tail away
            $counter=1;
            $cleandChronic = array();
            foreach ($chronic as $chronicItem){
                $cleandChronic[] = $chronicItem;
                if ($counter==$this->chronicLength) break;
                $counter++;
            }
            $chronic = $cleandChronic;
            $this->saveChronic($chronic);
        }
        
        
        
        /*
	private function updateChronic($steamObject) {
		$type = getObjectType($steamObject);
		if (array_search($type, array("document", "forum", "referenceFolder", "user", "trashbin", "gallery", "portal", "userHome", "groupWorkroom", "room", "container")) !== false) {
			$user = lms_steam::get_current_user();
			$chronic = $this->loadChronic();
			$pos = array_search($steamObject, $chronic);
			if ($pos === false) {
				$chronic[] = $steamObject;
			} else {
				unset($chronic[$pos]);
				$chronic = array_values($chronic);
				$chronic[] = $steamObject;
			}
			if (count($chronic) > CHRONIC_LENGTH) {
				$chronic = array_slice($chronic, count($chronic) - CHRONIC_LENGTH, CHRONIC_LENGTH);
			}
			$user->set_attribute("USER_CHRONIC", $chronic);
		}
	}
	
        */
        
        
        
        /*
	private function loadChronic() {
		$ids = array();
		$user = lms_steam::get_current_user();
		$chronic = $user->get_attribute("USER_CHRONIC");
		$result = array();
		if (is_array($chronic)) {
			foreach ($chronic as $item) {
				if ($item instanceof steam_object) {
					$env = $item->get_environment();
					if (!($env instanceof steam_trashbin)) {
						$id = $item->get_id();
						if (array_search($id, $ids) === false) {
							$result[] = $item;
							$ids[] = $id;
						}
					}
				}
			}
		}
		return $result;
	}
        */
      
        
        private function getEntryName($chronicEntry){
            $content = explode(":", $chronicEntry);
            $entryType = $content[0];
            if($entryType=="oid"){
                $objectId = $content[1];
                $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		return $steamObject->get_name();
            }
            else if($entryType=="cmd"){
                return "command";
            }
            else if($entryType=="pth"){
                return "path";
            }
            else if($entryType=="oth"){
                $type = $content[1];
                var_dump($type);
                if($type=="profile") return "Profil";
                if($type=="desktop") return "Schreibtisch";
                if($type=="bookmarks") return "Lesezeichen";
                return "Unbekannt";
            }
            return "Unbekannter Name";
        }
        
        
        private function getEntryPath($chronicEntry){
            $content = explode(":", $chronicEntry);
            $entryType = $content[0];
            
            if($entryType=="oid"){
                $objectId = $content[1];
                return \ExtensionMaster::getInstance()->getUrlForObjectId($objectId, "view");
                //return "dummypath";
            }
            else if($entryType=="cmd"){
                return "command";
            }
            else if($entryType=="pth"){
                return "path";
            }
            else if($entryType=="oth"){
                $type = $content[1];
                if($type=="profile") return "profile";
                if($type=="desktop") return "desktop";
                if($type=="bookmarks") return "bookmarks";
                return "";
            }
            return "";
        }
        
        
        //loads the chronic and returns it
        private function loadChronic(){
            $user = lms_steam::get_current_user();
            $chronic = $user->get_attribute("USER_CHRONIC");
            return $chronic;
        }
        
        private function saveChronic($chronic){
            $user = lms_steam::get_current_user();
            $chronic = $user->set_attribute("USER_CHRONIC",$chronic);
        }
        
}
?>