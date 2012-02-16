<?php
namespace Calendar\Commands;
class UploadIcsFile extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $id;
	private $params;

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

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		if(!empty($_FILES)){
			$array=file($_FILES["uploadedfile"]["tmp_name"]);
			$string="";
			foreach($array as $row){
				$string.=$row . " \n";
			}
			$obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			$icsFile = \steam_factory::create_textdoc($GLOBALS["STEAM"]->get_id(), "ics file ". $this->id, $string);	
			$obj->set_attribute("CALENDAR_ICS_FILE_IN", $icsFile);		
		}else{
			$rawHtml = new \Widgets\RawHtml();
// HTML CODE FROM http://www.tizag.com/phpT/fileupload.php			
			$rawHtml->setHtml('<form enctype="multipart/form-data" action="#" method="POST">
<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
Choose a file to upload: <input name="uploadedfile" type="file" /><br />
<input type="submit" value="Upload File" />
</form>
		');
			$frameResponseObject->addWidget($rawHtml);

		}
		return $frameResponseObject;
	}
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;

	}
}
?>