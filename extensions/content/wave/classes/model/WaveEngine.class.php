<?php
namespace Wave\Model;
class WaveEngine {
	private $sideId;
	private $internalPath;
	private $siteUrl;
	
	public function __construct($sideId, $internalPath, $sideUrl) {
		$this->sideId = $sideId;
		$this->internalPath = $internalPath;
		$this->siteUrl = $sideUrl;
	}
	
	function cleanArray(&$arr) {
	  foreach($arr as $k => $v)
	    if (is_array($v))
	      cleanArray($arr[$k]);
	    else
	      $arr[$k] = stripslashes($v);
	}
	
	public function getSide() {
		return new WaveSide($this->sideId, $this);
	}
	
	public function getSideUrl() {
		return $this->siteUrl;
	}
	
	public function getCurrentObject() {
		if (isset($this->internalPath[0]) && ($this->internalPath[0] === "themes")) {
			$theme = $this->getTheme(urldecode($this->internalPath[1]));
			$downloadPath = $this->internalPath;
			array_shift($downloadPath);
			array_shift($downloadPath);
			$download = $theme->getDownload($downloadPath);
			return $download;
		}
		$internalPathString = implode("/", $this->internalPath);
		$currentObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $this->getSide()->get_path() . "/" . $internalPathString);
		if ($currentObject instanceof \steam_container) {
			return WavePage::getInstanceFor($currentObject->get_id(), $this->getSide());
		}
	}
	
	public function getTheme($themeString) {
		$externalThemeBasePath = \Wave::getInstance()->getExtensionPath() . "themes/" . $themeString . ".rwtheme/";
		if (file_exists($externalThemeBasePath)) {
			return new ExternalWaveTheme($externalThemeBasePath, $this);
		} else {
			die("Theme " . $themeString . " not found!");
		}
	}
	
	public function getFullMenuAsArray($page = null) {
		if ($page === null) { 			 		// first loop
			return $this->getFullMenuAsArray(WavePage::getInstanceFor($this->getSide()->get_id(), $this->getSide())->getSubPages(false));
		} else if ($page instanceof WavePage) {	// second loop
			$result = array();
			$result[$page->get_id()] = $this->getFullMenuAsArray($page->getSubPages(false));
			return $result;
		} else if (is_array($page)) {			// the rest		
			$result = array();
			foreach ($page as $aPage) {
				$result[$aPage->get_id()] = $this->getFullMenuAsArray($aPage->getSubPages(false));
			}
			return $result;
		}
	}
	
	public function getFullMenuAsHtml($array = null) {
		$currentPage = $this->getCurrentObject();
		if ($array === null) {
			return $this->getFullMenuAsHtml($this->getFullMenuAsArray());
		} else if (empty($array)) {
			return "";
		} else {
			$html = "<ul>";
			foreach ($array as $pageId => $subPages) {
				if ($pageId === $currentPage->get_id()) {
					$current = " class=\"current\" ";
				} else {
					$current = "";
				}
				$page = WavePage::getInstanceFor($pageId, $this->getSide());
				$html .= "<li{$current}><a href=\"{$page->getPageUrl()}\">{$page->getPageName()}</a>" . $this->getFullMenuAsHtml($subPages) . "</li>";  
			}
			$html .= "</ul>";
			return $html;
		}
	}
	
	function determineStyle() {
			// determine style
	/*	if ( isset($_GET["style"])) {
		     define( "STYLE_PATH", STYLE_ROOT . $_GET[ "style" ] . "/");
		     define( "STYLE_NAME", $_GET[ "style" ]);
		     $_SESSION[STEAMWEB_PATH_INTERN][ "style" ] = STYLE_NAME;
		}
		else {
		//  if (isset($_SESSION[STEAMWEB_PATH_INTERN][ "style" ])) {
		//       define( "STYLE_NAME", $_SESSION[STEAMWEB_PATH_INTERN][ "style" ] );
		//       define( "STYLE_PATH", STYLE_ROOT . STYLE_NAME . "/");
		//  }
		//  else {
		  	//TODO: rewrite ****
		  	$steam_connector = new steam_connector(
		            STEAM_SERVER,
		            STEAM_PORT,
		            STEAM_GUEST_LOGIN,
		            STEAM_GUEST_PASSWORD
		            );
		   
		    $akt_page_object = SiteManager::getCurrentPageObject();
		    
		    if (is_object($akt_page_object) && $akt_page_object instanceof steam_container && $akt_page_object->get_attribute("STEAMWEB_STYLENAME") !== 0) {
		    	define( "STYLE_NAME", $akt_page_object->get_attribute("STEAMWEB_STYLENAME") );
		    	define( "STYLE_PATH", STYLE_ROOT . $akt_page_object->get_attribute("STEAMWEB_STYLENAME") . "/");
		    } else {
		  	// *****
		  		//$akt_page_object->set_attribute("STEAMWEB_STYLENAME", "naturgarten-start");
		    	define( "STYLE_NAME", STANDARDSTYLE );
		    	define( "STYLE_PATH", STYLE_ROOT . STANDARDSTYLE . "/");
		    }
		//  }
		}*/
	}
	
	public function run($path = null) {
		
		
		$html_footer_code = "";
		$html_navbar_code = "";
		$html_navbar_full_code = "";
		$html_sidebar_code = "";
		$html_integratedfeed_code = "";
		$html_breadcrumb_code = "";
		$html_search_code = "";
		$html_header_code  = "";
		$html_content_code = "";
		$html_login_info_code = "";
		$html_htmlheader_code = "";
		$html_navbar_prev_url = "";
		$html_navbar_next_url = "";
		$html_navbar_main_parent_url = "";
		$html_navbar_main_parent_title = "";
		$html_navbar_main_parent_number = "";
        
		$wave_style = $this->determineStyle();

    
	    if (!isset($_SESSION[WAVE_PATH_INTERN]["web_root"])) {
	      $_SESSION[WAVE_PATH_INTERN]["web_root"] = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), WAVE_PATH_INTERN  );
	    }
    
    	$web_root = $_SESSION[WAVE_PATH_INTERN]["web_root"];
 
	    if ($path) {
	      $akt_page = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), WAVE_PATH_INTERN . $path);
	    }
	    else {
	      $akt_page = $web_root;
	      $path = "/";
	    }
	    
		if (!$akt_page || !($akt_page instanceof \steam_container)) {
			unset($_SESSION[WAVE_PATH_INTERN]["web_root"]);
	      	throw new \Exception("WAVE Extension not configured.");
	    }
	    
	    return $akt_page->get_name();
    
 /*   if ($akt_page !== 0 && $akt_page instanceof steam_container) {
    	//TODO: move to control helper
	    $control_file = $akt_page->get_object_by_name("control.xml");
	    if ($control_file !== 0 && $control_file instanceof steam_document) {
	    	$xml = simplexml_load_string($control_file->get_content());
	    	if ($xml->steamweb_type == "REDIRECT") {
	    		$redirectControl = new RedirectControl();
	    		$redirectControl->getHtml($xml);
	    	}
	    }
    }
    
    
    if ( isset($_GET["mode"]) && $_GET["mode"] == "download") {
      $akt_page = steam_factory::get_object( $steam_connector, (int)$_GET[ "id" ], CLASS_DOCUMENT);
      $path = "/download_" . $_GET[ "id" ]; // use special path for id calls
    }
    if ($akt_page === 0) {
      $file_found = FALSE;
      $akt_page = $web_root;
    }
    else {
      $file_found = TRUE;
    }

    $access_read = $akt_page->check_access_read($steam_connector->get_login_user());
    
    if ( isset($_GET["output"]) && $_GET["output"] == "rss" ) {
      include( MODULE_PATH . "common/rss.control.php");
    }
    
    if ( ($akt_page->get_type() & CLASS_DOCUMENT) === CLASS_DOCUMENT ) {
      $is_document = TRUE;
    }
    else $is_document = FALSE;
    
    if ($access_read) {
      $mimetype= $akt_page->get_attribute(DOC_MIME_TYPE);
      $textpos = strstr((string)$mimetype, "text");
    }
    
    $do_login = (((isset($_GET["page"]) && $_GET["page"] == "login") || !$access_read)?TRUE:FALSE);

    if (!$do_login && $file_found && $is_document && ( ($mimetype !== 0 && (strlen($textpos) == 0)) || $path == "robots.txt" || $mimetype == "text/css") && $mimetype != "source/pike" ) {
    $download_object = $akt_page;
      include( MODULE_PATH . "helper/get.php");
    }
    else {
      $html_portalseite = new IntegratedTemplate();
      $index_template = TEMPLATE_PATH . "index." . STYLE_NAME  .".template.html";
      if (!file_exists($index_template)) {
      	$steam_doc = steam_factory::path_to_object( $steam_connector, STEAMWEB_PATH_INTERN . "/style/" . STYLE_NAME . "/index.template.html");
      	if ($steam_doc === 0) {
      		echo "Can't find index.template.html.";
      		die;
      	} else {
      		$myFile = TEMP_PATH . "index." . STYLE_NAME  .".template.html";
			$fh = fopen($myFile, 'w') or die("can't open temp file");
			fwrite($fh, $steam_doc->get_content());
			fclose($fh);
      		$index_template = $myFile;
      	}
      }
      $html_portalseite->loadTemplateFile( $index_template );
       include( MODULE_PATH . "common/global_infos.control.php");
      try {
        if ($do_login)         include( MODULE_PATH . "common/login.control.php");
        else if (!$file_found) include( MODULE_PATH . "common/notfound.control.php");
        else                   include( MODULE_PATH . "common/content.control.php");
      } catch(Exception $ex) {
        $html_content_code = "<h1>Error</h1>An error occured during website creation.";
      }
      
      if (!$ajaxcall && $access_read) {
        include( MODULE_PATH . "common/breadcrumb.control.php");
        include( MODULE_PATH . 'common/search.control.php' );
        include( MODULE_PATH . "common/login_info.control.php");
        include( MODULE_PATH . "common/navbar.control.php");
        include( MODULE_PATH . "common/footer.control.php");
        if (!ENABLE_UPB_MODE) include( MODULE_PATH . "common/sidebar.control.php");
        include( MODULE_PATH . "common/integratedfeed.control.php");
        if ( !ENABLE_UPB_MODE && (!(isset($_GET["mode"]) && $_GET["mode"]=="edit") || $do_login) )
          include( MODULE_PATH . "common/header.control.php");
      }
      
      if (isset($_SESSION[STEAMWEB_PATH_INTERN][ "userdata" ][ "OBJ_NAME" ]) &&  $_SESSION[STEAMWEB_PATH_INTERN][ "userdata" ][ "OBJ_NAME" ] != "guest" && $_SESSION[STEAMWEB_PATH_INTERN][ "admin" ]) {
        $html_footer_code .= ", Serverzugriffe: " . ($steam_connector->get_request_count() - $requestcount_old);
        if (function_exists('memory_get_peak_usage')) {
          $html_footer_code .= ", Speicherverbrauch: " . number_format(memory_get_peak_usage());
        }
      }
      
      if (ENABLE_UPB_MODE && $upb_title_html !==0) 
        $site_title = $upb_title_html;
      else 
        $site_title = strip_tags($_SESSION[STEAMWEB_PATH_INTERN][ "SITE_TITLE" ]);
     
      // variable page is set and valid
      $html_portalseite->setVariable(array(
            "STYLE_PATH"                => STYLE_PATH,
            "SITE_TITLE"                => $site_title,
            "SITE_HEADER"               => $_SESSION[STEAMWEB_PATH_INTERN][ "SITE_TITLE" ],
            "SITE_SLOGAN"               => $_SESSION[STEAMWEB_PATH_INTERN][ "SITE_SLOGAN" ],
            "FOOTER"                    => $html_footer_code,      
            "NAVBAR"                    => $html_navbar_code,
      		"NAVBAR_FULL"				=> $html_navbar_full_code,
            "NAVBAR_PREV_URL"			=> $html_navbar_prev_url,
      		"NAVBAR_NEXT_URL"			=> $html_navbar_next_url,
      		"NAVBAR_MAIN_PARENT_URL"	=> $html_navbar_main_parent_url,
      		"NAVBAR_MAIN_PARENT_TITLE"	=> $html_navbar_main_parent_title,
      		"NAVBAR_MAIN_PARENT_NUMBER"	=> $html_navbar_main_parent_number,
      		"PAGE_TITLE"				=> $html_pagetitle,
      		"PAGE_NUMBER"				=> $html_pagenumber,
            "SIDEBAR"                   => $html_sidebar_code,
            "PLUGIN_SIDEBAR"            => $html_integratedfeed_code,
            "BREADCRUMB"                => $html_breadcrumb_code,
            'SEARCH'                    => $html_search_code,
            "CONTENT"                   => $html_header_code . $html_content_code,
            "LOGO"                      => "<img src=" . LOGO_PATH. " />",
            "LOGIN"                     => $html_login_info_code,
            "HEADER"                    => $html_htmlheader_code,
            "DUMMY"                     => ""
            ));

      $loginInfoAjaxControl = new LoginInfoAjaxControl();
      $html_portalseite->setVariable("LOGIN_INFO_AJAX", $loginInfoAjaxControl->getHtml());
      
      if ($ajaxcall || $type == "rss") {
        $siteoutput = $content;
      }
      else {
        $siteoutput = $html_portalseite->get();
        $ssp = new ssp();
        $siteoutput = $ssp->parse_content($siteoutput);
      }
      if (!$ajaxcall && $type != "rss") {
        
        ini_set('zlib.output_compression_level', 5);
        // Start output buffering
        ob_start('ob_gzhandler');
        
        $mylastm = time();
        $mlast_modified = substr(date('r', $mylastm), 0, -5).'GMT';
        $metag = '"'.md5($mlast_modified).'"';
        header('Cache-Control: private');
        header('Cache-Control: must-revalidate');
        header("Last-Modified: $mlast_modified");
        header("ETag: $metag");
      }
      echo($siteoutput);
      
      if (USE_CACHE && !$ajaxcall) {
        if ($steam_connector->get_login_user()->get_name() == "guest" && !$do_login) {
          cache_debug( "index: Saving cache file for path=" . $path  . " style=".STYLE_NAME . " type=" . $type);
          $timestampcache->save($mylastm, $path, STYLE_NAME . $type . "_timestamp");
          
          $cachedata = array();
          $cachedata["siteoutput"] = $siteoutput;
          if (!$file_found) $cachedata["notfound"] = "true";
          $cache->save($cachedata, $path, STYLE_NAME . $type);
        }
        else {
          cache_debug( "index: Removing cache file(s) for path=" . $path);
          if (is_array($_SESSION[STEAMWEB_PATH_INTERN][ "styles" ])) {
            foreach( $_SESSION[STEAMWEB_PATH_INTERN][ "styles" ] as $stylename) {
              $timestampcache->remove($path, $stylename . $type . "_timestamp");
              $cache->remove($path, $stylename . $type);
              $timestampcache->remove($path, $stylename . "rss" . "_timestamp");
              $cache->remove($path, $stylename . "rss");
            }
          }
          else {
            $timestampcache->remove($path, STYLE_NAME . $type . "_timestamp");
            $cache->remove($path, STYLE_NAME . $type);
            $timestampcache->remove($path, STYLE_NAME . "rss" . "_timestamp");
            $cache->remove($path, STYLE_NAME . "rss");
          }
        }
      }
    }
  $steam_connector->disconnect();
  }
}*/
	}
	
}
?>