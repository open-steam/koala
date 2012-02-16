<?php
/*
 * Diese Klasse entkoppelt die Anwendung von dem HTTP-Protokoll. 
 */
class HttpRequest implements IRequest{

	// Speicherung aller Parameter, die per HTTP-Protokoll übermittelt wurden.
	private $parameters;
	
	/*
	 * Konstruktor
	 */
	public function __construct(){
		$this->parameters = $_REQUEST;
	}
	
	/*
	 * Prüft, ob ein bestimmter Parameter übergeben wurde.
	 */
	public function issetParameter($name){
		return isset($this->parameters[$name]);
	}
	
	/*
	 * Liefert den Wert eines bestimmten Parameters.
	 */
	public function getParameter($name){
		if($this->issetParameter($name)) return $this->parameters[$name];
		return null;
	}
	
	/*
	 * Liefert eine Liste mit allen Namen der übergebenen Parameter.
	 */
	public function getParameterNames(){
		return array_keys($this->parameters);
	}
	
	/*
	 * Gibt einen bestimmten Header zurück.
	 */
	public function getHeader($name){
		$name = 'HTTP_'.strtoupper(str_replace('-', '_', $name));
		if(isset($_SERVER[$name])) return $_SERVER[$name];
		return null;
	}
}
?>