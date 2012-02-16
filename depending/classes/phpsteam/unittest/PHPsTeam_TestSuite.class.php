<?php
error_reporting((E_ALL | E_NOTICE) & ~E_DEPRECATED);
define("PATH_ROOT", "/Volumes/Users/Entwicklung/php/workspace/koala-2_1/classes/");
define("PATH_UNITTEST", "/Volumes/Users/Entwicklung/php/workspace/koala-2_1/classes/PHPsTeam/unittest/");

/*require(PATH_ROOT . 'Autoloader.class.php');
Autoloader::setCacheFilePath(PATH_ROOT . '../temp/class_path_cache.txt');
Autoloader::excludeFolderNamesMatchingRegex('/^CVS|\..*$/');
Autoloader::setClassPaths(array(
    PATH_ROOT . "PHPsTeam/"
));
spl_autoload_register(array('Autoloader', 'loadClass'));*/

require PATH_ROOT . "autoloader/Autoloader.php";
Autoloader::getRegisteredAutoloader()->remove();
$autoloader = new Autoloader(PATH_ROOT);
$autoloader->register();
$autoloader->getIndex()->setIndexPath(PATH_ROOT . "../temp/phpsteam_unittest_autoloader.gz");


require_once('classes/autorun.php');
//require_once PATH_UNITTEST . 'classes/simpletest.php';
//require_once PATH_UNITTEST . 'inc/showpasses.class.php';

SimpleTest::prefer(new showpasses());

class PHPsTeam_TestSuite extends TestSuite {
	
	function PHPsTeam_TestSuite() {
		$this->TestSuite("all PHPsTeam unit tests");
		$this->addFile(PATH_UNITTEST . "steam_connector_test.class.php");
		$this->addFile(PATH_UNITTEST . "steam_factory_test.class.php");
		$this->addFile(PATH_UNITTEST . "steam_document_test.class.php");
	}
	
}
?>