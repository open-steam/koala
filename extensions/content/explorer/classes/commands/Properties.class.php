<?php
namespace Explorer\Commands;

class Properties extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
		$type = "";
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

		$isWriteable=$object->check_access_write();


		$type = getObjectType($object);
		switch ($type) {
			case "document":
				$labelName = "Dateiname";
				$typeName = "Dokument";
				break;
					
			case "forum":
				$labelName = "Forumname";
				$typeName = "Forum";
				break;
					
			case "referenceFolder":
				$labelName = "Linkname";
				$typeName = "Referenz";
				break;

			case "referenceFile":
				$labelName = "Linkname";
				$typeName = "Referenz";
				break;
					
			case "user":
				$labelName = "Benutzername";
				$typeName = "Benutzer";
				break;
					
			case "group":
				$labelName = "Gruppenname";
				$typeName = "Gruppe";
				break;
					
			case "trashbin":
				$labelName = "Papierkorb";
				$typeName = "Papierkorb";
				break;
					
			case "gallery":
				$labelName = "Galeriename";
				$typeName = "Galerie";
				break;
					
			case "portal":
				$labelName = "Portalname";
				$typeName = "Portal";
				break;

			case "portalColumn":
				$labelName = "Spaltenname";
				$typeName = "Portal-Spalte";
				break;
					
			case "portalPortlet":
				$labelName = "Portletname";
				$typeName = "Portal-Portlet";
				break;
					
			case "userHome":
				$labelName = "Ordnername";
				$typeName = "Benutzerordner";
				break;

			case "groupWorkroom":
				$labelName = "Ordnername";
				$typeName = "Gruppen-Arbeitsraum";
				break;
					
			case "room":
				$labelName = "Ordnername";
				$typeName = "Ordner";
				break;
					
			case "container":
				$labelName = "Ordnername";
				$typeName = "Ordner";
				break;
                        
                        case "docextern":
				$labelName = "Internet-Link-Name";
				$typeName = "Internet-Referenz";
				break;
            
			case "unknown":
				$labelName = "Name";
				$typeName = "unbekannt";
				break;
    
			default:
				$labelName = "Name";
				$typeName = "unbekannt";
				break;
		}

		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Eigenschaften von »" . getCleanName($object) . "«<br>({$typeName})");

		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);

		if ($type == "userHome" || $type == "groupWorkroom") {
			$dataNameInput= new \Widgets\TextInput();
			$dataNameInput->setLabel("{$labelName}");
			$dataNameInput->setData($object);
			$dataNameInput->setReadOnly(true);
			$dataNameInput->setContentProvider(\Widgets\DataProvider::staticProvider(getCleanName($object, -1)));

			/*		$titelInput = new \Widgets\TextInput();
			 $titelInput->setLabel("Titel (Beschreibung)");
			 $titelInput->setData($object);
			 $titelInput->setContentProvider(\Widgets\DataProvider::staticProvider(getCleanName($object)));*/
		} else {
			$dataNameInput= new \Widgets\TextInput();
			$dataNameInput->setLabel("{$labelName}");
			if(!$isWriteable){
				$dataNameInput->setReadOnly(true);
			}
			$dataNameInput->setData($object);
			$dataNameInput->setContentProvider(new NameAttributeDataProvider("OBJ_NAME", getCleanName($object, -1)));
			if($type == "document"){
				$docType = $object->get_attribute("DOC_MIME_TYPE");
				$isJpg=strpos($docType,"jpg") !== false;
				$isJpeg= strpos($docType,"jpeg") !== false;
				$isGif= strpos($docType,"gif") !== false;
				$isPng = strpos($docType,"png") !== false;
				if($isGif || $isJpeg  || $isJpg || $isPng){
					$textArea = new \Widgets\Textarea();
					$textArea->setLabel("Beschreibung");
					$textArea->setData($object);
					if(!$isWriteable){
						//Fehlt Methode
					}
					$textArea->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
					$textArea->setHeight(100);
					$desc = $object->get_attribute("OBJ_DESC");
					if($desc !== 0){
						$jsWrapperPicture = new \Widgets\JSWrapper();
						$jsWrapperPicture->setJs('$(".plain").val("'.$desc.'")');
					}
				}
			}

			/*		$titelInput = new \Widgets\TextInput();
			 $titelInput->setLabel("Titel (Beschreibung)");
			 $titelInput->setData($object);
			 $titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));*/
		}

		$ownerField = new \Widgets\TextField();
		$ownerField->setLabel("Besitzer");
		$creator= $object->get_creator();
		$creatorName = getCleanName($creator);
		$ownerField->setValue($creatorName);

		$changedField = new \Widgets\TextField();
		$changedField->setLabel("zuletzt geändert");
		$changedDate = $object->get_attribute(OBJ_LAST_CHANGED);
		$changedDate = getFormatedDate($changedDate);
		$changedField->setValue($changedDate);

		$createdField = new \Widgets\TextField();
		$createdField->setLabel("erstellt");
		$createDate = $object->get_attribute(OBJ_CREATION_TIME);
		$createDate = getFormatedDate($createDate);
		$createdField->setValue($createDate);
		

		//$hiddenCheckbox = new \Widgets\Checkbox();
		//$hiddenCheckbox->setLabel("Verstecken");
		//$hiddenCheckbox->setCheckedValue("1");
		//$hiddenCheckbox->setUncheckedValue(0);
		//$hiddenCheckbox->setData($object);
		//$hiddenCheckbox->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:hidden"));

		$containerViewRadio = new \Widgets\RadioButton();
		$containerViewRadio->setLabel("Erstes Dokument");
		$containerViewRadio->setData($object);
		$containerViewRadio->setOptions(array(array("name"=>"Normal (Ordneransicht)", "value"=>"normal"),array("name"=>"Deckblatt (statt der Ordneransicht)", "value"=>"index"),array("name"=>"Kopfdokument (über der Ordneransicht)", "value"=>"head")));
		$containerViewRadio->setDefaultChecked("normal");
		$containerViewRadio->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:presentation"));
		if(!$isWriteable){
			//WIDGET-Eigenschaft fehlt noch
		}

		// 		$descriptionTextarea = new \Widgets\Textarea();
		// 		$descriptionTextarea->setLabel("Beschreibung");
		// 		$descriptionTextarea->setData($object);
		// 		$descriptionTextarea->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:description"));

		//TODO: value is array
		$keywordArea = new \Widgets\TextInput();
		$keywordArea->setLabel("Schlüsselwörter");
		$keywordArea->setData($object);
		$keywordArea->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_KEYWORDS"));
		if(!$isWriteable){
			$keywordArea->setReadOnly(true);
		}

		//TODO: bid-attribute
		$descriptionInput = new \Widgets\TextInput();
		$descriptionInput->setLabel("Beschreibung");
		$descriptionInput->setData($object);
		$descriptionInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:description"));
		if(!$isWriteable){
			$descriptionInput->setReadOnly(true);
		}

		$checkboxInput = new \Widgets\Checkbox();
		$checkboxInput->setLabel("Benutzer dürfen editieren?");
		$checkboxInput->setCheckedValue("1");
		$checkboxInput->setUncheckedValue(0);
		$checkboxInput->setData($object);
		$checkboxInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:forum_is_editable"));
		if(!$isWriteable){
			//WIDGET-Eigenschaft fehlt noch
		}

		$seperator= new \Widgets\RawHtml();
		$seperator->setHtml("<br style=\"clear:both\"/>");
		$headlineAlg=new \Widgets\RawHtml();
		$headlineAlg->setHtml("<h3>Allgemein</h3>");
		$headlineMeta=new \Widgets\RawHtml();
		$headlineMeta->setHtml("<h3>Meta-Informationen</h3>");
		$headlineView=new \Widgets\RawHtml();
		$headlineView->setHtml("<h3>Darstellung</h3>");

		$dialog->addWidget($headlineAlg);
		//$dialog->addWidget($titelInput);
		//$dialog->addWidget($seperator);

		if($type == "document"){

			$docType = $object->get_attribute("DOC_MIME_TYPE");
			$isJpg=strpos($docType,"jpg") !== false;
			$isJpeg= strpos($docType,"jpeg") !== false;
			$isGif= strpos($docType,"gif") !== false;
			$isPng = strpos($docType,"png") !== false;
			if($isGif || $isJpeg  || $isJpg || $isPng){
				$fileName = new \Widgets\TextInput();
				$fileName->setLabel("Dateiname");
				if(!$isWriteable){
					$fileName->setReadOnly(true);
				}
				$fileName->setData($object);
				$fileName->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_NAME"));
				$dialog->addWidget($fileName);
			}

		}else{
			$dialog->addWidget($dataNameInput);
		}

		$dialog->addWidget($seperator);
		$dialog->addWidget($ownerField);
		$dialog->addWidget($seperator);
		$dialog->addWidget($changedField);
		$dialog->addWidget($seperator);
		$dialog->addWidget($createdField);
		$dialog->addWidget($seperator);
		if ($type == "container" || $type == "room") {
			$dialog->addWidget($headlineView);
			//$dialog->addWidget($hiddenCheckbox);
			//$dialog->addWidget($seperator);
			$dialog->addWidget($containerViewRadio);
			$dialog->addWidget($seperator);
		}
		else if($type == "document"){
			$docType = $object->get_attribute("DOC_MIME_TYPE");
			$isJpg=strpos($docType,"jpg") !== false;
			$isJpeg= strpos($docType,"jpeg") !== false;
			$isGif= strpos($docType,"gif") !== false;
			$isPng = strpos($docType,"png") !== false;
			if($isGif || $isJpeg  || $isJpg || $isPng){
				$dialog->addWidget($textArea);
				$dialog->addWidget($jsWrapperPicture);
			}


		}
		else if ($type == "forum") {
			$creatorId=$creator->get_id();
			$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
			$currentUserId = $currentUser->get_id();
			if($currentUserId == $creatorId){
				//				$checkValue= $object->get_attribute("bid:forum_is_editable");
				//				$checked = $checkValue ? 1 : 0;
				//				$checkboxInput;
				$dialog->addWidget($checkboxInput);
				$dialog->addWidget($seperator);
			}
		}

		// 		$dialog->addWidget($headlineMeta);
		// 		$dialog->addWidget($keywordArea);
		// 		$dialog->addWidget($seperator);
		// 		$dialog->addWidget($descriptionInput);

                
                //www-link
		if($type == "docextern"){
			$urlInput = new \Widgets\TextInput();
			$urlInput->setLabel("URL");
			$urlInput->setData($object);
			$urlInput->setContentProvider(\Widgets\DataProvider::attributeProvider("DOC_EXTERN_URL"));
                        $dialog->addWidget($urlInput);
			$dialog->addWidget($seperator);
		}

                
                
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {

		$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
		$object = $currentUser->get_workroom();

		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Eigenschaften von " . $object->get_name());

		$dialog->setButtons(array(array("name"=>"speichern", "href"=>"save")));
		return $dialog->getHtml();
	}
}

class NameAttributeDataProvider extends \Widgets\AttributeDataProvider {

	public function getUpdateCode($object, $elementId, $successMethode = "") {
		if (is_int($object)) {
			$objectId = $object;
		} else {
			$objectId = $object->get_id();
		}
		$function = ($successMethode != "") ? ", function(response){{$successMethode}({$elementId}, response);}" : ",''";
		return <<< END
	sendRequest('databinding', {'id': {$objectId}, 'attribute': 'OBJ_DESC', 'value': ''}, '', 'data');
	sendRequest('databinding', {'id': {$objectId}, 'attribute': '{$this->getAttribute()}', 'value': value}, '', 'data'{$function});
END;
	}

}

?>