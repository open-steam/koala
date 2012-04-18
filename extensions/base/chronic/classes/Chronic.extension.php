<?php
class Chronic extends AbstractExtension implements IMenuExtension {
	private $chronicLength = 15;
        
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
                    $backEntry = $chronic[1];
                    return array("name" => "zurück", "link" => $this->getEntryPath($backEntry));
		}
		return "";
        }
	
        
        //get entry for up button
	private function getParentEntry() {
            /*
            $type = getObjectType(self::$currentObject);
            if (array_search($type, array("forum", "referenceFolder", "trashbin", "gallery", "portal", "room", "container")) !== false) {
                    $steam_object = self::$currentObject->get_environment();
                    return array("name" => "nach oben ( <img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($steam_object)."\"></img> " . getCleanName($steam_object, 20) . " )", "link" => $this->getEntryPath("oid:".$steam_object->get_id()));
            }
            return "";
            */
            
            $chronic = $this->loadChronic();
            
            if (!isset($chronic[0])) return "";
            
            $currentLocation = $chronic[0];
            $content = explode(":", $currentLocation);
            $entryType = $content[0];
            $currentObjectId = $content[1];
            
            if($entryType==="oid"){
                //find object
                try{
                    $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectId);
                }  catch (\steam_exception $e){
                    //object not found
                    return "";
                }
                
                
                //find parent
                $environmentObject = $steamObject;
                try{
                    $environmentObject = $steamObject->get_environment();
                    if("0"==$environmentObject) throw new \steam_exception;
                }catch (\steam_exception $e){
                    //no environment
                    return "";
                }
                
                //is Presentation, autoforward case
                while($environmentObject->get_attribute("bid:presentation")==="index"){ 
                    $environmentObject = $environmentObject->get_environment();
                }
                return array("name" => "nach oben ( <img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($environmentObject)."\"></img> " . getCleanName($environmentObject, 20) . " )", "link" => $this->getEntryPath("oid:".$environmentObject->get_id()));
            }
            return "";
        }
       
        
        //add a new object to chronic
        private function updateChronic($entry){
            //var_dump($entry);
            $chronic = $this->loadChronic();
            
            //remove entry before adding
            while(array_search($entry, $chronic)!==FALSE){
                $key = array_search($entry, $chronic);
                unset($chronic[$key]);
            }
            
            //add entry
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
 
        
        private function getEntryName($chronicEntry){
            $content = explode(":", $chronicEntry);
            $entryType = $content[0];
            if($entryType=="oid"){
                $objectId = $content[1];
                
                try{
                $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
                }  catch (Exception $e){
                    return "(Objekt gelöscht)";
                }
                
                if($steamObject instanceof \steam_object){
                    return $steamObject->get_name();
                }else{
                    return "invalid_name";
                }
            }
            else if($entryType=="cmd"){
                return "command";
            }
            else if($entryType=="pth"){
                return "path";
            }
            else if($entryType=="oth"){
                $type = $content[1];
                if($type==="profile") return "Profil";
                if($type==="desktop") return "Schreibtisch";
                if($type==="bookmarks") return "Lesezeichen";
                return "Ungültiger oth-Eintrag";
            }
            return "Ungültiger Eintrag";
        }
        
        
        private function getEntryPath($chronicEntry){
            $content = explode(":", $chronicEntry);
            $entryType = $content[0];
            
            if($entryType=="oid"){
                $objectId = $content[1];
                try{
                    $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
                    if($steamObject instanceof \steam_object){
                        return \ExtensionMaster::getInstance()->getUrlForObjectId($objectId, "view");
                    }  else {
                        return "/";
                    }
                }  catch (Exception $e){
                    return "(Objekt gelöscht)";
                }
            }
            else if($entryType=="cmd"){
                return "command";
            }
            else if($entryType=="pth"){
                return "path";
            }
            else if($entryType=="oth"){
                $type = $content[1];
                if($type==="profile") return "/profile/";
                if($type==="desktop") return "/desktop/";
                if($type==="bookmarks") return "/bookmarks/";
                return "";
            }
            return "Debug:$chronicEntry";
        }
        
        
        //loads the chronic and returns it
        private function loadChronic(){
            $user = lms_steam::get_current_user();
            $chronic = $user->get_attribute("USER_CHRONIC");
            return $this->validateChronic($chronic);
        }
        
        private function saveChronic($chronic){
            $user = lms_steam::get_current_user();
            $chronic = $this->validateChronic($chronic);
            $user->set_attribute("USER_CHRONIC",$chronic);
        }
        
        
        private function validateChronic($chronic){
            foreach ($chronic as $chronicKey => $chronicEntry){
                $content = explode(":", $chronicEntry);
                $entryType = $content[0];
                $target = $content[1];
                
                $valid=false;
                if($entryType==="oth") $valid = true;
                if($entryType==="oid") $valid = true;
                if (!$valid) unset($chronic[$chronicKey]);
            }
            return $chronic;
        }
}
?>