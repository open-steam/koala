<?php
class Person {
	private $firstname;
	private $lastname;
	private $email;
	
	function __construct($firstname, $lastname, $email) {
		$this->firstname = $firstname;
		$this->lastname = $lastname;
		$this->email = $email;
	}
	
	public function getFirstname() {
		return $this->firstname;
	}
	
	public function getLastname() {
		return $this->lastname;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
}