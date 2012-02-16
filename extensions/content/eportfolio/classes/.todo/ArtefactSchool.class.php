<?php
class ArtefactSchool extends ArtefactModel{
	
	private $entrySchoolTyp = array("haupt" => "Volks/- Hauptschulabschluss", "real" => "Mittlere Reife/Realschulabschluss", "fh" => "Fachhochschulreife", "abi" => "Abitur", "sonst"=>"Sonstige");
	private $entrySchoolNote = array(1 => "Sehr Gut (1)", 2 => "Gut (2)", 3 => "Befriedigend (3)", 4 => "Ausreichend (4)");
	
	public static function create($name, $description = "", $content = "", $mimeType = "application/x-msdownload", $user = null){
		return self::createObject($name, $description, $content, $mimeType, "SCHOOL", $user);
	}
	
	public function getSchoolTypeRaw() {
		return $this->get_attribute("PORTFOLIO_ENTRY_SCHOOL_TYPE");
	}
	
	public function setSchoolTypeRaw($value) {
		return $this->set_attribute("PORTFOLIO_ENTRY_SCHOOL_TYPE", $value);
	}
	
	public function getSchoolType() {
		$raw = $this->getSchoolTypeRaw();
		return isset($this->entrySchoolTyp[$raw]) ? $this->entrySchoolTyp[$raw] : "k.A.";
	}
	
	public function getSchoolNoteRaw() {
		return $this->get_attribute("PORTFOLIO_ENTRY_SCHOOL_NOTE");
	}
	
	public function setSchoolNoteRaw($value) {
		return $this->set_attribute("PORTFOLIO_ENTRY_SCHOOL_NOTE", $value);
	}
	
	public function getSchoolNote() {
		$raw = $this->getSchoolNoteRaw();
		return isset($this->entrySchoolNote[$raw]) ? $this->entrySchoolNote[$raw] : "k.A.";
	}
}
?>