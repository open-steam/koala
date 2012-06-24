<?php

class wiki_indexer extends koala_indexer {

	protected $wiki_id;
	protected $object_iterator;
	protected $index_dir;
	protected $group_name;

	public function __construct($wiki_id) {
		parent::__construct();
		require_once PATH_LIB.'wiki_handling.inc.php';
		$this->wiki_id = $wiki_id;
		$this->object_iterator = new wiki_iterator_aggregate($wiki_id);
		$this->index_dir = BASE_INDEX_DIR . $wiki_id;
		$this->group_name = lms_steam::get_groupname_for_object_id($this->wiki_id, false);
	}
	
	public function add_documents(){
		foreach($this->object_iterator as $steam_wiki){
			$this->add_new_document("", "", "","", $steam_wiki, false, false);	
		}
	}
	
	public function remove_document($id){
		$index = index_provider::get_index($this->wiki_id);

		$term = new Zend_Search_Lucene_Index_Term($id);
		$query = new Zend_Search_Lucene_Search_Query_Term($term);
		$hits = $index->find($query);

		foreach ($hits as $hit) {
			$index->delete($hit->id);
		}
		$index->commit();
	}

	public function add_new_document($id, $name, $content, $url="", $document=null, $optimize=false, $commit=true) {
		
		if(!empty($document)){
			$steamWiki = $document;
			$id = $document->get_id();
			$name = $document->get_name();
			$content = strip_tags(wiki_to_html_plain($document));
		}
		else{
			$steamWiki = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id, CLASS_DOCUMENT);
			$content = strip_tags(wiki_to_html_plain($steamWiki));
		}
		
		if(substr($name, -5) === ".wiki"){
			$name = substr($name,0, -5);
		}

		$index = index_provider::get_index($this->wiki_id);

		$doc = new Zend_Search_Lucene_Document();
		if(empty($url)){
			$url = PATH_SERVER . "/wiki/" . $id . "/";
		}

		$doc->addField(Zend_Search_Lucene_Field::unIndexed("URL", $url, 'utf-8'));
		$doc->addField(Zend_Search_Lucene_Field::text("docID", $id, 'utf-8'));
		$doc->addField(Zend_Search_Lucene_Field::text("docName", $name, 'utf-8'));
		$doc->addField(Zend_Search_Lucene_Field::unStored("content", $content, 'utf-8'));
		$doc->addField(Zend_Search_Lucene_Field::unIndexed("type", "wiki", 'utf-8'));
		$doc->addField(Zend_Search_Lucene_Field::unIndexed("group", $this->group_name, 'utf-8'));
		
		$index->addDocument($doc);

		if($commit){
			$index->commit();
		}
		if($optimize){
			$index->optimize();
		}
	}

	public function update_changed_document($docID, $c_doc_name="", $c_content="", $document="") {
		/*$index = index_provider::get_index($this->wiki_id);


		$term = new Zend_Search_Lucene_Index_Term($docID);
		$query = new Zend_Search_Lucene_Search_Query_Term($term);
		$hits = $index->find($query);

		foreach ($hits as $hit) {
			$index->delete($hit->id);
		}*/
		$this->remove_document($docID);

		$content = strip_tags(wiki_to_html_plain($document));
		$doc_name = $document->get_name();
		
		
		$url = PATH_SERVER . "/wiki/" . $document->get_id() . "/";
		$this->add_new_document($docID, $doc_name, $content, $url);
	}
}