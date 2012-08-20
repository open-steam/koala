<?php
namespace bid2PathCompatibility\Commands;
class Index extends \AbstractCommand implements \IFrameCommand{
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
        
        }
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
                $requestUrl = urldecode($_SERVER['REQUEST_URI']);
                
                //not tested
                if(FALSE!==strpos($requestUrl, "index.php?object=/")){
                        $searchString = "index.php?object?=/";
                        $begin = strpos($requestUrl, $searchString) + strlen($searchString);
                        $this->redirectToSteamPath("/");
                }
                
                //tested
                if(FALSE!==strpos($requestUrl, "index.php?object=")){
                        $searchString = "index.php?object=";
                        $begin = strpos($requestUrl, $searchString) + strlen($searchString);
                        $this->redirectToObjectId(substr($requestUrl,$begin));
                }
                
                //not tested
                if(FALSE!==strpos($requestUrl, "/home/")){
                        $steamPath = $requestUrl;
                        $this->redirectToSteamPath($steamPath);
                }
            
                //not tested
                if(FALSE!==strpos($requestUrl, "/hilfe/")){
                        $steamPath = $requestUrl;
                        $this->redirectToSteamPath($steamPath);
                }
                
                //not tested
                if(FALSE!==strpos($requestUrl, "/schulen/")){
                        $steamPath = $requestUrl;
                        $this->redirectToSteamPath($steamPath);
                }
                
                //not tested
                if(FALSE!==strpos($requestUrl, "/lernstatt_intern/")){
                        $steamPath = $requestUrl;
                        $this->redirectToSteamPath($steamPath);
                }
                
                //not tested
                if(FALSE!==strpos($requestUrl, "/externe_partner/")){
                        $steamPath = $requestUrl;
                        $this->redirectToSteamPath($steamPath);
                }
                
                //not tested
                if(FALSE!==strpos($requestUrl, "/schulen/")){
                        $steamPath = $requestUrl;
                        $this->redirectToSteamPath($steamPath);
                }
                
                //not tested
                if(FALSE!==strpos($requestUrl, "/dialog/")){
                        $steamPath = $requestUrl;
                        $this->redirectToSteamPath($steamPath);
                }
            
                //not tested
                if(FALSE!==strpos($requestUrl, "/partner/")){
                        $steamPath = $requestUrl;
                        $this->redirectToSteamPath($steamPath);
                }
                
                //not tested
                if(FALSE!==strpos($requestUrl, "/projekte/")){
                        $steamPath = $requestUrl;
                        $this->redirectToSteamPath($steamPath);
                }
                
                //not tested, download case
                if(FALSE!==strpos($requestUrl, "/tools/get.php?object=")){
                        $searchString = "/tools/get.php?object=";
                        $begin = strpos($requestUrl, $searchString) + strlen($searchString);
                        
                        $destination = substr($requestUrl,$begin);
                        $isObjectId = $destination === (string)(intval($destination)) ;
                        
                        if($isObjectId){
                            //destination is a number
                            $this->redirectToDownloadObjectId($destination);
                        }  else {
                            //destination is a path/string
                            $this->redirectToDownloadPath($destination);
                        }
                }
                
                //TODO /download/
                //not tested, download case
                if(FALSE!==strpos($requestUrl, "/download/")){
                        $searchString = "/download/";
                        $begin = strpos($requestUrl, $searchString) + strlen($searchString);
                        
                        $destination = substr($requestUrl,$begin);
                        
                        $destArray = explode("/", $destination);
                        
                        $objectId = $destArray[0];
                        $name = $destArray[1];
                             
                        $this->redirectToDownloadObjectId($objectId, $name);
                }
                
                die("URL-Umleitung fehlgeschlagen");
            
                $rawWidget = new \Widgets\RawHtml();
                $rawWidget->setHtml("Test bid2PathCompatibility ".$requestUrl);
                $frameResponseObject->addWidget($rawWidget);
                return $frameResponseObject;
	}
        
        
        /*
         * redirects to the extension/object with a steam path
         */
        private function redirectToSteamPath($steamPath){
                $objectId = $this->getObjectId($steamPath);
                $url = \ExtensionMaster::getInstance()->getUrlForObjectId($objectId, "view");
                header("Location: ".$url);
                die;
        }
        
        
        /*
         * redirects to the extension/object with a object id
         */
        private function redirectToObjectId($objectId){
                $objectId = (string)intval($objectId);
                $url = \ExtensionMaster::getInstance()->getUrlForObjectId($objectId, "view");
		header("Location: ".$url);
                die;
        }
        
        private function redirectToDownloadObjectId($objectId, $name=""){
                $objectId = (string)intval($objectId);
                
                if($name==""){
                    $url = "/download/document/".$objectId;
                }else{
                    $url = "/download/document/".$objectId."/".$name;
                }
                    
                header("Location: ".$url);
                die;
        }
        
        private function redirectToDownloadPath($steamPath){
                $url = "/download/document/".$this->getObjectId($steamPath);
                header("Location: ".$url);
                die;
        }
        
        
        
        
        /*
         * gets a objectId to a steam path
         */
        private function getObjectId($path){
            $object = \steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $path);
            return $object->get_id();
        }
}
?>