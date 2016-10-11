<?php
namespace Questionnaire\Commands;
class UploadImage extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	//TODO: never used

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
			isset($this->params["destid"]) ? $this->id = $this->params["destid"]: "";
		}
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
    // list of valid extensions, ex. array("jpeg", "xml", "bmp")
    $allowedExtensions = array();
		// max file size in bytes
		$sizeLimit = return_bytes(ini_get('post_max_size'));
		$destId = $this->id;
		$destObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $destId);
		//remove old image
		$oldImageId = $destObject->get_attribute("bid:rfb:picture_id");
		if ($oldImageId !== 0) {
			$oldImage = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $oldImageId);
			if ($oldImage instanceof \steam_document) {
				$destObject->delete_attribute("bid:rfb:picture_id");
				$oldImage ->delete();
			}
		}

		// upload image
		//$envid = $destObject->get_environment()->get_id();
		$uploader = new qqFileUploader($allowedExtensions, $sizeLimit, $this->id);
		$result = $uploader->handleUpload(PATH_TEMP);

		// set new image
		$destObject->set_attribute("bid:rfb:picture_id", $result["oid"]);
		// to pass data through iframe you will need to encode all html tags
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
		die;
		//$ajaxResponseObject->setStatus("ok");
		//return $ajaxResponseObject;
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

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760, $envid){
    	$this->envid = $envid;
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
            throw new \Exception("{'error':'increase post_max_size and upload_max_filesize to $size'}");
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

        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }

        $size = $this->file->getSize();

        if ($size == 0) {
            return array('error' => 'File is empty');
        }

        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }

        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }

        //create empty steam_document and check write access
        $steam_document = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $this->file->getName(), "", "" , 0);
        $destObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->envid);

        $steam_document->set_acquire($destObject);

        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }

        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
        	$steam_document->set_content(file_get_contents($uploadDirectory . $filename . '.' . $ext));
        	unlink($uploadDirectory . $filename . '.' . $ext);
            return array('success'=>true, 'oid'=>$steam_document->get_id());
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }

    }
}
?>
