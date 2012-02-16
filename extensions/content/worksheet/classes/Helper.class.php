<?php
	namespace Worksheet;

	class Helper
	{


		/*
		*  this class contains a set of static helper functions
		*/


		public static function htmlentitiesDeep($str)
		{
			
			if (is_array($str)) {
				
				return array_map("\Worksheet\Helper::htmlentitiesDeep", $str);
				
			} else {
				return htmlspecialchars($str, ENT_COMPAT, "UTF-8");
			}
			
		}
		
		
		public static function addSlashesDeep($str)
		{
			
			if (is_array($str)) {
				
				return array_map("\Worksheet\Helper::addSlashesDeep", $str);
				
			} else {
				return addslashes($str);
			}
			
		}


		public static function convertFromXMLBool($xmlint)
		{
			$xmlint = (String) $xmlint;
			$xmlint = (int) $xmlint;
			
			if ($xmlint == 1) {
				return true;
			} else {
				return false;
			}
			
		}
		
		public static function convertToXMLBool($bool)
		{
			if ($bool) {
				return 1;
			} else {
				return 0;
			}
		}
		

		
		
		public static function textEncode($text)
		{	
			$text = trim($text);
			return urlencode($text);
			
		}
		
		public static function textDecode($text)
		{

			$text = urldecode($text);
			return $text;
		
		}
		
		
		
		
		public static function error($error, $solution)
		{
			$portal = \lms_portal::get_instance();
			$portal->set_problem_description($error, $solution);
		}
		

		
		
		public static function print_r($var)
		{
			echo '<pre>'.print_r($var, true).'</pre>';
		}
		
		
		/*
		*  convert a simpleXML structure into an array
		*/
		
		static function xmlToArray($arrObjData, $arrSkipIndices = array())
		{
		    $arrData = array();

		    // if input is object, convert into array
		    if (is_object($arrObjData)) {
		        $arrObjData = get_object_vars($arrObjData);
		    }

		    if (is_array($arrObjData)) {
		
		        foreach ($arrObjData as $index => $value) {
		
		            if (is_object($value) || is_array($value)) {
		
		                $value = self::xmlToArray($value, $arrSkipIndices); // recursive call
		
						if (is_array($value) AND count($value) == 0) {
							$value = false;
						}
		
					}
					
		            if (in_array($index, $arrSkipIndices)) {
		                continue;
		            }
		
					if (!is_array($value)) {
						$value = self::textDecode($value);
					}
		
		            $arrData[$index] = $value;
		
		        }
		
		    }

		    return $arrData;
		}
		

		
		/*
		*  convert a (maybe deep) arrray into an xml-structure
		*/
		
		public static function arrayToXml($array, $parentSxmlNode)
		{ 
//			var_dump($array); die();
				foreach ($array as $key=>$value) {
					
					if (is_array($value)) {
						
						$keys = array_keys($value);
						
						if (!isset($keys[0])) {
							$keys[0] = 0;
						}
						
						$checkKey = (int) $keys[0];
						
						if ($keys[0] === $checkKey) {
						//skip current array and add childs to parent node with the name of current array, if keys of subarray are numbers + recursive call with new node

							/* values are arrays */
							if (is_array($value[0])) {
						
								foreach ($value as $k=>$v) {

									$newnode = $parentSxmlNode->addChild($key);
									self::arrayToXml($v, $newnode);
								
								}
							
							/* values are not arrays (add child-nodes named after singular of keyname) */
							} else {
								
								$newnode = $parentSxmlNode->addChild($key);

								if (substr($key, -1) == "s") {
									$keySingular = substr($key, 0, -1);
								} else {
									$keySingular = $key;
								}
								
								foreach ($value as $k=>$v) {

									$newnode->addChild($keySingular, $v);
								
								}
								
							}
							
						} else {
						//add child to parent node with the name of current array + recursive call with new node
						
							$newnode = $parentSxmlNode->addChild($key);
							self::arrayToXml($value, $newnode);
						
						}
						
					} else {
						//add child to parent node with the name of current array and insert encoded value
						$parentSxmlNode->addChild($key, self::textEncode($value));
					}
				
				}
			
		}
		
		
	  	/** Prettifies an XML string into a human-readable and indented work of art 
		  *  @param string $xml The XML as a string 
		  *  @param boolean $html_output True if the output should be escaped (for use in HTML) 
		  */  
		 public static function xmlpp($xml, $html_output=false) {  
		     $xml_obj = new \SimpleXMLElement($xml);  
		     $level = 4;  
		     $indent = 0; // current indentation level  
		     $pretty = array();  

		     // get an array containing each XML element  
		     $xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));  

		     // shift off opening XML tag if present  
		     if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0])) {  
		       $pretty[] = array_shift($xml);  
		     }  

		     foreach ($xml as $el) {  
		       if (preg_match('/^<([\w])+[^>\/]*>$/U', $el)) {  
		           // opening tag, increase indent  
		           $pretty[] = str_repeat(' ', $indent) . $el;  
		           $indent += $level;  
		       } else {  
		         if (preg_match('/^<\/.+>$/', $el)) {              
		           $indent -= $level;  // closing tag, decrease indent  
		         }  
		         if ($indent < 0) {  
		           $indent += $level;  
		         }  
		         $pretty[] = str_repeat(' ', $indent) . $el;  
		       }  
		     }     
		     $xml = implode("\n", $pretty);     
		     return ($html_output) ? htmlentities($xml) : $xml;  
		 }
		

		

	}
	

?>