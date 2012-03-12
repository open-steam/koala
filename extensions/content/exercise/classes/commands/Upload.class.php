<?php
namespace Exercise\Commands;
class Upload extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		
		$envid = $_REQUEST["baseroom"];
		if ((isset($_REQUEST["trackmyfile"]))&&(!empty($_REQUEST["trackmyfile"]))) {
			$uploader = new qqFileUploader($envid, $_REQUEST["trackmyfile"]);
		}
		else {
			$uploader = new qqFileUploader($envid);
		}
		$result = $uploader->handleUpload(PATH_TEMP);
		
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
		die;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
	}
}

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new \Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;
    private $envid;
    private $trackid = false;

    function __construct($envid, $trackid = false, array $allowedExtensions = array(), $sizeLimit = 1){     
    	$this->envid = $envid;  
    	$this->trackid = $trackid; 
    	
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        $ext = $pathinfo['extension'];
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        #Create empty document inside the $container for a file (one of several)!!  of the Exercise
        $container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->envid);
        $document1 = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $this->file->getName(), "", "" , $container);
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
        	
        	#Move Contents of uploaded file to the steam document
        	$document1->set_content(file_get_contents($uploadDirectory . $filename . '.' . $ext));
        	
        	#Set attribute for identifying the file as new (in case user wants to discard changes)
        	if ($this->trackid != false) {
        		$document1->set_attribute("SL_SOLFILE_ID", $this->trackid);
        	}
        	$document1->set_attribute("IS_NEW", "TRUE");
        	$document1->set_attribute("DELETEFLAG", "FALSE");
        	
        	unlink($uploadDirectory . $filename . '.' . $ext);
            return array('success'=>true,'steamid'=>$document1->get_id());
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }        
    }    
}
?>