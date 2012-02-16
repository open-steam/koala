<?php

	namespace Worksheet;
	
	/**
	* block dataclass based on block dataclass of coactum course editor (by Tobias Kempkensteffen, Felix Winkelnkemper, (c) 2010, coactum GmbH, Paderborn)
    * -------------
	*
	* by Tobias Kempkensteffen <tobias.kempkensteffen@gmail.com>
	*
	*/
	class Block
	{
		
		protected $id;
		protected $steamObj;
		

		public function getType() {
			return $this->steamObj->get_attribute("worksheet_type");
		}
		
		public function setType($type)
		{
			$this->steamObj->set_attribute("worksheet_type", $type);
		}
		
		
		public function getContent() {
			
			$content = Array();

			$content['text'] = $this->steamObj->get_content();

			$content2 = $this->steamObj->get_attribute("worksheet_content");

			if (is_array($content2)) {
				$content = array_merge($content, $content2);
			}
			
			return $content;
			
		}
		
		public function setContent($content)
		{
			$this->steamObj->set_attribute("worksheet_content", $content);
		}
		
		
		public function getSolution() {
			
			$data = $this->steamObj->get_attribute("worksheet_solution");
			
			if (is_array($data) AND isset($data['solution'])) {

				return $data['solution'];
				
			} else {
				
				return false;
				
			}
			
		}
		
		public function clearSolution()
		{
			
			$this->steamObj->set_attribute("worksheet_solution", false);
				
		}
		
		public function setSolution($data)
		{
			$solution = $this->steamObj->get_attribute("worksheet_solution");
			
			if (!is_array($solution)) {
				$solution = Array();
			}
			
			$solution["solution"] = $data;
			
			$this->steamObj->set_attribute("worksheet_solution", $solution);
		}
		
		public function getCorrection()
		{
			$data = $this->steamObj->get_attribute("worksheet_solution");
			
			if (is_array($data) AND isset($data['correction'])) {

				return $data['correction'];
				
			} else {
				
				return false;
				
			}
		}
		
		
		public function setCorrection($data)
		{
			$solution = $this->steamObj->get_attribute("worksheet_solution");
			
			if (!is_array($solution)) {
				$solution = Array();
			}
			
			$solution["correction"] = $data;
			
			$this->steamObj->set_attribute("worksheet_solution", $solution);
		}
		
		
		
		
		public function archiveSolution()
		{
			$solutions = $this->getOld_solutions();
			
			$solutions[] = Array(
				"solution" => $this->getSolution(),
				"correction" => $this->getCorrection()
			);
			
			$this->setOld_solutions($solutions);
			$this->clearSolution();
		}
		
		public function getOld_solutions() {
			
			$data = $this->steamObj->get_attribute("worksheet_old_solutions");
			
			if (is_array($data)) {
				return $data;
			} else {
				return Array();
			}
			
		}
		
		public function setOld_solutions($old_solutions)
		{
			$this->steamObj->set_attribute("worksheet_old_solutions", $old_solutions);
		}
		
		
		public function getOrder() {
			return $this->steamObj->get_attribute("worksheet_order");
		}
		
		public function setOrder($order)
		{
			$this->steamObj->set_attribute("worksheet_order", $order);
		}
		
		

		public function getId() {
			return $this->id;
		}
	

		public function setName($name)
		{
			$this->steamObj->set_name($name);
		}
		
		public function getName()
		{
			return $this->steamObj->get_name();
		}


		
		
		public function getCreationTime()
		{
			return $this->steamObj->get_attribute(OBJ_CREATION_TIME);
		}
		
		
		
		
		
		/*
		* create an object representing a block
		*/
		public function __construct($blockId)
		{
			$this->getBlockById($blockId);
		}
		
		/*
		*  Load the block by id
		*/
		function getBlockById($id)
		{
			
			$obj = \steam_factory::get_object($GLOBALS[ "STEAM" ]->get_id(), $id);

			$this->id = $id;
			$this->steamObj = $obj;

			return true;
			
		}


		
		public function getSampleText()
		{
			$this->markSampleUsed();
			
			$content = $this->getContent();
			return $content['sample_text'];
			
		}


		
		public function markSampleUsed()
		{
			$content = $this->getContent();
			
			$content['sample_used'] = true;
			
			$this->setContent($content);
			
		}
		

		
		
		
		public function resetSampleUsed()
		{
			$content = $this->getContent();
			
			$content['sample_used'] = false;
			
			$this->setContent($content);
			
		}
		
		
		public function setSampleDisplayed()
		{
			$content = $this->getContent();
			
			$content['sample_displayed'] = true;
			
			$this->setContent($content);
			
		}
		
		
		public function resetSampleDisplayed()
		{
			$content = $this->getContent();
			
			$content['sample_displayed'] = false;
			
			$this->setContent($content);
			
		}
		


		public static function create($type, $steamObj)
		{
			
			if (!self::checkType($type)) {
				throw new \Exception("unknown block type");
			}
			
			$doc = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "Neue Aufgabe", "", "text/html", $steamObj);
			
			$newBlock = new \Worksheet\Block($doc->get_id());
			
			$newBlock->setType($type);
			
			return $newBlock;
			
		}
		

		
		/*
		*	get a view HTML representation of the current block.
		*/
		public function getViewHtml()
		{
			
				$tpl = new \Worksheet\Template;
			
				$content = $this->getContent();
				$type = $this->getType();
			
				if (file_exists(dirname(__FILE__)."/../blocks/".$type."/view.php")) {
					include dirname(__FILE__)."/../blocks/".$type."/view.php";
				}
				
				$tpl->assign("data", $content);
				
				$tpl->display("../../blocks/".$type."/view.html");
				
				return $tpl->getHtml();
				
		}
		
		
		
		/*
		*	get a view HTML representation of the current block for the build preview
		*/
		public function getBuildViewHtml()
		{
			
				$tpl = new \Worksheet\Template;
			
				$content = $this->getContent();
				$type = $this->getType();
			
				if (file_exists(dirname(__FILE__)."/../blocks/".$type."/build_view.php")) {
					include dirname(__FILE__)."/../blocks/".$type."/build_view.php";
				}
				
				$tpl->assign("data", $content);
				
				$tpl->display("../../blocks/".$type."/build_view.html");
				
				return $tpl->getHtml();
				
		}
		
		
		
		/*
		*	get a view HTML representation of the current block for the build editing
		*/
		public function getBuildEditHtml()
		{
			
				$tpl = new \Worksheet\Template;
			
				$content = $this->getContent();
				$type = $this->getType();
			
				if (file_exists(dirname(__FILE__)."/../blocks/".$type."/build_edit.php")) {
					include dirname(__FILE__)."/../blocks/".$type."/build_edit.php";
				}
				
				$tpl->assign("data", $content);
				
				$tpl->display("../../blocks/".$type."/build_edit.html");
				
				return $tpl->getHtml();
				
		}
		
		
		
		public function saveBuildEdit($_POST)
		{
			
			$content = $this->getContent();
			$type = $this->getType();

			if (file_exists(dirname(__FILE__)."/../blocks/".$type."/build_edit_save.php")) {
				include dirname(__FILE__)."/../blocks/".$type."/build_edit_save.php";
			}

			$this->setContent($content);
			$this->setName($_POST['name']);
			
		}
		
		
		
		
		/*
		*	get a view HTML representation of the current block for editing
		*/
		public function getEditHtml($worksheetStatus)
		{

				$tpl = new \Worksheet\Template;
			
				$content = $this->getContent();
				$type = $this->getType();
				
				$solution = $this->getSolution();
				$correction = $this->getCorrection();
				$old_solutions = $this->getOld_solutions();
			
				if (file_exists(dirname(__FILE__)."/../blocks/".$type."/edit.php")) {
					include dirname(__FILE__)."/../blocks/".$type."/edit.php";
				}
				
				//temp:
				//$content['text'] = preg_replace('@\<\!\-\-\ Worksheet\_\[(.*)\]\[(.*)\]\[(.*)\]\ \-\-\>@isU', '<img src="http://192.168.124.134/bidowl-3_0/worksheet/asset/js/tinymce/plugins/WorksheetVideo/img/test.png" alt="" />', $content['text']);
				

				
				$tpl->assign("data", $content);

				
				$tpl->assign("correction", $correction);

				$tpl->assign("solution", $solution);

				foreach ($old_solutions as $mkey=>$solution) {

					$data = Array(
						"score" => 0,
						"max" => 0
					);
					
					if (file_exists(dirname(__FILE__)."/../blocks/".$type."/score.php")) {
						include dirname(__FILE__)."/../blocks/".$type."/score.php";
					}
					
					$old_solutions[$mkey]['score'] = $data;
					
				}
				
				$tpl->assign("old_solutions", $old_solutions);

				$tpl->assign("worksheet_status", $worksheetStatus);

				$tpl->assign("BLOCK_ID", $this->id);
				
				$tpl->display("../../blocks/".$type."/edit.html");
				
				return $tpl->getHtml();
				
		}
		

		
		
		
		public function getCorrectHtml()
		{
			
				$tpl = new \Worksheet\Template;
			
				$content = $this->getContent();
				$type = $this->getType();
				
				$solution = $this->getSolution();
				$correction = $this->getCorrection();
			
				if (file_exists(dirname(__FILE__)."/../blocks/".$type."/correct.php")) {
					include dirname(__FILE__)."/../blocks/".$type."/correct.php";
				}
				
				//temp:
				//$content['text'] = preg_replace('@\<\!\-\-\ Worksheet\_\[(.*)\]\[(.*)\]\[(.*)\]\ \-\-\>@isU', '<img src="http://192.168.124.134/bidowl-3_0/worksheet/asset/js/tinymce/plugins/WorksheetVideo/img/test.png" alt="" />', $content['text']);
				

				
				$tpl->assign("data", $content);

				$tpl->assign("correction", $correction);

				$tpl->assign("solution", $solution);
				$tpl->assign("old_solutions", $this->getOld_solutions());
				
				$tpl->assign("BLOCK_ID", $this->id);
				
				$tpl->display("../../blocks/".$type."/correct.html");
				
				return $tpl->getHtml();
				
		}
		

		
		
		public static function getBlockTypes() {
			
			$blockDirs = scandir(dirname(__FILE__)."/../blocks");
			
			if ($blockDirs) {
			
				$blocks = Array();
			
				foreach ($blockDirs as $blockDir) {
				
					if ($blockDir != "." AND $blockDir != ".." AND is_dir(dirname(__FILE__)."/../blocks/".$blockDir) AND file_exists(dirname(__FILE__)."/../blocks/".$blockDir."/info.txt")) {
				
						$blockInfoStr = file_get_contents(dirname(__FILE__)."/../blocks/".$blockDir."/info.txt");
						$blockInfo = explode("\n", $blockInfoStr, 2);
				
						$title = str_replace(Array("\r", "\n"), "", $blockInfo[0]);
						$description = nl2br($blockInfo[1]);
				
						$blocks[] = Array(
							"name" => $blockDir,
							"title" => $title,
							"description" => $description,
							"image" => (file_exists(dirname(__FILE__)."/../blocks/".$blockDir."/preview.jpg"))
						);
						
					}
				
				}
				
				if (count($blocks) > 0) {
					return $blocks;
				} else return false;
			
			} else return false;
			
		}
		
		
		public static function checkType($type)
		{
			
			$type = preg_replace('@[^a-zA-Z0-9_-]is@', "", $type);
			
			return (file_exists(dirname(__FILE__)."/../blocks/".$type) AND is_dir(dirname(__FILE__)."/../blocks/".$type) AND file_exists(dirname(__FILE__)."/../blocks/".$type."/info.txt"));
			
		}
		
		
		
		
		public function getScoreInfo()
		{
			$solutions = $this->getOld_solutions();

			if (count($solutions) == 0) return false;

			$type = $this->getType();
			$content = $this->getContent();
			
			$solution = $solutions[count($solutions)-1];
			
			$data = Array(
				"score" => 0,
				"max" => 0
			);
			
			if (file_exists(dirname(__FILE__)."/../blocks/".$type."/score.php")) {
				include dirname(__FILE__)."/../blocks/".$type."/score.php";
			}
			
			return $data;
			
		}
		
		
		public function getScore()
		{
			
			$data = $this->getScoreInfo();

			if ($data === false) return false;
			
			return $data['score'];
			
		}
		
		
		public function getMaxScore()
		{

			$data = $this->getScoreInfo();
			
			if ($data === false) return false;

			return $data['max'];
			
		}
		
		
		
	}
	
?>