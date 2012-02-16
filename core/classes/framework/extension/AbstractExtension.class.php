<?php
abstract class AbstractExtension extends AbstractCommandAdapter implements IExtension {
	
	protected $initOnce = false;
	private $_td_stack = array(); // text domains stack
	
	private function __construct() {
		$defFile = $this->getExtensionPath() . "etc/default.def.php";
		if (file_exists($defFile)) {
			include_once $defFile;
		}
		if (!$this->initOnce) {
			$this->init();
			$this->initOnce = true;
		}
		
		// setup language support
		$extensionLocalePath = $this->getExtensionPath() . "locale/";
		if (file_exists($extensionLocalePath)) {
			bindtextdomain($this->getId(), $extensionLocalePath);
			bind_textdomain_codeset($this->getId(), CHARSET );
		}
	}
	
	public function getText($string) {
		$extensionLocalePath = $this->getExtensionPath() . "locale/";
		if (file_exists($extensionLocalePath)) {
			$this->set_textdomain($this->getId());
			$result = gettext($string);
			$this->restore_textdomain();
			return $result;
		} else {
			return gettext($string);
		}
	}
	
	/**
	 * Sets a new text domain after recording the current one
	 * so it can be restored later with restore_textdomain().
	 *
	 * It's possible to nest calls to these two functions.
	 * @param string the new text domain to set
	 */
	private function set_textdomain($td) {		 
		$old_td = textdomain(NULL);
		 
		if ($old_td) {
			if (!strcmp($old_td, $td)) {
				array_push($this->_td_stack, false);
			}
			else {
				array_push($this->_td_stack, $old_td);
			}
		}
		 
		textdomain($td);
	}
	
	/**
	 * Restore the text domain active before the last call to
	 * set_textdomain().
	 */
	private function restore_textdomain()
	{
		$old_td = array_pop($this->_td_stack);
		if ($old_td) {
			textdomain($old_td);
		}
	}
	
