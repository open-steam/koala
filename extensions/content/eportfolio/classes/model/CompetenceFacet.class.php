<?php
namespace Portfolio\Model;
class CompetenceFacet {
	public $name;
	public $short;
	public $description;

	public function __construct($name, $description, $short = "") {
		$this->name = $name;
		$this->description = $description;
		if ($short == "")
			$short = substr($name, 0, 1);
		$this->short = $short;
	}
}
?>