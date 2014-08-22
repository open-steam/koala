<?php
namespace bid2PathCompatibility\Commands;
class Index extends \AbstractCommand implements \IFrameCommand{
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {

        }

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
                //logging::write_log( LOG_ERROR, "b2pc-frameResponse"); //test

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

                if(strstr($requestUrl, "/modules/portal2/portlets/msg/rss.php?object=")){
                    $id = str_replace("/modules/portal2/portlets/msg/rss.php?object=", "", $requestUrl);
                    header("location: " . PATH_SERVER . "/portletMsg/rss/{$id}");
                    die;
                }

                throw new \Exception("URL-Umleitung fehlgeschlagen");

                $rawWidget = new \Widgets\RawHtml();
                $rawWidget->setHtml("Test bid2PathCompatibility ".$requestUrl);
                $frameResponseObject->addWidget($rawWidget);
                return $frameResponseObject;
	}


        /*
         * redirects to the extension/object with a steam path
         */
        private function redirectToSteamPath($steamPath){
                //logging::write_log( LOG_ERROR, "b2pc-redirectToSteamPath"); //test
                $objectId = $this->getObjectId($steamPath);
                $url = \ExtensionMaster::getInstance()->getUrlForObjectId($objectId, "view");
                header("Location: ".$url);
                die;
        }


        /*
         * redirects to the extension/object with a object id
         */
        private function redirectToObjectId($objectId){
                try {
                    $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
                } catch (\Exception $e){
                    $url = "/404/";
                    header("Location: ".$url);
                    die;
                }
                if ($object instanceof \steam_object) {
                    $url = \ExtensionMaster::getInstance()->getUrlForObjectId($objectId, "view");
                    if (empty($url)) {
                        $url = "/404/";
                    }
                    header("Location: ".$url);
                    die;
                } else {
                    $url = "/404/";
                    header("Location: ".$url);
                    die;
                }

        }

        private function redirectToDownloadObjectId($objectId, $name=""){
                //logging::write_log( LOG_ERROR, "b2pc-redirectToDownloadObjectId"); //test
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
                //logging::write_log( LOG_ERROR, "b2pc-redirectToDownloadPath"); //test
                $url = "/download/document/".$this->getObjectId($steamPath);
                header("Location: ".$url);
                die;
        }




        /*
         * gets a objectId to a steam path
         */
        private function getObjectId($path){
            try{
                $object = \steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), $path);
                if (is_null($object)){
                    throw new \steam_exception;
                }

                if (!is_object($object)){
                    throw new \steam_exception;
                }

                return $object->get_id();
            }  catch (\Exception $e){
                $url = "/404/";
                header("Location: ".$url);
                die;
            }
        }


        public function isGuestAllowed(\IRequestObject $requestObject) {
		return true;
	}
}
?>