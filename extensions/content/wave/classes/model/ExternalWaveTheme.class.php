<?php
namespace Wave\Model;
class ExternalWaveTheme extends WaveTheme {
	
	private $externalThemeBasePath;
	
	public function __construct($externalThemeBasePath, $myEngine) {
		$this->setEngine($myEngine);
		$this->externalThemeBasePath = $externalThemeBasePath;
		$this->setThemeName(str_replace(".rwtheme", "", basename($externalThemeBasePath)));
		$this->setThemeBaseUrl($this->getEngine()->getSideUrl() . "themes/" . $this->getThemeName() . "/");
		$this->loadPlistXml();
		$this->loadHtmlTemplate();
	}
	
	private function loadPlistXml() {
		$plistXmlFilePath = $this->externalThemeBasePath . "Contents/Theme.plist";
		$plistXmlFile = fopen($plistXmlFilePath, 'r');
		$plistXmlContent = fread($plistXmlFile, filesize($plistXmlFilePath));
		fclose($plistXmlFile);
		$this->setPlistXml(simplexml_load_string($plistXmlContent));
	}
	
	private function loadHtmlTemplate() {
		//$htmlTemplateName = $this->plistXML->RWTemplateHTML;
		$htmlTemplateName = "index.html";
		$htmlTemplateFilePath = $this->externalThemeBasePath . "Contents/" . $htmlTemplateName;
		$htmlTemplateFile = fopen($htmlTemplateFilePath, 'r');
		$plistXmlContent = fread($htmlTemplateFile, filesize($htmlTemplateFilePath));
		fclose($htmlTemplateFile);
		$this->setHtmlTemplate($plistXmlContent);
	}
	
	public function getDownload($downloadPathArray) {
		$relateThemePath = implode("/", $downloadPathArray);
		$absoluteThemePath = $this->externalThemeBasePath . "Contents/" .  $relateThemePath;
		return new ExternalWaveDownload($absoluteThemePath);
	}
	
	
}
?>