	public static function getInstance() {
		static $instance = null;
		if (!isset($instance)) {
			$instance = new static;
			if (!function_exists($name=get_called_class())) {
				$strCode = "function {$name} (\$attribute = null) {
								static \$obj = null;
								!\$obj AND \$obj = {$name}::getInstance();
								return \$attribute ? \$obj->\$attribute : \$obj;
							}";
				eval($strCode);
			}
		}
		return $instance;
	}
	
	public function init() {
		
	}
	
	public function getId() {
		return get_class($this);
	}
	
	public function getIcon() {
		//TODO
	}
	
	public function getChangelog() {
		$file = $this->getExtensionPath() . "changelog";
		if (file_exists($file)) {
			return file_get_contents($file);
		}
		$changelog = <<< END
No changelog file exists for this extension. Put a file named "changelog" to extension folder. 
Format the content like this:

YYYY-MM-DD  John Doe  <johndoe@example.com>

	* myfile.ext (myfunction): my changes made
	additional changes

	* myfile.ext (unrelated_change): my changes made
	to myfile.ext but completely unrelated to the above

	* anotherfile.ext (somefunction): more changes		
END;
		return $changelog;
	}
	
	public function getReadme() {
		$file = $this->getExtensionPath() . "README";
		if (file_exists($file)) {
			return file_get_contents($file);
		}
		$readme = <<< END
No README file exists for this extension. Put a file named "README" to extension folder. Format the 
content in markdown syntax (see http://en.wikipedia.org/wiki/Markdown).
END;
		return $readme;
	}
	
	public function getInstall()  {
		$file = $this->getExtensionPath() . "INSTALL";
		if (file_exists($file)) {
			return file_get_contents($file);
		}
		$install = <<< END
No INSTALL file exists for this extension. Put a file named "INSTALL" to extension folder. Format the 
content in markdown syntax (see http://en.wikipedia.org/wiki/Markdown).
END;
		return $install;
	}
	public function getLicense() {
		$file = PATH_CORE . "gpl-3.0.txt";
		if (file_exists($file)) {
			return file_get_contents($file);
		}
		$license = <<< END
License missing! Please add a valid license file.
END;
	}
	
	public function getMaintainer() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		$result[] = new Person("Marcel", "Jakoblew", "mjako@uni-paderborn.de");
		return $result;
	}
	
	public function readCSS($fileName = "style.css") {
		$cssFile = $this->getExtensionPath() . "ui/css/$fileName";
		if (file_exists($cssFile)) {
			$content = file_get_contents($cssFile);
			return str_replace("{PATH_URL}", PATH_URL, $content);
		}
		return null;
	}
	
	public function readJS($fileName = "code.js") {
		$jsFile = $this->getExtensionPath() . "ui/js/$fileName";
		if (file_exists($jsFile)) {
			return file_get_contents($jsFile);
		}
		return null;
	}
	
	public function addCSS($fileName = "style.css") {
		$css = $this->readCSS($fileName);
		if ($css && $css != "") {
			lms_portal::get_instance()->add_css_style($css);
		}
	}
	
	public function addJS($fileName = "code.js") {
		$js = $this->readJS($fileName);
		if ($js && $js != "") {
			lms_portal::get_instance()->add_javascript_code($this->getName(), $js);
		}
	}
	
	public function getExtensionPath() {
		$class = new ReflectionClass(get_class($this));
		$path =  dirname(dirname($class->getFileName())) . "/";
		return $path;
	}
	
	public function getExtensionUrl() {
		$namespace = $this->getUrlNamespaces();
		return PATH_URL . $namespace[0] ."/";
	}
	
	public function getFrameworkPath() {
		return dirname(dirname(__FILE__)) . "/";
	}
	
	public function getDepending() {
		return array();
	}
	
	public function getAssetUrl() {
		return strtolower(PATH_URL . $this->getName() . "/asset/");
	}
	
	public function downloadAsset($path) {
		if (!is_array($path)) {
			return false;
		}
		
		//while ($path && array_shift($path) != "asset") {}
		
		$this->prepareDownload("asset", $path);
	}
	
	public function downloadCss($path) {
		if (!is_array($path)) {
			return false;
		}
		
		//while ($path && array_shift($path) != "css") {}
		
		$this->prepareDownload("ui/css", $path);
	}
	
	public function downloadJs($path) {
		if (!is_array($path)) {
			return false;
		}
		
		//while ($path && array_shift($path) != "js") {}
		
		$this->prepareDownload("ui/js", $path);
	}
	
	
	public function prepareDownload($internalFolder, $internalPath) {
		if (!is_array($internalPath)) {
			return false;
		}
		if ($internalPath) {
			$file_path = implode("/", $internalPath);
			$fileName = $this->getExtensionPath() . $internalFolder . "/" . $file_path;
			$this->download($fileName);
		}
	}
	
	public function download($absoluteFilePath) {
		if (file_exists($absoluteFilePath)) {
			// Must be fresh start
		    if( headers_sent() ) {
		    	throw new Exception("Headers Sent"); 
		    }
		    // Required for some browsers
			if(ini_get('zlib.output_compression')) {
    			ini_set('zlib.output_compression', 'Off');
			}
			
			if (strEndsWith($absoluteFilePath, ".css", false)) {
				$mimetype = "text/css";
			} else if (strEndsWith($absoluteFilePath, ".js", false)) {
				$mimetype = "text/javascript";
			} else if (strEndsWith($absoluteFilePath, ".gif", false)) {
				$mimetype = "image/gif";
			} else {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mimetype = finfo_file($finfo, $absoluteFilePath);
				finfo_close($finfo);
			}
			
			// Getting headers sent by the client.
    		$headers = apache_request_headers();

		    // Checking if the client is validating his cache and if it is current.
		    if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) >= filemtime($absoluteFilePath))) {
		        // Client's cache IS current, so we just respond '304 Not Modified'.
		        header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($absoluteFilePath)).' GMT', true, 304);
		    } else {
		    	$offset = 60 * 60 * 24 * 3;
				header("Pragma: public" );
				header("Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT"); 
				header("Expires: 0");
				// Image not cached or cache outdated, we respond '200 OK' and output the image.
       	 		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($absoluteFilePath)).' GMT', true, 200);
	     		header("Cache-Control: must-revalidate, post-check=0, pre-check=0, max-age=3600-" );
	     		header("Cache-Control: private",false); // required for certain browsers 
	      		header("Content-Type: " . $mimetype);
	      		header("Content-Disposition: inline; filename=\"".basename($absoluteFilePath)."\";" );
	    		header("Content-Transfer-Encoding: binary"); 
	      		header("Content-Length:" .  filesize($absoluteFilePath));
	      		
	      		ob_clean();
	    		flush();
	    		@readfile($absoluteFilePath); 
		    }
		} else {
			header("HTTP/1.0 404 Not Found");
			echo "<h1>HTTP/1.0 404 Not Found</h1>";
		}
	}
	
	public function getInfoHtml() {
		$content = new HTML_TEMPLATE_IT();
		$content->loadTemplateFile($this->getFrameworkPath() . "ui/ExtensionInfo.template.html");
		$content->setVariable("EXTENSION_NAME", $this->getName());
		$content->setVariable("EXTENSION_ID", $this->getId());
		$content->setVariable("EXTENSION_DESCIPTION", $this->getDesciption());
		//$this->getIcon();
		$content->setVariable("EXTENSION_VERSION", $this->getVersion());
		$autors = $this->getAuthors();
		$personHtml = "";
		foreach($autors as $autor) {
			$personHtml .= $autor->getFirstname() . " " . $autor->getLastname() . " (<a href=\"mailto:" . $autor->getEmail() . "\">" . $autor->getEmail() . "</a>)<br>";
		}
		$content->setVariable("EXTENSION_AUTHORS", $personHtml);
		$maintainers = $this->getMaintainer();
		$personHtml = "";
		foreach($maintainers as $maintainer) {
			$personHtml .= $maintainer->getFirstname() . " " . $maintainer->getLastname() . " (<a href=\"mailto:" . $maintainer->getEmail() . "\">" . $maintainer->getEmail() . "</a>)<br>";
		}
		$content->setVariable("EXTENSION_MAINTAINER", $personHtml);
		
		$content->setVariable("EXTENSION_CHANGELOG", $this->getChangelog());
		$content->setVariable("EXTENSION_README", $this->getReadme());
		$content->setVariable("EXTENSION_INSTALL", $this->getInstall());
		$content->setVariable("EXTENSION_LICENSE", $this->getLicense());
		return $content->get();
	}
	
	public function loadTemplate($templateName) {
		$content = new HTML_TEMPLATE_IT();
		$file = $this->getExtensionPath() . "ui/html/" . $templateName;
		if (file_exists($file)) {
			$content->loadTemplateFile($this->getExtensionPath() . "ui/html/" . $templateName);
			return $content;
		} else {
			throw new Exception("Template ". $templateName ." doesn't exist.");
		}
		
	}
	
	public function getPriority() {
		return 0;
	}
	
	public function getUrlNamespaces() {
		return array(strtolower(get_class($this)));
	}
	
	public function getCurrentObject(UrlRequestObject $urlRequestObject) {
		return null;
	}
	
}
?>