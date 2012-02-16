<?php

class SteamDavFile extends Sabre_DAV_File {

    protected $steamObject;

    public function __construct($steamObject) {
    	if (($steamObject instanceof steam_container) || !($steamObject instanceof steam_object)) {
    		throw new Sabre_DAV_Exception('Only instances of steam_object allowed to be passed in the object argument');
    	}
    	$this->steamObject = $steamObject;
     }

    public function getName() {
        return $this->steamObject->get_identifier();
    }

    public function get() {
    	if ($this->steamObject instanceof steam_document) {
    		return $this->steamObject->get_content();
    	} else {
    		return "";
    	}
    }
    
    public function getSize() {
        if ($this->steamObject instanceof steam_document) {
    		return $this->steamObject->get_content_size();
    	} else {
    		return 0;
    	}
    }
    
    public function getETag() {
    	return '"' . md5($this->get()) . '"';
    }
    
    public function getLastModified() {
        return $this->steamObject->get_attribute(OBJ_LAST_CHANGED);
    }

}

?>