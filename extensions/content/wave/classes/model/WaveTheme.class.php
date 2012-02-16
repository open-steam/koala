<?php
namespace Wave\Model;
/**
 * Wheel model object for a wave side. It's a object with following attributes:
 * OBJ_TYPE: "container_wavetheme"
 * WAVETHEME_TYPE: "<type name string>"
 * @author Dominik Niehus <nicke@uni-paderborn.de>
 *
 */
abstract class WaveTheme extends WaveObject{
	private $myEngine;
	private $htmlTemplate;
	private $plistXml;
	private $themeBaseUrl;
	private $themeName;
	
	private $header = "";
	private $title = "";
	private $style_variations = "";
	private $user_styles = "";
	private $user_javascript = "";
	private $plugin_header = "";
	private $user_header = "";
	private $toolbar = "";
	private $logo = "";
	private $site_title = "";
	private $site_slogan = "";
	private $sidebar_title = "";
	private $sidebar = "";
	private $plugin_sidebar = "";
	private $breadcrumb = "";
	private $content = "";
	private $footer = "";
	private $prev_chapter = "";
	private $chapter_menu = "";
	private $next_chapter = "";
	
	public function setEngine($engine) {
		$this->myEngine = $engine;
	}
	
	public function getEngine() {
		return $this->myEngine;
	}
	
	public function setHtmlTemplate($htmlTemplate) {
		$this->htmlTemplate = $htmlTemplate;
		$this->convertRW2IT();
	}
	
	public function getHtmlTemplate() {
		return $this->htmlTemplate;
	}
	
	public function setPlistXml($plistXml) {
		$this->plistXml = $plistXml;
	}
	
	public function getPlistXml() {
		return $this->plistXml;
	}
	
	public function setThemeBaseUrl($themeBaseUrl) {
		$this->themeBaseUrl = $themeBaseUrl;
	}
	
	public function getThemeBaseUrl() {
		return $this->themeBaseUrl;
	}
	
	public function setThemeName($themeName) {
		$this->themeName = $themeName;
	}
	
	public function getThemeName() {
		return $this->themeName;
	}
	
	abstract function getDownload($downloadPathArray);
	
	private function convertRW2IT() {
		$this->htmlTemplate = preg_replace("/%pathto\((.*)\)%/U", $this->getThemeBaseUrl() . "$1", $this->htmlTemplate);
		
		$this->htmlTemplate = str_replace("%header%", "{HEADER}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%title%", "{TITLE}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%style_variations%", "{STYLE_VARIATIONS}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%user_styles%", "{USER_STYLES}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%user_javascript%", "{USER_JAVASCRIPT}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%plugin_header%", "{PLUGIN_HEADER}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%user_header%", "{USER_HEADER}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%toolbar%", "{TOOLBAR}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%logo%", "{LOGO}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%site_title%", "{SITE_TITLE}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%site_slogan%", "{SITE_SLOGAN}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%sidebar_title%", "{SIDEBAR_TITLE}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%sidebar%", "{SIDEBAR}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%plugin_sidebar%", "{PLUGIN_SIDEBAR}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%breadcrumb%", "{BREADCRUMB}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%content%", "{CONTENT}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%footer%", "{FOOTER}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%prev_chapter%", "{PREV_CHAPTER}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%chapter_menu%", "{CHAPTER_MENU}", $this->htmlTemplate);
		$this->htmlTemplate = str_replace("%next_chapter%", "{NEXT_CHAPTER}", $this->htmlTemplate);
	}
	
	public function getHtml() {
		$content = new \HTML_TEMPLATE_IT();
		$content->setTemplate($this->htmlTemplate);
		$content->setVariable("HEADER", $this->header);
		$content->setVariable("TITLE", $this->title);
		$content->setVariable("STYLE_VARIATIONS", $this->style_variations);
		$content->setVariable("USER_STYLES", $this->user_styles);
		$content->setVariable("USER_JAVASCRIPT", $this->user_javascript);
		$content->setVariable("PLUGIN_HEADER", $this->plugin_header);
		$content->setVariable("USER_HEADER", $this->user_header);
		$content->setVariable("TOOLBAR", $this->toolbar);
		$content->setVariable("LOGO", $this->logo);
		$content->setVariable("SITE_TITLE", $this->site_title);
		$content->setVariable("SITE_SLOGAN", $this->site_slogan);
		$content->setVariable("SIDEBAR_TITLE", $this->sidebar_title);
		$content->setVariable("SIDEBAR", $this->sidebar);
		$content->setVariable("PLUGIN_SIDEBAR", $this->plugin_sidebar);
		$content->setVariable("BREADCRUMB", $this->breadcrumb);
		$content->setVariable("CONTENT", $this->content);
		$content->setVariable("FOOTER", $this->footer);
		$content->setVariable("PREV_CHAPTER", $this->prev_chapter);
		$content->setVariable("CHAPTER_MENU", $this->chapter_menu);
		$content->setVariable("NEXT_CHAPTER", $this->next_chapter);
		return $content->get();
	}
	
	public function setHeader($header) {
		$this->header = $header;
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function setStyleVariations($styleVariations) {
		$this->style_variations = $styleVariations;
	}
	
	public function setUserStyles($userStyles) {
		$this->user_styles = $userStyles;
	}
	
	public function setUserJavascript($userJavascript) {
		$this->user_javascript = $userJavascript;
	}
	
	public function setPluginHeader($pluginHeader) {
		$this->plugin_header = $pluginHeader;
	}
	
	public function setUserHeader($userHeader) {
		$this->user_header = $userHeader;
	}
	
	public function setToolbar($toolbar) {
		$this->toolbar = $toolbar;
	}
	
	public function setLogo($logo) {
		$this->logo = $logo;
	}
	
	public function setSiteTitle($siteTitle) {
		$this->site_title = $siteTitle;
	}
	
	public function setSiteSlogan($siteSlogan) {
		$this->site_slogan = $siteSlogan;
	}
	
	public function setSidebarTitle($sidebarTitle) {
		$this->sidebar_title = $sidebarTitle;
	}
	
	public function setSidebar($sidebar) {
		$this->sidebar = $sidebar;
	}
	
	public function setPluginSidebar($pluginSidebar) {
		$this->plugin_sidebar = $pluginSidebar;
	}
	
	public function setBreadcrumb($breadcrumb) {
		$this->breadcrumb = $breadcrumb;
	}
	
	public function setContent($content) {
		$this->content = $content;
	}
	
	public function setFooter($footer) {
		$this->footer = $footer;
	}
	
	public function setPrevChapter($prevChapter) {
		$this->prev_chapter = $prevChapter;
	}
	
	public function setChapterMenu($chapterMenu) {
		$this->chapter_menu = $chapterMenu;
	}
	
	public function setNextChapter($nextChapter) {
		$this->next_chapter = $nextChapter;
	}
}