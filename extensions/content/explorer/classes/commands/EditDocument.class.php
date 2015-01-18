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
				
                                //document type: html text
                                if($mimetype == "text/html"){
                                	$actionBar->setActions(array(
					array("name"=>"Anzeigen", "link"=> PATH_URL . "Explorer/ViewDocument/" . $this->id . "/"),
					array("name"=>"Quelltext", "link"=> PATH_URL . "Explorer/CodeEditDocument/" . $this->id . "/"),
					array("name"=>"Eigenschaften", "ajax"=>array("onclick"=>array("command"=>"properties", "params"=>array("id"=>$this->id), "requestType"=>"popup"))),
					array("name"=>"Rechte", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>$this->id), "requestType"=>"popup")))
					));

				}
                                
                                //document type: simple text    
                                else{
                                	$actionBar->setActions(array(
					array("name"=>"Anzeigen", "link"=> PATH_URL . "Explorer/ViewDocument/" . $this->id . "/"),
					array("name"=>"Eigenschaften", "ajax"=>array("onclick"=>array("command"=>"properties", "params"=>array("id"=>$this->id), "requestType"=>"popup"))),
					array("name"=>"Rechte", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>$this->id), "requestType"=>"popup")))
					));
				}

				$contentText = new \Widgets\Textarea();
				$contentText->setWidth(945);
				$contentText->setheight(400);
				$contentText->setData($object);
				
                                
                                $contentText->setTextareaClass("mce-full");
				
                                
                                //convert
                                if (strstr($mimetype, "text/plain")) {
                                    $bidDokument = new \BidDocument($object);
                                    $html = $bidDokument->get_content();
                                }else{
                                    $html = cleanHTML($object->get_content());
                                    
                                    //make html modifications
                                    $htmlDocument = new \HtmlDocument();
                                    $html = $htmlDocument->makeEditorModifications($html, $object);
                                }
                                
				$contentText->setContentProvider( new \Widgets\TextContentDataProvider($html));
				$clearer = new \Widgets\Clearer();
                                
                                $saveButton = new \Widgets\SaveButton();
				

				$frameResponseObject->setTitle($name);
				$frameResponseObject->addWidget($actionBar);
				$frameResponseObject->addWidget($contentText);
                                $frameResponseObject->addWidget($saveButton);
				$frameResponseObject->addWidget($clearer);
				return $frameResponseObject;
			}
		} else {
			ExtensionMaster::getInstance()->send404Error();
		}
	}
}
?>