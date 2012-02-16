<?php
namespace Wave\Model;
class XmlHelper {
	
	public static function xml_to_array($xml) {
		return self::convert_xml($xml);
	}
	
	private static function convert_xml($xml) {
		if ($xml->getName() == "array") {
			$result = array();
			$items = $xml->children();
			foreach ($items as $item) {
				$result[] = self::convert_xml($item);
			}
			return $result;
		} else if ($xml->getName() == "dict") {
			$result = array();
			$keys = $xml->key;
			$i = 0;
			foreach ($keys as $key) {
				$children = $xml->children();
				$value = $children[$i*2+1];
				$result[(string)$key] = self::convert_xml($value);
				$i++;
			}
			return $result;
		} else if ($xml->getName() == "string") {
			return (string) $xml;
		} else if ($xml->getName() == "boolean") {
			return (boolean) $xml;
		}
	}
	
}