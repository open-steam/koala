<?php
class wiki_iterator_aggregate implements IteratorAggregate {

	public $entries;
	private $steamWiki;

	public function __construct($id) {
		$this->steamWiki = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id, CLASS_CONTAINER);
		$this->entries = $this->steamWiki->get_inventory(CLASS_DOCUMENT, array(), SORT_NAME);
	}

	public function getIterator() {
		return new ArrayIterator($this->getDocuments());
	}

	public function getDocuments() {
		return $this->entries;
	}
}