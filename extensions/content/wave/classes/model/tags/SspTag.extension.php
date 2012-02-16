<?php
defined("SSP_ENABLED") or define("SSP_ENABLED", true);
define("SSP_DEBUG", false);
defined("SSP_API_URL") or define("SSP_API_URL", "bilder.naturgartenbuch.de");
defined("SSP_API_KEY") or define("SSP_API_KEY", "local-d38ab8b85536305862721f7b0aac7fa5");
class SspTag extends AbstractExtension implements ITagExtension{
	
	public function getName() {
		return "SspTag";
	}
	
	public function getDesciption() {
		return "Extension for wave-cms.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function processContent($html, $page) {
		if (SSP_ENABLED) {
			$director = new Director(SSP_API_KEY, SSP_API_URL);
			$director->format->add(array('name' => 'full', 'width' => '800', 'height' => '500', 'crop' => 0, 'quality' => 75, 'sharpening' => 1));
			
			if (SSP_DEBUG) {
				$albums = $director->album->all();
				foreach($albums as $album) {
					echo $album->name . " => " . $album->id . "<br>";
				}
				die;
			}
			
			$pattern = "/<ssp.*?\\/>/";
			if (preg_match_all($pattern, $html, $matches) > -1) {
				foreach ($matches[0] as $match) {
					$xml_ssp = simplexml_load_string($match);
					if (!$xml_ssp instanceof SimpleXMLElement) {
						$html = str_replace($match, "Defekte Daten im ssp Tag.");
						continue;
					}
					if (isset($xml_ssp["type"]) && $xml_ssp["type"] == "single") {
						$maxheight = 444;
						$maxwidth = 444;
					} else if (isset($xml_ssp["type"]) && $xml_ssp["type"] == "triple") {
						$maxheight = 255;
						$maxwidth = 255;
					} else {
						if (isset($xml_ssp["maxheight"])) {
							$maxheight = (string)$xml_ssp["maxheight"];
						} else {
							$maxheight = 250;
						}
						if (isset($xml_ssp["maxwidth"])) {
							$maxwidth = (string)$xml_ssp["maxwidth"];
						} else {
							$maxwidth = 250;
						}
					}
					
					if (isset($xml_ssp["style"])) {
						$style = "style=\"" . $xml_ssp["style"] .  "\"";
					} else {
						$style = "";
					}
					
					if (isset($xml_ssp["rel"])) {
						$rel = "rel=\"" . $xml_ssp["rel"] .  "\"";
					} else {
						$rel = "rel=\"content\"";
					}
					
					$thumb_name = 'thumb'.$maxwidth.'x'.$maxheight;
					$director->format->add(array('name' => $thumb_name, 'width' => $maxwidth, 'height' => $maxheight, 'crop' => 0, 'quality' => 75, 'sharpening' => 1));
					
					$album = $director->album->get($page->get_attribute("WAVEPAGE_MODULE_SSP_ALBUM_NO"));
					$contents = $album->contents[0];
					$img = $contents->content[(int)$xml_ssp["img_no"] - 1];
					if ($img == null) {
						$html = str_replace($match, "Das Bild " . $xml_ssp["img_no"] . " im Album " . $xml_ssp["album_no"] . " gibt es nicht.");
						continue;
					}
					if (isset($xml_ssp["nocaption"])) {
						$html = str_replace($match, "<a " . $style . " href=\"".$img->full->url."\" ". $rel ." title=\"".$img->title."\"><img title=\"".strip_tags($img->title)."\" src=\"" . $img->$thumb_name->url . "\" /></a>", $html);
					} else {
						$html = str_replace($match, "<a " . $style . " href=\"".$img->full->url."\" ". $rel ." title=\"".$img->title."\"><img title=\"".strip_tags($img->title)."\" src=\"" . $img->$thumb_name->url . "\" /></a><div style=\"height=100%\" class=\"caption\">".$img->title." [<a href=\"/quellen#".$img->tags."\">".$img->tags."</a>]</div>", $html);
					}
				}
			}
		}
		return $html;
	}
	
}
?>