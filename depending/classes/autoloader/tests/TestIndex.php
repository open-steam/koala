<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the test cases for implementations of AutoloaderIndex
 *
 * PHP version 5
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.
 * If not, see <http://php-autoloader.malkusch.de/en/license/>.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id: TestIndex.php,v 1.3 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The Autoloader is used for class loading.
 */
require_once dirname(__FILE__) . "/../Autoloader.php";

/**
 * The tests need an one-time initialisation.
 */
TestIndex::classConstructor();

/**
 * AutoloaderIndex test cases
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AutoloaderIndex
 * @see        AutoloaderIndex_CSV
 * @see        AutoloaderIndex_Dummy
 * @see        AutoloaderIndex_IniFile
 * @see        AutoloaderIndex_PDO
 * @see        AutoloaderIndex_PHPArrayCode
 * @see        AutoloaderIndex_SerializedHashtable
 * @see        AutoloaderIndex_SerializedHashtable_GZ
 */
class TestIndex extends PHPUnit_Framework_TestCase
{

    /**
     * Test files are stored in __DIR__ . '/' . INDEX_DIRECTORY.
     */
    const INDEX_DIRECTORY = 'index';

    /**
     * Creates the directory for the test classes
     *
     * @return void
     */
    static public function classConstructor()
    {
        if (! file_exists(self::getIndexDirectory())) {
            mkdir(self::getIndexDirectory());

        }
    }

    /**
     * Test the context
     * 
     * Setting no context should return the class path context. Setting a context
     * should return the setted context.
     *
     * @param String          $expectedContext expected context
     * @param AutoloaderIndex $index           index
     *
     * @see AutoloaderIndex::setContext()
     * @see AutoloaderIndex::getContext()
     * @dataProvider provideTestContext
     * @return void
     */
    public function testContext($expectedContext, AutoloaderIndex $index)
    {
        $getContext = new ReflectionMethod($index, "getContext");
        $getContext->setAccessible(true);
        $this->assertTrue((bool) $getContext->invoke($index));
        $this->assertEquals($expectedContext, $getContext->invoke($index));
    }

    /**
     * Testcases for testContext()
     *
     * @see testContext()
     * @return array
     */
    public function provideTestContext()
    {
        $cases = array();

        // defined context
        $context = uniqid();
        $index   = new AutoloaderIndex_Dummy();
        $index->setContext($context);
        $cases[] = array($context, $index);

        // generated context
        $index = new AutoloaderIndex_Dummy();
        $this->_initIndex($index);
        $getContext = new ReflectionMethod($index, "getContext");
        $getContext->setAccessible(true);
        $cases[] = array($getContext->invoke($index), $index);

        // generated context
        $index = new AutoloaderIndex_Dummy();
        $this->_initIndex($index);
        // copied from AutoloaderIndex::getContext()
        $context = md5($index->getAutoloader()->getPath());
        $cases[] = array($context, $index);

        return $cases;
    }

    /**
     * Asserts that AutoloaderIndex_PDO::getSQLiteInstance() doesn't throw any
     * exception
     *
     * @see initIndex()
     * @see AutoloaderIndex_PDO::getSQLiteInstance()
     * @return void
     */
    public function testGetDefaultSQLiteIndex()
    {
        $index = AutoloaderIndex_PDO::getSQLiteInstance();
        $this->_initIndex($index);
    }

    /**
     * Asserts that AutoloaderIndex::getPath() returns the expected path for a class
     *
     * @param AutoloaderIndex $index The tested index
     * @param String          $class The searched class
     * @param String          $path  The expected path for $class
     *
     * @dataProvider provideExistingClassesWithPaths
     * @see AutoloaderIndex::getPath()
     * @return void
     */
    public function testGetPath(AutoloaderIndex $index, $class, $path)
    {
        $this->assertEquals($path, $index->getPath($class));
    }

