<?php
namespace Explorer\Commands;

class EditDocument extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		if (isset($this->id)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			if ($object instanceof \steam_document) {
				$mimetype = $object->get_attribute(DOC_MIME_TYPE);
				$objName = $object->get_name();
				$objDesc = trim($object->get_attribute(OBJ_DESC));
				if (($objDesc === 0) || ($objDesc === "")) {
					$name = $objName;
				} else {
					$name = $objDesc . " (" . $objName . ")";
				}

				$actionBar = new \Widgets\ActionBar();
				if($mimetype == "text/html"){
					$actionBar->setActions(array(
					array("name"=>"Anzeigen", "link"=> PATH_URL . "Explorer/ViewDocument/" . $this->id . "/"),
					//array("name"=>"Bearbeiten", "link"=> PATH_URL . "Explorer/EditDocument/" . $this->id . "/"),
					array("name"=>"Quelltext", "link"=> PATH_URL . "Explorer/CodeEditDocument/" . $this->id . "/"),
					//array("name"=>"Herunterladen", "link"=> PATH_URL . "Download/Document/" . $this->id . "/"),
					array("name"=>"Eigenschaften", "ajax"=>array("onclick"=>array("command"=>"properties", "params"=>array("id"=>$this->id), "requestType"=>"popup"))),
					array("name"=>"Rechte", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>$this->id), "requestType"=>"popup")))
					));

				}else{
					$actionBar->setActions(array(
					array("name"=>"Anzeigen", "link"=> PATH_URL . "Explorer/ViewDocument/" . $this->id . "/"),
					//array("name"=>"Bearbeiten", "link"=> PATH_URL . "Explorer/EditDocument/" . $this->id . "/"),
					//array("name"=>"Quelltext", "link"=> PATH_URL . "Explorer/CodeEditDocument/" . $this->id . "/"),
					//array("name"=>"Herunterladen", "link"=> PATH_URL . "Download/Document/" . $this->id . "/"),
					array("name"=>"Eigenschaften", "ajax"=>array("onclick"=>array("command"=>"properties", "params"=>array("id"=>$this->id), "requestType"=>"popup"))),
					array("name"=>"Rechte", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>$this->id), "requestType"=>"popup")))
					));
				}

				$contentText = new \Widgets\Textarea();
				$contentText->setWidth(945);
				$contentText->setheight(400);
				$contentText->setData($object);
				$contentText->setTextareaClass("mce-full");
				if ($mimetype !== "text/html") {
					$contentText->setTextareaClass("plain");
				}

				$html = cleanHTML($object->get_content());
				$dirname = dirname($object->get_path()) . "/";
				preg_match_all('/src="([%a-z0-9.\-_\/]*)"/iU', $html, $matches);
				$orig_matches = $matches[0];
				$path_matches = $matches[1];
				foreach($path_matches as $key => $path) {
					$path = urldecode($path);
					if (parse_url($path, PHP_URL_SCHEME) != null) {
						continue;
					}
					$ref_object = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $dirname . $path);
					if ($ref_object instanceof \steam_object) {
						$new_path = PATH_URL . "Download/Document/" . $ref_object->get_id();
					} else {
						$new_path = PATH_URL . "styles/standard/images/404.jpg";
					}
					$html = str_replace($orig_matches[$key], "src=\"$new_path\" data-mce-src=\"$path\"", $html);
				}

				$contentText->setContentProvider(\Widgets\DataProvider::contentProvider($html));
				$clearer = new \Widgets\Clearer();
				// 				$html = "";
				// 				if ($mimetype == "image/png" || $mimetype == "image/jpeg" || $mimetype == "image/gif") {  // Image
				// 					$html = "<div style=\"text-align:center\"><img style=\"max-width:100%\" title=\"{$name}\" alt=\"Bild: {$name}\" src=\"" . PATH_URL . "Download/Document/" . $this->id . "/\"></div>";
				// 				} else if ($mimetype == "text/html") {
				// 					$html = strip_tags($object->get_content(),"<h1><h2><h3><h4><h5><p><a><div><style><b><i><strong><img>");
				// 				} else if (strstr($mimetype, "text")) {
				// 					$html = "<pre>{$object->get_content()}</pre>";
				// 				} else {
				// 					header("location: " . PATH_URL . "Download/Document/" . $this->id . "/");
				// 				}
				// 				$rawHtml = new \Widgets\RawHtml();
				// 				$rawHtml->setHtml($html);

				//$rawHtml->addWidget($breadcrumb);
				//$rawHtml->addWidget($environment);
				//$rawHtml->addWidget($loader);

				$frameResponseObject->setTitle($name);
				$frameResponseObject->addWidget($actionBar);
				//$frameResponseObject->addWidget($rawHtml);
				$frameResponseObject->addWidget($contentText);
				$frameResponseObject->addWidget($clearer);
				return $frameResponseObject;
			}
		} else {
			ExtensionMaster::getInstance()->send404Error();
		}
	}
}
?>