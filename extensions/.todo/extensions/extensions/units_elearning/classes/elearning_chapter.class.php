<?php
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_object.class.php");
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_media.class.php");
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_question.class.php");

define("ENABLE_HTML5_VIDEO", false);

class elearning_chapter extends elearning_object {
	
	private $cache;
	private $steam_object_path;
	
	function __construct($parent_tmp, $steamObject_tmp) {
		$this->parent = $parent_tmp;
		$this->steamObject = $steamObject_tmp;
		//get meta data
		$this->cache = get_cache_function( "unit_elearning", 3600 );
		$this->steam_object_path = $this->cache->call(array($this->steamObject, "get_path"));
		$doc = $this->cache->call("steam_factory::get_object_by_name", $GLOBALS[ "STEAM" ]->get_id(), $this->steam_object_path . "/chapter.xml");
		$this->xml = simplexml_load_string($this->cache->call(array($doc, "get_content")));
	}
	
	function get_content_html() {
		if ($this->get_chapter_count() == count($this->parent->get_chapters())-1) {
				//$exam = $this->get_parent()->get_exam_by_type("final_exam");
				//$exam->set_enabled(true);
				elearning_user::get_instance(lms_steam::get_current_user()->get_name(), elearning_mediathek::get_instance()->get_course()->get_id())->set_exam_enabled();
		}
		
		global $STYLE;
		$doc = $this->cache->call("steam_factory::get_object_by_name", $GLOBALS[ "STEAM" ]->get_id(), $this->steam_object_path . "/content.html");
		
		$content = $this->get_navigation_html();
		
		$content .= $this->cache->call(array($doc, "get_content"));
		// remove video javascript
		$content = preg_replace("/.*swfobject.js.*/", "", $content);
		$content = preg_replace("/.*video.js.*/", "", $content);
		
		

		
		// replace video div for jw flv
//		$content = preg_replace("/<div class=\"video\">media\\/(.*?)\\.f4v<\\/div>/", "<script src=\"../../scripts/flash/mediaplayer/swfobject.js\" type=\"text/javascript\"></script><div class=\"video\" id=\"video_$1\"></div>
//																							<script type=\"text/javascript\">
//																							    onload = function() {replaceVideo();}
//																							function replaceVideo() {
//  																								var so = new SWFObject('../../scripts/flash/mediaplayer/player.swf','mpl','720','430','9');
//  																								so.addParam('allowfullscreen','true');
//  																								so.addParam('allowscriptaccess','always');
//  																								so.addParam('wmode','opaque');
//  																								so.addVariable('file', '/semester/WS0910/01/units/1008/elearning/einleitung/media/$1.f4v');
//  																								so.write('video_$1');}
//																							</script>", $content);

		if (!ENABLE_HTML5_VIDEO) {
		//replace video div for flowplayer
		
		
		global $portal;
		$portal->add_javascript_src("elearing_chapter", "/styles/" . $STYLE . "/assets/flowplayer/flowplayer-3.2.2.min.js");
		
		$content = preg_replace("/<div class=\"video\">(.*?)\\/(.*?)\\.(.*?)<\\/div>/", "<a  
																							 href=\"".$_SERVER["REQUEST_URI"]."$1/$2.$3\"  
																							 class=\"elearning_video\"  
																							 id=\"video_$2\"> 
																							 <img src=\"".$_SERVER["REQUEST_URI"]."$1/$2.jpg\" />
																						</a> 
																					
																						<!-- this will install flowplayer inside previous A- tag. -->
																						<script type=\"text/javascript\">
																							flowplayer(\"video_$2\", \"/styles/" . $STYLE . "/assets/flowplayer/flowplayer-3.2.2.swf\", {
																							 play:{replayLabel: \"noch einmal abspielen\"},     
																							  clip:  { 
																							        autoPlay: true, 
																							        autoBuffering: false
																							  },
   																						      onFinish:function() { 
																						           this.unload();
																						      }
																							});
																						</script>", $content);
		} else {	
	   //replace video div with HTML5 video and flowplayer fallback
		$content = preg_replace("/<div class=\"video\">(.*?)\\/(.*?)\\.(.*?)<\\/div>/", "<script type=\"text/javascript\" src=\"/styles/" . $STYLE . "/assets/html5video/video.js\"></script>
						  <div class=\"noprint\">																 <link rel=\"stylesheet\" href=\"/styles/" . $STYLE . "/assets/html5video/video-js.css\" type=\"text/css\" media=\"screen\" title=\"Video JS\" charset=\"utf-8\">
							  <div class=\"video-js-box\">
							    <video class=\"video-js\" width=\"720\" height=\"405\" poster=\"".$_SERVER["REQUEST_URI"]."$1/$2.jpg\" controls preload=\"none\">
							      <source src=\"".$_SERVER["REQUEST_URI"]."$1/$2.mp4\" type='video/mp4; codecs=\"avc1.42E01E, mp4a.40.2\"'>
							      <source src=\"".$_SERVER["REQUEST_URI"]."$1/$2.webm\" type='video/webm; codecs=\"vp8, vorbis\"'>
							      <source src=\"".$_SERVER["REQUEST_URI"]."$1/$2.theora.ogv\" type='video/ogg; codecs=\"theora, vorbis\"'>
							      <!-- flash fallback -->
							      <object class=\"vjs-flash-fallback\" width=\"720\" height=\"405\" type=\"application/x-shockwave-flash\"
							        data=\"/styles/" . $STYLE . "/assets/flowplayer/flowplayer-3.2.2.swf\">
							        <param name=\"movie\" value=\"/styles/" . $STYLE . "/assets/flowplayer/flowplayer-3.2.2.swf\" />
							        <param name=\"allowfullscreen\" value=\"true\" />
							        <param name=\"flashvars\" value='config={\"clip\":{\"url\":\"".$_SERVER["REQUEST_URI"]."$1/$2.flv\",\"autoPlay\":false,\"autoBuffering\":false}}' />
							        <!-- image fallback -->
							        <img src=\"".$_SERVER["REQUEST_URI"]."$1/$2.jpg\" width=\"720\" height=\"405\" alt=\"Vorschau Bild\"
							          title=\"Video kann nicht abgespielt werden. Bitte prüfen sie die Systemanforderungen in der Hilfe.\" />
							      </object>
							    </video>
							  </div>
						  </div>
						  <div class=\"printonly\">
							  <img src=\"".$_SERVER["REQUEST_URI"]."$1/$2.jpg\" width=\"720\" height=\"405\" alt=\"Druckbild\"
								          title=\"Druckbild.\" />
						  </div>
						  
						  <script type=\"text/javascript\" charset=\"utf-8\">
    window.onload = function(){
      VideoJS.setup();
    }

  </script>", $content);
		}
		
		
		//replace question
		preg_match_all("/<div class=\"question\">(.*?)\\/(.*?)\\.(.*?)<\\/div>/", $content, $matches);
		
		$q_matches = $matches[2];
		
		if ($q_matches != null) {
			for ($i=0; $i<count($q_matches); $i++) {
				$q = $this->get_question_by_id($q_matches[$i]);
				if ($q != null) {
					if ($i == 0) { //first question
						$q_html = $q->get_question_script_html("../../"); 
						$q_html .= $q->get_question_html();
					} else {
						$q_html = $q->get_question_html();
					}
					$content = preg_replace("/<div class=\"question\">(.*?)\\/".$q_matches[$i]."\\.(.*?)<\\/div>/", $q_html, $content);
				} else {
					error_log("elearning: Frage " . $q_matches[$i] . " in Kapitel " . $this->get_id() . " nicht gefunden.");
				}
			}
		
		
		}
		$content .= $this->get_footer_html();
		
		$content .= "
		<div id=\"overlay\"></div>
		<div id=\"message\">
    		<div id=\"messagetext\"></div>
    		<div id=\"messageCloseButton\" onclick=\"closeMessageWindow()\">[<a href=\"javascript:closeMessageWindow()\">Schließen</a>]</div>
		</div>
		";
		
		$content = "<div class=\"watermark middle right\"><div><img src=\"".PATH_URL."styles/".$STYLE."/images/wasserzeichen.png\" alt=\"Confidential\" /></div></div>" . $content;
		
		return $content;
	}
	
	private function get_navigation_html() {
		global $STYLE;
		$chapterlist = "<ul>";
		$chapters = $this->parent->get_chapters();
		foreach ($chapters as $chapter) {
			$chapterlist .= "<li><a class=\"elearning_menu_item\" href=\"../". $chapter->get_id() ."/\">" . $chapter->get_name() . "</a></li>";
		}
		$chapterlist .= "</ul>";
		$previous_chapter = $this->get_previous_chapter();
		if ($previous_chapter != NULL) {
			$back = "<a class=\"noprint\" href=\"../" . $previous_chapter->get_id() . "/\"><img style=\"display:block;float:left;margin-right:5px;position:relative;top:-1px\" width=\"16\" height=\"16\" src=\"/styles/" . $STYLE ."/images/page_layout/pfeilLinks.png\" alt=\"\"/>Kapitel zurück</a>";
		} else {
			$back = "";
		}
		
		$index = "<div class=\"noprint\"><div id=\"elearning_menu\"><ul><li><a href=\"#\">Kapitelauswahl</a>$chapterlist</li></ul></div></div>";
		$next_chapter = $this->get_next_chapter();
		if ($next_chapter != NULL) {
			$forward = "<a class=\"noprint\" href=\"../" . $next_chapter->get_id() . "/\">Kapitel vor<img style=\"display:block;float:right;margin-left:5px;position:relative;top:-1px\" width=\"16\" height=\"16\" src=\"/styles/" . $STYLE ."/images/page_layout/pfeilRechts.png\" alt=\"\"/></a>";
		} else {
			$forward = "";
		}
		
		
		$html = "<table style=\"width:100%;\"><tr><td style=\"text-align:left;width:50%\">$back</td><td style=\"text-align:right;width:50%\">$forward</td></table><div style=\"position: absolute;left: 365px;top: 50px\">$index</div><a name=\"top\"></a>";
		return $html;
	}
	
	private function get_footer_html() {
		global $STYLE;
		$previous_chapter = $this->get_previous_chapter();
		if ($previous_chapter != NULL) {
			$back = "<a class=\"noprint\" href=\"../" . $previous_chapter->get_id() . "/\"><img style=\"display:block;float:left;margin-right:5px;position:relative;top:-1px\" width=\"16\" height=\"16\" src=\"/styles/" . $STYLE ."/images/page_layout/pfeilLinks.png\" alt=\"\"/>Kapitel zurück</a>";
		} else {
			$back = "";
		}
		$index = "<a class=\"noprint\" href=\"#top\"><img style=\"margin-right:5px\" width=\"16\" height=\"16\" src=\"/styles/" . $STYLE ."/images/page_layout/pfeilOben.png\" alt=\"\"/>zum Kapitelanfang</a>";
		$next_chapter = $this->get_next_chapter();
		if ($next_chapter != NULL) {
			$forward = "<a class=\"noprint\" href=\"../" . $next_chapter->get_id() . "/\">Kapitel vor<img style=\"display:block;float:right;margin-left:5px;position:relative;top:-1px\" width=\"16\" height=\"16\" src=\"/styles/" . $STYLE ."/images/page_layout/pfeilRechts.png\" alt=\"\"/></a>";
		} else {
			$forward = "";
		}
		
		$html = "<table style=\"width:100%\"><tr><td style=\"text-align:left;width:33%\">$back</td><td style=\"text-align:center;width:33%\">$index</td><td style=\"text-align:right;width:33%\">$forward</td></table>";
		
		return $html;
	}
	
	function get_media() {
		$array = $this->get_xmlhelper()->xml_to_array($this->xml->content->array);
		foreach ($array as $item) {
			if ($item["type"]  == "media_files" && $item["direct_url_access"] == true) {
				return $this->create_media_objects($item["id"]);

			}
		}
	}
	
	private function create_media_objects($id) {
		$steam_container = $this->cache->call("steam_factory::get_object_by_name", $GLOBALS[ "STEAM" ]->get_id(), $this->steam_object_path . "/" . $id);
		$steam_objects = $this->cache->call(array($steam_container, "get_inventory"));
		$result = array();
		foreach ($steam_objects as $so) {
			if ($so instanceof steam_document) {
				$steam_document_name = $this->cache->call(array($so, "get_name"));
				$xml_string = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
				$xml_string .= "<media_file>";
				$xml_string .= "<id>" . $steam_document_name ."</id>";
				$xml_string .= "<name>" . $steam_document_name . "</name>";
				$xml_string .= "<description>" . $steam_document_name . "</description>";
				$xml_string .= "</media_file>";
				$xml = simplexml_load_string($xml_string);
				$mf = new elearning_media($this, $so, $xml);
				$result[] = $mf;
			}
		}
		return $result;
	}
	
	function get_media_by_id($id) {
		$m = $this->get_media();
		foreach ($m as $media) {
			if ($media->get_id() == $id) {
				return $media;
			}
		}
	}
	
	function get_previous_chapter() {
		$chapters = $this->parent->get_chapters();
		$i=0;
		foreach ($chapters as $chapter) {
			if ($chapter->get_id() == $this->get_id()) {
				break;
			}
			$i++;
		}
		if ($i > 0) {
			return $chapters[$i-1];
		}
		return NULL;
	}
	
	function get_next_chapter() {
		$chapters = $this->parent->get_chapters();
		$i=0;
		foreach ($chapters as $chapter) {
			if ($chapter->get_id() == $this->get_id()) {
				break;
			}
			$i++;
		}
		if ($i < count($chapters)-1) {
			return $chapters[$i+1];
		}
		return NULL;
	}
	
	function get_questions($user = null) {
		isset($user) or $user = lms_steam::get_current_user();
		$array = $this->get_xmlhelper()->xml_to_array($this->xml->content->array);
		foreach ($array as $item) {
			if ($item["type"]  == "questions_files") {
				return $this->create_question_objects($item["id"], $user);

			}
		}
	}
	
	private function create_question_objects($id, $user = null) {
		isset($user) or $user = lms_steam::get_current_user();
		
		$steam_container = $this->cache->call("steam_factory::get_object_by_name", $GLOBALS[ "STEAM" ]->get_id(), $this->steam_object_path . "/" . $id);
		$steam_objects = $this->cache->call(array($steam_container, "get_inventory"));
		$result = array();
		foreach ($steam_objects as $so) {
			$result[] = elearning_question::create_question($this, $so, $user);
		}
		return $result;
	}
	
	function get_question_by_id($id, $user = null) {
		isset($user) or $user = lms_steam::get_current_user();
		$q = $this->get_questions($user);
		foreach ($q as $question) {
			if ($question != null && $question->get_id() == $id) {
				return $question;
			}
		}
		return null;
	}
	
	function get_chapter_count() {
		$chapters = $this->parent->get_chapters();
		$i=0;
		foreach ($chapters as $chapter) {
			if ($chapter->get_id() == $this->get_id()) {
				return $i;
			}
			$i++;
		}
	
	}
	
}
?>