    /**
     * Asserts that the index will throw an AutoloaderException_Index_NotFound
     * Exception for an unknown class
     *
     * @param AutoloaderIndex $index The tested AutoloaderIndex object
     *
     * @dataProvider provideIndexes
     * @expectedException AutoloaderException_Index_NotFound
     * @see AutoloaderIndex::getPath()
     * @see AutoloaderException_Index_NotFound
     * @return void
     */
    public function testFailGetPath(AutoloaderIndex $index)
    {
        $index->getPath("ClassWhichDoesntExist" . uniqid());
    }

    /**
     * Asserts that AutoloaderIndex::hasPath() returns false for a not existing
     * class
     *
     * @param AutoloaderIndex $index The tested AutoloaderIndex object
     *
     * @dataProvider provideIndexes
     * @see AutoloaderIndex::hasPath()
     * @return void
     */
    public function testHasNotPath(AutoloaderIndex $index)
    {
        $this->assertFalse($index->hasPath("ClassWhichDoesntExist" . uniqid()));
    }

    /**
     * Asserts that AutoloaderIndex::hasPath() returns true for a existing class
     *
     * @param AutoloaderIndex $index The tested AutoloaderIndex object
     * @param String          $class A class which exists in $index
     *
     * @dataProvider provideExistingClassesWithPaths
     * @see AutoloaderIndex::hasPath()
     * @return void
     */
    public function testHasPath(AutoloaderIndex $index, $class)
    {
        $this->assertTrue($index->hasPath($class));
    }

    /**
     * Asserts that AutoloaderIndex::unsetPath() removes an existing class from an
     * index
     *
     * @param AutoloaderIndex $index The tested AutoloaderIndex object
     * @param String          $class A class which exists in $index
     *
     * @dataProvider provideExistingClassesWithPaths
     * @see AutoloaderIndex::unsetPath()
     * @return void
     */
    public function testUnsetPath(AutoloaderIndex $index, $class)
    {
        $this->assertTrue($index->hasPath($class));
        $path = $index->getPath($class);
        $index->unsetPath($class);
        $this->assertFalse($index->hasPath($class));

        if ($index instanceof AutoloaderIndex_SerializedHashtable) {
            $index = $this->_getIndexFromPersistence($index);
            $this->assertFalse($index->hasPath($class));

        }
        $index->setPath($class, $path);
    }


    /**
     * Provides test cases for classes which exists in an index with an expected
     * path
     *
     * A test case consists of an index, a class which exists in that index
     * and the path which the index would return for that class.
     *
     * @see testGetPath()
     * @see testHasPath()
     * @see testUnsetPath()
     * @return Array array($index, $class, $path)
     */
    public function provideExistingClassesWithPaths()
    {
        $cases    = array();
        $classes  = array(
            "TestClassA"
                => "classes/TestClassA.php",

            "TestClassB"
                => "classes/TestClassB.php",

            "TestClassC1"
                => "classes/TestClassC.php",

            "TestClassC2"
                => "classes/TestClassC.php",

            'de\malkusch\autoloader\test\TestClassA'
                => "classes/ns1/TestClassA.php",

            'de\malkusch\autoloader\test\TestClassB'
                => "classes/ns1/TestClassB.php",

            'de\malkusch\autoloader\test2\TestClassB'
                => "classes/ns2/TestClassB.php",

            'de\malkusch\autoloader\test3\TestClassC1'
                => "classes/ns3/TestClassC.php",

            'de\malkusch\autoloader\test3\TestClassC2'
                => "classes/ns3/TestClassC.php"
        );
        foreach ($classes as $class => $path) {
            // simple test with non persistent state
            foreach ($this->_getIndexes() as $index) {
                $cases[] = array(
                    $index,
                    $class,
                    $path
                );
                $index->setPath($class, $path);

            }

            // test with persistent state
            foreach ($this->_getPersistentIndexes() as $index) {
                $index->setPath($class, $path);
                $cases[] = array(
                    $this->_getIndexFromPersistence($index),
                    $class,
                    $path
                );

            }

            // test both with persistent and non persistent state
            foreach ($this->_getPersistentIndexes() as $index) {
                $index->setPath($class, $path);
                $persistentIndex = $this->_getIndexFromPersistence($index);

                $class2 = "{$class}_NonPersistent";
                $path2  = "{$path}/NonPersistent";
                $persistentIndex->setPath($class2, $path2);

                $cases[] = array(
                    $persistentIndex,
                    $class,
                    $path
                );
                $cases[] = array(
                    $persistentIndex,
                    $class2,
                    $path2
                );

            }
        }
        return $cases;
    }

