<?php
namespace Widgets;

class Grid extends Widget {
	
	private $data;
	
	/**
	 * the data array should look like:
	 * { "headline" => { 
	 * 						{ "name" => widget or string,
	 * 						  "colspan" => int or string,			// (optional, default = 1)
	 * 						  "colspan" => int or string			// (optional)
	 * 						}, ...
	 * 					},											// (optional)
	 *   "rows" => {
	 *   					{ 										// a row
	 *   						{									// a field
	 *   							"content" => widget or sting
	 *   							"type" => "label" or "value"	// (optional, default = "value")
	 *   							"colspan" => int or string		// (optional, default = 1)
	 *   						}, ...
	 *   					}, ...
	 *   		   }
	 * @param array $data
	 */
	public function setData($data) {
		$this->data = $data;
	}
	
	public function getHtml() {
		if (!is_array($this->data)) {
			$this->data = array();
		}
		
		if (isset($this->data["headline"]) && is_array($this->data["headline"])) {
			$headline = $this->data["headline"];
		} else {
			$headline = array();
		}
		
		if (isset($this->data["rows"]) && is_array($this->data["rows"])) {
			$rows = $this->data["rows"];
		} else {
			$rows = array();
		}
		
		foreach($headline as $count => $headline_item) {
			if (is_array($headline_item)) {
				$this->getContent()->setCurrentBlock("BLOCK_GRID_HEADER");
				if (isset($headline_item["name"])) {
					if ($headline_item["name"] instanceof Widget) {
						$this->getContent()->setVariable("GRID_HEADER", $headline_item["name"]->getHtml());
					} else {
						$this->getContent()->setVariable("GRID_HEADER", $headline_item["name"]);
					}
				}
				if (isset($headline_item["colspan"])){
					$this->getContent()->setVariable("GRID_HEADER_COLSPAN", $headline_item["colspan"]);
				} else {
					$this->getContent()->setVariable("GRID_HEADER_COLSPAN", 1);
				}
				// todo $headline_item["width"]
				$this->getContent()->parse("BLOCK_GRID_HEADER");
			}
		}
		
		foreach($rows as $count_row => $row) {
			if (is_array($row)) {
				foreach ($row as $count_field => $field) {
					if (is_array($field)) {
						if (isset($field["type"]) && ($field["type"] === "label" || $field["type"] === "value")) {
							$field_type = strtoupper($field["type"]);
						} else {
							$field_type = strtoupper("value");
						}
						if (isset($field["content"])) {
							if ($field["content"] instanceof Widget) {
								$this->getContent()->setVariable("GRID_FIELD_" . $field_type, $field["content"]->getHtml());
							} else {
								$this->getContent()->setVariable("GRID_FIELD_" . $field_type, $field["content"]);
							}
						}
						if (isset($field["colspan"])) {
							$this->getContent()->setVariable("GRID_FIELD_{$field_type}_COLSPAN" , $field["colspan"]);
						} else {
							$this->getContent()->setVariable("GRID_FIELD_{$field_type}_COLSPAN" , "1");
						}
					}
					$this->getContent()->parse("BLOCK_GRID_FIELD");
				}
				$this->getContent()->parse("BLOCK_GRID_ROW");
			}
		}
		
		
		return $this->getContent()->get();
	}
	
}
?>