<?php

namespace Postbox\Commands;

class Upload extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->id = $this->params[0] : "";
        } else if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array();
        // max file size in bytes
        $sizeLimit = return_bytes(ini_get('post_max_size'));
        $envid = $_REQUEST["destid"];

        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit, $envid);
        $result = $uploader->handleUpload(PATH_TEMP);
        // to pass data through iframe you will need to encode all html tags
        //echo the result and terminate the script
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
        die;
        //$ajaxResponseObject->setStatus("ok");
        //return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array();
        // max file size in bytes
        $sizeLimit = return_bytes(ini_get('post_max_size'));
        $envid = $_REQUEST["destid"];

        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit, $envid);
        $result = $uploader->handleUpload(PATH_TEMP);
        // to pass data through iframe you will need to encode all html tags
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
        die;
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

        if ($realSize != $this->getSize()) {
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
        if (isset($_SERVER["CONTENT_LENGTH"])) {
            return (int) $_SERVER["CONTENT_LENGTH"];
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
        if (!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)) {
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

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760, $envid) {
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

    private function checkServerSettings() {
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));

        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit) {
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            throw new \Exception("{'error':'increase post_max_size and upload_max_filesize to $size'}");
        }
    }

    private function toBytes($str) {
        $val = trim($str);
        $last = strtolower($str[strlen($str) - 1]);
        switch ($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }

    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE) {
        if (!is_writable($uploadDirectory)) {
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        if (!$this->file) {
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


        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of ' . $these . '.');
        }

        //create empty steam_document and check write access
        $env = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->envid);
        
        $obj = $env->get_environment();
        

        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();

        //check if the user folder already exists
        $objPath = $obj->get_attribute("OBJ_PATH");
        $currentUserFullName = $currentUser->get_full_name();
        $filePath = $objPath . "/postbox_container/" . $currentUserFullName;
        $file = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $filePath);

        if ($file instanceof \steam_container) {
            $folderId = $file->get_id();
        } else {
            $folderId = 0;
        }
       
        if ($folderId != 0) {
            $isFolderAva = true;
        } else {
            $isFolderAva = false;
        }
        
        $username = $currentUser->get_full_name();
        $usernameShort = $currentUser->get_name();
        if ($isFolderAva) {
            $container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $folderId);
        } else {
            $container = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $username, $env);
        }
        
        if (!$replaceOldFile) {
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        //if we cannot have two files with the same name, generally rename each uploaded file with a date string at the end
        if (defined("API_DOUBLE_FILENAME_NOT_ALLOWED") && API_DOUBLE_FILENAME_NOT_ALLOWED){
            $filename.= date(" Y-m-d H-i", time());
        }
        
        $steam_document = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $usernameShort . "_" . $filename . "." . $ext, "", "", $container);

        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)) {
            if (defined("ENABLE_AUTOMATIC_IMAGE_SCALING") && ENABLE_AUTOMATIC_IMAGE_SCALING) {
                try {
                    $imagick = new \Imagick();
                    $imagick->readimage($uploadDirectory . $filename . '.' . $ext);
                    if ($imagick->valid()) {
                        $imageProperties = $imagick->getimagegeometry();
                        if ($imageProperties["width"] > 1920 || $imageProperties["height"] > 1080) {
                            $imagick->resizeimage(1920, 1080, \Imagick::FILTER_UNDEFINED, 0, true);
                            $imagick->writeimage($uploadDirectory . $filename . '.' . $ext);
                        }
                    }
                } catch (\IMagickException $e) {
                    // file is no picture
                }
            }

            $steam_document->set_content(file_get_contents($uploadDirectory . $filename . '.' . $ext));
            unlink($uploadDirectory . $filename . '.' . $ext);
            return array('success' => true);
        } else {
            return array('error' => 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
    }

}

?>