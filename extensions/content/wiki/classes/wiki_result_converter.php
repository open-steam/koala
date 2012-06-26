<?php
class wiki_result_converter extends result_converter {

	public function __construct(){
		require_once PATH_LIB.'wiki_handling.inc.php';
	}

	protected function get_excerpts($hit, $queryInstance) {
		/*
		$bench = new mybenchmark();
		$bench->start();
		$steamWiki = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $hit->docID, CLASS_DOCUMENT);
		$wikitext = wiki_to_html_plain($steamWiki);
		$bench->stop();
		Registry::get('logger')->info("Time to fetch wiki: ".$bench->getExcecutionTime());
		$bench->reset();
		
		$bench->start();
		//echo $queryInstance->highlightMatches($wikitext, new my_highlighter());
		$high = $queryInstance->highlightMatches($wikitext);

		$regex = '/(.{0,100})(\<b style="color:black;background-color:#66ffff"\>)(.{0,100})/';


		$matches = array();
		preg_match_all($regex, $high, $matches, PREG_OFFSET_CAPTURE);

		foreach ($matches[0] as $a) {
			$results[] = "..." . strip_tags(trim($a[0]), "<b>") . "...";
		}
		$bench->stop();
		Registry::get('logger')->info("Time to calculate exc.: ".$bench->getExcecutionTime());
		$bench->reset();*/
		
		$results = array();

		return $results;
	}

	public function convert($hit, $queryInstance) {
		$excerpts = $this->get_excerpts($hit, $queryInstance);
		$ret = array();
		foreach ($excerpts as $ex) {
			$sr = new search_result($hit, $ex);
			$ret[] = $sr;
		}
		return $ret;
	}
}