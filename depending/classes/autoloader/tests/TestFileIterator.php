<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the test cases for implementations of AutoloaderFileIterator
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
 * @version    SVN: $Id: TestFileIterator.php,v 1.3 2011/01/11 14:25:31 nicke Exp $
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * The Autoloader is used for class loading.
 */
require_once dirname(__FILE__) . "/../Autoloader.php";

/**
 * AutoloaderFileIterator test cases
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Test
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.11
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AutoloaderFileIterator
 * @see        AutoloaderFileIterator_PriorityList
 * @see        AutoloaderFileIterator_Simple
 * @see        AutoloaderFileIterator_SimpleCached
 */
class TestFileIterator extends PHPUnit_Framework_TestCase
{

    /**
     * Asserts that an AutoloaderFileIterator finds all files in a path
     *
     * @param AutoloaderFileIterator $iterator      The AutoloaderFileIterator
     * @param String                 $path          A path for searching
     * @param Array                  $expectedFiles The expected files
     *
     * @dataProvider provideTestCompleteIteration
     * @return void
     */
    public function testCompleteIteration(
        AutoloaderFileIterator $iterator, $path, Array $expectedFiles
    ) {
        $autoloader = new Autoloader($path);
        $iterator->setAutoloader($autoloader);

        $expectedFiles = array_flip($expectedFiles);
        foreach ($iterator as $file) {
            $file = realpath($file);
            $this->assertArrayHasKey($file, $expectedFiles);
            unset($expectedFiles[$file]);

        }
        $this->assertEquals(0, count($expectedFiles));
    }

