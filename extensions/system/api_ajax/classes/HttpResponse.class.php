<?php
/*
 * Diese Klasse entkoppelt die Anwendung vom HTTP-Protokoll
 */
class HttpResponse implements IResponse{

	// HTTP-Status
	private $status = '200 OK';
	
	// Header-Daten
	private $headers = array();
	
	// Beinhaltet den HTML- und PHP-Code, der zum Client geschickt werden soll.
	private $body = null;
	
	/*
	 * Ändern des HTTP-Status.
	 */
	public function setStatus($status){
		$this->status = $status;
	}
	
	/*
	 * Hinzufügen eines Headers.
	 */
	public function addHeader($name, $value){
		$this->headers[$name] = $value;
	}
	
	/*
	 * Speichert die an den Client zu sendenen Daten in der $body-Variablen zwischen.
	 */
	public function write($data){
		$this->body .= $data;
	}	
	
	/*
	 * Sendet letztendlich die Daten (HTML-Ausgabe) an den Client zurück.
	 */
	public function flush(){
		header("HTTP/1.0 {$this->status}");
		foreach($this->headers as $name => $value) header("{$name}: {$value}");
		print $this->body;
		$this->headers = array();
		$this->body = null;	
	}
}
?>