    /**
     * Returns Indexes which are tested in all tests
     *
     * @see testFailGetPath()
     * @see testHasNotPath()
     * @return Array
     */
    public function provideIndexes()
    {
        $cases = array();
        foreach ($this->_getIndexes() as $index) {
            $cases[] = array($index);

        }
        foreach ($this->_getIndexes() as $index) {
            $index->setPath("AnyClass", "AnyPath");
            $cases[] = array($index);

        }
        foreach ($this->_getPersistentIndexes() as $index) {
            $cases[] = array($this->_getIndexFromPersistence($index));

        }
        foreach ($this->_getPersistentIndexes() as $index) {
            $index->setPath("AnyClass", "AnyPath");
            $cases[] = array($this->_getIndexFromPersistence($index));

        }
        return $cases;
    }

    /**
     * Returns an instance of AutoloaderIndex_SerializedHashtable_GZ which is tested
     * in theses tests
     *
     * @return AutoloaderIndex_SerializedHashtable_GZ
     */
    private function _createAutoloaderIndexSerializedHashtableGZ()
    {
        $index = new AutoloaderIndex_SerializedHashtable_GZ();
        $this->_initIndex($index);
        return $index;
    }

    /**
     * Returns an instance of AutoloaderIndex_PDO which is tested in theses tests
     *
     * @param PDO $pdo The PDO object for the AutoloaderIndex_PDO object
     *
     * @return AutoloaderIndex_PDO
     */
    private function _createAutoloaderIndexPDO(PDO $pdo)
    {
        $index = new AutoloaderIndex_PDO($pdo);
        $this->_initIndex($index);
        return $index;
    }

    /**
     * Returns an instance of AutoloaderIndex_PDO with a SQLite PDO object which is
     * tested in theses tests
     *
     * @param String $filename The path to the SQLite database
     *
     * @return AutoloaderIndex_PDO
     */
    private function _createAutoloaderIndexPdoSqLite($filename = null)
    {
        $index = AutoloaderIndex_PDO::getSQLiteInstance($filename);
        $this->_initIndex($index);
        return $index;
    }

    /**
     * Returns an instance of AutoloaderIndex_PDO with a MySQL PDO object which is
     * tested in theses tests
     *
     * The PDO object is initialized with the test database "mysql:dbname=test".
     *
     * @return AutoloaderIndex_PDO
     */
    private function _createAutoloaderIndexPdoMySQL()
    {
        $index = new AutoloaderIndex_PDO(new PDO("mysql:dbname=test"));
        $this->_initIndex($index);
        return $index;
    }

    /**
     * Returns an instance of AutoloaderIndex_IniFile which is tested in theses
     * tests
     *
     * @return AutoloaderIndex_IniFile
     */
    private function _createAutoloaderIndexIniFile()
    {
        $index = new AutoloaderIndex_IniFile();
        $this->_initIndex($index);
        return $index;
    }

    /**
     * Returns an instance of AutoloaderIndex_CSV which is tested in theses tests
     *
     * @return AutoloaderIndex_CSV
     */
    private function _createAutoloaderIndexCSV()
    {
        $index = new AutoloaderIndex_CSV();
        $this->_initIndex($index);
        return $index;
    }