    /**
     * Provides test cases for testCompleteIteration()
     *
     * A test case is an AutoloaderFileIterator, a path and a list of files which
     * are expected to be found in that path.
     *
     * @return Array
     * @see testCompleteIteration()
     */
    public function provideTestCompleteIteration()
    {
        $alTestHelper   = new AutoloaderTestHelper($this);
        $cases          = array();
        $rootDir        = $alTestHelper->getClassDirectory("testCompleteIteration");
        $files          = array();

        AutoloaderTestHelper::deleteDirectory("testCompleteIteration");

        $files[] = $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("A", "testCompleteIteration")
        );
        $files[] = $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("B", "testCompleteIteration")
        );
        $files[] = $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("C", "testCompleteIteration/C")
        );
        $files[] = $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("D", "testCompleteIteration/C")
        );
        $files[] = $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("E", "testCompleteIteration/E")
        );
        $files[] = $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("F", "testCompleteIteration/E/F")
        );
        $files[] = $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("G", "testCompleteIteration/C")
        );

        // ignored Files
        $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("I1", "testCompleteIteration/.CVS/")
        );
        $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("I2", "testCompleteIteration/.CVS/")
        );
        $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("I3", "testCompleteIteration/.CVS/test")
        );
        $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("I4", "testCompleteIteration/.svn")
        );
        $classDirectory
            = AutoloaderTestHelper::getClassDirectory("testCompleteIteration");
        touch("$classDirectory/test.dist");
        touch("$classDirectory/test.DIST");
        touch("$classDirectory/test.jpeg");
        touch("$classDirectory/test.jpg");
        touch("$classDirectory/test.gif");
        touch("$classDirectory/test.png");
        touch("$classDirectory/test.svg");
        touch("$classDirectory/test.ogm");
        touch("$classDirectory/test.ogg");
        touch("$classDirectory/test.mp3");
        touch("$classDirectory/test.wav");
        touch("$classDirectory/test.mpeg");
        touch("$classDirectory/test.mpg");

        $files[] = $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("H", "testCompleteIteration")
        );
        $files[] = $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("I", "testCompleteIteration")
        );
        $files[] = $alTestHelper->getGeneratedClassPath(
            $alTestHelper->makeClass("J", "testCompleteIteration")
        );

        foreach ($files as & $file) {
            $file = realpath($file);

        }

        $cases[] = array(
            new AutoloaderFileIterator_Simple(),       $rootDir, $files
        );
        $cases[] = array(
            new AutoloaderFileIterator_SimpleCached(), $rootDir, $files
        );
        $cases[] = array(
            new AutoloaderFileIterator_PriorityList(), $rootDir, $files
        );

        return $cases;
    }

    /**
     * Asserts that files, which match a given pattern are not found by an
     * AutoloaderFileIterator object
     *
     * @param AutoloaderFileIterator $iterator         The AutoloaderFileIterator
     * @param Array                  $notExpectedFiles Files which are not expected
     * @param String                 $path             The searched class path
     *
     * @dataProvider provideTestSkipPatterns
     * @return void
     */
    public function testSkipPatterns(
        AutoloaderFileIterator $iterator, Array $notExpectedFiles, $path
    ) {
        $autoloader = new Autoloader($path);
        $iterator->setAutoloader($autoloader);

        foreach ($notExpectedFiles as & $file) {
            $file = realpath($file);

        }
        $notExpectedFiles = array_flip($notExpectedFiles);
        foreach ($iterator as $file) {
            $this->assertFalse(
                array_key_exists(realpath($file), $notExpectedFiles),
                "should not find '$file'"
            );

        }
    }

    /**
     * Provides test cases for testSkipPatterns()
     *
     * A test case is an instance of AutoloaderFileIterator, a list of files and
     * a search path. The AutoloaderFileIterator object is configured with a skip
     * pattern. The search path should contain files which would match these
     * patterns. The list of files are these files which would match the skip
     * pattern and are not expected to be found by the AutoloaderFileIterator.
     *
     * @see testSkipPatterns()
     * @return Array
     */
    public function provideTestSkipPatterns()
    {
        AutoloaderTestHelper::deleteDirectory("testSkipPatterns");

        $alTestHelper       = new AutoloaderTestHelper($this);
        $cases              = array();
        $onlyIgnoredfiles   = array(
            $alTestHelper->getGeneratedClassPath(
                $alTestHelper->makeClass("A", "testSkipPatterns/onlyIgnored/.CVS")
            ),
            $alTestHelper->getGeneratedClassPath(
                $alTestHelper->makeClass("B", "testSkipPatterns/onlyIgnored/.svn")
            ),
            $alTestHelper->getGeneratedClassPath(
                $alTestHelper->makeClass("C", "testSkipPatterns/onlyIgnored/.svn/C")
            ),
            $alTestHelper->getGeneratedClassPath(
                $alTestHelper->makeClass(
                    "D", "testSkipPatterns/onlyIgnored/myPattern1"
                )
            ),
            $alTestHelper->getGeneratedClassPath(
                $alTestHelper->makeClass(
                    "myPattern2", "testSkipPatterns/onlyIgnored/"
                )
            ),
        );
        $mixedfiles = array(
            $alTestHelper->getGeneratedClassPath(
                $alTestHelper->makeClass("A", "testSkipPatterns/mixed/.CVS")
            ),
            $alTestHelper->getGeneratedClassPath(
                $alTestHelper->makeClass("B", "testSkipPatterns/mixed/.svn")
            ),
            $alTestHelper->getGeneratedClassPath(
                $alTestHelper->makeClass("C", "testSkipPatterns/mixed/.svn/C")
            ),
            $alTestHelper->getGeneratedClassPath(
                $alTestHelper->makeClass("D", "testSkipPatterns/mixed/myPattern1")
            ),
            $alTestHelper->getGeneratedClassPath(
                $alTestHelper->makeClass("myPattern2", "testSkipPatterns/mixed/")
            )
        );
        $alTestHelper->makeClass("E", "testSkipPatterns/mixed/");
        $alTestHelper->makeClass("F", "testSkipPatterns/mixed/F");

        $simpleIterator = new AutoloaderFileIterator_Simple();
        $simpleIterator->addSkipPattern('~myPattern1~');
        $simpleIterator->addSkipPattern('~myPattern2~');

        $simpleCachedIterator = new AutoloaderFileIterator_SimpleCached();
        $simpleCachedIterator->addSkipPattern('~myPattern1~');
        $simpleCachedIterator->addSkipPattern('~myPattern2~');

        $priorityIterator = new AutoloaderFileIterator_PriorityList();
        $priorityIterator->addSkipPattern('~myPattern1~');
        $priorityIterator->addSkipPattern('~myPattern2~');


        $cases[] = array(
            $simpleIterator,
            $onlyIgnoredfiles,
            AutoloaderTestHelper::getClassDirectory("testSkipPatterns/onlyIgnored")
        );
        $cases[] = array(
            $simpleIterator,
            $mixedfiles,
            AutoloaderTestHelper::getClassDirectory("testSkipPatterns/mixed")
        );
        $cases[] = array(
            $simpleCachedIterator,
            $onlyIgnoredfiles,
            AutoloaderTestHelper::getClassDirectory("testSkipPatterns/onlyIgnored")
        );
        $cases[] = array(
            $simpleCachedIterator,
            $mixedfiles,
            AutoloaderTestHelper::getClassDirectory("testSkipPatterns/mixed")
        );
        $cases[] = array(
            $priorityIterator,
            $onlyIgnoredfiles,
            AutoloaderTestHelper::getClassDirectory("testSkipPatterns/onlyIgnored")
        );
        $cases[] = array(
            $priorityIterator,
            $mixedfiles,
            AutoloaderTestHelper::getClassDirectory("testSkipPatterns/mixed")
        );

        return $cases;
    }

    /**
     * Asserts that an AutoloaderFileIterator might be empty
     *
     * @param AutoloaderFileIterator $iterator The tested AutoloaderFileIterator
     * @param String                 $path     A path where no files are found
     *
     * @dataProvider provideTestEmptyIterator
     * @return void
     */
    public function testEmptyIterator(AutoloaderFileIterator $iterator, $path)
    {
        $autoloader = new Autoloader($path);
        $iterator->setAutoloader($autoloader);

        foreach ($iterator as $file) {
            $this->fail("An empty iterator  was expected. But '$file' was found.");

        }
    }

    /**
     * Provides test cases for testEmptyIterator()
     *
     * A test case consists of an instance of AutoloaderFileIterator and a path.
     * The iterator should not find any file in that path.
     *
     * @see testEmptyIterator()
     * @return Array
     */
    public function provideTestEmptyIterator()
    {
        AutoloaderTestHelper::deleteDirectory("testEmptyIterator");

        $alTestHelper       = new AutoloaderTestHelper($this);
        $cases              = array();

        $alTestHelper->makeClass("A", "testEmptyIterator/onlyIgnored/.CVS");
        $alTestHelper->makeClass("B", "testEmptyIterator/onlyIgnored/.svn");
        $alTestHelper->makeClass("C", "testEmptyIterator/onlyIgnored/.svn/C");
        $alTestHelper->makeClass("D", "testEmptyIterator/onlyIgnored/myPattern1");
        $alTestHelper->makeClass("myPattern2", "testEmptyIterator/onlyIgnored/");
        mkdir(
            AutoloaderTestHelper::getClassDirectory(
                "testEmptyIterator/onlyIgnored/emptyDir"
            )
        );

        mkdir(AutoloaderTestHelper::getClassDirectory("testEmptyIterator/empty"));

        $simpleIterator = new AutoloaderFileIterator_Simple();
        $simpleIterator->addSkipPattern('~myPattern1~');
        $simpleIterator->addSkipPattern('~myPattern2~');

        $simpleCachedIterator = new AutoloaderFileIterator_SimpleCached();
        $simpleCachedIterator->addSkipPattern('~myPattern1~');
        $simpleCachedIterator->addSkipPattern('~myPattern2~');

        $priorityIterator = new AutoloaderFileIterator_PriorityList();
        $priorityIterator->addSkipPattern('~myPattern1~');
        $priorityIterator->addSkipPattern('~myPattern2~');

        $cases[] = array(
            $simpleIterator,
            AutoloaderTestHelper::getClassDirectory("testEmptyIterator/empty")
        );
        $cases[] = array(
            $simpleIterator,
            AutoloaderTestHelper::getClassDirectory("testEmptyIterator/onlyIgnored")
        );
        $cases[] = array(
            $simpleCachedIterator,
            AutoloaderTestHelper::getClassDirectory("testEmptyIterator/empty")
        );
        $cases[] = array(
            $simpleCachedIterator,
            AutoloaderTestHelper::getClassDirectory("testEmptyIterator/onlyIgnored")
        );
        $cases[] = array(
            $priorityIterator,
            AutoloaderTestHelper::getClassDirectory("testEmptyIterator/empty")
        );
        $cases[] = array(
            $priorityIterator,
            AutoloaderTestHelper::getClassDirectory("testEmptyIterator/onlyIgnored")
        );

        return $cases;
    }

    /**
     * Asserts that AutoloaderFileIterator_PriorityList returns an ordered list of
     * files
     *
     * The list should be ordered by the distance between classname and filename.
     *
     * @see AutoloaderFileIterator_PriorityList
     * @return void
     */
    public function testPreferedPattern()
    {
        AutoloaderTestHelper::deleteDirectory("testPreferedPattern");
        $alTestHelper = new AutoloaderTestHelper($this);
        $classPath = AutoloaderTestHelper::getClassDirectory("testPreferedPattern");

        $alTestHelper->makeClass("A", "testPreferedPattern");
        touch("$classPath/B.inc");
        touch("$classPath/C.unimportant");
        $alTestHelper->makeClass("D", "testPreferedPattern");
        touch("$classPath/E.inc");
        touch("$classPath/F.unimportant");
        $alTestHelper->makeClass("G", "testPreferedPattern/sub");
        touch("$classPath/sub/H.inc");
        touch("$classPath/sub/I.unimportant");
        $alTestHelper->makeClass("J", "testPreferedPattern/sub");
        touch("$classPath/sub/K.inc");
        touch("$classPath/sub/L.unimportant");

        $iterator   = new AutoloaderFileIterator_PriorityList();
        $autoloader = new Autoloader($classPath);
        $iterator->setAutoloader($autoloader);

        $isUnimportantExpected = false;
        foreach ($iterator as $file) {
            if (! preg_match('~\.(inc|php)$~', $file)) {
                $isUnimportantExpected = true;

            } elseif ($isUnimportantExpected) {
                $this->fail("Did not expect the prefered file '$file'.");

            }
        }
    }

    /**
     * Asserts that an instance of AutoloaderFileIterator can be reused and finds
     * every time the same results
     *
     * @param AutoloaderFileIterator $iterator The tested AutoloaderFileIterator
     *
     * @dataProvider provideTestRepeatedIteratorUse
     * @return void
     */
    public function testRepeatedIteratorUse(AutoloaderFileIterator $iterator)
    {

        $foundFiles = array();
        foreach ($iterator as $file) {
            $foundFiles[] = $file;

        }

        $this->_assertEqualFoundFiles($foundFiles, $iterator);
        $this->_assertEqualFoundFiles($foundFiles, $iterator);
        $this->_assertEqualFoundFiles($foundFiles, $iterator);
    }

    /**
     * Asserts that an AutoloaderFileIterator finds the expected list of files
     *
     * @param array                  $expectedFiles A list of expected files
     * @param AutoloaderFileIterator $iterator      A tested AutoloaderFileIterator
     *
     * @see testRepeatedIteratorUse()
     * @return void
     */
    private function _assertEqualFoundFiles(
        Array $expectedFiles, AutoloaderFileIterator $iterator
    ) {
        foreach ($iterator as $file) {
            $this->assertEquals(array_shift($expectedFiles), $file);

        }
    }

    /**
     * Provides test cases for testRepeatedIteratorUse()
     *
     * A test case consists of an AutoloaderFileIterator object which is configured
     * to search in a not empty class path.
     *
     * @see testRepeatedIteratorUse()
     * @return Array
     */
    public function provideTestRepeatedIteratorUse()
    {
        AutoloaderTestHelper::deleteDirectory("testRepeatedIteratorUse");
        $alTestHelper = new AutoloaderTestHelper($this);


        $alTestHelper->makeClass("A", "testRepeatedIteratorUse");
        $alTestHelper->makeClass("B", "testRepeatedIteratorUse");
        $alTestHelper->makeClass("C", "testRepeatedIteratorUse/C");
        $alTestHelper->makeClass("D", "testRepeatedIteratorUse/D");
        $alTestHelper->makeClass("E", "testRepeatedIteratorUse/D");
        $alTestHelper->makeClass("F", "testRepeatedIteratorUse/D");
        $alTestHelper->makeClass("G", "testRepeatedIteratorUse/D/G");
        $alTestHelper->makeClass("H", "testRepeatedIteratorUse");

        $autoloader = new Autoloader(
            AutoloaderTestHelper::getClassDirectory("testRepeatedIteratorUse")
        );


        $simpleIterator = new AutoloaderFileIterator_Simple();
        $simpleIterator->setAutoloader($autoloader);

        $simpleCachedIterator = new AutoloaderFileIterator_SimpleCached();
        $simpleCachedIterator->setAutoloader($autoloader);

        $priorityIterator = new AutoloaderFileIterator_PriorityList();
        $priorityIterator->setAutoloader($autoloader);

        return array(
            array($simpleIterator, 1),
            array($simpleIterator, 3),
            array($simpleIterator, 0),
            array($simpleIterator),
            array($simpleIterator, 1),
            array($simpleIterator, 3),
            array($simpleIterator, 0),
            array($simpleCachedIterator, 1),
            array($simpleCachedIterator, 3),
            array($simpleCachedIterator, 0),
            array($simpleCachedIterator),
            array($simpleCachedIterator, 1),
            array($simpleCachedIterator, 3),
            array($simpleCachedIterator, 0),
            array($priorityIterator, 1),
            array($priorityIterator, 3),
            array($priorityIterator, 0),
            array($priorityIterator),
            array($priorityIterator, 1),
            array($priorityIterator, 3),
            array($priorityIterator, 0)
        );
    }

    /**
     * Asserts that the class name is contained in the first results of an
     * AutoloaderFileIterator_PriorityList instance
     *
     * @param String $path  The class path
     * @param String $class The class name which should be found first
     * @param int    $limit Up to $limit first results should contain the class name
     *
     * @dataProvider provideTestPriorityOrder
     * @see AutoloaderFileIterator_PriorityList()
     * @return void
     */
    public function testPriorityOrder($path, $class, $limit)
    {
        $autoloader = new Autoloader(
            AutoloaderTestHelper::getClassDirectory($path)
        );
        $priorityIterator = new AutoloaderFileIterator_PriorityList();
        $priorityIterator->setAutoloader($autoloader);
        $priorityIterator->setClassname($class);

        $i = 0;
        foreach ($priorityIterator as $file) {
            $this->assertTrue(
                (bool) preg_match("~$class~", basename($file))
            );
            $i++;
            if ($i >= $limit) {
                break;

            }
        }
    }

    /**
     * Provides test cases for testPriorityOrder()
     *
     * A test case consists of a class path, a class name and a limit.
     * The class path should contain files which have the class name in their
     * filename. The limit specifies how many classes are expected to be the
     * first results.
     *
     * @see testPriorityOrder()
     * @return Array
     */
    public function provideTestPriorityOrder()
    {
        AutoloaderTestHelper::deleteDirectory("testPriorityOrder");
        $alTestHelper = new AutoloaderTestHelper($this);

        $alTestHelper->makeClass("anyClass", "testPriorityOrder");
        $alTestHelper->makeClass("anyClass", "testPriorityOrder");
        $alTestHelper->makeClass("anyClass", "testPriorityOrder/C");
        $alTestHelper->makeClass("anyClass", "testPriorityOrder/D");
        $alTestHelper->makeClass("anyClass", "testPriorityOrder/D");
        $alTestHelper->makeClass("anyClass", "testPriorityOrder/D");
        $alTestHelper->makeClass("priorityClass", "testPriorityOrder/D/G");
        $alTestHelper->makeClass("priorityClass", "testPriorityOrder");
        $alTestHelper->makeClass("otherClass", "testPriorityOrder/D/G");
        $alTestHelper->makeClass("otherClass", "testPriorityOrder");

        return array(
            array("testPriorityOrder", "priorityClass", 2),
            array("testPriorityOrder", "otherClass",    2)
        );
    }

    /**
     * Asserts that the AutoloaderFileIterator is still working in a huge
     * environment
     *
     * @param AutoloaderFileIterator $iterator The tested AutoloaderFileIterator
     * @param String                 $path     The searched class path
     *
     * @dataProvider provideTestLoadsOfFiles
     * @return void
     */
    public function testLoadsOfFiles(AutoloaderFileIterator $iterator, $path)
    {
        $iterator->setAutoloader(
            new Autoloader(AutoloaderTestHelper::getClassDirectory($path))
        );

        foreach ($iterator as $file) {
            // Do nothing but iterate through the class path.
        }
    }

    /**
     * Provides testLoadsOfFiles() with test cases
     *
     * A test case is an instance of AutoloaderFileIterator and a class path.
     * The class path should contain a large amount of files.
     *
     * @see testLoadsOfFiles()
     * @return Array
     */
    public function provideTestLoadsOfFiles()
    {
        AutoloaderTestHelper::deleteDirectory("testLoadsOfFiles");
        $alTestHelper = new AutoloaderTestHelper($this);

        for ($i = 0; $i < 150; $i++) {
            $alTestHelper->makeClass("anyClass", "testLoadsOfFiles/flat");

        }

        for ($i = 0; $i < 150; $i++) {
            $alTestHelper->makeClass(
                "anyClass", "testLoadsOfFiles" . str_repeat('/sub', $i)
            );

        }

        return array(
            array(
                new AutoloaderFileIterator_PriorityList(),
                "testLoadsOfFiles/flat"
            ),
            array(
                new AutoloaderFileIterator_Simple(),
                "testLoadsOfFiles/flat"
            ),
            array(
                new AutoloaderFileIterator_SimpleCached(),
                "testLoadsOfFiles/flat"
            ),
            array(
                new AutoloaderFileIterator_PriorityList(),
                "testLoadsOfFiles/sub"
            ),
            array(
                new AutoloaderFileIterator_Simple(),
                "testLoadsOfFiles/sub"
            ),
            array(
                new AutoloaderFileIterator_SimpleCached(),
                "testLoadsOfFiles/sub"
            )
        );
    }

}