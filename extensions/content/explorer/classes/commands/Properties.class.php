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
				$typeName = "unbekannt".$type;
				break;
			default:
				$labelName = "Name";
				$typeName = "unbekannt".$type;
				break;
		}
		
		
		// --- create dialog section ---
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Eigenschaften von »" . getCleanName($object) . "«<br>({$typeName})");
		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);

		
		// --- some tests ---
		$documentIsPicture = false;
		if($type == "document"){
			$docType = $object->get_attribute("DOC_MIME_TYPE");
			$isJpg=strpos($docType,"jpg") !== false;
			$isJpeg= strpos($docType,"jpeg") !== false;
			$isGif= strpos($docType,"gif") !== false;
			$isPng = strpos($docType,"png") !== false;
			if($isGif || $isJpeg  || $isJpg || $isPng){
				$documentIsPicture = true;
			}	
		}
		
		
		// --- create widgets section ---
		$nameInput= new \Widgets\TextInput();
		$nameInput->setLabel("{$labelName}");
		$nameInput->setData($object);
		$nameInput->setContentProvider(new NameAttributeDataProvider("OBJ_NAME", getCleanName($object, -1)));
		
		
		if($type == "document"){
			if($documentIsPicture){
				$descriptionTextArea = new \Widgets\Textarea();
				$descriptionTextArea->setLabel("Beschreibung");
				$descriptionTextArea->setData($object);
				$descriptionTextArea->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
				$descriptionTextArea->setHeight(100);
				$desc = $object->get_attribute("OBJ_DESC");
				if($desc !== 0){
					$jsWrapperPicture = new \Widgets\JSWrapper();
					$jsWrapperPicture->setJs('$(".plain").val("'.$desc.'")');
				}
			}
		}
	
		
		//userhome and workroom
		if ($type == "userHome" || $type == "groupWorkroom") {
			unset($nameInput);
			$nameInput= new \Widgets\TextInput();
			$nameInput->setLabel("{$labelName}");
			$nameInput->setData($object);
			$nameInput->setReadOnly(true);
			$nameInput->setContentProvider(\Widgets\DataProvider::staticProvider(getCleanName($object, -1)));
		}
		
		
		//name for pictures
		if($type == "document"){
			if($documentIsPicture){
				$documentFileNameInput = new \Widgets\TextInput();
				$documentFileNameInput->setLabel("Dateiname");
				$documentFileNameInput->setData($object);
				$documentFileNameInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_NAME"));
			}
		}
		
		
		//owner field
		$ownerField = new \Widgets\TextField();
		$ownerField->setLabel("Besitzer");
		$creator= $object->get_creator();
		$creatorName = getCleanName($creator);
		$ownerField->setValue($creatorName);

		
		//last modified
		$changedField = new \Widgets\TextField();
		$changedField->setLabel("zuletzt geändert");
		$changedDate = $object->get_attribute(OBJ_LAST_CHANGED);
		$changedDate = getFormatedDate($changedDate);
		$changedField->setValue($changedDate);

		
		//creation time
		$createdField = new \Widgets\TextField();
		$createdField->setLabel("erstellt");
		$createDate = $object->get_attribute(OBJ_CREATION_TIME);
		$createDate = getFormatedDate($createDate);
		$createdField->setValue($createDate);

		
		// --- special object fields ---
		//www-link
		if($type == "docextern"){
			$urlInput = new \Widgets\TextInput();
			$urlInput->setLabel("URL");
			$urlInput->setData($object);
			$urlInput->setContentProvider(\Widgets\DataProvider::attributeProvider("DOC_EXTERN_URL"));
		}
		
		
		//folder view
		$containerViewRadio = new \Widgets\RadioButton();
		$containerViewRadio->setLabel("Erstes Dokument");
		$containerViewRadio->setData($object);
		$containerViewRadio->setOptions(array(array("name"=>"Normal (Ordneransicht)", "value"=>"normal"),array("name"=>"Deckblatt (statt der Ordneransicht)", "value"=>"index"),array("name"=>"Kopfdokument (über der Ordneransicht)", "value"=>"head")));
		$containerViewRadio->setDefaultChecked("normal");
		$containerViewRadio->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:presentation"));

		
		//keywords - not used
		//TODO: value is array
		$keywordInput = new \Widgets\TextInput();
		$keywordInput->setLabel("Schlüsselwörter");
		$keywordInput->setData($object);
		$keywordInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_KEYWORDS"));

		
		//description - not used
		//TODO: bid-attribute
		$descriptionInput = new \Widgets\TextInput();
		$descriptionInput->setLabel("Beschreibung");
		$descriptionInput->setData($object);
		$descriptionInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:description"));

		
		//forum user edit sanction
		$checkboxInput = new \Widgets\Checkbox();
		$checkboxInput->setLabel("Benutzer dürfen editieren?");
		$checkboxInput->setCheckedValue("1");
		$checkboxInput->setUncheckedValue(0);
		$checkboxInput->setData($object);
		$checkboxInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:forum_is_editable"));
		
		
		//element separator
		$seperator= new \Widgets\RawHtml();
		$seperator->setHtml("<br style=\"clear:both\"/>");
		
		
		//headline
		$headlineAlg=new \Widgets\RawHtml();
		$headlineAlg->setHtml("<h3>Allgemein</h3>");
		$headlineMeta=new \Widgets\RawHtml();
		$headlineMeta->setHtml("<h3>Meta-Informationen</h3>");
		$headlineView=new \Widgets\RawHtml();
		$headlineView->setHtml("<h3>Darstellung</h3>");
		
		
		// --- add widgets section ---
		$dialog->addWidget($headlineAlg);
		
		//name field handling
		$nameFieldSet = false;
		//pictures
		if(($type == "document") && $documentIsPicture){
			$dialog->addWidget($documentFileNameInput);
			$nameFieldSet = true;
		}
		/*
		if(($type == "document") && !$documentIsPicture){
			$dialog->addWidget($nameInput); //tritt ein wenn nicht document
		}
		*/
		if(!$nameFieldSet){
			$dialog->addWidget($nameInput); //tritt ein wenn nicht document
		}
		
		
		if($type=="docextern"){
			$dialog->addWidget($seperator);
			$dialog->addWidget($urlInput);
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
			$dialog->setForceReload(true);
		}
		else if($type == "document"){
			if($documentIsPicture){
				$dialog->addWidget($descriptionTextArea);
				$dialog->addWidget($jsWrapperPicture);
			}
		}
		else if ($type == "forum") {
			$creatorId=$creator->get_id();
			$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
			$currentUserId = $currentUser->get_id();
			if($currentUserId == $creatorId){
				$dialog->addWidget($checkboxInput);
				$dialog->addWidget($seperator);
			}
		}
		

		//finish response
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