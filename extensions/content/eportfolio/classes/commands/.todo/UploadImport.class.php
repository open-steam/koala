<?php
namespace Portfolio\Commands;
class UploadImport extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
		$allowedExtensions = array("zip");
		$sizeLimit = return_bytes(ini_get('post_max_size'));
		//		$envid = $_REQUEST["envid"];

		$uploader = new qqFileUploader($allowedExtensions, $sizeLimit, $envid);
		$result = $uploader->handleUpload(PATH_TEMP);
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

		//$this->checkServerSettings();

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
	function handleUpload($uploadDirectory){
			
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


		if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
			$zip = new ZipArchive;
			$res = $zip->open($uploadDirectory . $filename . '.' . $ext);
			if ($res === TRUE) {
				$uuid = new UUID();
				$zip->extractTo($uploadDirectory . "/" . $uuid);
				$zip->close();
			}

			self::importFolder($uploadDirectory . "/" . $uuid);

			unlink($uploadDirectory . $filename . '.' . $ext);
			return array('success'=>true);
		} else {
			return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
		}

	}

	function importFolder($directory){
		$xmlDoc = simplexml_load_file($directory . "/leap2A.xml" );
		$author = $xmlDoc->author->name;
		$updated = $xmlDoc->updated;
		foreach ($xmlDoc->entry as $entry) {
			$fileUri = $directory . "/" . $entry->link['href'];
			$mimeType = detectMimeType($fileUri);
			$content = file_get_contents($fileUri, $mimeType);
			$name = $entry->title;
			$artefactType = $entry->category['type'];
			switch ($artefactType){
				case "SCHOOL":
					$artefact = ArtefactSchool::create($name);
				case "EMPLOYMENT":
					$artefact = ArtefactEmployment::create($name);
				case "CERTIFICATE":
					$artefact = ArtefactCertificate::create($name);
				default:
					$artefact = ArtefactCertificate::create($name);
			}
			$artefact->setData($content, $mimeType);
			foreach ($entry->competence as $comp){
				$artefact->addCompetenceString($comp->index, $comp->rating);
			}
			$updated = strtotime($entry->updated);
			$artefact->set_attribute("OBJ_LAST_MODIFIED", $updated); //"OBJ_MODIFICATION_TIME"?
			$published = strtotime($entry->published);
			

		}
	}
}
?>