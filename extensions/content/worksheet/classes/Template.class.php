<?php
	
	namespace Worksheet;

	
	include_once(dirname(__FILE__)."/smarty/Smarty.class.php"); 

	

	class Template
	{

		protected $smarty = false;
		protected $html = "";
			
		function __construct($id=false)
		{
			
			$this->smarty = new \Smarty;

			$this->smarty->compile_check = true;
			$this->smarty->debugging = false;

			$this->smarty->template_dir = dirname(__FILE__)."/../ui/html";
			$this->smarty->compile_dir =  dirname(__FILE__)."/../templates_c";
			
			$this->assign("PATH_URL", PATH_URL);
			$this->assign("ID", $id);
			
			/* add header files only if $id is set (otherwise this would be called with each preview) */
			if ($id) {
				$this->display("../css/style.css");
				$this->display("header.template.html");
				$this->display("jsheader.template.html");
			}
			
			return $this->smarty;
			
		}

		
		public function fetch($templateFile)
		{
			
			return $this->smarty->fetch($templateFile);
			
		}
		
		
		public function display($templateFile)
		{
			
			$this->html .= $this->smarty->fetch($templateFile);
			
		}
		
		
		public function assign($key, $value)
		{
			
			$this->smarty->assign($key, $value);
			
		}
	
	
		public function parse($frameResponseObject)
		{
			
			$rawHtml = new \Widgets\RawHtml();
			$rawHtml->setHtml($this->html);
			$frameResponseObject->addWidget($rawHtml);
			
		}


		public function getHtml()
		{
			return $this->html;
		}
		
		
		public function loadEditor()
		{
			
			$this->display("editor.template.html");
			
		}

	
	}
	

?>