<?php

defined("FILE_PERSISTENCE") or define("FILE_PERSISTENCE", false);
defined("FILE_PERSISTENCE_PATH") or define("FILE_PERSISTENCE_PATH", "/path/for/file/persistence/");
defined("FILE_PERSISTENCE_HASHED") or define("FILE_PERSISTENCE_HASHED", false);

class FilePersistence {
	
	private static $instance;
	
	private function __construct() {
		
	}
	
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		} else {
			return self::$instance;
		}
	}
	
	public static function isEnabled() {
		return FILE_PERSISTENCE;
	}
	
	public static function usesFilePersistence($steamDocument) {
		if (!self::isEnabled() || (self::isEnabled() && !$steamDocument->get_attribute("DOC_FILE_PERSISTENCE"))) {
			return false;
		} else {
			return true;
		}
	}
	
	public function save($steamDocument, $content) {
		if (!$this->isEnabled()) {
			return false;
		}
		$persistenceFile = new PersistenceFile($steamDocument);
		if ($steamDocument->steam_command($this, "set_content", array($persistenceFile->getFilePersistenceId()))) {
			if (mkdir($persistenceFile->getFolderPath(), 0777, true)) {
				if (file_put_contents($persistenceFile->getFileContentPath(), $content)) {
					$steamDocument->set_attribute("DOC_FILE_PERSISTENCE", 1);
					file_put_contents($persistenceFile->getFileIdsPath(), $steamDocument->get_id() . ",\n", FILE_APPEND);
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
		return true;
	}
	
	public function read($steamDocument) {
		if (!$this->isEnabled()) {
			return false;
		}
		$persistenceFile = new PersistenceFile($steamDocument);
		if (file_exists($persistenceFile->getFileContentPath())) {
			return file_get_contents($persistenceFile->getFileContentPath());
		} else {
			return "CONTENT MISSING";
		}
	}
	
	public function delete($steamDocument) {
		if (!$this->isEnabled()) {
			return false;
		}
		//TODO
	}
	
	public function getSize($steamDocument) {
		if (!$this->isEnabled()) {
			return -1;
		}
		$persistenceFile = new PersistenceFile($steamDocument);
		if (file_exists($persistenceFile->getFileContentPath())) {
			return filesize($persistenceFile->getFileContentPath());
		} else {
			return -1;
		}
	}
	
}

class PersistenceFile {
	private $uuid, $folder, $fileContent, $fileIds;
	
	public function __construct($steamDocument = null) {
		if ($steamDocument && ($steamDocument->get_attribute("DOC_FILE_PERSISTENCE") === 1)) {
			$this->uuid = $steamDocument->get_content_internal();
			$this->folder = FILE_PERSISTENCE_PATH . $uuid[0] . "/" . $uuid[1] . "/" . $uuid[2] . "/" . $uuid[3] . "/" . $uuid[4] . "/" . $uuid[5] . "/" . $uuid[6] . "/" . substr($uuid, 7) . "/";
			$this->fileContent = $folder . "content";
		} else {
			$this->uuid = UUID::generate(UUID::UUID_RANDOM, UUID::FMT_STRING);
		}
		$this->folder = FILE_PERSISTENCE_PATH . $uuid[0] . "/" . $uuid[1] . "/" . $uuid[2] . "/" . $uuid[3] . "/" . $uuid[4] . "/" . $uuid[5] . "/" . $uuid[6] . "/" . substr($uuid, 7) . "/";
		$this->fileContent = $this->folder . "content";
		$this->fileIds = $this->folder . "ids";
	}
	
	public function getFolderPath() {
		return $this->folder;
	}
	
	public function getFileContentPath() {
		return $this->fileContent;
	}
	
	public function getFileIdsPath() {
		return $this->fileIds;
	}
	
	public function getFilePersistenceId() {
		return $this->uuid;
	}
}
?>