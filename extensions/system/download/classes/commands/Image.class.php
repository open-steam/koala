<?php
namespace Download\Commands;
class Image extends AbstractDownloadCommand implements \IResourcesCommand {
	
	protected $width;
	protected $height;

	public function validateData(\IRequestObject $requestObject) {
		if (parent::validateData($requestObject)) {
			if (isset($this->params[1]) && isset($this->params[2])) {
				$this->width = $this->params[1];
				$this->height = $this->params[2];
				if (isset($this->params[3])) {
					$this->filename = $this->params[3];
				}
			}
			return true;
		}
		return false;
	}
}
?>