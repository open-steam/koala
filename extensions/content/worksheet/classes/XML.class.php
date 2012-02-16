<?php

	namespace Worksheet;
	
	/**
	* XML dataclass
    * -------------
	*
	* by Tobias Kempkensteffen <tobias.kempkensteffen@gmail.com>
	*
	*/
	class XML
	{
		
		protected $xml;
		
		function __construct($xmlString=false)
		{
			if ($xmlString !== false) {
				$this->xml = simplexml_load_string($xmlString);
			} else {
				$this->xml = simplexml_load_string("<data></data>");
			}
		}
		
		public function addString($name, $value='')
		{
			$this->xml->addChild($name, Helper::textEncode($value));
		}
		
		public function getString($name)
		{
			return (String) Helper::textDecode($this->xml->$name);
		}
		
		public function getXML()
		{
			return Helper::xmlpp($this->xml->asXML());
		}
		
		public function addArray($name, $value)
		{
			$parent = $this->xml->addChild($name, "");
			Helper::arrayToXML($value, $parent);

		}
		
		public function getArray($name)
		{
			return Helper::xmlToArray($this->xml->$name);;
		}
		
	}
	
?>