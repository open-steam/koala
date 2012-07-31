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
        
        public function getOldPaths(){
            return $keyStrings = array("index.php?object=","/home/","/hilfe/","/schulen/","/lernstatt_intern/","/externe_partner/",   //steam.lspb.de
                                    "/schulen/", "/dialog/", "/partner/", "/projekte/"                                                //bid-owl.de
                    );
        }
}
?>