    /**
     * Returns an instance of AutoloaderIndex_PHPArrayCode which is tested in theses
     * tests
     *
     * @return AutoloaderIndex_PHPArrayCode
     */
    private function _createAutoloaderIndexPHPArrayCode()
    {
        $index = new AutoloaderIndex_PHPArrayCode();
        $this->_initIndex($index);
        return $index;
    }

    /**
     * Returns an instance of AutoloaderIndex_SerializedHashtable which is tested in
     * theses tests
     *
     * @return AutoloaderIndex_SerializedHashtable
     */
    private function _createAutoloaderIndexSerializedHashtable()
    {
        $index = new AutoloaderIndex_SerializedHashtable();
        $this->_initIndex($index);
        return $index;
    }

    /**
     * Returns an instance of AutoloaderIndex_Dummy which is tested in theses tests
     *
     * @return AutoloaderIndex_Dummy
     */
    private function _createAutoloaderIndexDummy()
    {
        $index = new AutoloaderIndex_Dummy();
        $this->_initIndex($index);
        return $index;
    }

    /**
     * Returns an index of the same class as $index
     *
     * The state of the returned index is loaded from its persistance layer.
     * This applies only to instances of AutoloaderIndex_File.
     *
     * @param AutoloaderIndex $index The index which should load its state
     *
     * @return AutoloaderIndex_File
     */
    private function _getIndexFromPersistence(AutoloaderIndex $index)
    {
        if ($index instanceof AutoloaderIndex_File) {
            $indexClass = get_class($index);
            $indexPath  = $index->getIndexPath();

            // Index should save its state now
            $index->__destruct();
            unset($index);

            $index = new $indexClass();
            $this->_initIndex($index);
            $index->setIndexPath($indexPath);

        }

        return $index;
    }

    /**
     * Initializes an index with an Autoloader and for
     * AutoloaderIndex_File instances with an index path
     *
     * @param AutoloaderIndex $index The index which should be initialized
     *
     * @return void
     */
    private function _initIndex(AutoloaderIndex $index)
    {
        $index->setAutoloader(new Autoloader());
        if ($index instanceof AutoloaderIndex_File) {
            $index->setIndexPath($this->_getIndexFile());

        }
    }

    /**
     * Returns a list of different AutoloaderIndex objects which are testet in all
     * tests
     *
     * @return Array
     */
    private function _getIndexes()
    {
        $indeces =  array(
            $this->_createAutoloaderIndexDummy(),
            $this->_createAutoloaderIndexPHPArrayCode(),
            $this->_createAutoloaderIndexCSV(),
            $this->_createAutoloaderIndexIniFile(),
            $this->_createAutoloaderIndexSerializedHashtable(),
            $this->_createAutoloaderIndexSerializedHashtableGZ(),
            $this->_createAutoloaderIndexPdoSqLite(
                tempnam(sys_get_temp_dir(), "PDOTest")
            )
        );

        try {
            $indeces[] = $this->_createAutoloaderIndexPdoMySQL();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());

        }

        return $indeces;
    }

    /**
     * Returns the same list as _getIndexes() without AutoloaderIndex_Dummy
     * instances
     *
     * @see _getIndexes()
     * @return Array
     */
    private function _getPersistentIndexes()
    {
        $indexes = array();
        foreach ($this->_getIndexes() as $index) {
            if ($index instanceof AutoloaderIndex_Dummy) {
                continue;

            }
            $indexes[] = $index;

        }
        return $indexes;
    }

    /**
     * Returns a generated path for a new index file
     *
     * AutoloaderIndex_File instances will use such a generated path.
     *
     * @return String
     */
    private function _getIndexFile()
    {
        return self::getIndexDirectory() . DIRECTORY_SEPARATOR . uniqid();
    }

    /**
     * Returns the path where index files are stored for these tests
     *
     * @return String
     */
    static public function getIndexDirectory()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . self::INDEX_DIRECTORY;
    }

}