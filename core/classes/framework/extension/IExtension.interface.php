<?php
interface IExtension {
	public function init();
	
	public function getId();
	public function getName();
	public function getDesciption();
	public function getIcon();
	public function getVersion();
	public function getChangelog();
	public function getReadme();
	public function getInstall();
	public function getLicense();
	public function getAuthors();
	public function getMaintainer();
	
	public function getUrlNamespaces();
	public function getInfoHtml();
	public function getDepending();
}
?>