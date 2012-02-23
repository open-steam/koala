<?php
namespace Portfolio\Commands;

class Edit extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $entry;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: null;
			isset($this->params["env"]) ? $env = $this->params["env"]: null;
			isset($this->params["type"]) ? $type = $this->params["type"]: null;
		}
		if (isset($env)) {
			$portfolioInstance = \Portfolio\Model\Portfolios::getInstanceByRoom($env);
			$this->entry = $portfolioInstance->createEntry($type);
		} else {
			$room = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			if ($room instanceof \steam_room) {
				$this->entry = \Portfolio\Model\Entry::getEntryByRoom($room);
			}
		}
		$this->id = $this->entry->get_id();
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$entry = $this->entry;
		
		$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle($entry::getEntryTypeEditDescription());
		$dialog->setDescription($entry::getEntryTypeEditInfo());

		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);
		$dialog->setForceReload(true);
		$dialog->setButtons(array(array("class"=>"negative", "js"=>"sendRequest('deleteEntry', {'id':'{$this->id}'}, '', 'data', null, function(response) {closeDialog(); location.reload();}); return false;", "label"=>"Löschen")));
		
		$clearer = new \Widgets\Clearer();
		
		$entryAttributes = $entry->getEntryAttributes();
		foreach($entryAttributes as $entryAttribute) {
			$widget = new $entryAttribute["widget"]();
			$widget->setData($this->id);
			$widget->setContentProvider(new \Widgets\AttributeDataProvider($entryAttribute["attributeName"]));
			$widget->setLabel($entryAttribute["label"]);
			$dialog->addWidget($widget);
			$dialog->addWidget($clearer);
		}
		
		
/*		$type = new \Widgets\ComboBox();
		$type->setData($this->id);
		$type->setContentProvider(new \Widgets\AttributeDataProvider("PORTFOLIO_ENTRY_SCHOOL_TYPE"));
		$type->setLabel("Schulabschluss");
		$type->setOptions(array(array("name"=>"Volks/- Hauptschulabschluss", "value"=>"haupt"),
								array("name"=>"Mittlere Reife/Realschulabschluss", "value"=>"real"),
								array("name"=>"Fachhochschulreife", "value"=>"fh"),
								array("name"=>"Abitur", "value"=>"abi"),
								array("name"=>"Sonstige", "value"=>"sonst")));
		
		$dialog->addWidget($type);
		$dialog->addWidget($clearer);
		
		$note = new \Widgets\ComboBox();
		$note->setData($this->id);
		$note->setContentProvider(new \Widgets\AttributeDataProvider("PORTFOLIO_ENTRY_SCHOOL_NOTE"));
		$note->setLabel("Druchschnittsnote");
		$note->setOptions(array(array("name"=>"Sehr gut (1)", "value"=>"1"),
								array("name"=>"Gut (2)", "value"=>"2"),
								array("name"=>"Befriedigend (3)", "value"=>"3"),
								array("name"=>"Ausreichend (4)", "value"=>"4")));
								
		$dialog->addWidget($note);
		$dialog->addWidget($clearer);
		
		$year = new \Widgets\DatePicker();
		$year->setData($this->id);
		$year->setContentProvider(new \Widgets\AttributeDataProvider("PORTFOLIO_ENTRY_DATE"));
		$year->setLabel("Abschlussdatum");
		$year->setPlaceholder("z.B. 01.01.1995");
		$dialog->addWidget($year);
		$dialog->addWidget($clearer);
		
		$bemerk = new \Widgets\TextInput();
		$bemerk->setLabel("Bemerkung");
		$bemerk->setData($this->id);
		$bemerk->setContentProvider(new \Widgets\AttributeDataProvider("PORTFOLIO_ENTRY_NOTE"));
		$bemerk->setPlaceholder("z.B. inhaltliche Schwerpunkte; besondere Leistungen");
		$dialog->addWidget($bemerk);
		$dialog->addWidget($clearer);*/
		
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
	}
}
?>