<?php
namespace Portfolio\Model;
class CompetenceActivity {
	public $name;
	public $description;
	public $index;
	public $job;
	public $niveau;
	public $niveauDescription;

	public function __construct($name, $description, $index, $job = "", $niveau = "", $niveauDescription = "") {
		$this->name = $name;
		$this->description = $description;
		$this->index = $index;
		$this->job = $job;
		$this->niveau = $niveau;
		$this->niveauDescription = $niveauDescription;
	}
	
	public function getDescriptionHtml(){
		return "<span title=\"Tätigkeitsfeldbeschreibung: {$this->description}\" style=\"border-bottom:dotted 2px;\">Tätigkeitsfeld {$this->index}: {$this->name}</span>";
	}
	
}
?>