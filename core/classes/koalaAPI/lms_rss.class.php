<?php
/**
 * class for collecting rss-feeds
 * 
 * PHP versions 5
 *
 * 
 * @author	Raimon Dworack <ray@upb.de>
 */
 
 
require_once ("XML/RSS.php");

class lms_rss {

	/**
	 * function get_items:
	 * this function returns the rss_feed items from a single url
	 *
	 * @param string $url the url of the rss_feed
	 * 
	 * @return mixed array with the items
	 *
	 */
	public function get_items($url) 
	{
		$rss = new XML_RSS($url);
		$rss->parse();
		$items = $rss->getItems();
		return $items;
	}
	/**
	* function get_merged items:
	* this function returns the rss_feed items from multiple url's sorted by
	* Date/Time (latest first)
	* 
	* @param array $urls array with the url's of the rss_feeds
	* 
	* @return array the array with the rss-feed items sorted by Date/Time (latest first)
	*/
	public function get_merged_items( $feeds = array ()) 
	{
		if ( ! is_array( $feeds ) )
		{
			return array();
		}
		$all_items = array ();
		foreach( $feeds as $feed )
		{
			$cache = get_cache_function( "rss", 600 );
			$items = $cache->call( "lms_rss::get_items", $feed["link"] );
			for ($j = 0; $j < count($items); $j++) 
			{
				$items[ $j ][ "lms:type" ]   = $feed[ "type" ];
				$items[ $j ][ "lms:source" ] = $feed[ "name" ];
				$items[ $j ][ "lms:rsslink" ]= $feed[ "link" ];
				$items[ $j ][ "lms:context_name" ] = $feed[ "context_name"];
				$items[ $j ][ "lms:context_link" ] = $feed[ "context_link"];
				array_push($all_items, $items[$j]);
			}
		}

		function compare($a, $b) 
		{
			return (strtotime($a["dc:date"]) < strtotime($b["dc:date"])) ? 1 : -1;
		}
		usort($all_items, "compare");
		return $all_items;
	}

}
?>
