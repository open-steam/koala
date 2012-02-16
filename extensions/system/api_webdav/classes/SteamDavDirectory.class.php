<?php

class SteamDavDirectory extends Sabre_DAV_Directory {

    protected $steamContainer;

    public function __construct($steamContainer) {
    	if (!($steamContainer instanceof steam_container)) {
    		throw new Sabre_DAV_Exception('Only instances of steam_container allowed to be passed in the container argument');
    	}
    	$this->steamContainer = $steamContainer;
          }

 /*   public function addChild(Sabre_DAV_INode $child) {

        $this->children[$child->getName()] = $child;

    }*/

    public function getName() {
        return $this->steamContainer->get_identifier();
    }

    private function createChild($object) {
		if ($object instanceof steam_container) {
			return new SteamDavDirectory($object);
		} else {
			return new SteamDavFile($object);
		}
    }
    
    
	function getChild($name) {
		$object = $this->steamContainer->get_object_by_name($name);
	
	    if (!$object instanceof steam_object) throw new Sabre_DAV_Exception_FileNotFound('The file with name: ' . $name . ' could not be found');
	
	    if ($name[0]=='.')  throw new Sabre_DAV_Exception_FileNotFound('Access denied');
	
	    return $this->createChild($object);
	}
    

    public function getChildren() {
    	$result = array();
    	
    	$objects = $this->steamContainer->get_inventory();
    	
    	foreach ($objects as $object) {
    		$result[] = $this->createChild($object);
    	}

        return $result;
    }
    
    public function getLastModified() {
        return $this->steamContainer->get_attribute(OBJ_LAST_CHANGED);
    }


}

?>