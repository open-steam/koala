<?php
class bid2PathCompatibility extends AbstractExtension {
	
	public function getName() {
		return "bid2PathCompatibility";
	}
	
	public function getDesciption() {
		return "Extension for redirecting bid 2 paths";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Marcel", "Jakoblew", "mjako@upb.de");
		return $result;
	}
	
	public function getPriority() {
		return 0;
	}
        
        
        /*
         * list of handled paths
         */
        public function getOldPaths(){
            return $keyStrings = array("index.php?object=","/home/","/hilfe/","/schulen/","/lernstatt_intern/","/externe_partner/",     //steam.lspb.de
                                    "/schulen/", "/dialog/", "/partner/", "/projekte/" ,                                                //bid-owl.de
                                    "/tools/get.php?" , "/download/"                                                                                  //download
                    );
        }    
        
        /*
         * blacklist for not handled paths
         */
        public function getIgnorePaths(){
            return $keyStrings = array("download/document");    
        }
}
?>